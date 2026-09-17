# 06. Tasks & Backlog

## Fase 1: Arquitectura, Documentación y Setup Inicial
- [x] Configurar estructura `/docs` con estándares del proyecto.
- [ ] Inicializar repositorio Git y conectar con GitHub remoto cuando se proporcione la URL.
- [x] Crear estructura base del plugin `wp-content/plugins/observatorio-survey/`.

## Fase 2: Base de Datos y Backend
- [x] Crear clase de activación `class-activator.php` con creación de tabla `wp_obs_survey_submissions`.
- [x] Implementar `class-rest-controller.php` para validación de datos, nonces y guardado de respuestas.
- [x] Implementar `class-shortcode.php` / bootstrap para renderizar el contenedor y pasos del formulario.

## Fase 3: Frontend y UX Multi-Paso
- [x] Diseñar interfaz responsive mobile-first siguiendo la estética del Observatorio (morado #381e72, magenta #d81b60).
- [x] Implementar `survey-wizard.js` (navegación multi-step, validación por campo/sección, barra de progreso).
- [x] Implementar Sección 1 a 6 con las 38 preguntas completas y lógica condicional.

## Fase 4: Panel Admin y Exportación
- [x] Crear menú y panel en WP Admin para listar respuestas.
- [x] Implementar exportador de datos en formato CSV/Excel.
- [ ] Pruebas end-to-end de envío, validación y seguridad.
