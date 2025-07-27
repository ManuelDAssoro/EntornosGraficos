<?php

function puedeAccederPromocion($categoriaCliente, $categoriaPromocion) {
    if (empty($categoriaPromocion) || $categoriaPromocion === 'inicial') {
        return true;
    }
    
    $jerarquia = ['inicial' => 1, 'medium' => 2, 'premium' => 3];
    
    $nivelCliente = $jerarquia[strtolower($categoriaCliente)] ?? 1;
    $nivelPromocion = $jerarquia[strtolower($categoriaPromocion)] ?? 1;
    
    return $nivelCliente >= $nivelPromocion;
}

function usarPromocion($codusuario, $codpromo, $pdo) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            SELECT p.*, l.nombrelocal 
            FROM promociones p
            JOIN locales l ON p.codlocal = l.codlocal
            WHERE p.codpromo = ? 
            AND p.estadopromo = 'activa'
            AND (p.fechadesdepromo <= CURRENT_DATE AND p.fechahastapromo >= CURRENT_DATE)
        ");
        $stmt->execute([$codpromo]);
        $promocion = $stmt->fetch();
        
        if (!$promocion) {
            throw new Exception("La promoción no es válida o ha expirado.");
        }
        $stmt = $pdo->prepare("SELECT categoriacliente FROM usuarios WHERE codusuario = ?");
        $stmt->execute([$codusuario]);
        $user = $stmt->fetch();
        $categoriaCliente = $user['categoriacliente'] ?? 'inicial';
        
        if (!puedeAccederPromocion($categoriaCliente, $promocion['categoriacliente'])) {
            throw new Exception("No tienes acceso a esta promoción. Necesitas categoría " . ucfirst($promocion['categoriacliente']) . " o superior.");
        }
        
        $stmt = $pdo->prepare("SELECT codusuario FROM uso_promociones WHERE codusuario = ? AND codpromo = ?");
        $stmt->execute([$codusuario, $codpromo]);
        if ($stmt->fetch()) {
            throw new Exception("Ya has utilizado esta promoción anteriormente.");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO uso_promociones (codusuario, codpromo, fechauso, estado) 
            VALUES (?, ?, CURRENT_DATE, 'aceptada')
        ");
        $stmt->execute([$codusuario, $codpromo]);
        
        $nuevaCategoria = actualizarCategoriaCliente($codusuario, $pdo);
        
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

function actualizarCategoriaCliente($codusuario, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as promociones_usadas 
        FROM uso_promociones 
        WHERE codusuario = ? AND estado = 'aceptada'
    ");
    $stmt->execute([$codusuario]);
    $result = $stmt->fetch();
    $promocionesUsadas = $result['promociones_usadas'];
    
    $categoria = 'inicial';
    if ($promocionesUsadas >= 10) {
        $categoria = 'premium';
    } elseif ($promocionesUsadas >= 3) {
        $categoria = 'medium';
    }
    
    $stmt = $pdo->prepare("UPDATE usuarios SET categoriacliente = ? WHERE codusuario = ?");
    $stmt->execute([$categoria, $codusuario]);
    
    return $categoria;
}

function getCategoryProgress($codusuario, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as promociones_usadas 
        FROM uso_promociones 
        WHERE codusuario = ? AND estado = 'aceptada'
    ");
    $stmt->execute([$codusuario]);
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

function getPromocionesDisponibles($codusuario, $pdo) {
    $stmt = $pdo->prepare("SELECT categoriacliente FROM usuarios WHERE codusuario = ?");
    $stmt->execute([$codusuario]);
    $user = $stmt->fetch();
    $categoriaCliente = $user['categoriacliente'] ?? 'inicial';
    
    $categoriaFilter = getCategoriaFilterSQL($categoriaCliente, 'p');
    
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombrelocal, l.ubicacionlocal, l.rubrolocal
        FROM promociones p
        JOIN locales l ON p.codlocal = l.codlocal
        WHERE p.estadopromo = 'activa'
        AND p.fechadesdepromo <= CURRENT_DATE
        AND p.fechahastapromo >= CURRENT_DATE
        AND $categoriaFilter
        AND p.codpromo NOT IN (
            SELECT codpromo FROM uso_promociones WHERE codusuario = ? AND estado = 'aceptada'
        )
        ORDER BY p.fechahastapromo ASC
    ");
    $stmt->execute([$codusuario]);
    return $stmt->fetchAll();
}

function getCategoryBadge($categoria) {
    switch (strtolower($categoria ?? '')) {
        case 'premium':
            return '<span class="badge bg-warning text-dark">Premium</span>';
        case 'medium':
            return '<span class="badge bg-info">Medium</span>';
        case 'inicial':
        default:
            return '<span class="badge bg-success">Inicial</span>';
    }
}

function getCategoriaFilterSQL($categoriaCliente, $tableAlias = 'p', $includeAll = false) {
    if ($includeAll) {
        return "1=1";
    }
    
    $column = ($tableAlias ? $tableAlias . '.' : '') . 'categoriacliente';
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

function canAccessNews($userCategory, $minCategory) {
    if ($minCategory === 'unlogged') return true;
    
    $hierarchy = ['inicial' => 1, 'medium' => 2, 'premium' => 3];
    $userLevel = $hierarchy[strtolower($userCategory)] ?? 1;
    $requiredLevel = $hierarchy[strtolower($minCategory)] ?? 1;
    
    return $userLevel >= $requiredLevel;
}

function expandirDiasAbreviados($diasAbrev) {
    if (empty($diasAbrev)) return '';
    
    $mapeo = [
        'L' => 'Lunes',
        'M' => 'Martes', 
        'X' => 'Miércoles',
        'J' => 'Jueves',
        'V' => 'Viernes',
        'S' => 'Sábado',
        'D' => 'Domingo'
    ];
    
    $dias = explode(',', $diasAbrev);
    $diasCompletos = array_map(function($dia) use ($mapeo) {
        return $mapeo[trim($dia)] ?? $dia;
    }, $dias);
    
    return implode(', ', $diasCompletos);
}

?>
