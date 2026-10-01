# 08. Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.7.1] - 2026-10-01

### Fixed
- **Lógica Condicional de Selección de Regiones en Paso 2:** Se aseguró el evento `change` e `input` con lector agnóstico (`getQ1Value`) sobre `<select id="q1_diag_place">`, garantizando la apertura instantánea del bloque de derivación (`#block-region-derivacion`) al elegir cualquier región distinta a *"Lima"*.

## [1.7.0] - 2026-10-01

### Changed
- **Selección Completa de Regiones en Pregunta 1 (Paso 2):** Se reemplazó el radio button genérico *"Otra región del Perú"* por un selector desplegable (`<select>`) con todas las regiones/departamentos del Perú (Amazonas, Áncash, Arequipa, Cusco, Piura, etc., y opción extranjero).
- **Lógica Condicional Dinámica:** La pregunta 1 mantiene la condición para Lima: si se selecciona *"Lima"*, las preguntas de derivación a Lima (Q2 a Q4) permanecen ocultas; si se selecciona cualquier otra región del Perú o extranjero, se despliega automáticamente el bloque de preguntas de derivación.

## [1.6.2] - 2026-09-28

### Fixed
- **Escape HTML en Respuestas No Contestadas:** Se corrigió el renderizado de la etiqueta `<em>` en la vista de detalle de encuestas del panel de administración (`class-admin-page.php`), mostrando el texto en cursiva atenuada en lugar del código HTML literal.

## [1.6.1] - 2026-09-28

### Fixed
- **Limpieza Automática de Pestaña `Incompleto`:** Al completar el Paso 6, el sistema ahora busca y elimina automáticamente la fila correspondiente en la pestaña `Incompleto` de Google Sheets (`delete_row_from_sheet`), evitando registros duplicados entre ambas pestañas.

## [1.6.0] - 2026-09-28

### Added
- **Captura Progresiva en Tiempo Real (Autosave Multi-Paso):** Guardado asíncrono en segundo plano tras validar y avanzar cada paso del wizard sin congelar ni demorar la navegación del usuario.
- **Doble Pestaña en Google Sheets (`Completo` e `Incompleto`):**
  - Respuestas parciales/abandonadas en los Pasos 1 a 5 se guardan o actualizan automáticamente (upsert por ID) en la pestaña `Incompleto`.
  - Respuestas finalizadas en el Paso 6 se registran de forma definitiva en la pestaña `Completo`.
- **Nuevo Endpoint REST `/survey/step-save`:** Permite almacenar de forma incremental los datos de contacto y respuestas por paso vinculados al `submission_id`.
- **Migración de Base de Datos:** Nueva columna `last_step_reached` (TINYINT) para rastrear el último paso alcanzado por el usuario.
- **Filtros y Métricas en WP Admin:**
  - Vistas filtradas por estado: `Todas`, `Completadas`, `Incompletas / En Progreso`.
  - Badges visuales identificando el paso donde el usuario abandonó la encuesta (ej. `⏳ Paso 3 / 6` vs `✓ Completa`).
  - Exportación de CSV segmentada por estado con columnas de `Estado` y `Último Paso`.

## [1.5.0] - 2026-09-28

### Changed
- **Restauración del Flujo Original del Wizard:** La sección de **Antecedentes y Perfil General** (Nombres, Apellidos, Edad, Celular, Correo, Región, etc.) vuelve a posicionarse en el **Paso 1 (Inicio)**, y la sección de **Tratamiento y Experiencia** junto al **Consentimiento y Envío** vuelve al **Paso 6 (Final)**.

## [1.4.0] - 2026-09-25

### Changed
- **Reordenamiento del Wizard Multi-Paso:** Se trasladó la sección de Antecedentes y Datos de Contacto (Nombres, Apellidos, Edad, Celular, Correo, Región, Paciente actual, Sistema de Salud y Consentimiento) al **Paso 6 (Paso Final)** para optimizar la tasa de completitud, iniciando directamente con las preguntas temáticas sobre el recorrido oncológico (Paso 1: Lugar de Diagnóstico y Derivación).
- La estructura, orden de columnas y compatibilidad con Base de Datos, Google Sheets y exportación CSV se mantiene 100% intacta.

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

