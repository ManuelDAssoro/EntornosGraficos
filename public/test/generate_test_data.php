<?php
require_once '../../config/db.php';  // Ajusta esta ruta si cambia

echo "<h1>🎯 Generador de Datos de Prueba Completo (PostgreSQL)</h1>";

try {
    // Estadísticas actuales
    echo "<h2>📊 Estado Actual</h2>";
    $stmt = $pdo->prepare("SELECT tipoUsuario, estado, COUNT(*) as count FROM usuarios GROUP BY tipoUsuario, estado ORDER BY tipoUsuario, estado");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    if ($rows) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'><tr><th>Tipo Usuario</th><th>Estado</th><th>Cantidad</th></tr>";
        foreach ($rows as $r) echo "<tr><td>{$r['tipoUsuario']}</td><td>{$r['estado']}</td><td>{$r['count']}</td></tr>";
        echo "</table>";
    } else {
        echo "<p>No hay usuarios.</p>";
    }

    echo "<h2>🚀 Generando Usuarios...</h2>";
    $combs = [
        ['admin1@shopping.com','administrador','activo','Administrador Principal',null],
        ['cliente1@shopping.com','cliente','activo','Cliente Básico','inicial'],
        ['cliente2@shopping.com','cliente','activo','Cliente Premium','premium'],
        // ...
    ];
    $users_created = $users_existed = 0;
    foreach ($combs as $u) {
        list($email,$tipo,$estado,$desc,$cat) = $u;
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "<p style='color:orange;'>⚠️ Ya existe $email</p>";
            $users_existed++;
        } else {
            if ($tipo === 'cliente') {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estado, categoriaCliente) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, password_hash('demo123', PASSWORD_DEFAULT), $tipo, $estado, $cat]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estado) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, password_hash('demo123', PASSWORD_DEFAULT), $tipo, $estado]);
            }
            echo "<p style='color:green;'>✅ Creado $email</p>";
            $users_created++;
        }
    }

    echo "<h2>🏪 Creando Locales...</h2>";
    $locales = [
        ['Tienda 1', 'Ubicación A', 'Rubro A'],
        ['Tienda 2', 'Ubicación B', 'Rubro B'],
        ['Tienda 3', 'Ubicación C', 'Rubro C'],
        // ...
    ];
    $ldt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE tipoUsuario='dueno' AND estado='activo' AND codUsuario NOT IN (SELECT codUsuario FROM locales WHERE codUsuario IS NOT NULL) LIMIT 1");
    $insl = $pdo->prepare("INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario) VALUES (?, ?, ?, ?)");
    $stmtChk = $pdo->prepare("SELECT codLocal FROM locales WHERE nombreLocal = ?");
    $l_created = $l_exist = 0;
    $dueños_asignados = 0;
    foreach ($locales as $i => $loc) {
        list($nom,$ubi,$rub) = $loc;
        $stmtChk->execute([$nom]);
        if ($stmtChk->fetch()) {
            echo "<p style='color:orange;'>⚠️ Ya existe local $nom</p>";
            $l_exist++;
        } else {
            $ldt->execute();
            $du = $ldt->fetch();
            $codU = ($du && $dueños_asignados < count($locales)) ? $du['codUsuario'] : null;
            $insl->execute([$nom, $ubi, $rub, $codU]);
            if ($codU) {
                $dueños_asignados++;
                echo "<p style='color:green;'>✅ Local $nom creado (dueño asignado)</p>";
            } else {
                echo "<p style='color:green;'>✅ Local $nom creado (sin dueño)</p>";
            }
            $l_created++;
        }
    }

    echo "<h2>🎉 Creando Promociones...</h2>";
    $stmtLocs = $pdo->query("SELECT codLocal, nombreLocal FROM locales WHERE codUsuario IS NOT NULL LIMIT 10");
    $locs = $stmtLocs->fetchAll();
    $prom = [
        ['20% OFF', 'inicial'],
        ['15% OFF', 'medium'],
        ['10% OFF', 'premium'],
        // ...
    ];
    $insP = $pdo->prepare("INSERT INTO promociones (textoPromo, fechaDesdePromo, fechaHastaPromo, diasSemana, categoriaCliente, estadoPromo, codLocal) VALUES (?, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days', ?, ?, 'activa', ?)");
    $chkP = $pdo->prepare("SELECT codPromo FROM promociones WHERE textoPromo = ? AND codLocal = ?");
    $p_created = 0;
    foreach ($prom as $i => $pdata) {
        list($txt,$cat) = $pdata;
        $loc = $locs[$i % count($locs)];
        $chkP->execute([$txt, $loc['codLocal']]);
        if (!$chkP->fetch()) {
            $insP->execute([$txt, 'Lunes,...', $cat, $loc['codLocal']]);
            echo "<p style='color:green;'>✅ Promo $txt creada para el local {$loc['nombreLocal']}</p>";
            $p_created++;
        } else {
            echo "<p style='color:orange;'>⚠️ Promo $txt ya existe en el local {$loc['nombreLocal']}</p>";
        }
    }

    echo "<h2>📊 Registros de Uso...</h2>";
    $usage = [
        ['medium@test.com', 5],
        ['premium@test.com', 3],
        // ...
    ];
    foreach ($usage as $u) {
        list($email,$cnt) = $u;
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $stmtP = $pdo->prepare("SELECT codPromo FROM promociones WHERE categoriaCliente = 'inicial' AND estadoPromo = 'activa' LIMIT ?");
            $stmtP->execute([$cnt]);
            $proms = $stmtP->fetchAll();
            foreach ($proms as $promo) {
                $days = rand(1,30) . ' days';
                $stmtU = $pdo->prepare("INSERT INTO uso_promociones (codUsuario, codPromo, fechaUso, estado) VALUES (?, ?, CURRENT_TIMESTAMP - INTERVAL '$days', 'usado') ON CONFLICT DO NOTHING");
                $stmtU->execute([$user['codUsuario'], $promo['codPromo']]);
            }
            echo "<p style='color:green;'>✅ Registros de uso creados para $email</p>";
        }
    }

    echo "<h2>📋 Tabla novedades...</h2>";
    $pdo->exec("DROP TABLE IF EXISTS novedades");
    $pdo->exec("
        CREATE TABLE novedades (
            id SERIAL PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            contenido TEXT NOT NULL,
            categoria_minima VARCHAR(20) CHECK (categoria_minima IN ('unlogged','inicial','medium','premium')) DEFAULT 'unlogged',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_publicacion DATE DEFAULT CURRENT_DATE,
            estado VARCHAR(20) CHECK (estado IN ('activa','inactiva')) DEFAULT 'activa',
            codUsuario INT
        )
    ");
    echo "<p style='color:blue;'>📋 Tabla 'novedades' creada.</p>";

    $stmt = $pdo->query("SELECT codUsuario FROM usuarios WHERE tipoUsuario='administrador' LIMIT 1");
    $admin = $stmt->fetch()['codUsuario'] ?? 1;
    $insN = $pdo->prepare("INSERT INTO novedades (titulo, contenido, categoria_minima, codUsuario) VALUES (?, ?, ?, ?)");
    $news = [
        ['Bienvenida', '¡Bienvenido a nuestra tienda!', 'unlogged'],
        // ...
    ];
    foreach ($news as $n) {
        list($t,$c,$cat) = $n;
        try { 
            $insN->execute([$t, $c, $cat, $admin]);
            echo "<p style='color:green;'>✅ Novedad $t creada.</p>"; 
        }
        catch(PDOException $e) { 
            echo "<p style='color:orange;'>⚠️ No se pudo crear $t: ".$e->getMessage()."</p>"; 
        }
    }

    echo "<h2>📈 Resumen</h2>";
    echo "<ul><li>Usuarios creados: $users_created</li><li>Usuarios existentes: $users_existed</li>";
    echo "<li>Locales creados: $l_created</li><li>Locales existentes: $l_exist</li>";
    echo "<li>Promociones creadas: $p_created</li></ul>";

} catch (Exception $e) {
    echo "<div style='background:#fdd;'>❌ Error: ".$e->getMessage()."</div>";
}
