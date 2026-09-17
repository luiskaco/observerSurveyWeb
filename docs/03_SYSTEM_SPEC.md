# 03. System Specification

## 1. Stack Técnico
- **Plataforma:** WordPress 6.x
- **PHP:** 8.1+ (OOP, Namespaces, PSR-12)
- **Base de Datos:** MySQL / MariaDB (InnoDB, `utf8mb4_unicode_ci`)
- **Frontend:** Vanilla JavaScript (ES6+), CSS3 con CSS Custom Properties
- **Seguridad:** WP Nonces, Sanitization & Escaping (`sanitize_text_field`, `sanitize_email`, etc.), Honeypot anti-bots, Rate limiting básico

## 2. Convenciones de Código
- **Plugin Name:** `observatorio-survey`
- **Namespace:** `Observatorio\Survey\`
- **Text Domain:** `observatorio-survey`
- **Shortcode:** `[encuesta_cancer_mama]`
- **REST Namespace:** `observatorio/v1`

## 3. Endpoints de la API
- **POST `/wp-json/observatorio/v1/survey/submit`**
  - **Headers:** `X-WP-Nonce`, `Content-Type: application/json`
  - **Payload:** Datos del formulario por sección.
  - **Responses:**
    - `200 OK`: `{"status": "success", "message": "Encuesta registrada exitosamente.", "id": 123}`
    - `400 Bad Request`: `{"status": "error", "message": "Datos de validación inválidos", "errors": {...}}`
    - `403 Forbidden`: `{"status": "error", "message": "Nonce inválido"}`
