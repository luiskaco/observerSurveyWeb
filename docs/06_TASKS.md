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

## Fase 11: Selector Completo de Regiones en Paso 2 (Pregunta 1)
- [x] Reemplazar radio button "Otra región del Perú" por selector `<select>` con todas las regiones del país.
- [x] Adaptar lógica condicional en `survey-wizard.js`: ocultar derivación solo si es "Lima", mostrar para cualquier otra región seleccionada.
- [x] Validar compatibilidad e inserción en Google Sheets (`Completo` e `Incompleto`).

## Fase 12: Segunda Encuesta Corta ([short_survey])
- [x] Definir banco final de preguntas a partir de la revisión del usuario (Word / Markdown), incorporando preguntas solicitadas (Q14, Q17 y Q21).
- [x] Registrar shortcode `[short_survey]` con soporte de contenedor y formulario dedicado (`templates/survey-container-short.php`).
- [x] Adaptar motor de guardado progresivo (REST API y frontend `survey-wizard.js`) con aislamiento de `localStorage` (`obs_survey_short_draft`, `obs_survey_short_submission_id`).
- [x] Sincronización Google Sheets hacia la pestaña configurable (por defecto `EncuestaCorta`) con columna bandera `Estado` (`Incompleto` / `Completo`) mediante upsert por ID.
- [x] Filtro y vista diferenciada en el panel de administración de WordPress para listar y exportar respuestas de ambas encuestas.
- [x] Corrección de consulta UNION en listado de WP Admin: resolver error de collations incompatibles (`utf8mb4_unicode_ci` vs `utf8mb4_unicode_520_ci`) y envolver consulta en subquery segura con pestañas de filtro completas.
- [x] Implementar botón de reinicio de contador para Encuesta Corta en el panel de WP Admin (vía nonce y confirmación de seguridad para reiniciar AUTO_INCREMENT a 1).
- [x] Reestructurar Encuesta Corta a 4 pasos (Opción A): Paso 1 (Perfil y Lugar de Diagnóstico), Paso 2 (Primera Señal y Exámenes), Paso 3 (Biopsia y Estadio), Paso 4 (Tratamiento y Experiencia), adaptando plantillas, wizard JS, REST API y badges de WP Admin.
- [x] Optimizar sincronización en lote y diagnóstico de Google Sheets: forzar modo manual y reportar detalle de errores en el panel de administración.
- [x] Actualización de opciones de preguntas en Encuesta Corta y Encuesta Larga según documento oficial (`2026_PUPSC_OC_Encuesta_Ruta_del_Paciente.docx`): sincronización de rangos temporales en Q4 (tiempo derivación Lima), Q6 (tiempo búsqueda de atención) y Q36 (tiempo total de la ruta del paciente).
- [x] Homologación de condiciones lógicas entre encuesta corta y larga (ocultamiento de Q10-Q14 si Q9 es No o No recuerdo).

