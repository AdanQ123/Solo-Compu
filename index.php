<?php
/**
 * PÁGINA PRINCIPAL - TIENDA "SOLO COMPU"
 * Carga los productos de la base de datos MySQL y permite filtrarlos.
 */
session_start();
require_once 'config/conexion.php';

// Inicializar variables de filtrado
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$categorias_seleccionadas = isset($_GET['categorias']) ? $_GET['categorias'] : [];
$calidades_seleccionadas = isset($_GET['calidades']) ? $_GET['calidades'] : [];
$marcas_seleccionadas = isset($_GET['marcas']) ? $_GET['marcas'] : [];
$precio_maximo = isset($_GET['precio_max']) ? intval($_GET['precio_max']) : 10000000;

// Construir consulta dinámica basada en los filtros aplicados
$query = "SELECT * FROM productos WHERE precio <= :precio_max";
$params = [':precio_max' => $precio_maximo];

if (!empty($busqueda)) {
    $query .= " AND (nombre_producto LIKE :buscar OR descripcion_corta LIKE :buscar2)";
    $params[':buscar'] = '%' . $busqueda . '%';
    $params[':buscar2'] = '%' . $busqueda . '%';
}

if (!empty($categorias_seleccionadas)) {
    $in_cat = [];
    foreach ($categorias_seleccionadas as $key => $cat) {
        $param_name = ":cat_$key";
        $in_cat[] = $param_name;
        $params[$param_name] = $cat;
    }
    $query .= " AND categoria IN (" . implode(',', $in_cat) . ")";
}

if (!empty($calidades_seleccionadas)) {
    $in_cal = [];
    foreach ($calidades_seleccionadas as $key => $cal) {
        $param_name = ":cal_$key";
        $in_cal[] = $param_name;
        $params[$param_name] = $cal;
    }
    $query .= " AND calidad IN (" . implode(',', $in_cal) . ")";
}

if (!empty($marcas_seleccionadas)) {
    $in_marca = [];
    foreach ($marcas_seleccionadas as $key => $marca) {
        $param_name = ":marca_$key";
        $in_marca[] = $param_name;
        $params[$param_name] = $marca;
    }
    $query .= " AND marca IN (" . implode(',', $in_marca) . ")";
}

$query .= " ORDER BY id_producto DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
    $error_msg = "Error al consultar productos: " . $e->getMessage();
}

