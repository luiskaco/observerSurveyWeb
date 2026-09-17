# Observatorio Survey — El Viaje de la Paciente con Cáncer de Mama

Plugin nativo y modular de WordPress desarrollado para el **Observatorio de Por Un Perú Sin Cáncer**, diseñado para recopilar y analizar los tiempos de espera y barreras en la atención oncológica en el Perú.

## 📁 Estructura del Plugin
```text
observatorio-survey/
├── observatorio-survey.php     # Bootstrap principal y cabeceras del plugin
├── docs/                       # Documentación técnica completa
│   ├── 01_PROJECT_PRD.md       # Requisitos de producto
│   ├── 02_SDD.md               # Diseño del sistema y arquitectura
│   ├── 03_SYSTEM_SPEC.md       # Especificaciones de endpoints y stack
│   ├── 04_DATA_MODEL.md        # Esquema SQL de la tabla de respuestas
│   ├── 05_ARCHITECTURE.md      # Estructura modular del código
│   ├── 06_TASKS.md             # Backlog de tareas
│   ├── 07_DECISIONS.md         # ADRs (Decisiones arquitectónicas)
│   └── 08_CHANGELOG.md         # Registro de versiones
├── includes/
│   ├── class-activator.php     # Creación de tabla SQL al activar
│   ├── class-deactivator.php   # Hooks de desactivación
│   ├── class-plugin.php        # Cargador central y registro de hooks
│   ├── class-rest-controller.php # Endpoint REST API (/survey/submit)
│   └── class-admin-page.php    # Panel Admin, detalle de respuestas y exportación CSV
├── templates/
│   └── survey-container.php    # Template HTML del cuestionario multi-paso
└── assets/
    ├── css/survey-frontend.css # Estilos personalizados (morado #381e72 y acento #d81b60)
    └── js/survey-wizard.js     # Lógica interactiva del wizard, validación y REST API
```

## 🛠️ Instalación y Uso
1. Colocar esta carpeta en `wp-content/plugins/observatorio-survey/`.
2. Activar el plugin desde **Plugins > Plugins instalados** en WordPress.
3. Insertar el shortcode:
   ```text
   [encuesta_cancer_mama]
   ```
4. Ver registros y exportar a CSV en **WP Admin > Encuestas Cáncer**.
