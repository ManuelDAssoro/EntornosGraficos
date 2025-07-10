<?php

function actualizarCategoriaCliente($codUsuario, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as promociones_usadas 
        FROM uso_promociones 
        WHERE codUsuario = ? AND estado = 'usado'
    ");
    $stmt->execute([$codUsuario]);
    $result = $stmt->fetch();
    $promocionesUsadas = $result['promociones_usadas'];
    
    $categoria = 'inicial';
    if ($promocionesUsadas >= 10) {
        $categoria = 'premium';
    } elseif ($promocionesUsadas >= 3) {
        $categoria = 'medium';
    }
    
    $stmt = $pdo->prepare("UPDATE usuarios SET categoriaCliente = ? WHERE codUsuario = ?");
    $stmt->execute([$categoria, $codUsuario]);
    
    return $categoria;
}

function puedeAccederPromocion($categoriaCliente, $categoriaPromocion) {
    $jerarquia = ['inicial' => 1, 'medium' => 2, 'premium' => 3];
    
    $nivelCliente = $jerarquia[$categoriaCliente] ?? 1;
    $nivelPromocion = $jerarquia[$categoriaPromocion] ?? 1;
    
    return $nivelCliente >= $nivelPromocion;
}

function getPromocionesDisponibles($codUsuario, $pdo) {
    $stmt = $pdo->prepare("SELECT categoriaCliente FROM usuarios WHERE codUsuario = ?");
    $stmt->execute([$codUsuario]);
    $user = $stmt->fetch();
    $categoriaCliente = $user['categoriaCliente'] ?? 'inicial';
    
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombreLocal, l.ubicacionLocal, l.rubroLocal
        FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.estadoPromo = 'activa'
        AND (p.fechaDesdePromo <= CURRENT_DATE AND p.fechaHastaPromo >= CURRENT_DATE)
        AND (
            (p.categoriaCliente = 'inicial') OR
            (p.categoriaCliente = 'medium' AND ? IN ('medium', 'premium')) OR
            (p.categoriaCliente = 'premium' AND ? = 'premium')
        )
        AND (
            p.diasSemana = '' OR 
            p.diasSemana IS NULL OR
            POSITION(TO_CHAR(CURRENT_DATE, 'Day') IN p.diasSemana) > 0
        )
        AND p.codPromo NOT IN (
            SELECT codPromo FROM uso_promociones WHERE codUsuario = ?
        )
        ORDER BY l.nombreLocal, p.fechaHastaPromo
    ");
    $stmt->execute([$categoriaCliente, $categoriaCliente, $codUsuario]);
    return $stmt->fetchAll();
}

function usarPromocion($codUsuario, $codPromo, $pdo) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            SELECT p.*, l.nombreLocal 
            FROM promociones p
            JOIN locales l ON p.codLocal = l.codLocal
            WHERE p.codPromo = ? 
            AND p.estadoPromo = 'activa'
            AND (p.fechaDesdePromo <= CURRENT_DATE AND p.fechaHastaPromo >= CURRENT_DATE)
        ");
        $stmt->execute([$codPromo]);
        $promocion = $stmt->fetch();
        
        if (!$promocion) {
            throw new Exception("La promoción no es válida o ha expirado.");
        }
        
        $stmt = $pdo->prepare("SELECT categoriaCliente FROM usuarios WHERE codUsuario = ?");
        $stmt->execute([$codUsuario]);
        $user = $stmt->fetch();
        $categoriaCliente = $user['categoriaCliente'] ?? 'inicial';
        
        if (!puedeAccederPromocion($categoriaCliente, $promocion['categoriacliente'])) {
            throw new Exception("No tienes acceso a esta promoción. Necesitas categoría " . ucfirst($promocion['categoriaCliente']) . " o superior.");
        }
        
        $stmt = $pdo->prepare("SELECT codUso FROM uso_promociones WHERE codUsuario = ? AND codPromo = ?");
        $stmt->execute([$codUsuario, $codPromo]);
        if ($stmt->fetch()) {
            throw new Exception("Ya has utilizado esta promoción anteriormente.");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO uso_promociones (codUsuario, codPromo, fechaUso, estado) 
            VALUES (?, ?, NOW(), 'usado')
        ");
        $stmt->execute([$codUsuario, $codPromo]);
        
        $nuevaCategoria = actualizarCategoriaCliente($codUsuario, $pdo);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'promocion' => $promocion,
            'nueva_categoria' => $nuevaCategoria,
            'categoria_anterior' => $categoriaCliente
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function getCategoryBadge($categoria) {
    $badges = [
        'inicial' => '<span class="badge bg-secondary"><i class="bi bi-star"></i> Inicial</span>',
        'medium' => '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Medium</span>',
        'premium' => '<span class="badge bg-success"><i class="bi bi-crown"></i> Premium</span>'
    ];
    
    return $badges[$categoria] ?? $badges['inicial'];
}

function getCategoryProgress($codUsuario, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as promociones_usadas 
        FROM uso_promociones 
        WHERE codUsuario = ? AND estado = 'usado'
    ");
    $stmt->execute([$codUsuario]);
    $result = $stmt->fetch();
    $used = $result['promociones_usadas'];
    
    $progress = [
        'used' => $used,
        'next_level' => null,
        'progress_percent' => 0,
        'next_level_name' => null
    ];
    
    if ($used < 3) {
        $progress['next_level'] = 3;
        $progress['next_level_name'] = 'Medium';
        $progress['progress_percent'] = ($used / 3) * 100;
    } elseif ($used < 10) {
        $progress['next_level'] = 10;
        $progress['next_level_name'] = 'Premium';
        $progress['progress_percent'] = (($used - 3) / 7) * 100;
    } else {
        $progress['progress_percent'] = 100;
    }
    
    return $progress;
}

function getCategoriaFilterSQL($categoriaCliente, $tableAlias = 'p', $includeAll = false) {
    if ($includeAll) {
        return "1=1";
    }
    
    $column = ($tableAlias ? $tableAlias . '.' : '') . 'categoriaCliente';
    $columnLower = "LOWER($column)";
    switch (strtolower($categoriaCliente)) {
        case 'premium':
            return "($column IS NULL OR $column = '' OR $columnLower IN ('inicial', 'medium', 'premium'))";
        case 'medium':
            return "($column IS NULL OR $column = '' OR $columnLower IN ('inicial', 'medium'))";
        case 'inicial':
        default:
            return "($column IS NULL OR $column = '' OR $columnLower = 'inicial')";
    }
}

?>
