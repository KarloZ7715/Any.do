# Any.do - Gestor de Tareas Profesional 📝

**Universidad Córdoba de Colombia** | Desarrollo Web II | 2025

Aplicación web moderna para gestión de tareas con características avanzadas: categorización, prioridades, subtareas, calendario interactivo y sincronización en tiempo real.

## Características

### Core

-   **CRUD de Tareas**: Crear, editar, eliminar y completar tareas
-   **Subtareas**: Desglosar tareas en pasos más pequeños
-   **Categorías**: Organizar tareas por categoría personalizada
-   **Prioridades**: Asignar niveles de prioridad (Alta, Media, Baja)
-   **Fechas y Horas**: Vencimiento con fecha específica e hora opcional

### Vistas

-   **Mi Calendario**: Vista de mes con todas las tareas
-   **Próximos 7 Días**: Timeline horizontal de próxima semana
-   **Todas mis Tareas**: Panel dual (lista + edición)
-   **Categorías**: Vista de todas las categorías

### UI/UX

-   **Dark/Light Mode**: Modo oscuro/claro con persistencia
-   **Tailwind CSS v4**: Diseño moderno y responsive
-   **Mobile Responsive**: Funcional en cualquier dispositivo
-   **Transiciones Suaves**: Animaciones fluidas
-   **Actualizaciones Optimistas**: UI sin esperar backend

### Technical

-   **Autenticación**: Email/contraseña
-   **Autorización**: Policies para proteger datos
-   **Búsqueda**: Full-text search con Laravel Scout
-   **Soft Deletes**: Tareas no se borran, se marcan como eliminadas
-   **Tests**: PHPUnit + SQLite in-memory

## Stack Tecnológico

### Backend

-   **Framework**: Laravel 12.35.1
-   **Base de Datos**: MySQL 8.2.12
-   **ORM**: Eloquent
-   **Validación**: Spatie Data (DTOs)
-   **Búsqueda**: Laravel Scout (Database driver)
-   **Autenticación**: Laravel Breeze + Inertia

### Frontend

-   **Bundler**: Vite
-   **Framework JS**: Vue 3 (Composition API)
-   **Routing**: Inertia.js (Hybrid SPA)
-   **Styling**: Tailwind CSS v4
-   **UI Components**: shadcn-vue
-   **State**: Pinia
-   **Validación**: Vee-Validate + Zod
-   **Drag & Drop**: @formkit/drag-and-drop

### DevTools

-   **PHP Static Analysis**: PHPStan level 5
-   **Linting**: ESLint + Prettier
-   **Testing**: PHPUnit + Vitest
-   **Version Control**: Git con commits organizados

## 📊 Estructura del Proyecto

```
app/
├── Http/Controllers/       # Coordinación HTTP
├── Services/               # Lógica de negocio
├── Repositories/           # Acceso a datos
├── Models/                 # Eloquent models
├── Policies/               # Autorización
└── Data/                   # Spatie Data DTOs

resources/js/
├── Pages/                  # Páginas Inertia
├── Components/             # Componentes Vue
├── Composables/            # Lógica reutilizable
├── Stores/                 # Pinia stores
└── Utils/                  # Helpers

database/
├── Migrations/             # Esquema BD
├── Seeders/                # Datos iniciales
└── Factories/              # Datos fake para tests

tests/
├── Unit/                   # Tests unitarios
├── Feature/                # Tests funcionales
└── JavaScript/             # Tests Vue
```

## 👤 Autores

-   **Carlos Canabal**
-   **Brayan Araujo**

## 📄 Licencia

Este proyecto es de uso educativo. Libre para usar, modificar y distribuir.