## Fase 13: Normalización y Correlatividad en Visor de Métricas (`observerMetricsWeb`)
- [x] Implementar catálogo canónico de orden oficial (`QUESTION_CANONICAL_ORDER`) en `app.js` para evitar ordenamiento por frecuencia y respetar la secuencia clínica/cronológica.
- [x] Normalizar sinónimos históricos y rangos incompatibles en el dataset (`data.js`) y en el visor (`app.js`).
- [x] Reemplazar textos de estado "Sin respuesta" por "No respondió" en filtros, tablas y gráficos.
- [x] Eliminar opción obsoleta "No corresponde" de la Pregunta 17 en templates del formulario y catálogo del visor.
- [x] Implementar cálculo condicional correlativo (`QUESTION_CONDITIONS`): las preguntas derivadas calculan sus porcentajes y métricas sobre la base de personas que cumplieron la condición previa.
- [x] Unificar opciones de tiempo extremo (`Más de 9 meses` y `Más de 1 año` agrupadas en `Más de 6 meses`).
- [x] Unificar opciones de tiempo corto en Q6 (`Menos de 1 semana` unificada en `1–7 días`).
- [x] Forzar presencia correlativa con valor 0 para opciones no seleccionadas (ej. `Estadio I`).
- [x] Posicionar opciones neutras `No recuerdo` y `No respondió` estrictamente al final en todas las preguntas.
- [x] Unificar opciones equivalentes de Q23 (Columna AI: Subtipo tumoral informado): consolidar `No me informaron` en `No me lo informaron` en `data.js`, `app.js` e incrementar `OBS_METRICS_VERSION` a `1.3.8`.
- [x] Adoptar Opción 1 de Universo Global Uniforme: calcular todas las preguntas sobre la base total de encuestados (612 personas), eliminar subconjuntos recortados y badges confusos de "cumplieron la condición", mostrando `No aplica / Sin respuesta` claramente en la distribución. Versión `1.3.9`.
- [x] Actualizar texto del badge de universo a `Total Encuestadas: X personas`. Versión `1.4.0`.
- [x] Ocultar badge `No aplica / Sin respuesta` en la cabecera y renombrar `Respondieron:` a `Derivada:` en las preguntas de derivación a Lima (Q3 y Q4). Versión `1.4.1`.
- [x] Aplicar etiqueta `Derivada:` a todas las preguntas condicionadas del formulario en lugar de `Respondieron:`. Versión `1.4.2`.
- [x] Implementar barra de filtros fija (`position: sticky; top: 0; z-index: 1000`) tanto en desktop como en mobile, eliminando `overflow-x: hidden` del contenedor principal que impedía el anclaje sticky. Versión `1.4.3`.
- [x] Revertir comportamiento sticky en la barra de filtros a flujo normal estático (`position: static`) por solicitud del usuario. Versión `1.4.4`.
- [x] Agregar selector de `Sector` en la barra de filtros (`Público`: Minsa, EsSalud, FFAA [485] vs `Privado`: Seguro Privado, Particular [127]) con integración a chips activos y botón de restablecer. Versión `1.4.5`.
- [x] Normalizar escala de tiempo acumulativa con "Hasta" en Pregunta 4 (Columna P: Tiempo de espera en Lima): `Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses` y `Más de 6 meses` en dataset y visor. Versión `1.4.6`.
- [x] Eliminar remanente antiguo en `normalizeAnswer()` que revertía `Hasta X` a `1–2 semanas` / `3–4 semanas` / `4 a 6 meses`, consolidando únicamente las opciones canónicas limpias. Versión `1.4.7`.
- [x] Aplicar escala acumulativa "Hasta" a Pregunta 6 (Columna R: Tiempo hasta buscar atención médica): `El mismo día`, `Hasta 7 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses`, `Más de 6 meses`. Versión `1.4.8`.
- [x] Extender escala acumulativa "Hasta" a todas las preguntas de tiempos del recorrido (Q7, Q11, Q12, Q16, Q19, Q20, Q25, Q29, Q30 y Q36): unificar en `Hasta 7 días`, `Hasta 15 días`, `Hasta 30 días`, `Hasta 3 meses`, `Hasta 6 meses` y `Más de 6 meses`. Versión `1.4.9`.
- [x] Armonizar y recalcular todos los textos de subtítulo y cajas de análisis clínico de las 43 variables: sincronizar con la escala "Hasta", eliminar referencias a muestras obsoletas (77 pacientes) y reflejar porcentajes exactos sobre la base de 612 encuestadas. Versión `1.5.0`.
- [x] Modernizar diseño y coherencia del bloque Hero y sus 4 tarjetas KPI: badges institucionales, efecto cristalino glassmorphism (`backdrop-filter: blur(8px)`), hover suave, tipografía de 2rem y porcentajes reactivos consistentes sobre la muestra total de 612 encuestadas. Versión `1.5.1`.
- [x] Eliminar badges contextuales del encabezado Hero para una vista más limpia y directa del título principal. Versión `1.5.2`.
- [x] Ocultar tarjeta y enlace de Perfil 5 (Columna L: Consentimiento Informado) en el visor de métricas. Versión `1.5.3`.
- [x] Normalizar texto en Pregunta 2 (Columna N: Derivada a Lima) cambiando `No corresponde (reside en Lima)` a `No corresponde (Reside en Lima)`. Versión `1.5.4`.
- [x] Ajustar Preguntas 3 y 4 (O y P): eliminar el item `No corresponde` de sus tablas/gráficos, calcular su `% Universo` exclusivamente sobre la base de personas derivadas (125) y restituir la etiqueta `Respondieron:` en la Pregunta 2 (padre del condicional). Versión `1.5.5`.
- [x] Homologar Pregunta 1 (Columna M: Lugar de diagnóstico) para replicar exactamente la métrica, dataset y análisis de Región de Residencia (Columna H), eliminando incongruencias de regiones no muestreadas. Versión 1.5.6.
- [x] Ocultar badge redundante de "Respondieron: XX (YY%)" en todas las tarjetas de preguntas, conservando únicamente el badge de "Total Encuestadas: XX personas". Versión 1.5.7.
- [x] Renombrar encabezado de columna de tabla a "% Derivado" en preguntas condicionadas con total derivadas (Q3 y Q4) y asegurar que el cálculo de % en tabla y gráficos use exclusivamente la cantidad de derivadas (125). Versión 1.5.8.
- [x] Extender lógica condicional a todas las preguntas dependientes (Q3, Q4, Q14, Q16, Q17, Q19, Q20, Q25, Q27): excluir "No corresponde" de tablas y gráficos, cambiar encabezado a "% Derivado", calcular el porcentaje y gráficos sobre la base de derivadas y actualizar narrativas de análisis clínico. Versión 1.5.9.
- [x] Homologar etiqueta de badge en Q3 y Q4 de "Total Derivadas a Lima" a "Total Derivadas" para uniformidad visual. Versión 1.6.0.
- [x] Calcular el "% Derivado" exactamente sobre el 100% de los datos derivados registrados (base de respuestas válidas de la subpoblación), garantizando que la suma de porcentajes de las opciones totalice el 100.0%. Versión 1.6.1.
- [x] Pregunta 6 (Columna R: Tiempo hasta buscar atención médica): fusionar el registro de "No respondió" dentro de "No recuerda" y eliminar la fila/opción "No respondió". Versión 1.6.2.
- [x] Pregunta 19 (Columna AE: Tiempo hasta realizar la biopsia): eliminar la opción/fila "No respondió" (2 casos) y reparar el "% Derivado" recalculándolo sobre el 100% de la base válida de 597 pacientes. Versión 1.6.3.
- [x] Pregunta 5 (Columna Q: Detección inicial de la señal): fusionar el registro de "No respondió" dentro de "No recuerda", eliminar la opción "No respondió" y recalcular el "% Universo" cerrando al 100.0% de las 612 encuestadas. Versión 1.6.4.
