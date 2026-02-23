<?php
require_once __DIR__ . '/include/config.php';

header('Content-Type: text/html; charset=UTF-8');

$apply = isset($_GET['apply']) && $_GET['apply'] === '1';
$uploadDir = __DIR__ . '/uploa_d/';
$thumbDir = __DIR__ . '/uploa_d/thumbnail/';

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
    echo '<h3>Error de conexion DB</h3>';
    exit;
}
$conn->set_charset('utf8');

function normalize_spaces($name) {
    return preg_replace('/[[:space:]]+/', '_', $name);
}

function split_name_ext($filename) {
    $dot = strrpos($filename, '.');
    if ($dot === false) {
        return array($filename, '');
    }
    return array(substr($filename, 0, $dot), substr($filename, $dot));
}

function db_name_exists($conn, $name, $excludeId) {
    $nameEsc = $conn->real_escape_string($name);
    $excludeId = intval($excludeId);
    $sql = "SELECT id FROM servicio_foto WHERE archivo='$nameEsc' AND id<>$excludeId LIMIT 1";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0);
}

function unique_name($conn, $baseName, $excludeId, $uploadDir) {
    $candidate = $baseName;
    if (!db_name_exists($conn, $candidate, $excludeId) && !file_exists($uploadDir . $candidate)) {
        return $candidate;
    }

    list($namePart, $extPart) = split_name_ext($baseName);
    for ($i = 1; $i <= 5000; $i++) {
        $candidate = $namePart . '_' . $i . $extPart;
        if (!db_name_exists($conn, $candidate, $excludeId) && !file_exists($uploadDir . $candidate)) {
            return $candidate;
        }
    }
    return $baseName . '_fix';
}

$sql = "SELECT id, id_servicio, archivo, fecha FROM servicio_foto WHERE archivo REGEXP '[[:space:]]'";
$result = $conn->query($sql);

echo '<h2>Fix espacios en servicio_foto</h2>';
echo '<p>Modo: <strong>' . ($apply ? 'APPLY' : 'PREVIEW') . '</strong></p>';
echo '<p>Para aplicar cambios: <a href="?apply=1">?apply=1</a></p>';

if (!$result) {
    echo '<p>Error en consulta.</p>';
    exit;
}

if ($result->num_rows === 0) {
    echo '<p>No se encontraron archivos con espacios.</p>';
    exit;
}

$ok = 0;
$warn = 0;
$rows = 0;

echo '<table border="1" cellpadding="6" cellspacing="0">';
echo '<tr><th>ID</th><th>Servicio</th><th>Original</th><th>Nuevo</th><th>Archivo</th><th>Thumb</th><th>DB</th><th>Estado</th></tr>';

while ($row = $result->fetch_assoc()) {
    $rows++;
    $id = intval($row['id']);
    $idServicio = intval($row['id_servicio']);
    $original = $row['archivo'];
    $normalized = normalize_spaces($original);
    $final = $normalized;

    if ($final !== $original) {
        $final = unique_name($conn, $final, $id, $uploadDir);
    }

    $fileOld = $uploadDir . $original;
    $fileNew = $uploadDir . $final;
    $thumbOld = $thumbDir . $original;
    $thumbNew = $thumbDir . $final;

    $fileStatus = 'NA';
    $thumbStatus = 'NA';
    $dbStatus = 'NA';
    $state = 'PENDIENTE';

    if ($apply) {
        if ($original !== $final) {
            if (file_exists($fileOld)) {
                if (!file_exists($fileNew)) {
                    $fileStatus = @rename($fileOld, $fileNew) ? 'RENAMED' : 'ERROR';
                } else {
                    $fileStatus = 'TARGET_EXISTS';
                }
            } else {
                $fileStatus = 'NOT_FOUND';
            }

            if (file_exists($thumbOld)) {
                if (!file_exists($thumbNew)) {
                    $thumbStatus = @rename($thumbOld, $thumbNew) ? 'RENAMED' : 'ERROR';
                } else {
                    $thumbStatus = 'TARGET_EXISTS';
                }
            } else {
                $thumbStatus = 'NOT_FOUND';
            }

            $newEsc = $conn->real_escape_string($final);
            $sqlUp = "UPDATE servicio_foto SET archivo='$newEsc' WHERE id=$id LIMIT 1";
            $dbStatus = $conn->query($sqlUp) ? 'UPDATED' : 'ERROR';
        } else {
            $state = 'SIN_CAMBIO';
        }

        if ($dbStatus === 'UPDATED') {
            $ok++;
            $state = 'OK';
        } else {
            $warn++;
            if ($state !== 'SIN_CAMBIO') {
                $state = 'REVISAR';
            }
        }
    } else {
        $state = ($original === $final) ? 'SIN_CAMBIO' : 'LISTO';
    }

    echo '<tr>';
    echo '<td>' . $id . '</td>';
    echo '<td>' . $idServicio . '</td>';
    echo '<td>' . htmlspecialchars($original, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($final, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . $fileStatus . '</td>';
    echo '<td>' . $thumbStatus . '</td>';
    echo '<td>' . $dbStatus . '</td>';
    echo '<td>' . $state . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '<p>Total revisados: <strong>' . $rows . '</strong></p>';
if ($apply) {
    echo '<p>OK: <strong>' . $ok . '</strong> | Revisar: <strong>' . $warn . '</strong></p>';
}

$conn->close();
