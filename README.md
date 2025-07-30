*Proyecto final para Entornos Gráficos 2025*

**Sistema web PHP/PostgreSQL de gestión de shopping center con autenticación multi-rol, sistema de promociones categorizadas, y panel administrativo para gestión de locales y contenido.**

## 🏢 Descripción del Proyecto

Este es un sistema web de gestión de Centro Comercial desarrollado en PHP con PostgreSQL que permite a administradores gestionar locales y dueños, a dueños de locales crear y administrar promociones, y a clientes registrados buscar y usar descuentos según su categoría de membresía (inicial, medium, premium), incluyendo funcionalidades de autenticación, recuperación de contraseña por email, sistema de novedades y aprobación de contenido.

## 🛠️ Stack Tecnológico

- **Backend**: PHP 8.2
- **Base de Datos**: PostgreSQL
- **Frontend**: Bootstrap 5.3, HTML5, CSS3, JavaScript
- **Email**: PHPMailer
- **Servidor**: PHP Built-in Server / Apache

## 👥 Roles del Sistema

### 🔧 Administrador
- Gestión completa de locales y dueños
- Aprobación/rechazo de solicitudes de dueños
- Administración de novedades del shopping
- Supervisión de promociones

### 🏪 Dueño de Local
- Creación y gestión de promociones
- Administración de su local asignado
- Aprobación de uso de promociones por clientes

### 🛍️ Cliente
- Navegación y búsqueda de promociones
- Sistema de categorías progresivas (inicial → medium → premium)
- Uso de descuentos según nivel de membresía
- Acceso a novedades del shopping

## ✨ Características Principales

- **Autenticación completa** con registro, login y recuperación de contraseña
- **Sistema de tokens** para confirmación por email
- **Categorización de clientes** con acceso diferenciado a promociones
- **Panel administrativo** para gestión integral
- **Interfaz responsive** con Bootstrap
- **Validación de formularios** tanto client-side como server-side
- **Sistema de notificaciones** por email

## 📁 Estructura del Proyecto

```
📦 EntornosGraficos/
├── 📂 config/           # Configuración de BD y email
├── 📂 public/           # Archivos públicos y páginas principales
│   ├── 📂 css/         # Estilos CSS personalizados
│   ├── 📂 js/          # Scripts JavaScript
│   └── 📂 layout/      # Templates reutilizables
├── 📂 src/             # Código fuente organizado (MVC)
└── 📂 vendor/          # Dependencias de Composer
```

## 🚀 Instalación y Configuración

### 💻 Desarrollo Local
1. **Clonar el repositorio**
2. **Instalar dependencias**: `composer install`
3. **Configurar base de datos** en `config/db.php`
4. **Configurar email** en `config/mail.php`

#### 🧪 Testing con XAMPP/Apache
Durante el desarrollo, el testing se realizó utilizando **XAMPP** con servidor **Apache** para simular un entorno de producción local antes del deploy a Render.com.

### 🐳 Deploy en Producción (Render.com)
El proyecto está configurado para deploy automático en **Render.com** utilizando **Docker**:

- **Dockerfile**: Configurado con PHP 8.2 y extensiones PostgreSQL
- **render.yaml**: Configuración de servicios y variables de entorno
- **Base de datos**: PostgreSQL gestionada por Render
- **Deploy automático**: Conectado al repositorio Git para CI/CD

#### Variables de entorno en Render:
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- Configuración automática desde la base de datos PostgreSQL de Render

---


