# Veterinaria San Antón 🐾

Sistema integral de gestión para clínicas veterinarias, desarrollado como proyecto para la cátedra de **Entornos Gráficos**. La plataforma permite la gestión de turnos, historiales médicos, internaciones y comunicación automatizada por correo electrónico.

**🔗 URL del Proyecto:** [https://entornosgraficos.infinityfreeapp.com/](https://entornosgraficos.infinityfreeapp.com/)

---

## 🏗️ Scaffolding del Proyecto

El proyecto sigue una estructura modular para facilitar el mantenimiento y la reutilización de componentes:

- **`/` (Raíz):** Contiene las vistas principales públicas (`index.php`, `nosotros.php`, `contactanos.php`, `profesionales.php`) y el archivo de estilos global `styles.css`.
- **`/shared`:** Componentes reutilizables de lógica y frontend (Navbar, Footer, Head unificado, Scripts comunes) y la conexión centralizada a la base de datos `db.php`.
- **`/vistaAdmin`:** Módulos exclusivos para la administración total de la clínica: gestión de especialistas, clientes, mascotas y control de hospitalizaciones.
- **`/vistaCliente`:** Interfaz privada para dueños de mascotas donde pueden gestionar sus perfiles y turnos.
- **`/vistaProfesional`:** Panel dedicado a veterinarios, enfocado en la atención médica diaria, carga de historias clínicas y visualización de agenda.

---

## 👥 Roles de Usuario y Acceso

El sistema implementa un robusto control de acceso basado en **sesiones PHP** para tres perfiles distintos:

| Rol               | Descripción                                                                      | Credenciales de Acceso                                      |
| :---------------- | :------------------------------------------------------------------------------- | :---------------------------------------------------------- |
| **Administrador** | Control total del sistema, auditoría de datos y gestión de personal médico.      | **User:** `admin@gmail.com` <br> **Pass:** `Admin123`       |
| **Especialista**  | Perfil médico: consulta de agenda, carga de evoluciones y atención de pacientes. | **User:** `pro@gmail.com` <br> **Pass:** `Pro123`           |
| **Cliente**       | Usuario final: registro de mascotas y autogestión de turnos médicos.             | **User:** `mateospertino@gmail.com` <br> **Pass:** `Mateo1` |

---

## 🛠️ Tecnologías y Librerías

Se utilizaron tecnologías estándar de la industria para garantizar un entorno web robusto y responsivo:

### **Backend y Base de Datos**

- **PHP:** Lógica del lado del servidor y gestión de sesiones.
- **MySQL:** Almacenamiento relacional (Usuarios, Mascotas, Atenciones, Horarios).
- **PHPMailer:** Motor para el envío automatizado de correos electrónicos de confirmación y cancelación.

### **Frontend (UI/UX)**

- **Bootstrap 4.5.2:** Framework para el diseño responsivo y componentes de interfaz.
- **FontAwesome 5.15.4:** Iconografía técnica.
- **DataTables:** Gestión avanzada de tablas con búsqueda, filtrado y paginación.
- **FullCalendar 5.11.5:** Calendario interactivo para la visualización de turnos.
- **SweetAlert2:** Librería para ventanas emergentes y confirmaciones estéticas.
- **jQuery 3.6.0:** Manipulación del DOM y peticiones AJAX para carga dinámica de datos.

---

## ✨ Principales Funcionalidades

- **📅 Autogestión de Turnos:** Selección inteligente de turnos filtrando por profesional o servicio con validación de disponibilidad en tiempo real.
- **🩺 Gestión Médica:** Registro detallado de evoluciones médicas y acceso rápido al historial clínico de los pacientes.
- **🏥 Sistema de Hospitalización:** Control de internaciones, permitiendo el seguimiento de estados y la gestión de altas médicas.
- **📧 Notificaciones Automáticas:** Comunicación inmediata vía email al cliente ante cualquier cambio o cancelación en su turno.
- **🔒 Seguridad de Datos:**
  - Validaciones _Server-side_ para prevenir accesos no autorizados mediante manipulación de parámetros URL.
  - Implementación de `LIMIT 1` y saneamiento de datos en operaciones críticas (Delete/Update) para garantizar la integridad de la base de datos.

---

© 2026 Veterinaria San Antón - Proyecto Académico de Entornos Gráficos.
