# 08. Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

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

