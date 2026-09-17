# 07. Architecture Decision Records (ADRs)

## ADR-001: Creación de Plugin Personalizado para Encuesta Oncológica

### Estado
Aceptado

### Contexto
El sitio web utilizaba formularios en Elementor, los cuales presentaban limitaciones de rendimiento, rigidez para flujos multi-pasos con validación compleja, dificultad para almacenar y estructurar respuestas clínicas en base de datos y falta de un módulo nativo de exportación analítica.

### Decisión
Desarrollar un plugin nativo de WordPress (`observatorio-survey`) con:
1. Arquitectura OOP en PHP 8.1+.
2. Tabla SQL dedicada para capturar respuestas sin sobrecargar `wp_posts`/`wp_postmeta`.
3. Frontend ultra liviano en Vanilla JS / CSS nativo con shortcode `[encuesta_cancer_mama]`.
4. Endpoint REST API con validación de datos y protección Nonce/Honeypot.

### Consecuencias
- **Positivas:** Carga rápida (< 300ms), control total del diseño, fácil mantenimiento, seguridad reforzada, exportación limpia a CSV.
- **Negativas:** Requiere mantenimiento del código del plugin en lugar de editar mediante interfaz visual de Elementor.