// Obtener marcas únicas de la base de datos para los filtros dinámicos
try {
    $stmt_marcas = $pdo->query("SELECT DISTINCT marca FROM productos ORDER BY marca ASC");
    $marcas_disponibles = $stmt_marcas->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $marcas_disponibles = ['AMD', 'Intel', 'ASUS', 'Lenovo', 'HP', 'Corsair'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo Compu - Tu Tienda de Hardware</title>
    <!-- CSS Propio Nativo -->
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <!-- BARRA DE NAVEGACIÓN SUPERIOR (NAVBAR) -->
    <nav class="navbar" id="main-nav">
        <div class="nav-left">
            <div class="logo-container">
                <!-- Imagen de logo placeholder o sutil para la tienda -->
                <img src="https://images.unsplash.com/photo-1547082299-de196ea013d6?w=100&auto=format&fit=crop" alt="Solo Compu Logo">
            </div>
            <a href="index.php" class="brand-name">SOLO<span>COMPU</span></a>
        </div>

        <!-- Barra de búsqueda -->
        <div class="nav-center">
            <form action="index.php" method="GET" class="search-form">
                <input type="search" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar componentes, portátiles, PCs..." class="search-input">
                <button type="submit" class="search-btn">Buscar</button>
            </form>
        </div>

        <!-- Acciones e Inicio de Sesión -->
        <div class="nav-right">
            <ul class="nav-links">
                <li><a href="index.php?categorias[]=componentes" class="nav-link">Componentes</a></li>
                <li><a href="index.php?categorias[]=portatiles" class="nav-link">Portátiles</a></li>
                <li><a href="index.php?categorias[]=computadores" class="nav-link">Computadores</a></li>
            </ul>

            <div class="nav-actions">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span style="font-size: 0.9rem; color: #007bff; font-weight: bold;">Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                    <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-solid">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- CONTENEDOR PRINCIPAL: DISTRIBUCIÓN ASIMÉTRICA -->
    <div class="store-container">
        
        <!-- SIDEBAR IZQUIERDA DE FILTROS -->
        <aside class="sidebar">
            <form action="index.php" method="GET">
                <!-- Conservar búsqueda actual en los filtros -->
                <?php if (!empty($busqueda)): ?>
                    <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                <?php endif; ?>

                <h3 class="sidebar-title">Filtrar Productos</h3>

                <!-- Filtro por Categorías -->
                <div class="filter-group">
                    <span class="filter-label">Categorías</span>
                    <label class="checkbox-option">
                        <input type="checkbox" name="categorias[]" value="componentes" <?php echo in_array('componentes', $categorias_seleccionadas) ? 'checked' : ''; ?>>
                        Componentes
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="categorias[]" value="portatiles" <?php echo in_array('portatiles', $categorias_seleccionadas) ? 'checked' : ''; ?>>
                        Portátiles
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="categorias[]" value="computadores" <?php echo in_array('computadores', $categorias_seleccionadas) ? 'checked' : ''; ?>>
                        Computadores
                    </label>
                </div>

                <!-- Filtro por Calidad / Gama -->
                <div class="filter-group">
                    <span class="filter-label">Calidad / Gama</span>
                    <label class="checkbox-option">
                        <input type="checkbox" name="calidades[]" value="Gama Alta" <?php echo in_array('Gama Alta', $calidades_seleccionadas) ? 'checked' : ''; ?>>
                        Gama Alta
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="calidades[]" value="Media" <?php echo in_array('Media', $calidades_seleccionadas) ? 'checked' : ''; ?>>
                        Gama Media
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="calidades[]" value="Economica" <?php echo in_array('Economica', $calidades_seleccionadas) ? 'checked' : ''; ?>>
                        Económica
                    </label>
                </div>

                <!-- Filtro por Marca -->
                <div class="filter-group">
                    <span class="filter-label">Marca</span>
                    <?php foreach ($marcas_disponibles as $m): ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="marcas[]" value="<?php echo htmlspecialchars($m); ?>" <?php echo in_array($m, $marcas_seleccionadas) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($m); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Control de Rango de Precio (Slider estilo Volumen) -->
                <div class="filter-group">
                    <span class="filter-label">Precio Máximo</span>
                    <div class="price-slider-container">
                        <input type="range" name="precio_max" min="500000" max="10000000" step="100000" value="<?php echo $precio_maximo; ?>" class="range-slider" id="priceRange">
                        <div class="price-values">
                            <span>$500.000 COP</span>
                            <span>$10.000.000 COP</span>
                        </div>
                        <div class="current-price-val" id="priceDisplay">
                            $<?php echo number_format($precio_maximo, 0, ',', '.'); ?> COP
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-solid btn-filter">Aplicar Filtros</button>
                <a href="index.php" class="btn btn-outline btn-filter" style="margin-top: 10px; display: block; text-align: center;">Limpiar Todo</a>
            </form>
        </aside>

        <!-- SECCIÓN DERECHA: CATÁLOGO DE PRODUCTOS -->
        <main class="catalog-section">
            <div class="catalog-header">
                <h2 class="catalog-title">
                    <?php 
                    if (!empty($busqueda)) {
                        echo 'Resultados para: "' . htmlspecialchars($busqueda) . '"';
                    } else {
                        echo 'Nuestros Productos';
                    }
                    ?>
                </h2>
                <span style="font-size: 0.9rem; color: #A0A0A0;"><?php echo count($productos); ?> productos encontrados</span>
            </div>

            <?php if (isset($error_msg)): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <?php if (empty($productos)): ?>
                <div class="no-results">
                    <p style="font-size: 1.2rem; margin-bottom: 10px;">No se encontraron productos con los filtros seleccionados.</p>
                    <p style="font-size: 0.9rem;">Prueba limpiando los filtros o realizando otra búsqueda.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($productos as $prod): ?>
                        <div class="product-card">
                            <div class="card-img-container">
                                <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="<?php echo htmlspecialchars($prod['nombre_producto']); ?>">
                            </div>
                            <div class="card-body">
                                <span class="card-badge <?php 
                                    if ($prod['calidad'] === 'Gama Alta') echo 'badge-alta';
                                    elseif ($prod['calidad'] === 'Media') echo 'badge-media';
                                    else echo 'badge-economica';
                                ?>">
                                    <?php echo htmlspecialchars($prod['calidad']); ?>
                                </span>
                                <h3 class="card-title"><?php echo htmlspecialchars($prod['nombre_producto']); ?></h3>
                                <p class="card-desc"><?php echo htmlspecialchars($prod['descripcion_corta']); ?></p>
                                <div class="card-price-row">
                                    <span class="card-price">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?> COP</span>
                                    <a href="detalle_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn-view">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- PIE DE PÁGINA (FOOTER ESTÁTICO) -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-brand">SOLO<span>COMPU</span></p>
            <p class="footer-contact">Contacto: soporte@solocompu.com | Teléfono: +57 (4) 555-0199 | Medellín, Colombia</p>
            <p class="footer-rights">&copy; <?php echo date('Y'); ?> Solo Compu. Todos los derechos reservados. Proyecto de Media Técnica en Desarrollo de Software.</p>
        </div>
    </footer>

    <!-- SCRIPT DE INTERACCIÓN DINÁMICA DEL SLIDER -->
    <script>
        const priceRange = document.getElementById('priceRange');
        const priceDisplay = document.getElementById('priceDisplay');

        priceRange.addEventListener('input', function() {
            // Formatear precio a COP
            const val = parseInt(this.value);
            const formatted = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(val);
            
            priceDisplay.textContent = formatted + ' COP';
        });
    </script>
</body>
</html>
