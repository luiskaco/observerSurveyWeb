# 08. Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [2.1.0] - 2026-10-20

### Removed
- **Ocultamiento de Perfil 5 (Consentimiento Informado) en Visor (`observerMetricsWeb` v1.5.3):**
  - Se filtró la pregunta de consentimiento (`col === 'L'`) en la función `renderDashboard()`, ocultando su tarjeta gráfica y enlace en el índice lateral.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.5.3`.

## [2.0.9] - 2026-10-20

### Removed
- **Eliminación de Badges en Encabezado Hero (`observerMetricsWeb` v1.5.2):**
  - Se retiraron las pastillas/badges superiores del bloque Hero (*Observatorio de Cáncer de Mama* y *Estudio de la Ruta del Paciente*), dejando el título principal como elemento dominante inmediato.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.5.2`.

## [2.0.8] - 2026-10-20

### Changed
- **Modernización Visual y Coherencia en Bloque Hero y Tarjetas KPI (`observerMetricsWeb` v1.5.1):**
  - Incorporados badges oficiales de contexto en el encabezado (*Observatorio de Cáncer de Mama* y *Estudio de la Ruta del Paciente*).
  - Rediseño de las tarjetas KPI con acabado glassmorphism (`backdrop-filter: blur(8px)`), bordes redondeados a 14px, elevación con sombra suave y micro-interacción hover.
  - Mayor jerarquía visual con números a 2rem (`font-weight: 800`) y mejor contraste en rótulos y subtítulos.
  - Sincronización y reactividad en tiempo real de los porcentajes y subtítulos de los 4 KPIs referenciados directamente a la muestra total de 612 encuestadas (o base filtrada).
  - Incremento de versión `OBS_METRICS_VERSION` a `1.5.1`.

## [2.0.7] - 2026-10-20

