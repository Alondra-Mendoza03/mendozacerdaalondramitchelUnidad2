<?php
session_start();

// Datos de conexión
$con = mysqli_connect("localhost", "root", "", "crud");

// Verificar la conexión
if (!$con) {
    die('Connection Failed: ' . mysqli_connect_error());
}

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (!$token) {
    die('Token no proporcionado.');
}

// Verificar el token
$stmt = $con->prepare("SELECT usuario_id, fecha_expiracion FROM recuperacion WHERE token = ? AND fecha_expiracion > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die('Token inválido o expirado.');
}

$stmt->bind_result($user_id, $fecha_expiracion);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restablecer'])) {
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

    // Actualizar la contraseña
    $stmt = $con->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
    $stmt->bind_param("si", $contrasena_hash, $user_id);

    if ($stmt->execute()) {
        // Eliminar el token después de usarlo
        $stmt = $con->prepare("DELETE FROM recuperacion WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "Contraseña restablecida con éxito.";
    } else {
        echo "Error al restablecer la contraseña.";
    }

    $stmt->close();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 form-container">
                <h2 class="text-center mb-4">Restablecer Contraseña</h2>
                <form method="post" action="restablecer.php?token=<?php echo htmlspecialchars($token); ?>">
                    <div class="mb-3">
                        <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                    </div>
                    <button type="submit" name="restablecer" class="btn btn-submit w-100">Restablecer Contraseña</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
