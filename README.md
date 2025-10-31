# HostTrack
### Airbnb Property Management System

**HostTrack** es un sistema de gestión de propiedades inspirado en Airbnb, que permite administrar propiedades, propietarios, huéspedes, reservas, pagos, limpiezas y usuarios.  
El sistema incluye control de roles, reportes exportables y registro de actividades.

---

## Tecnologías utilizadas

- PHP 8  
- MySQL / phpMyAdmin  
- HTML, CSS, JavaScript  
- FPDF (exportación a PDF)

---

## Estructura del proyecto

```
APMS/
│
├── config/
│   └── config.php
│
├── backend/
│   ├── models/
│   ├── controllers/
│   └── helpers/
│
├── frontend/
│   ├── login.php
│   ├── dashboard.php
│   ├── users/
│   ├── owners/
│   ├── properties/
│   ├── guests/
│   ├── reservations/
│   ├── payments/
│   ├── cleanings/
│   ├── cleaning_responsibles/
│   └── reports/
│
└── vendor/
```

---

## Roles del sistema

| Rol | Descripción | Permisos |
|------|-------------|-----------|
| **Administrador (1)** | Control total del sistema | Incluye gestión de usuarios |
| **Staff (2)** | Personal administrativo | Todas las funciones excepto usuarios |
| **Cleaner (3)** | Personal de limpieza | Solo puede registrar y ver limpiezas propias |

---

## Funcionalidades principales

- CRUD completo para todas las entidades principales  
- Validaciones de negocio (reservas sin solapamientos, documentos únicos, etc.)  
- Sistema de login con control de sesiones y roles  
- Registro de actividades (logs automáticos)  
- Exportación de reportes en PDF y Excel  
- Eliminación lógica para mantener historial  
- Dashboard dinámico según tipo de usuario  

---

## Requisitos

- PHP 8 o superior  
- MySQL 5.7 o superior  
- XAMPP (o entorno local equivalente)

---

## Instalación

1. Copiar el proyecto en la carpeta del servidor local (`htdocs` si usas XAMPP).  
2. Crear una base de datos llamada **apms** en phpMyAdmin.  
3. Importar el archivo `.sql` del proyecto.  
4. Configurar la conexión en `config/config.php`.  
5. Iniciar Apache y MySQL.  
6. Abrir en el navegador:
   ```
   http://localhost/APMS/frontend/login.php
   ```

---

## Usuario administrador inicial

```
Usuario: admin
Contraseña: 123456
```

---

## Reportes disponibles

- Propiedades  
- Reservas  
- Pagos  
- Limpiezas  
- Registro de actividades (solo administrador)

---

## Autor

Proyecto desarrollado con fines académicos como sistema de gestión de propiedades tipo Airbnb.  
Jeronimo Escudero Cuartas.
© 2025 **HostTrack**

