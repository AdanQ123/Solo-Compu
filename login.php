<?php
/**
 * INICIO DE SESIÓN - "SOLO COMPU"
 * Formulario centrado encerrado en una tarjeta media circular con estilo oscuro.
 */
session_start();
require_once 'config/conexion.php';

// Si ya hay una sesión activa, conservar el destino correspondiente.
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . (!empty($_SESSION['es_admin']) ? 'admin.php' : 'index.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($correo) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            // Buscar el usuario por correo
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['es_admin'] = ($usuario['rol'] === 'admin');
                $_SESSION['usuario_nombre'] = $_SESSION['es_admin'] ? 'Admin' : $usuario['nombre'];
                $_SESSION['usuario_correo'] = $usuario['correo'];

                header('Location: ' . ($_SESSION['es_admin'] ? 'admin.php' : 'index.php'));
                exit;
            } else{
                $error = 'No se encontro el usuario';
            }
        } catch (Exception $e) {
            $error = 'Ocurrió un error en el servidor. Inténtalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Solo Compu</title>
    <!-- CSS Propio Nativo -->
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="auth-page-bg">

    <!-- TARJETA DE AUTENTICACIÓN - CUADRADO MEDIO CIRCULAR EN EL CENTRO -->
    <div class="auth-card">
        
        <div class="auth-header">
            <div class="auth-logo">
                <!-- Icono sutil del logo circular -->
                <span style="font-size: 1.8rem; font-weight: bold; color: #ffffff;">SC</span>
            </div>
            <h1 class="auth-title">Iniciar Sesión</h1>
            <p class="auth-subtitle">Accede a tu cuenta de Solo Compu</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <!-- Correo electrónico -->
            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="nombre@ejemplo.com" required value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
            </div>

            <!-- Contraseña -->
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- Botón de Ingresar -->
            <button type="submit" class="btn btn-solid btn-auth">Ingresar a la Tienda</button>
        </form>

        <!-- Footer de la tarjeta con enlace a Registro -->
        <div class="auth-footer">
            <p>¿No tienes una cuenta? <a href="registro.php" class="auth-link">Regístrate gratis aquí</a></p>
            <p style="margin-top: 15px;"><a href="index.php" style="color: #757c88; font-size: 0.85rem;">← Volver al catálogo de productos</a></p>
        </div>

    </div>

</body>
</html>
