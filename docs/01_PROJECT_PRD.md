# 01. Project PRD — Plugin de Encuesta Oncológica (Observatorio)

## 1. Resumen del Producto
Plugin para WordPress diseñado a medida para recopilar información estructurada sobre el viaje de la paciente con cáncer de mama en el Perú ("Del primer signo al inicio del tratamiento"). Reemplaza los formularios pesados de Elementor por una solución nativa, ligera, modular y de alto rendimiento.

## 2. Objetivos
- **Experiencia de usuario (UX):** Formulario multi-pasos (wizard), rápido, accesible y con validación fluida en tiempo real.
- **Rendimiento:** Carga mínima de scripts/estilos sin depender de Elementor ni librerías pesadas.
- **Seguridad & Datos:** Protección anti-spam (Honeypot / Nonces), sanitización/validación de datos, almacenamiento seguro en base de datos y exportación (CSV/Excel).
- **Integración:** Incrustación simple mediante shortcode (e.g. `[encuesta_cancer_mama]`) y/o bloque Gutenberg.

## 3. Público Objetivo
Pacientes con cáncer de mama en Perú, familiares o cuidadores que comparten su experiencia clínica y tiempos de atención.

## 4. Requerimientos Funcionales
- Visualización de secciones/pasos (multi-step) con barra de progreso.
- Paso 1: Antecedentes & Consentimiento informado (anonimización y uso estadístico).
- Pasos siguientes: Detección, diagnóstico, tiempos de espera, acceso a tratamiento y medicación.
- Validación frontend y backend en cada paso.
- Almacenamiento estructurado en tabla de BD dedicada o CPT con meta fields.
- Panel de administración para ver envíos, métricas y exportación de datos.

## 5. Requerimientos No Funcionales
- Mobile-first, responsive y compatible con los estándares de diseño del Observatorio.
- Tiempo de carga < 300ms para los assets del formulario.
- Compatibilidad con WordPress 6.x y PHP 8.1+.
