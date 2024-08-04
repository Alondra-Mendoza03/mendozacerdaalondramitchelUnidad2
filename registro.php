<?php
// Datos de conexión
$con = mysqli_connect("localhost", "root", "", "crud");

// Verificar la conexión
if (!$con) {
    die('Connection Failed: ' . mysqli_connect_error());
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar'])) {
    // Sanitizar y validar datos
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $nombre_completo = filter_var($_POST['nombre_completo'], FILTER_SANITIZE_STRING);
    $contrasena = $_POST['contrasena'];
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico no válido.");
    }
    
    // Verificar si el correo electrónico ya está registrado
    $stmt = $con->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        die("El correo electrónico ya está registrado.");
    }
    
    $stmt->close();
    
    // Hash de la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);
    
    // Insertar el nuevo usuario en la base de datos
    $stmt = $con->prepare("INSERT INTO usuarios (correo, nombre_completo, contrasena) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $correo, $nombre_completo, $contrasena_hash);
    
    if ($stmt->execute()) {
        // Mostrar una alerta y redirigir a la página de inicio de sesión
        echo "<script>
            alert('Registro exitoso. Por favor, inicie sesión.');
            window.location.href = 'login.html'; // O 'login.php' si estás usando PHP para el login
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

// Cerrar la conexión
$con->close();
?>
