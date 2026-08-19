<?php
/**
 * DETALLE DE PRODUCTO - "SOLO COMPU"
 * Muestra información detallada de un hardware seleccionado por ID.
 */
session_start();
require_once 'config/conexion.php';

// Obtener el ID del producto de manera segura
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_producto <= 0) {
    header('Location: index.php');
    exit;
}

try {
    // Consultar el producto por su ID
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = :id");
    $stmt->execute([':id' => $id_producto]);
    $producto = $stmt->fetch();

    if (!$producto) {
        // Si el producto no existe, redirigir a la tienda
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    die("Error al consultar el detalle del producto: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre_producto']); ?> - Solo Compu</title>
    <!-- CSS Propio Nativo -->
    <link rel="stylesheet" href="css/estilos.css?v=<?php echo filemtime(__DIR__ . '/css/estilos.css'); ?>">
</head>
<body>

    <!-- BARRA DE NAVEGACIÓN SUPERIOR (NAVBAR) -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo-container">
                <img src="https://images.unsplash.com/photo-1547082299-de196ea013d6?w=100&auto=format&fit=crop" alt="Solo Compu Logo">
            </div>
            <a href="index.php" class="brand-name">SOLO<span>COMPU</span></a>
        </div>

        <div class="nav-right">
            <ul class="nav-links">
                <li><a href="index.php?categorias[]=componentes" class="nav-link">Componentes</a></li>
                <li><a href="index.php?categorias[]=portatiles" class="nav-link">Portátiles</a></li>
                <li><a href="index.php?categorias[]=computadores" class="nav-link">Computadores</a></li>
            </ul>

            <div class="nav-actions">
                <a href="index.php?carrito=1" class="nav-link cart-link" title="Ver carrito"><span class="cart-icon">&#128722;</span> <span>Carrito</span></a>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span style="font-size: 0.9rem; color: #007bff; font-weight: bold;">Hola, <?php echo !empty($_SESSION['es_admin']) ? 'Admin' : htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                    <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-solid">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- DETALLES DEL PRODUCTO -->
    <div class="detail-container">
        
        <!-- Enlace para volver -->
        <a href="index.php" class="btn-back">← Volver al catálogo de productos</a>

        <div class="detail-card">
            <!-- Columna de Imagen -->
            <div class="detail-img-container">
                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
            </div>

            <!-- Columna de Información -->
            <div class="detail-info">
                <span class="detail-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                <h1 class="detail-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>

                <div class="detail-meta-row">
                    <span class="detail-badge <?php 
                        if ($producto['calidad'] === 'Gama Alta') echo 'badge-alta';
                        elseif ($producto['calidad'] === 'Media') echo 'badge-media';
                        else echo 'badge-economica';
                    ?>">
                        Calidad: <?php echo htmlspecialchars($producto['calidad']); ?>
                    </span>
                    <span class="detail-badge badge-media" style="background-color: rgba(0, 123, 255, 0.15); color: #007bff; border-color: #007bff;">
                        Marca: <?php echo htmlspecialchars($producto['marca']); ?>
                    </span>
                </div>

                <div class="detail-price">
                    $<?php echo number_format($producto['precio'], 0, ',', '.'); ?> COP
                </div>

                <p class="detail-desc-title">Especificaciones Técnicas / Descripción Completa:</p>
                <div class="detail-desc">
                    <?php echo nl2br(htmlspecialchars($producto['descripcion_completa'])); ?>
                </div>

                <!-- Botón de Acción -->
                <form action="index.php" method="post">
                    <input type="hidden" name="accion_carrito" value="agregar">
                    <input type="hidden" name="id_producto" value="<?php echo (int) $producto['id_producto']; ?>">
                    <button type="submit" class="btn btn-solid btn-buy">&#128722; Añadir al Carrito de Compras</button>
                </form>
            </div>
        </div>

    </div>

    <!-- PIE DE PÁGINA (FOOTER ESTÁTICO) -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-brand">SOLO<span>COMPU</span></p>
            <p class="footer-contact">Contacto: soporte@solocompu.com | Teléfono: +57 (4) 555-0199 | Medellín, Colombia</p>
            <p class="footer-rights">&copy; <?php echo date('Y'); ?> Solo Compu. Todos los derechos reservados. Proyecto de Media Técnica en Desarrollo de Software.</p>
        </div>
    </footer>

</body>
</html>
