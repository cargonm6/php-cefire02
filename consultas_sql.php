<?php

// =========================
// CONFIGURACIÓN DE CONEXIÓN
// =========================

// Variables de entorno
$dbHost = getenv('DB_HOST');
$dbName = 'prueba';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

// Validar variables
if (!$dbHost || !$dbUser || $dbPass === false) {
    throw new RuntimeException(
        'Faltan variables de entorno para la conexión a la base de datos.'
    );
}

// DSN
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {

    // Opciones PDO
    $options = [

        // Mostrar errores como excepciones
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Fetch asociativo
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Seguridad en prepared statements
        PDO::ATTR_EMULATE_PREPARES => false,

        // SSL Azure MySQL
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/BaltimoreCyberTrustRoot.crt.pem',

        // Desactivar validación estricta del certificado
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    // Conexión
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

} catch (PDOException $e) {

    error_log('Error PDO: ' . $e->getMessage());

    die(
        'Error al conectar con la base de datos: ' .
        htmlspecialchars($e->getMessage())
    );
}


// =====================================
// INSERTAR NUEVO CONTENIDO EN LA TABLA
// =====================================

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener contenido del formulario
    $contenido = trim($_POST['contenido'] ?? '');

    // Validar que no esté vacío
    if ($contenido !== '') {

        try {

            // Prepared statement
            $sql = "INSERT INTO prueba (contenido)
                    VALUES (:contenido)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':contenido' => $contenido
            ]);

            $mensaje = 'Contenido insertado correctamente.';

        } catch (PDOException $e) {

            $mensaje = 'Error al insertar: ' .
                htmlspecialchars($e->getMessage());
        }

    } else {

        $mensaje = 'El contenido no puede estar vacío.';
    }
}


// ==========================
// RECUPERAR TODOS LOS DATOS
// ==========================

try {

    $stmt = $pdo->query(
        "SELECT id, contenido
         FROM prueba
         ORDER BY id DESC"
    );

    $filas = $stmt->fetchAll();

} catch (PDOException $e) {

    die(
        'Error al recuperar datos: ' .
        htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba PDO MySQL</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            font-size: 16px;
        }

        button {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .mensaje {
            margin: 15px 0;
            padding: 10px;
            background: #dff0d8;
            border: 1px solid #b2d8b2;
            border-radius: 5px;
        }

        .registro {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .registro-id {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

    </style>

</head>
<body>

    <h1>Insertar contenido</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label for="contenido">
            Escribe un contenido:
        </label>

        <br><br>

        <textarea
            name="contenido"
            id="contenido"
            required
        ></textarea>

        <br>

        <button type="submit">
            Guardar
        </button>

    </form>


    <h2>Entradas almacenadas</h2>

    <?php if (count($filas) > 0): ?>

        <?php foreach ($filas as $fila): ?>

            <div class="registro">

                <div class="registro-id">
                    ID: <?php echo $fila['id']; ?>
                </div>

                <div>
                    <?php echo nl2br(htmlspecialchars($fila['contenido'])); ?>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No hay registros todavía.</p>

    <?php endif; ?>

</body>
</html>
