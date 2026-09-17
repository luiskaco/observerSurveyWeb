# 05. Architecture Decisions & Patterns

## 1. Estructura de Directorios del Plugin

```text
wp-content/plugins/observatorio-survey/
├── observatorio-survey.php          # Entry point y cabeceras WP
├── includes/
│   ├── class-plugin.php             # Core bootstrap & hooks
│   ├── class-activator.php          # Migración DB y setup
│   ├── class-deactivator.php        # Cleanup opcional
│   ├── class-rest-controller.php    # Manejador de endpoints REST API
│   ├── class-database.php           # Capa de abstracción para queries SQL
│   ├── class-admin-page.php         # Interfaz en WP Admin + exportador
│   └── class-shortcode.php          # Renderizado del shortcode frontend
├── templates/
│   ├── survey-container.php         # Template principal del formulario
│   ├── steps/                       # Subplantillas por paso (modular)
│   │   ├── step-1-antecedentes.php
│   │   └── ...
│   └── admin-dashboard.php          # Template del panel admin
├── assets/
│   ├── css/
│   │   ├── survey-frontend.css      # Estilos optimizados y variables CSS
│   │   └── survey-admin.css
│   └── js/
│       ├── survey-wizard.js         # Lógica multi-step, validaciones y envío
│       └── survey-admin.js
└── languages/
```

## 2. Decisiones Clave
1. **Tabla dedicada vs Custom Post Type:** Se utiliza una tabla SQL dedicada (`wp_obs_survey_submissions`) para evitar sobrecargar `wp_posts` y `wp_postmeta` con cientos de campos JSON y permitir exportaciones analíticas ultrarrápidas.
2. **REST API Nativo:** En vez de `admin-ajax.php`, se usan endpoints de la `WP REST API` para mejor rendimiento, control de headers y caché.
3. **Vanilla JS sin dependencias:** Cero jQuery o React en el frontend para mantener el tamaño del bundle por debajo de 25KB y velocidad de carga instantánea.
