# 02. SDD — System Design Document

## 1. Arquitectura General
El plugin `observatorio-survey` funcionará como un módulo desacoplado dentro del ecosistema WordPress:

```
[ Frontend: Shortcode / UI Multi-Step ]
                 │
                 ▼
         (Fetch / REST API)
                 │
                 ▼
[ WordPress Backend: REST Controller / Nonce Auth / Sanitizer ]
                 │
                 ▼
[ Data Layer: Custom Table `wp_obs_survey_responses` ]
                 │
                 ▼
[ Admin Dashboard: WP Admin List Table + CSV Exporter ]
```

## 2. Componentes Principales
1. **Core Plugin Bootstrap:** Registra hooks, activation/deactivation hooks, scripts y estilos.
2. **REST API Endpoints:**
   - `POST /wp-json/observatorio/v1/survey/submit`: Valida y almacena las respuestas del formulario.
   - `GET /wp-json/observatorio/v1/survey/export`: Exportación de registros (solo administradores).
3. **Frontend Engine:**
   - Renderizador HTML accesible con soporte semántico.
   - Vanilla JS para manejo de estado del wizard multi-paso, persistencia temporal (`localStorage`) y validación instantánea.
   - CSS modular con variables de color del Observatorio.
4. **Admin Panel:**
   - Vista de tabla con filtros, métricas básicas y botón de descarga CSV.
