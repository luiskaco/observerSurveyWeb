# 08. Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.3.2] - 2026-09-25

### Changed
- Actualización de título general a **"LA RUTA DE LA PACIENTE CON CÁNCER DE MAMA"** en el template público, encabezados de administración y documentación.
- Incremento de versión a `1.3.2` para despliegue y reemplazo en hosting.

## [1.3.1] - 2026-09-24

### Fixed
- **Endpoint REST Público:** Configurado `permission_callback` para permitir envíos públicos sin bloqueo por caducidad de nonces `wp_rest` en páginas con caché o usuarios anónimos, protegido por Honeypot anti-spam (`hp_field`).
- **JavaScript Wizard (`survey-wizard.js`):** Corrección del scope de la función `toTitleCase` que impedía el submit, URL de endpoint inyectada directamente desde `wp_localize_script`, y validación dinámica de preguntas condicionales ignorando bloques ocultos con scroll automático hacia errores.
- **Plantilla HTML (`survey-container.php`):** Ocultamiento estático y clase `.obs-conditional-block` en subpreguntas condicionales Q23 y Q27.


### Added
- **Formateo de Fecha de Registro:** La columna "Fecha Registro" ahora se formatea explícitamente en `DD/MM/YYYY HH:MM:SS` (ej. `24/09/2026 05:07:22`), evitando que Google Sheets la interprete como un número de serie decimal (`46285.77549`).

### Changed
- Actualización de versión del plugin a `1.3.0` para refresco automático de assets en producción / hosting.


## [1.2.0] - 2026-09-23

### Added
- Formateo automático de nombres y apellidos en Title Case (Mayúscula inicial) con soporte para caracteres UTF-8 (tildes y ñ).
- Formateo de número de teléfono/celular en bloques de 3 dígitos (`333 333 333`) en la interfaz (máscara en vivo), base de datos, Google Sheets y exportación CSV.

## [1.1.0] - 2026-09-18

### Added
- Integración nativa con Google Sheets API v4 mediante Service Account OAuth2 JWT (`Google_Sheets`).
- Submenú y vista de ajustes de Google Sheets en WP Admin (`observatorio-survey-gsheets`).
- Botón interactivo de prueba de conexión AJAX y sincronizador en lote para registros pendientes.
- Soporte para creación automática de cabeceras en Google Sheets.
- Sincronización en tiempo real al registrar respuestas en el endpoint REST `/survey/submit`.
- Columnas `synced_to_sheets` y `synced_at` en tabla SQL `wp_obs_survey_submissions`.

## [1.0.0] - 2026-09-18

### Added
- Estructura de documentación del proyecto en `/docs` (PRD, SDD, Especificaciones, Modelo de Datos, Arquitectura, Backlog, ADRs y Changelog).
- Formulario multi-paso responsive con shortcode `[encuesta_cancer_mama]`.
- Panel de administración y exportador de datos a formato CSV.

