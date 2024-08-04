<?php
// Datos de conexión
$con = mysqli_connect("localhost", "root", "", "crud");

// Verificar la conexión
if (!$con) {
    die('Connection Failed: ' . mysqli_connect_error());
}

// Procesar el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Sanitizar y validar datos
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'];
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico no válido.");
    }
    
    // Consultar usuario
    $stmt = $con->prepare("SELECT id, contrasena FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        // Correo no registrado
        echo "<script>
            alert('Correo electrónico no registrado.');
            window.location.href = 'login.html'; // Cambia a 'login.php' si usas PHP para login
        </script>";
        exit();
    }
    
    $stmt->bind_result($id, $contrasena_hash);
    $stmt->fetch();
    
    if (password_verify($contrasena, $contrasena_hash)) {
        // Iniciar sesión (opcional, puedes usar sesiones para manejar la autenticación)
        session_start();
        $_SESSION['user_id'] = $id; // Guarda el ID del usuario en la sesión
        
        // Redirigir a index.php
        header("Location: index.php");
        exit(); // Asegúrate de llamar a exit después de header para detener la ejecución del script
    } else {
        // Contraseña incorrecta
        echo "<script>
            alert('Contraseña incorrecta.');
            window.location.href = 'login.html'; // Cambia a 'login.php' si usas PHP para login
        </script>";
        exit();
    }
    
    $stmt->close();
}

// Cerrar la conexión
$con->close();
?>
