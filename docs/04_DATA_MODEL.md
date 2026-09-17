# 04. Data Model

## 1. Esquema de Base de Datos

### Tabla: `{$wpdb->prefix}obs_survey_submissions`

| Campo | Tipo | Nulo | Descripción |
|-------|------|------|-------------|
| `id` | `BIGINT(20) UNSIGNED` | NO | Primary Key, Auto Increment |
| `survey_type` | `VARCHAR(50)` | NO | Tipo/código de encuesta (ej. `cancer_mama_journey`) |
| `consent_accepted` | `TINYINT(1)` | NO | `1` si aceptó consentimiento y autorización |
| `first_name` | `VARCHAR(100)` | NO | Nombres del participante |
| `last_name` | `VARCHAR(100)` | NO | Apellidos del participante |
| `age` | `SMALLINT UNSIGNED` | NO | Edad |
| `phone` | `VARCHAR(30)` | NO | Número de celular / teléfono |
| `email` | `VARCHAR(150)` | NO | Correo electrónico |
| `region` | `VARCHAR(100)` | SÍ | Región o departamento de residencia |
| `responses_json` | `LONGTEXT` | NO | JSON con todas las respuestas por sección y preguntas |
| `ip_address` | `VARCHAR(45)` | SÍ | IP anonimizada / hash para control |
| `user_agent` | `VARCHAR(255)` | SÍ | User Agent del navegador |
| `status` | `VARCHAR(20)` | NO | `completed`, `partial`, `spam` |
| `created_at` | `DATETIME` | NO | Fecha y hora de envío |
| `updated_at` | `DATETIME` | NO | Fecha y hora de actualización |

## 2. Índices
- `PRIMARY KEY (id)`
- `KEY idx_survey_type (survey_type)`
- `KEY idx_email (email)`
- `KEY idx_created_at (created_at)`
