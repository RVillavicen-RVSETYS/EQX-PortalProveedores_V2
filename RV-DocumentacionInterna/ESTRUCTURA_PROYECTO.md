# ESTRUCTURA DE PROYECTO BASE (PHP MVC)

Este documento describe una estructura de directorios estándar y un conjunto de convenciones para proyectos de desarrollo en PHP puro, siguiendo el patrón Modelo-Vista-Controlador (MVC).

## ESTRUCTURA DE DIRECTORIOS

- **/app**: Contiene el núcleo de la aplicación.
  - **/app/Controllers**: Clases que manejan la lógica de control. Reciben las peticiones del usuario, interactúan con los modelos y cargan las vistas correspondientes. Se pueden organizar en subdirectorios según el módulo (ej. `Usuarios/`, `Productos/`).

  - **/app/Globals**: Generalmente contiene codigo que se usa en mas de un area, (ej. Validación de ordenes de compras se usa del lado del proveedor y del lado del administrador).
    - ***/app/Globals/Controllers**: Controladores comunes.
    - ***/app/Globals/Functions**: Funciones que se usan en una o varias partes del sistema (ej. visualización de tickets).
    - ***/app/Globals/Services**: Dentro se almacenan Servicios o API's que conectan a otros servicios externos (ej. validación de Facturas).
  - **/app/Models**: Clases que representan la capa de datos. Se encargan de interactuar con la base de datos (consultas, inserciones, etc.). Generalmente, cada modelo corresponde a una tabla de la base de datos (ej. `UsuarioModel.php`).
  - **/app/Views**: La capa de presentación. Contiene plantillas (HTML/PHP) que renderizan la interfaz de usuario.
    - ***/app/Views/Layout***: Partes reutilizables de la plantilla, como la cabecera (`header.php`), el pie de página (`footer.php`) y el menú de navegación (`menu.php`).
    - ***/app/Views/pages***: Vistas específicas para cada módulo o controlador (ej. `usuarios/perfil.php`).
  - **/app/Core** (o **/app/Lib**): Clases de ayuda, lógica de negocio transversal o bibliotecas propias que son reutilizables en toda la aplicación (ej. `Validador.php`, `NotificadorEmail.php`).

- **/config**: Archivos de configuración de la aplicación.
  - `database.php`: Credenciales y configuración de la conexión a la base de datos.
  - `app.php`: Constantes y variables de configuración globales (entorno, rutas, etc.).

- **/core**: Contiene el framework base de la aplicación.
  - `App.php`: El enrutador principal (Front Controller). Analiza la URL y dirige cada petición al controlador y método adecuados.

- **/public**: El directorio raíz del servidor web (Document Root). Es el único directorio al que el usuario debe tener acceso directo.
  - `index.php`: El punto de entrada único para todas las peticiones. Inicializa la aplicación.
  - `.htaccess`: Reglas de reescritura para dirigir todo el tráfico a `index.php`.
  - **/assets**: Archivos estáticos como CSS, JavaScript, imágenes, fuentes, etc.

- **/storage** (o **/documentos**): Directorio para archivos subidos por los usuarios o generados por la aplicación. Debe tener permisos de escritura.
  - **/logs**  (o **/storage/logs**): Archivos de registro de la aplicación (`app.log`).

- **/documentacionInterna**: Documentación del proyecto (documentación de API, manuales, etc.).

- **/vendor**: Dependencias de terceros gestionadas por Composer.

## CONVENCIONES DE CÓDIGO

1.  **Nomenclatura**:
    - **Clases**: `PascalCase` (ej. `MiClaseController`).
    - **Métodos y funciones**: `camelCase` (ej. `miMetodo`).
    - **Variables**: `camelCase` o `snake_case` (ser consistente).
2.  **Estilo**: Seguir un estándar como PSR-12 para mantener la consistencia en el formato del código.
3.  **Comentarios**: Usar comentarios para explicar el "porqué" de un código complejo, o el "qué" hace.
4.  **Seguridad**:
    - Utilizar sentencias preparadas (prepared statements) para todas las consultas a la base de datos para prevenir inyección SQL.
    - Filtrar y validar todos los datos de entrada del usuario.
    - Escapar toda la salida que se renderiza en HTML para prevenir ataques XSS.