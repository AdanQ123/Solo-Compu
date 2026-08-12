<?php
/**
 * REGISTRO DE USUARIOS - "SOLO COMPU"
 * Formulario centrado encerrado en una tarjeta media circular con estilo oscuro.
 */
session_start();
require_once 'config/conexion.php';

// Si ya hay una sesión activa, redirigir a la tienda
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
            $stmt->execute([':correo' => $correo]);
            
            if ($stmt->fetch()) {
                $error = 'Este correo electrónico ya está registrado.';
            } else {
                // Encriptar la contraseña de manera segura
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insertar el nuevo usuario en la base de datos
                $stmt_insert = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password) VALUES (:nombre, :correo, :password)");
                $stmt_insert->execute([
                    ':nombre' => $nombre,
                    ':correo' => $correo,
                    ':password' => $password_hash
                ]);

                $success = '¡Registro exitoso! Ya puedes iniciar sesión con tus credenciales.';
                // Limpiar campos para que no se muestren de nuevo
                $nombre = $correo = '';
            }
        } catch (Exception $e) {
            $error = 'Ocurrió un error al procesar el registro. Inténtalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cuenta - Solo Compu</title>
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
            <h1 class="auth-title">Crear Cuenta</h1>
            <p class="auth-subtitle">Regístrate para comprar componentes y PCs</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <p style="margin-top: 8px;"><a href="login.php" class="auth-link" style="color: #28a745; text-decoration: underline;">Ir a Iniciar Sesión →</a></p>
            </div>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <!-- Nombre Completo -->
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Juan Pérez" required value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>">
            </div>

            <!-- Correo electrónico -->
            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="nombre@ejemplo.com" required value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>">
            </div>

            <!-- Contraseña -->
            <div class="form-group">
                <label for="password" class="form-label">Contraseña (Mínimo 6 caracteres)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required minlength="6">
            </div>

            <!-- Botón de Registro -->
            <button type="submit" class="btn btn-solid btn-auth">Registrarse ahora</button>
        </form>

        <!-- Footer de la tarjeta con enlace a Login -->
        <div class="auth-footer">
            <p>¿Ya tienes una cuenta registrada? <a href="login.php" class="auth-link">Inicia sesión aquí</a></p>
            <p style="margin-top: 15px;"><a href="index.php" style="color: #757c88; font-size: 0.85rem;">← Volver al catálogo de productos</a></p>
        </div>

    </div>

</body>
</html>
