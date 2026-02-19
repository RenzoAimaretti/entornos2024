# entornos2024

Veterinaria San Antón 🐾
Sistema integral de gestión para clínicas veterinarias, desarrollado como proyecto para la cátedra de Entornos Gráficos. La plataforma permite la gestión de turnos, historiales médicos, internaciones y comunicación automatizada por correo electrónico.

URL del Proyecto: https://entornosgraficos.infinityfreeapp.com/

🏗️ Scaffolding del Proyecto
El proyecto sigue una estructura modular para facilitar el mantenimiento y la reutilización de componentes:

/ (Raíz): Contiene las vistas principales públicas (Inicio, Nosotros, Contacto) y el archivo de estilos global styles.css.

/shared: Componentes reutilizables de lógica y frontend (Navbar, Footer, Head unificado, Scripts) y la conexión a la base de datos db.php.

/vistaAdmin: Módulos exclusivos para la gestión total de la clínica (especialistas, clientes, mascotas y hospitalizaciones).

/vistaCliente: Interfaz para que los dueños de mascotas gestionen sus turnos y perfiles.

/vistaProfesional: Panel para veterinarios, enfocado en la atención diaria, historial médico y calendario de turnos.

👥 Roles de Usuario y Acceso
El sistema implementa un control de acceso basado en sesiones PHP para tres perfiles distintos:

Rol:
Administrador
Descripción:
Control total: altas de especialistas, gestión de internaciones y auditoría.
Credenciales de Acceso:
Email: admin@gmail.com
Pass: Admin123

Rol:
Especialista
Descripción:
Perfil médico: consulta de turnos asignados, carga de atenciones y pacientes.
Credenciales de Acceso:
Email: pro@gmail.com
Pass: Pro123

Rol:
Cliente
Descripción:
Usuario final: autogestión de turnos y visualización de sus mascotas.
Credenciales de Acceso:
Email: mateospertino@gmail.com
Pass: Mateo1

🛠️ Tecnologías y Librerías
Se utilizaron tecnologías estándar de la industria para garantizar un entorno web responsivo y funcional:

Backend y Base de Datos
PHP: Lógica del servidor y gestión de sesiones de usuario.

MySQL: Almacenamiento relacional de datos (Tablas de usuarios, mascotas, atenciones, etc.).

PHPMailer: Motor para el envío de correos electrónicos de confirmación y cancelación de turnos.

Frontend (UI/UX)
Bootstrap 4.5.2: Framework principal para el diseño responsivo.

FontAwesome 5.15.4: Iconografía técnica y de interfaz.

DataTables: Gestión avanzada de tablas con búsqueda y paginación en tiempo real.

FullCalendar 5.11.5: Interfaz de calendario para la visualización de turnos médicos.

SweetAlert2: Sistema de alertas interactivas para confirmaciones y errores.

jQuery 3.6.0: Manipulación del DOM y peticiones AJAX para carga dinámica de datos.

✨ Principales Funcionalidades
Autogestión de Turnos: Los clientes pueden solicitar turnos filtrando por profesional o servicio, con validación de horarios disponibles.

Gestión Médica: Los especialistas pueden registrar evoluciones médicas y consultar atenciones previas de cada paciente.

Sistema de Hospitalización: Módulo para el seguimiento de mascotas internadas, permitiendo el alta médica y actualización de estado.

Notificaciones Automáticas: Envío de correos electrónicos ante cambios en el estado de los turnos.

Seguridad de Datos: Validaciones en el lado del servidor para prevenir accesos no autorizados a través de parámetros URL y uso de LIMIT 1 en operaciones críticas de base de datos.
