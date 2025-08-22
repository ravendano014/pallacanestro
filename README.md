# CRUD de Equipos - Pallacanestro

Este proyecto contiene un sistema CRUD completo para la gestión de equipos de baloncesto usando PHP, MySQL, y una interfaz estilo Metro UI.

## Estructura de Archivos

```
pallacanestro/
├── Connections/
│   └── Connection.php          # Archivo de conexión a la base de datos
├── assets/
│   ├── css/
│   │   └── metro.css          # Framework CSS estilo Metro UI
│   └── js/
│       └── metro.js           # Framework JavaScript con funcionalidades CRUD
├── teams_crud.php             # Página principal del CRUD de equipos
└── README.md                  # Este archivo
```

## Configuración

### 1. Base de Datos
Asegúrate de que tu base de datos MySQL tenga la tabla `Teams` con la siguiente estructura:

```sql
CREATE TABLE Teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50),
    coach VARCHAR(100),
    founded_year INT,
    logo_url VARCHAR(255),
    description TEXT
);
```

### 2. Configuración de Conexión
Edita el archivo `Connections/Connection.php` y ajusta los parámetros de conexión:

```php
$servername = "localhost";      // Tu servidor MySQL
$username = "tu_usuario";       // Tu usuario MySQL
$password = "tu_contraseña";    // Tu contraseña MySQL
$database = "nombre_bd";        // Nombre de tu base de datos
```

### 3. Servidor Web
Coloca los archivos en tu servidor web (XAMPP, WAMP, LAMP, etc.) y accede a:
```
http://localhost/pallacanestro/teams_crud.php
```

## Funcionalidades

### ✅ Crear Equipos
- Formulario modal con validación en tiempo real
- Campos: Nombre (*requerido), Ciudad, Entrenador, Año de Fundación, URL del Logo, Descripción
- Validación del lado cliente y servidor

### ✅ Leer/Listar Equipos
- Tabla responsive con información de todos los equipos
- Muestra logos de equipos (si están disponibles)
- Iconos automáticos para equipos sin logo (iniciales del nombre)

### ✅ Actualizar Equipos
- Edición en modal con formulario pre-rellenado
- Mismas validaciones que en creación
- Confirmación de guardado exitoso

### ✅ Eliminar Equipos
- Botón de eliminación con confirmación
- Eliminación vía AJAX sin recargar página
- Mensaje de confirmación antes de eliminar

## Características Técnicas

### Framework Metro UI
- Diseño moderno inspirado en Microsoft's Metro UI
- Responsive design para móviles y tablets
- Colores y tipografía consistente con el estilo Metro
- Animaciones suaves y transiciones

### JavaScript/AJAX
- Operaciones sin recargar página
- Validación en tiempo real de formularios
- Manejo de errores y mensajes de éxito
- Modales interactivos

### PHP/MySQL
- Conexión segura usando PDO
- Prepared statements para prevenir SQL injection
- Manejo de errores robusto
- Separación de lógica y presentación

## Uso del Sistema

### Agregar un Nuevo Equipo
1. Haz clic en el botón "+" Nuevo Equipo
2. Llena el formulario (el nombre es obligatorio)
3. Haz clic en "Guardar Equipo"
4. El equipo aparecerá en la lista automáticamente

### Editar un Equipo
1. Haz clic en el botón "✏️ Editar" del equipo deseado
2. Modifica los datos en el formulario
3. Haz clic en "Guardar Equipo"
4. Los cambios se reflejarán inmediatamente

### Eliminar un Equipo
1. Haz clic en el botón "🗑️ Eliminar" del equipo deseado
2. Confirma la eliminación en el diálogo
3. El equipo será removido de la lista

## Personalización

### Colores y Tema
Puedes modificar los colores del tema editando las variables CSS en `assets/css/metro.css`:

```css
:root {
    --primary-color: #0078d4;    /* Color principal */
    --secondary-color: #106ebe;  /* Color secundario */
    --success-color: #107c10;    /* Color de éxito */
    --warning-color: #ff8c00;    /* Color de advertencia */
    --danger-color: #d13438;     /* Color de peligro */
}
```

### Campos de la Tabla
Para agregar más campos a la tabla Teams, necesitarás:

1. Modificar la estructura de la base de datos
2. Actualizar las funciones PHP en `teams_crud.php`
3. Agregar los campos al formulario HTML
4. Actualizar las columnas de la tabla de visualización

## Seguridad

- **SQL Injection**: Protegido usando prepared statements
- **XSS**: Todos los datos son escapados con `htmlspecialchars()`
- **CSRF**: Considera agregar tokens CSRF para mayor seguridad
- **Validación**: Validación tanto del lado cliente como servidor

## Compatibilidad

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Navegadores**: Chrome, Firefox, Safari, Edge (versiones recientes)
- **Dispositivos**: Compatible con móviles y tablets

## Solución de Problemas

### Error de Conexión a la Base de Datos
- Verifica los parámetros de conexión en `Connection.php`
- Asegúrate de que el servidor MySQL esté ejecutándose
- Confirma que el usuario tenga permisos en la base de datos

### Los Estilos No Se Cargan
- Verifica que la ruta a `assets/css/metro.css` sea correcta
- Comprueba los permisos de los archivos CSS

### Las Funciones JavaScript No Funcionan
- Abre la consola del navegador para ver errores
- Verifica que `assets/js/metro.js` se cargue correctamente
- Asegúrate de que no haya conflictos con otras librerías JavaScript

## Expansión del Sistema

Este CRUD de equipos puede ser expandido fácilmente para incluir:
- Gestión de jugadores
- Gestión de torneos
- Estadísticas de equipos
- Sistema de autenticación
- Carga de imágenes para logos
- Exportación a PDF/Excel
- Búsqueda y filtros avanzados

---

**Desarrollado con ❤️ para la gestión de equipos de baloncesto**
