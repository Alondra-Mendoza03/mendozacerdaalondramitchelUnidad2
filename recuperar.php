<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye el autoload de Composer para PHPMailer
require 'vendor/autoload.php';

// Datos de conexión
$con = mysqli_connect("localhost", "root", "", "crud");

// Verificar la conexión
if (!$con) {
    die('Connection Failed: ' . mysqli_connect_error());
}

// Procesar el formulario de recuperación de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recuperar'])) {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico no válido.");
    }

    // Verificar si el correo electrónico está registrado
    $stmt = $con->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        die("Correo electrónico no registrado.");
    }

    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    // Generar un token de recuperación único
    $token = bin2hex(random_bytes(50));
    $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Guardar el token en la base de datos
    $stmt = $con->prepare("INSERT INTO recuperacion (usuario_id, token, fecha_expiracion) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $expiracion);
    $stmt->execute();
    $stmt->close();

    // Configurar y enviar el correo electrónico
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Configura el servidor SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@example.com'; // Tu correo electrónico
        $mail->Password   = 'your-email-password';     // Tu contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your-email@example.com', 'Soporte');
        $mail->addAddress($correo);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña';
        $mail->Body    = 'Haz clic en el siguiente enlace para restablecer tu contraseña: <a href="http://yourdomain.com/restablecer.php?token=' . $token . '">Restablecer Contraseña</a>';

        $mail->send();
        echo 'Correo de recuperación enviado.';
    } catch (Exception $e) {
        echo 'No se pudo enviar el correo. Error: ', $mail->ErrorInfo;
    }
}

// Cerrar la conexión
$con->close();
?>
