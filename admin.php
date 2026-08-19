<?php
session_start();
require_once 'config/conexion.php';

if (empty($_SESSION['es_admin'])) {
	header('Location: login.php');
	exit;
}

if (empty($_SESSION['admin_csrf'])) {
	$_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['admin_csrf'];
$mensaje = '';
$error = '';
$seccion = $_GET['seccion'] ?? 'productos';

function escapar($valor) {
	return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function productoVacio() {
	return ['id_producto' => '', 'nombre_producto' => '', 'categoria' => 'componentes', 'precio' => '', 'imagen' => '', 'descripcion_corta' => '', 'descripcion_completa' => '', 'calidad' => 'Media', 'marca' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$accion = $_POST['accion'] ?? '';
	$seccion = $_POST['seccion'] ?? 'productos';

	if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
		$error = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
	} else {
		try {
			if ($accion === 'guardar_producto') {
				$id = (int) ($_POST['id_producto'] ?? 0);
				$datos = [
					':nombre_producto' => trim($_POST['nombre_producto'] ?? ''),
					':categoria' => $_POST['categoria'] ?? '',
					':precio' => (int) ($_POST['precio'] ?? 0),
					':imagen' => trim($_POST['imagen'] ?? ''),
					':descripcion_corta' => trim($_POST['descripcion_corta'] ?? ''),
					':descripcion_completa' => trim($_POST['descripcion_completa'] ?? ''),
					':calidad' => $_POST['calidad'] ?? '',
					':marca' => trim($_POST['marca'] ?? '')
				];
				if ($datos[':nombre_producto'] === '' || $datos[':precio'] < 0 || $datos[':imagen'] === '' || $datos[':descripcion_corta'] === '' || $datos[':descripcion_completa'] === '' || $datos[':marca'] === '') {
					throw new Exception('Completa todos los campos del producto y usa un precio válido.');
				}
				if ($id > 0) {
					$datos[':id_producto'] = $id;
					$sql = "UPDATE productos SET nombre_producto = :nombre_producto, categoria = :categoria, precio = :precio, imagen = :imagen, descripcion_corta = :descripcion_corta, descripcion_completa = :descripcion_completa, calidad = :calidad, marca = :marca WHERE id_producto = :id_producto";
					$pdo->prepare($sql)->execute($datos);
					$mensaje = 'Producto actualizado correctamente.';
				} else {
					$sql = "INSERT INTO productos (nombre_producto, categoria, precio, imagen, descripcion_corta, descripcion_completa, calidad, marca) VALUES (:nombre_producto, :categoria, :precio, :imagen, :descripcion_corta, :descripcion_completa, :calidad, :marca)";
					$pdo->prepare($sql)->execute($datos);
					$mensaje = 'Producto creado correctamente.';
				}
			} elseif ($accion === 'eliminar_producto') {
				$pdo->prepare('DELETE FROM productos WHERE id_producto = :id')->execute([':id' => (int) $_POST['id_producto']]);
				$mensaje = 'Producto eliminado correctamente.';
			} elseif ($accion === 'guardar_usuario') {
				$id = (int) ($_POST['id_usuario'] ?? 0);
				$nombre = trim($_POST['nombre'] ?? '');
				$correo = trim($_POST['correo'] ?? '');
				$rol = $_POST['rol'] ?? 'usuario';
				$password = $_POST['password'] ?? '';
				if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !in_array($rol, ['usuario', 'admin'], true)) {
					throw new Exception('Revisa el nombre, correo y rol del usuario.');
				}
				if ($id > 0) {
					$sql = 'UPDATE usuarios SET nombre = :nombre, correo = :correo, rol = :rol';
					$datos = [':nombre' => $nombre, ':correo' => $correo, ':rol' => $rol, ':id' => $id];
					if ($password !== '') {
						$sql .= ', password = :password';
						$datos[':password'] = password_hash($password, PASSWORD_BCRYPT);
					}
					$pdo->prepare($sql . ' WHERE id_usuario = :id')->execute($datos);
					$mensaje = 'Usuario actualizado correctamente.';
				} else {
					if (strlen($password) < 6) {
						throw new Exception('La contraseña debe tener al menos 6 caracteres.');
					}
					$sql = 'INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, :rol)';
					$pdo->prepare($sql)->execute([':nombre' => $nombre, ':correo' => $correo, ':password' => password_hash($password, PASSWORD_BCRYPT), ':rol' => $rol]);
					$mensaje = 'Usuario creado correctamente.';
				}
			} elseif ($accion === 'eliminar_usuario') {
				$id = (int) $_POST['id_usuario'];
				if ($id === (int) $_SESSION['usuario_id']) {
					throw new Exception('No puedes eliminar la cuenta con la que estás conectado.');
				}
				$pdo->prepare('DELETE FROM usuarios WHERE id_usuario = :id')->execute([':id' => $id]);
				$mensaje = 'Usuario eliminado correctamente.';
			}
		} catch (PDOException $e) {
			$error = $e->errorInfo[1] === 1062 ? 'Ese correo electrónico ya está registrado.' : 'No se pudo guardar la información.';
		} catch (Exception $e) {
			$error = $e->getMessage();
		}
	}
}

$productoEditar = productoVacio();
$usuarioEditar = ['id_usuario' => '', 'nombre' => '', 'correo' => '', 'rol' => 'usuario'];
if (isset($_GET['editar_producto'])) {
	$stmt = $pdo->prepare('SELECT * FROM productos WHERE id_producto = :id');
	$stmt->execute([':id' => (int) $_GET['editar_producto']]);
	$productoEditar = $stmt->fetch() ?: $productoEditar;
	$seccion = 'productos';
}
if (isset($_GET['editar_usuario'])) {
	$stmt = $pdo->prepare('SELECT id_usuario, nombre, correo, rol FROM usuarios WHERE id_usuario = :id');
	$stmt->execute([':id' => (int) $_GET['editar_usuario']]);
	$usuarioEditar = $stmt->fetch() ?: $usuarioEditar;
	$seccion = 'usuarios';
}

$buscarProducto = trim($_GET['buscar_producto'] ?? '');
$categoriaProducto = $_GET['categoria'] ?? '';
$precioMaximo = max(0, (int) ($_GET['precio_max'] ?? 100000000));
$query = 'SELECT * FROM productos WHERE precio <= :precio_max';
$params = [':precio_max' => $precioMaximo];
if ($buscarProducto !== '') {
	$query .= ' AND (nombre_producto LIKE :buscar OR marca LIKE :buscar2)';
	$params[':buscar'] = '%' . $buscarProducto . '%';
	$params[':buscar2'] = '%' . $buscarProducto . '%';
}
if (in_array($categoriaProducto, ['componentes', 'portatiles', 'computadores'], true)) {
	$query .= ' AND categoria = :categoria';
	$params[':categoria'] = $categoriaProducto;
}
$stmt = $pdo->prepare($query . ' ORDER BY id_producto DESC');
$stmt->execute($params);
$productos = $stmt->fetchAll();

$buscarUsuario = trim($_GET['buscar_usuario'] ?? '');
$rolUsuario = $_GET['rol'] ?? '';
$query = 'SELECT id_usuario, nombre, correo, rol, fecha_registro FROM usuarios WHERE 1 = 1';
$params = [];
if ($buscarUsuario !== '') {
	$query .= ' AND (nombre LIKE :buscar OR correo LIKE :buscar2)';
	$params[':buscar'] = '%' . $buscarUsuario . '%';
	$params[':buscar2'] = '%' . $buscarUsuario . '%';
}
if (in_array($rolUsuario, ['usuario', 'admin'], true)) {
	$query .= ' AND rol = :rol';
	$params[':rol'] = $rolUsuario;
}
$stmt = $pdo->prepare($query . ' ORDER BY id_usuario DESC');
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Administración - Solo Compu</title>
	<link rel="stylesheet" href="css/estilos.css?v=<?php echo filemtime(__DIR__ . '/css/estilos.css'); ?>">
</head>
<body class="admin-page">
	<nav class="admin-nav">
		<a href="admin.php" class="admin-brand">SOLO<span>COMPU</span> / ADMIN</a>
		<div class="admin-tabs"><a href="admin.php?seccion=productos" class="<?php echo $seccion === 'productos' ? 'active' : ''; ?>">Productos</a><a href="admin.php?seccion=usuarios" class="<?php echo $seccion === 'usuarios' ? 'active' : ''; ?>">Usuarios</a></div>
		<div class="admin-nav-actions"><a href="index.php" class="btn btn-outline">Catálogo</a><a href="logout.php" class="btn btn-solid">Salir</a></div>
	</nav>
	<main class="admin-shell">
		<div class="admin-heading"><div><h1>Panel de administración</h1><p>Gestiona el catálogo y las cuentas desde aquí.</p></div></div>
		<?php if ($mensaje !== ''): ?><div class="alert alert-success admin-alert"><?php echo escapar($mensaje); ?></div><?php endif; ?>
		<?php if ($error !== ''): ?><div class="alert alert-error admin-alert"><?php echo escapar($error); ?></div><?php endif; ?>
		<?php if ($seccion === 'usuarios'): ?>
			<div class="admin-grid">
				<section class="admin-panel"><h2><?php echo $usuarioEditar['id_usuario'] ? 'Editar usuario' : 'Nuevo usuario'; ?></h2><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?php echo escapar($csrf); ?>"><input type="hidden" name="accion" value="guardar_usuario"><input type="hidden" name="seccion" value="usuarios"><input type="hidden" name="id_usuario" value="<?php echo escapar($usuarioEditar['id_usuario']); ?>"><label>Nombre<input type="text" name="nombre" maxlength="100" required value="<?php echo escapar($usuarioEditar['nombre']); ?>"></label><label>Correo<input type="email" name="correo" maxlength="100" required value="<?php echo escapar($usuarioEditar['correo']); ?>"></label><label>Rol<select name="rol"><option value="usuario" <?php echo $usuarioEditar['rol'] === 'usuario' ? 'selected' : ''; ?>>Usuario</option><option value="admin" <?php echo $usuarioEditar['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option></select></label><label>Contraseña <?php echo $usuarioEditar['id_usuario'] ? '(dejar vacía para conservarla)' : ''; ?><input type="password" name="password" minlength="6" <?php echo $usuarioEditar['id_usuario'] ? '' : 'required'; ?>></label><div class="admin-form-actions"><button type="submit" class="btn btn-solid">Guardar usuario</button><?php if ($usuarioEditar['id_usuario']): ?><a href="admin.php?seccion=usuarios" class="btn btn-outline">Cancelar</a><?php endif; ?></div></form></section>
				<section class="admin-panel"><h2>Usuarios registrados</h2><form method="get" class="admin-form admin-filter-form admin-user-filter"><input type="hidden" name="seccion" value="usuarios"><label>Buscar por nombre o correo<input type="search" name="buscar_usuario" value="<?php echo escapar($buscarUsuario); ?>" placeholder="Buscar..."></label><label>Rol<select name="rol"><option value="">Todos</option><option value="usuario" <?php echo $rolUsuario === 'usuario' ? 'selected' : ''; ?>>Usuarios</option><option value="admin" <?php echo $rolUsuario === 'admin' ? 'selected' : ''; ?>>Admins</option></select></label><button type="submit" class="btn btn-solid">Filtrar</button></form><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th><th>Acciones</th></tr></thead><tbody><?php foreach ($usuarios as $usuario): ?><tr><td><?php echo escapar($usuario['nombre']); ?></td><td><?php echo escapar($usuario['correo']); ?></td><td><span class="admin-badge <?php echo $usuario['rol'] === 'admin' ? 'admin' : ''; ?>"><?php echo $usuario['rol'] === 'admin' ? 'Administrador' : 'Usuario'; ?></span></td><td><?php echo escapar($usuario['fecha_registro']); ?></td><td><div class="admin-actions"><a class="btn btn-outline" href="admin.php?seccion=usuarios&editar_usuario=<?php echo (int) $usuario['id_usuario']; ?>">Editar</a><?php if ((int) $usuario['id_usuario'] !== (int) $_SESSION['usuario_id']): ?><form method="post" onsubmit="return confirm('¿Eliminar este usuario?');"><input type="hidden" name="csrf" value="<?php echo escapar($csrf); ?>"><input type="hidden" name="accion" value="eliminar_usuario"><input type="hidden" name="seccion" value="usuarios"><input type="hidden" name="id_usuario" value="<?php echo (int) $usuario['id_usuario']; ?>"><button class="btn admin-danger" type="submit">Eliminar</button></form><?php endif; ?></div></td></tr><?php endforeach; ?><?php if (!$usuarios): ?><tr><td colspan="5">No hay usuarios que coincidan con el filtro.</td></tr><?php endif; ?></tbody></table></div></section>
			</div>
		<?php else: ?>
			<div class="admin-grid">
				<section class="admin-panel"><h2><?php echo $productoEditar['id_producto'] ? 'Editar producto' : 'Nuevo producto'; ?></h2><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?php echo escapar($csrf); ?>"><input type="hidden" name="accion" value="guardar_producto"><input type="hidden" name="seccion" value="productos"><input type="hidden" name="id_producto" value="<?php echo escapar($productoEditar['id_producto']); ?>"><label>Nombre<input type="text" name="nombre_producto" maxlength="150" required value="<?php echo escapar($productoEditar['nombre_producto']); ?>"></label><label>Categoría<select name="categoria"><option value="componentes" <?php echo $productoEditar['categoria'] === 'componentes' ? 'selected' : ''; ?>>Componentes</option><option value="portatiles" <?php echo $productoEditar['categoria'] === 'portatiles' ? 'selected' : ''; ?>>Portátiles</option><option value="computadores" <?php echo $productoEditar['categoria'] === 'computadores' ? 'selected' : ''; ?>>Computadores</option></select></label><label>Precio<input type="number" name="precio" min="0" required value="<?php echo escapar($productoEditar['precio']); ?>"></label><label>Imagen (URL)<input type="url" name="imagen" maxlength="255" required value="<?php echo escapar($productoEditar['imagen']); ?>"></label><label>Marca<input type="text" name="marca" maxlength="50" required value="<?php echo escapar($productoEditar['marca']); ?>"></label><label>Calidad<select name="calidad"><option value="Economica" <?php echo $productoEditar['calidad'] === 'Economica' ? 'selected' : ''; ?>>Económica</option><option value="Media" <?php echo $productoEditar['calidad'] === 'Media' ? 'selected' : ''; ?>>Media</option><option value="Gama Alta" <?php echo $productoEditar['calidad'] === 'Gama Alta' ? 'selected' : ''; ?>>Gama Alta</option></select></label><label>Descripción corta<textarea name="descripcion_corta" maxlength="255" required><?php echo escapar($productoEditar['descripcion_corta']); ?></textarea></label><label>Descripción completa<textarea name="descripcion_completa" required><?php echo escapar($productoEditar['descripcion_completa']); ?></textarea></label><div class="admin-form-actions"><button type="submit" class="btn btn-solid">Guardar producto</button><?php if ($productoEditar['id_producto']): ?><a href="admin.php" class="btn btn-outline">Cancelar</a><?php endif; ?></div></form></section>
				<section class="admin-panel"><h2>Catálogo de productos</h2><form method="get" class="admin-form admin-filter-form admin-product-filter"><input type="hidden" name="seccion" value="productos"><label>Buscar por nombre o marca<input type="search" name="buscar_producto" value="<?php echo escapar($buscarProducto); ?>" placeholder="Buscar..."></label><label>Categoría<select name="categoria"><option value="">Todas</option><option value="componentes" <?php echo $categoriaProducto === 'componentes' ? 'selected' : ''; ?>>Componentes</option><option value="portatiles" <?php echo $categoriaProducto === 'portatiles' ? 'selected' : ''; ?>>Portátiles</option><option value="computadores" <?php echo $categoriaProducto === 'computadores' ? 'selected' : ''; ?>>Computadores</option></select></label><label>Precio máximo<input type="number" name="precio_max" min="0" value="<?php echo $precioMaximo; ?>"></label><button type="submit" class="btn btn-solid">Filtrar</button></form><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Imagen</th><th>Producto</th><th>Precio</th><th>Detalles</th><th>Acciones</th></tr></thead><tbody><?php foreach ($productos as $producto): ?><tr><td><img class="admin-thumb" src="<?php echo escapar($producto['imagen']); ?>" alt="<?php echo escapar($producto['nombre_producto']); ?>"></td><td><strong><?php echo escapar($producto['nombre_producto']); ?></strong><br><span class="admin-badge"><?php echo escapar($producto['categoria']); ?></span> <span class="admin-badge"><?php echo escapar($producto['marca']); ?></span></td><td>$<?php echo number_format((int) $producto['precio'], 0, ',', '.'); ?></td><td class="admin-description"><?php echo escapar($producto['descripcion_corta']); ?><br><small><?php echo escapar($producto['calidad']); ?></small></td><td><div class="admin-actions"><a class="btn btn-outline" href="admin.php?editar_producto=<?php echo (int) $producto['id_producto']; ?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este producto?');"><input type="hidden" name="csrf" value="<?php echo escapar($csrf); ?>"><input type="hidden" name="accion" value="eliminar_producto"><input type="hidden" name="seccion" value="productos"><input type="hidden" name="id_producto" value="<?php echo (int) $producto['id_producto']; ?>"><button class="btn admin-danger" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; ?><?php if (!$productos): ?><tr><td colspan="5">No hay productos que coincidan con el filtro.</td></tr><?php endif; ?></tbody></table></div></section>
			</div>
		<?php endif; ?>
	</main>
</body>
</html>
