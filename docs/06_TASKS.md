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

## Fase 4: Panel Admin, Exportación y Google Sheets
- [x] Crear menú y panel en WP Admin para listar respuestas.
- [x] Implementar exportador de datos en formato CSV/Excel.
- [x] Integración nativa con Google Sheets API v4 (Service Account OAuth2 JWT).
- [x] Panel de configuración de Google Sheets con prueba de conexión en WP Admin.
## Fase 5: Mejoras de Formato de Datos
- [x] Formateo automático de Nombres y Apellidos en Title Case (Mayúscula inicial) con soporte UTF-8.
- [x] Formateo de número de teléfono/celular separado en bloques de 3 dígitos (ej: 333 333 333) en Base de Datos, Google Sheets y CSV.

## Fase 6: Corrección de Guardado y Sincronización
- [x] fix: Resolver fallo de guardado en base de datos y envío a Google Sheets — causa: Restricción estricta de nonce wp_rest en endpoints públicos, URL REST inconsistente con query params y bloques condicionales con atributos required no ocultos inicialmente en el frontend.

## Fase 7: Reordenamiento de Flujo UX
- [x] Trasladar el paso de Antecedentes y Consentimiento al Paso 6 (Final) para mejorar la conversión del formulario, manteniendo la persistencia e integridad de datos en BD y Google Sheets/CSV.

## Fase 8: Reversión de Flujo UX
- [x] Revertir Antecedentes como Paso 1 y Tratamiento + Consentimiento como Paso 6.

## Fase 9: Captura Progresiva en Tiempo Real y Doble Pestaña Google Sheets
- [x] Base de Datos: Agregar columna `last_step_reached` a la tabla `wp_obs_survey_submissions` con migración runtime no destructiva.
- [x] Backend / REST API: Crear endpoint `/survey/step-save` para guardado progresivo asíncrono y actualizar `/survey/submit` para asociar `submission_id`.
- [x] Google Sheets: Integrar soporte de dos pestañas (`Completo` e `Incompleto`), con upsert por ID en `Incompleto` y registro en `Completo` al finalizar.
- [x] Frontend: Implementar guardado en segundo plano al hacer clic en "SIGUIENTE", persistiendo el `submission_id` en localStorage.
- [x] Panel WP Admin: Filtros visuales por estado (Todas / Completas / Incompletas), badges de paso alcanzado y exportación segmentada a CSV.

## Fase 10: Micro-Checkpoints Motivacionales y Celebración UX
- [x] Implementar overlay/modal interactivo de transición de pasos (`survey-container.php`).
- [x] Lógica de control en `survey-wizard.js`: auto-avance 3.2s, botón de salto inmediato, omitir en retroceso, iconos SVG animados.
- [x] Estilos CSS responsive, paleta oficial (#381e72 / #d81b60), micro-animaciones y barra de cuenta regresiva (`survey-frontend.css`).
- [x] Pantalla final de agradecimiento con animación de confeti suave y botón para compartir en WhatsApp.