### Changed
- **Coherencia y Actualización Integral de Textos de Análisis y Subtítulos (`observerMetricsWeb` v1.5.0):**
  - Se recalcularon y sincronizaron todas las narrativas en las cajas de análisis (`q.analysis`) y subtítulos (`q.subtitle`) de las 43 variables del visor.
  - Corrección de menciones de muestras desactualizadas (ej. eliminación del texto "aplica a 77 pacientes" en Q3 y Q27).
  - Alineación completa de la redacción de tiempos con la nueva escala canónica acumulativa: `Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses` y `Más de 6 meses`.
  - Consistencia porcentual calculada exactamente sobre el universo global de 612 encuestadas.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.5.0`.

## [2.0.6] - 2026-10-20

### Changed
- **Estandarización Total de Escala "Hasta" en Todas las Preguntas Temporales (`observerMetricsWeb` v1.4.9):**
  - Aplicada la escala uniforme acumulativa a todas las preguntas de tiempos del recorrido:
    - **Pregunta 7:** Tiempo primera consulta (`El mismo día`, `Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`).
    - **Pregunta 11:** Tiempo realizar examen (`Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No corresponde`).
    - **Pregunta 12:** Tiempo resultado examen (`El mismo día`, `Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No corresponde`).
    - **Pregunta 16:** Tiempo tras derivación (`Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No corresponde`).
    - **Pregunta 19:** Tiempo biopsia (`Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No corresponde`).
    - **Pregunta 20:** Tiempo resultado biopsia (`Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No corresponde`).
    - **Pregunta 25:** Tiempo estudios estadio (`Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No recuerdo`, `No corresponde`).
    - **Pregunta 29:** Tiempo confirmación a indicación (`Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `No recuerdo`).
    - **Pregunta 30:** Tiempo indicación a inicio (`Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `Todavía no lo he iniciado`, `No recuerdo`).
    - **Pregunta 36:** Tiempo total recorrido (`Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`, `Todavía no lo he iniciado`, `No recuerdo`).
  - Actualizado de forma integral en `data.js`, `QUESTION_CANONICAL_ORDER` y `normalizeAnswer()` en `app.js`.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.9`.

## [2.0.5] - 2026-10-20

### Changed
- **Escala de Tiempos Acumulativa "Hasta" en Pregunta 6 (`observerMetricsWeb` v1.4.8):**
  - Aplicada la misma convención de escala a la Pregunta 6 (Tiempo hasta buscar atención médica - Columna R):
    - `El mismo día`: 56 personas (9.2%)
    - `Hasta 7 días`: 219 personas (35.8%)
    - `Hasta 30 días`: 143 personas (23.4%)
    - `Hasta 3 meses`: 112 personas (18.3%)
    - `Hasta 6 meses`: 27 personas (4.4%)
    - `Más de 6 meses`: 54 personas (8.8%)
    - `No respondió`: 1 persona (0.2%)
  - Actualizado en `data.js`, catálogo canónico `QUESTION_CANONICAL_ORDER['R']` y normalizador en `app.js`.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.8`.

## [2.0.4] - 2026-10-20

### Fixed
- **Eliminación de Nombres Duplicados Antiguos en Pregunta 4 (`observerMetricsWeb` v1.4.7):**
  - Se corrigió el mapeo de normalización en `app.js` que estaba reconvirtiendo las opciones a los nombres viejos (`1–2 semanas`, `3–4 semanas`, `4 a 6 meses`).
  - Ahora todas las respuestas se asignan y contabilizan de forma limpia y exclusiva en la nueva escala:
    - `Hasta 7 días`: 18 personas (2.9%)
    - `Hasta 15 días`: 5 personas (0.8%)
    - `Hasta 30 días`: 42 personas (6.9%)
    - `Hasta 3 meses`: 27 personas (4.4%)
    - `Hasta 6 meses`: 13 personas (2.1%)
    - `Más de 6 meses`: 10 personas (1.6%)
    - `No corresponde`: 497 personas (81.2%)
  - Quedan eliminadas las filas repetidas y los ceros artificiales.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.7`.

## [2.0.3] - 2026-10-20

### Changed
- **Escala de Tiempos con Límite Acumulativo "Hasta" en Pregunta 4 (`observerMetricsWeb` v1.4.6):**
  - Se normalizaron las opciones de tiempo en la Pregunta 4 (Tiempo de espera atención en Lima - Columna P):
    - `Menos de 1 semana` $\rightarrow$ `Hasta 7 días` (18 personas)
    - `1–2 semanas` $\rightarrow$ `Hasta 15 días` (5 personas)
    - `3–4 semanas` $\rightarrow$ `Hasta 30 días` (42 personas)
    - `2-3 meses` $\rightarrow$ `Hasta 3 meses` (27 personas)
    - `4 a 6 meses` $\rightarrow$ `Hasta 6 meses` (13 personas)
    - `Más de 6 meses` (10 personas)
    - `No corresponde` (497 personas)
  - Actualizado en `data.js`, `QUESTION_CANONICAL_ORDER` y `normalizeAnswer()` en `app.js`.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.6`.

## [2.0.2] - 2026-10-20

### Added
- **Filtro de Sector (Público vs Privado) en Visor (`observerMetricsWeb` v1.4.5):**
  - Incorporado nuevo selector `Sector` en la barra de controles con las opciones:
    - *Todos los Sectores* (612 personas)
    - *Público* (485 personas: Minsa, EsSalud, FFAA)
    - *Privado* (127 personas: Seguro Privado, Particular)
  - Filtro reactivo conectado con KPIs, conteo de muestra, chips de filtros activos y botón "Restablecer".
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.5`.

## [2.0.1] - 2026-10-20

### Changed
- **Reversión de Barra de Filtros a Flujo Normal (`observerMetricsWeb` v1.4.4):**
  - Se retiró `position: sticky` de `.filter-bar` dejándolo estático (`position: static`) en desktop y mobile.
  - Se restauró la protección `overflow-x: hidden` en el envoltorio general.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.4`.

## [2.0.0] - 2026-10-20

### Changed
- **Barra de Filtros Sticky Persistente al Scrollear (`observerMetricsWeb` v1.4.3):**
  - Se eliminó `overflow-x: hidden` de `.obs-metrics-wrapper` para restaurar el contexto de desplazamiento nativo de la ventana, permitiendo que `position: sticky` funcione sin bloqueos.
  - Se configuró `.filter-bar` con `position: -webkit-sticky; position: sticky; top: 0; z-index: 1000;` con soporte desktop y mobile.
  - Sombra y difuminado de fondo (`backdrop-filter: blur(10px)`) para legibilidad durante el scroll sobre las tarjetas.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.3`.

## [1.9.9] - 2026-10-20

### Changed
- **Etiqueta "Derivada:" en Preguntas Condicionadas (`observerMetricsWeb` v1.4.2):**
  - Se configuró el conjunto de preguntas subordinadas/condicionadas (`CONDITIONAL_QUESTION_COLS`: Q2, Q3, Q4, Q10–Q14, Q16, Q17, Q19, Q20, Q21, Q23, Q25, Q27).
  - En todas las preguntas condicionadas se sustituyó dinámicamente la etiqueta `Respondieron:` por `Derivada:` para señalar que la muestra responde a una condición previa.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.2`.

## [1.9.8] - 2026-10-20

### Changed
- **Ajustes de Cabecera en Visor de Métricas (`observerMetricsWeb` v1.4.1):**
  - Ocultado el badge rojo `No aplica / Sin respuesta: X personas` para evitar ruido visual en las tarjetas.
  - Personalizado el badge de respuesta en las preguntas de derivación a Lima (Pregunta 3 y Pregunta 4) cambiando `Respondieron:` por `Derivada:`.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.1`.

## [1.9.7] - 2026-10-20

### Changed
- **Ajuste de Etiqueta de Muestra en Visor (`observerMetricsWeb` v1.4.0):**
  - Modificado el texto del badge de universo de `Base total:` a `Total Encuestadas: X personas` para mayor claridad conceptual en la lectura de métricas.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.4.0`.

## [1.9.6] - 2026-10-20

### Changed
- **Universo Global Uniforme en Visor de Métricas (`observerMetricsWeb` v1.3.9):**
  - Todas las preguntas se calculan ahora siempre sobre la **base total de encuestados** (612 personas o el total resultante de filtros generales).
  - Se eliminaron las etiquetas confusas de subconjuntos ("Aplica a X personas que cumplieron la condición").
  - En las preguntas condicionales, los casos no aplicables se integran claramente como `No corresponde` o `No aplica / Sin respuesta` en la distribución.
  - Incremento de versión `OBS_METRICS_VERSION` a `1.3.9`.

## [1.9.5] - 2026-10-20

### Changed
- **Normalización y Unificación en Visor de Métricas (`observerMetricsWeb` v1.3.8):**
  - **Pregunta 23 (Subtipo tumoral informado - Columna AI):** Unificadas todas las respuestas variantes `"No me informaron"` a la opción oficial canónica `"No me lo informaron"`, eliminando la fila duplicada en tablas y gráficos del visor.
  - Actualización directa sobre el dataset estático (`data.js`) y filtro de normalización en tiempo de ejecución (`app.js`).
  - Incremento de versión `OBS_METRICS_VERSION` a `1.3.8` para invalidación inmediata de caché de navegador.

## [1.9.4] - 2026-10-16

### Changed
- **Incremento de Versión para Despliegue:** Actualización de versión a `1.9.4` para forzar la invalidación de caché de scripts y estilos en entornos de producción.

## [1.9.3] - 2026-10-16

### Changed
- **Homologación de Lógica Condicional y Opciones en Encuesta Larga:**
  - **Pregunta 9 (Solicitud de exámenes):** Se encapsuló el grupo de preguntas Q10 a Q14 dentro de `#block-examenes-solicitados` en el Paso 4, ocultándose dinámicamente si en Q9 se responde "No" o "No recuerdo".
  - **Sincronización Total de Rangos:** Alineadas las opciones de Q11, Q12, Q19, Q20, Q25, Q29 y Q30 a la escala oficial (`2–3 meses` y `4–6 meses`).
- **Versión 1.9.3:** Actualizada cabecera y constante del plugin.

## [1.9.2] - 2026-10-16

### Changed
- **Sincronización de Opciones con Documento Oficial (`2026_PUPSC_OC_Encuesta_Ruta_del_Paciente.docx`):**
  - **Pregunta 4 (Tiempo de espera atención en Lima):** Actualizados rangos a `2-3 meses` y `4 a 6 meses` tanto en Encuesta Corta como en Encuesta Larga.
  - **Pregunta 6 (Tiempo búsqueda de atención médica):** Actualizados rangos a `2-3 meses` y `4 a 6 meses` en ambas encuestas.
  - **Pregunta 36 (Tiempo total del recorrido):** Alineada escala temporal completa (`Menos de 1 semana`, `1–4 semanas`, `2-3 meses`, `4 a 6 meses`, `Más de 6 meses`, `Más de 9 meses`, `Más de 1 año`, `Todavía no lo he iniciado`, `No recuerdo`) en ambas encuestas.
- **Incremento de Versión para Cache-Busting:** Subida de versión a `1.9.2` para garantizar la recarga de plantillas y scripts en producción.

## [1.9.1] - 2026-10-14

### Added / Fixed
- **Lógica Condicional Completa en Wizard:**
  - `Q9` (*¿Solicitaron exámenes?*): Si responde "No", se ocultan automáticamente las preguntas hijas Q10 a Q14.
  - `Q13` (*¿Dificultades en exámenes?*): Si responde "No", se oculta Q14 (*Principal dificultad*), evitando contradicciones y requerimientos obligatorios redundantes.
  - `Q15` (*¿Derivación a otro centro?*): Si responde "No", se ocultan automáticamente Q16 (*Tiempo hasta ser atendida*) y Q17 (*Cambio de sistema público/privado*).
  - `Q18` (*¿Indicaron biopsia?*): Si responde "No", se ocultan automáticamente Q19 (*Tiempo de realización*), Q20 (*Tiempo de resultado*) y Q21 (*Establecimiento de biopsia*).
- **Limpieza de Datos Fantasma:** Se asegura la invocación de `clearInputsInside()` al desmarcar opciones detonantes para mantener la integridad de respuestas en base de datos y Google Sheets.
- **Incremento de Versión:** Actualización a `1.9.1` para forzar la invalidación de caché de scripts en navegadores y servidores en producción.

## [1.9.0] - 2026-10-11

### Changed
- **Reestructuración de la Encuesta Corta a 4 Pasos:** Optimizado el flujo del formulario `[short_survey]` agrupando secciones para reducir la fricción de navegación:
  - *Paso 1:* Perfil y Lugar de Diagnóstico (Sección 1 + Bloque A).
  - *Paso 2:* Primera Señal y Exámenes (Bloque B + Bloque D).
  - *Paso 3:* Biopsia y Estadio (Bloque F).
  - *Paso 4:* Tratamiento, Barreras y Experiencia (Bloque I).
- **Adaptación en Backend y Panel:** Sincronizado el controlador REST (`step-save` hasta paso 4) y el badge de progreso en WP Admin (`⏳ Paso x / 4` para encuesta corta y `⏳ Paso x / 6` para encuesta original).
- **Cache-Busting & Auto-Upgrade:** Incrementada versión a 1.9.0 para forzar la recarga de scripts/estilos en navegadores y activar migración automática `maybe_upgrade()` en el hosting.

## [1.8.0] - 2026-10-11

### Added
- **Segunda Encuesta Corta Integrada (`[short_survey]`):** Soporte completo para encuesta corta anonimizada en coexistencia armónica con la encuesta original.
- **Plantilla Dedicada `templates/survey-container-short.php`:** Paso 1 con perfil/antecedentes sin captura de datos de contacto personales, redacción en 3ra persona ("la paciente / familiar o cuidador") e inclusión de preguntas 14, 17 y 21.
- **Sincronización a Google Sheets en Pestaña Única (`EncuestaCorta`):** Configurable en WP Admin (`Observatorio Encuestas -> Google Sheets`). Guarda el progreso de pasos con bandera de estado (`Incompleto`) y actualiza a `Completo` al finalizar mediante upsert por ID, sin eliminar filas entre pestañas.
- **Aislamiento en Frontend (`survey-wizard.js`):** Separación de claves en `localStorage` (`obs_survey_short_draft`, `obs_survey_short_submission_id`) y detección dinámica del tipo de encuesta (`survey_type: cancer_mama_corta`).
- **Filtros y Métricas en Panel Admin de WordPress:** Pestaña de filtro dedicada *"Solo Encuesta Corta"*, formateo de participante anónima y soporte de exportación segmentada a CSV.
- **Corrección de Collation en Consultas UNION:** Solucionado el error MySQL 1271 (`Illegal mix of collations for operation 'UNION'`) entre `wp_obs_survey_submissions` (`utf8mb4_unicode_ci`) y `wp_obs_survey_short_submissions` (`utf8mb4_unicode_520_ci`). Se agregó sincronización automática de collation en `class-activator.php` y se encapsuló la consulta en subquery segura.
- **Botón de Reinicio de Contador para Encuesta Corta:** Agregado botón en la barra superior de acciones de WP Admin protegido por nonces y diálogo de confirmación `confirm()`. Limpia las respuestas de prueba de la tabla `wp_obs_survey_short_submissions` y restablece el `AUTO_INCREMENT` a 1 de forma segura.

## [1.7.2] - 2026-10-01

### Changed
- **Incremento de Versión para Cache-Busting:** Actualización de versión del plugin a 1.7.2 para forzar la recarga de scripts (`survey-wizard.js?ver=1.7.2`) en el hosting y navegadores de producción.

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

