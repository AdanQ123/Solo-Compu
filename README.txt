===========================================================
INSTRUCCIONES PARA MONTAR EL PROYECTO "SOLO COMPU" EN XAMPP
===========================================================

Este proyecto está diseñado para funcionar en un entorno de desarrollo PHP local (como XAMPP, WampServer o Laragon) con una base de datos MySQL.

REQUISITOS PREVIOS:
1. Tener instalado XAMPP (u otro servidor web local con PHP 7.4 o superior y MySQL/MariaDB).
2. Tener el panel de control de XAMPP encendido, con los módulos Apache y MySQL iniciados ("Start").

PASOS PARA EL MONTAJE:

PASO 1: COPÍAR LOS ARCHIVOS AL DIRECTORIO WEB (HTDOCS)
1. Copia toda la carpeta "solo_compu_php" (que contiene index.php, login.php, registro.php, db.sql, la carpeta "css", etc.).
2. Pégala dentro de la carpeta "htdocs" de tu instalación de XAMPP.
   - Ruta típica en Windows: C:\xampp\htdocs\solo_compu_php
   - Ruta típica en MacOS: /Applications/XAMPP/xamppfiles/htdocs/solo_compu_php

PASO 2: CREAR E IMPORTAR LA BASE DE DATOS EN MYSQL
1. Abre tu navegador web e ingresa a phpMyAdmin: http://localhost/phpmyadmin
2. Haz clic en la pestaña "Bases de datos" (Database) en la parte superior.
3. En el campo "Crear base de datos", escribe el nombre: solocompu_db
4. Selecciona el cotejamiento "utf8mb4_unicode_ci" y haz clic en "Crear".
5. Una vez creada la base de datos, selecciónala en el menú de la izquierda.
6. Haz clic en la pestaña "Importar" (Import) en la parte superior.
7. Presiona el botón "Seleccionar archivo" (Choose File) y busca el archivo "db.sql" que está dentro de tu carpeta "solo_compu_php".
8. Baja hasta el final de la página y haz clic en el botón "Importar" o "Continuar" (Go).
9. ¡Listo! Verás que se crearon las tablas "usuarios" y "productos" cargadas con los datos de prueba.

PASO 3: PROBAR LA APLICACIÓN EN TU NAVEGADOR
1. En tu navegador web favorito, escribe la siguiente dirección URL:
   http://localhost/solo_compu_php
2. ¡Felicidades! Ya puedes navegar por la tienda de hardware "Solo Compu", usar la barra de filtros dinámica, el slider de volumen para limitar precios, ingresar a ver los detalles de los componentes y registrar nuevos usuarios de prueba.

NOTAS DE CONFIGURACIÓN (SI ES NECESARIO):
- Si cambiaste las credenciales de tu base de datos local (por ejemplo, si le pusiste contraseña al usuario 'root' de MySQL o si tu servidor corre en un puerto diferente), puedes ajustar los parámetros de conexión fácilmente en el archivo:
  solo_compu_php/config/conexion.php

¡Mucho éxito en la entrega de tu proyecto de Media Técnica en Desarrollo de Software!
===========================================================
