<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="obs-survey-container" id="obs-survey-app">
    <!-- Header General -->
    <header class="obs-survey-header">
        <div class="obs-badge">ENCUESTA PACIENTES DE CÁNCER DE MAMA</div>
        <h1 class="obs-main-title">LA RUTA DE LA PACIENTE CON CÁNCER DE MAMA</h1>
        <p class="obs-subtitle">Del primer signo al inicio del tratamiento</p>
        <div class="obs-decorative-bars">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <p class="obs-description">
            Este formulario tiene como objetivo conocer tu experiencia desde que apareció la primera señal o hallazgo hasta el inicio de tu tratamiento. Tus respuestas ayudarán a identificar tiempos de espera, barreras y brechas en la atención del cáncer de mama en el Perú. Responder esta encuesta toma aproximadamente 5 minutos. No necesitas recordar fechas exactas; utilizaremos rangos de tiempo.
        </p>
    </header>

    <!-- Card Principal -->
    <div class="obs-survey-card">
        <!-- Barra de Progreso -->
        <div class="obs-progress-bar-wrapper">
            <div class="obs-progress-meta">
                <span class="obs-step-title-display">Paso 1 de 4: Perfil y Lugar de Diagnóstico</span>
                <span class="obs-step-percentage">25%</span>
            </div>
            <div class="obs-progress-track">
                <div class="obs-progress-fill" style="width: 25%;"></div>
            </div>
            <div class="obs-step-pills">
                <button type="button" class="obs-step-pill active" data-step="1" title="Perfil y Lugar de Diagnóstico">1</button>
                <button type="button" class="obs-step-pill" data-step="2" title="Primera Señal y Exámenes">2</button>
                <button type="button" class="obs-step-pill" data-step="3" title="Biopsia y Estadio">3</button>
                <button type="button" class="obs-step-pill" data-step="4" title="Tratamiento y Experiencia">4</button>
            </div>
        </div>

        <div class="obs-mandatory-notice">
            *TODAS LAS PREGUNTAS SON OBLIGATORIAS
        </div>

        <!-- Banner de Hito Motivacional Integrado -->
        <div class="obs-milestone-banner" id="obs-milestone-banner" style="display: none;" role="status" aria-live="polite">
            <div class="obs-milestone-content">
                <div class="obs-milestone-icon" id="obs-ms-icon"></div>
                <div class="obs-milestone-text">
                    <strong class="obs-milestone-title" id="obs-ms-title">¡Gran comienzo!</strong>
                    <span class="obs-milestone-desc" id="obs-ms-desc">Tu experiencia ayuda a visibilizar la detección temprana.</span>
                </div>
            </div>
            <button type="button" class="obs-milestone-close" id="obs-ms-close" aria-label="Cerrar">&times;</button>
        </div>

        <!-- Formulario -->
        <form id="obs-survey-form" novalidate>
            <!-- Identificador de Encuesta Corta -->
            <input type="hidden" name="survey_type" value="cancer_mama_corta">
            <!-- Honeypot anti-spam -->
            <input type="text" name="hp_field" style="display: none !important;" tabindex="-1" autocomplete="off">

            <!-- ========================================================== -->
            <!-- PASO 1: PERFIL Y ANTECEDENTES                              -->
            <!-- ========================================================== -->
            <div class="obs-wizard-step active" data-step="1">
                <div class="obs-step-heading">
                    <h3>SECCIÓN 1: PERFIL Y ANTECEDENTES</h3>
                    <p class="obs-step-subinfo">Información general de la participante y perfil de atención.</p>
                </div>

                <div class="obs-field-group">
                    <label>¿Es Ud. actualmente paciente con cáncer de mama o familiar / cuidador de una paciente con cáncer de mama? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card">
                            <input type="radio" name="is_current_patient" value="Si" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">Sí</span>
                            </span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="is_current_patient" value="No" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">No</span>
                            </span>
                        </label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label for="region">Región de residencia de la paciente <span class="req">*</span></label>
                    <select id="region" name="region" required>
                        <option value="">Selecciona tu región...</option>
                        <option value="Amazonas">Amazonas</option>
                        <option value="Áncash">Áncash</option>
                        <option value="Apurímac">Apurímac</option>
                        <option value="Arequipa">Arequipa</option>
                        <option value="Ayacucho">Ayacucho</option>
                        <option value="Cajamarca">Cajamarca</option>
                        <option value="Callao">Callao</option>
                        <option value="Cusco">Cusco</option>
                        <option value="Huancavelica">Huancavelica</option>
                        <option value="Huánuco">Huánuco</option>
                        <option value="Ica">Ica</option>
                        <option value="Junín">Junín</option>
                        <option value="La Libertad">La Libertad</option>
                        <option value="Lambayeque">Lambayeque</option>
                        <option value="Lima">Lima</option>
                        <option value="Loreto">Loreto</option>
                        <option value="Madre de Dios">Madre de Dios</option>
                        <option value="Moquegua">Moquegua</option>
                        <option value="Pasco">Pasco</option>
                        <option value="Piura">Piura</option>
                        <option value="Puno">Puno</option>
                        <option value="San Martín">San Martín</option>
                        <option value="Tacna">Tacna</option>
                        <option value="Tumbes">Tumbes</option>
                        <option value="Ucayali">Ucayali</option>
                    </select>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>Sistema de salud en el que se atiende la paciente <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card">
                            <input type="radio" name="health_system" value="Minsa" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">Minsa (SIS)</span>
                            </span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="health_system" value="EsSalud" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">EsSalud</span>
                            </span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="health_system" value="FFAA" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">FFAA - Marina / Ejército / Policía / FAP</span>
                            </span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="health_system" value="Seguro Privado" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">Seguro Privado / EPS</span>
                            </span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="health_system" value="Particular" required>
                            <span class="obs-choice-content">
                                <span class="obs-radio-circle"></span>
                                <span class="obs-choice-label">Particular (Sin seguro)</span>
                            </span>
                        </label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>¿Qué edad tenía la paciente cuando recibió el diagnóstico de cáncer de mama? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="Menos de 30 años" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 30 años</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="30–39 años" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">30–39 años</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="40–49 años" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">40–49 años</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="50–59 años" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">50–59 años</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="60–69 años" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">60–69 años</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="70 años a más" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">70 años a más</span></span>
                        </label>
                        <label class="obs-choice-card">
                            <input type="radio" name="age_diagnosis" value="No recuerdo" required>
                            <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span>
                        </label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>
                <!-- Subsección A: Lugar de Diagnóstico y Derivación (dentro de Paso 1) -->
                <div class="obs-step-heading" style="margin-top: 35px; padding-top: 25px; border-top: 2px dashed #e2e8f0;">
                    <h3>LUGAR DE DIAGNÓSTICO Y DERIVACIÓN</h3>
                    <p class="obs-step-subinfo">Información sobre la región donde fue diagnosticada la paciente y derivaciones.</p>
                </div>

                <div class="obs-field-group">
                    <label for="q1_diag_place">1. ¿Dónde fue diagnosticada la paciente con cáncer de mama? <span class="req">*</span></label>
                    <select id="q1_diag_place" name="q1_diag_place" required>
                        <option value="">Selecciona la región del diagnóstico...</option>
                        <option value="Lima">Lima</option>
                        <option value="Callao">Callao</option>
                        <option value="Amazonas">Amazonas</option>
                        <option value="Áncash">Áncash</option>
                        <option value="Apurímac">Apurímac</option>
                        <option value="Arequipa">Arequipa</option>
                        <option value="Ayacucho">Ayacucho</option>
                        <option value="Cajamarca">Cajamarca</option>
                        <option value="Cusco">Cusco</option>
                        <option value="Huancavelica">Huancavelica</option>
                        <option value="Huánuco">Huánuco</option>
                        <option value="Ica">Ica</option>
                        <option value="Junín">Junín</option>
                        <option value="La Libertad">La Libertad</option>
                        <option value="Lambayeque">Lambayeque</option>
                        <option value="Loreto">Loreto</option>
                        <option value="Madre de Dios">Madre de Dios</option>
                        <option value="Moquegua">Moquegua</option>
                        <option value="Pasco">Pasco</option>
                        <option value="Piura">Piura</option>
                        <option value="Puno">Puno</option>
                        <option value="San Martín">San Martín</option>
                        <option value="Tacna">Tacna</option>
                        <option value="Tumbes">Tumbes</option>
                        <option value="Ucayali">Ucayali</option>
                        <option value="Fuera del Perú / Extranjero">Fuera del Perú / Extranjero</option>
                    </select>
                    <span class="obs-field-error"></span>
                </div>

                <!-- Bloque condicional: Solo si es fuera de Lima -->
                <div class="obs-conditional-block" id="block-region-derivacion" style="display: none;">
                    <div class="obs-conditional-notice">
                        <span class="dashicons dashicons-info"></span> Preguntas para pacientes diagnosticadas fuera de Lima:
                    </div>

                    <div class="obs-field-group">
                        <label>2. ¿Fue derivada la paciente a Lima para continuar su atención por cáncer de mama? <span class="req">*</span></label>
                        <div class="obs-options-grid cols-2">
                            <label class="obs-choice-card">
                                <input type="radio" name="q2_derivada_lima" value="Si">
                                <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span>
                            </label>
                            <label class="obs-choice-card">
                                <input type="radio" name="q2_derivada_lima" value="No">
                                <span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span>
                            </label>
                        </div>
                        <span class="obs-field-error"></span>
                    </div>

                    <div class="obs-field-group" id="block-etapa-lima" style="display: none;">
                        <label>3. ¿En qué etapa de atención la paciente fue derivada a Lima? <span class="req">*</span></label>
                        <div class="obs-options-grid cols-2">
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Confirmación del diagnóstico / biopsia"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Confirmación del diagnóstico / biopsia</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Estudios para determinar el estadio"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estudios para determinar el estadio</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Consulta con especialista"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Consulta con especialista</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Cirugía"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Cirugía</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Quimioterapia"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Quimioterapia</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Radioterapia"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Radioterapia</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q3_etapa_derivada" value="Otro tratamiento"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otro tratamiento</span></span></label>
                        </div>
                        <span class="obs-field-error"></span>
                    </div>

                    <div class="obs-field-group" id="block-tiempo-lima" style="display: none;">
                        <label>4. Desde que indicaron la derivación de la paciente hasta que pudo ser atendida en Lima, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                        <div class="obs-options-grid cols-2">
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="Menos de 1 semana"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="1–2 semanas"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–2 semanas</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="3–4 semanas"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–4 semanas</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="1–3 meses"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–3 meses</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="3–6 meses"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–6 meses</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="Más de 6 meses"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="Más de 9 meses"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                            <label class="obs-choice-card"><input type="radio" name="q4_tiempo_atencion_lima" value="Más de 1 año"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        </div>
                        <span class="obs-field-error"></span>
                    </div>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- PASO 2: PRIMERA SEÑAL Y EXÁMENES                           -->
            <!-- ========================================================== -->
            <div class="obs-wizard-step" data-step="2">
                <div class="obs-step-heading">
                    <h3>B. APARECE UNA SEÑAL Y PRIMERA ATENCIÓN</h3>
                    <p class="obs-step-subinfo">Momento en que se identifica la primera señal y tiempos hacia la primera consulta.</p>
                </div>

                <div class="obs-field-group">
                    <label>5. ¿Cómo detectó inicialmente la paciente algo que podía indicar un problema en su mama? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Encontró un bulto o nódulo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Encontró un bulto o nódulo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Notó un cambio en la mama, piel o pezón" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Notó un cambio en la mama, piel o pezón</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Tuvo secreción por el pezón" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Tuvo secreción por el pezón</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Tuvo dolor u otra molestia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Tuvo dolor u otra molestia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Una mamografía detectó una alteración" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Una mamografía detectó una alteración</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Una ecografía detectó una alteración" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Una ecografía detectó una alteración</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Otro examen detectó una alteración" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otro examen detectó una alteración</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Fue detectado durante un control de rutina" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Fue detectado durante un control de rutina</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="Otro" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otro</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q5_deteccion_inicial" value="No recuerda" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerda</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>6. Después de identificar la señal, ¿cuánto tiempo pasó para que la paciente buscara atención médica? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="El mismo día" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">El mismo día</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="1–7 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–7 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="8–30 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">8–30 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="1–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="3–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q6_tiempo_hasta_buscar_atencion" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>7. Después de buscar atención, ¿cuánto tiempo pasó hasta su primera consulta médica? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="El mismo día" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">El mismo día</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="1–7 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–7 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="8–30 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">8–30 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q7_tiempo_primera_consulta" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>8. ¿En qué tipo de establecimiento fue la primera consulta de la paciente? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q8_tipo_establecimiento_primera_consulta" value="Público" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Público</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q8_tipo_establecimiento_primera_consulta" value="Privado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Privado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q8_tipo_establecimiento_primera_consulta" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>9. ¿Le solicitaron a la paciente exámenes para evaluar la mama? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q9_solicitaron_examenes" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q9_solicitaron_examenes" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q9_solicitaron_examenes" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>
                <!-- Subsección D: Exámenes y Derivación (dentro de Paso 2) -->
                <div class="obs-step-heading" style="margin-top: 35px; padding-top: 25px; border-top: 2px dashed #e2e8f0;">
                    <h3>D. EXÁMENES Y DERIVACIÓN</h3>
                    <p class="obs-step-subinfo">Exámenes por imágenes solicitados y tiempos hacia la derivación médica.</p>
                </div>

                <div class="obs-field-group">
                    <label>10. ¿Qué exámenes le solicitaron a la paciente? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="Mamografía" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Mamografía</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="Ecografía mamaria" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Ecografía mamaria</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="Mamografía y ecografía" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Mamografía y ecografía</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="Resonancia magnética" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Resonancia magnética</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="Otro" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otro</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q10_examenes_solicitados" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>11. Desde que le solicitaron a la paciente el examen hasta que pudo realizarlo, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="1–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q11_tiempo_hasta_realizar_examen" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>12. Desde que la paciente realizó el examen hasta que recibió el resultado, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="El mismo día" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">El mismo día</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="1–7 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–7 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="8–30 días" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">8–30 días</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q12_tiempo_hasta_resultado_examen" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>13. ¿Tuvo la paciente alguna dificultad para realizar los exámenes? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q13_dificultad_examenes" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q13_dificultad_examenes" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q13_dificultad_examenes" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group" id="block-q14">
                    <label>14. ¿Cuál fue la principal dificultad para realizar los exámenes? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="No había citas disponibles" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No había citas disponibles</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="La cita estaba disponible después de mucho tiempo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">La cita estaba disponible después de mucho tiempo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="No había equipos disponibles" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No había equipos disponibles</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="No había personal especializado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No había personal especializado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="Problemas con el seguro o autorización" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Problemas con el seguro o autorización</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="Se tuvo que pagar el examen" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Se tuvo que pagar el examen</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="El establecimiento estaba lejos" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">El establecimiento estaba lejos</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="Otra" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otra</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q14_principal_dificultad_examenes" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>15. ¿Derivaron a la paciente a otro establecimiento o especialista? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q15_derivaron_otro_establecimiento" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q15_derivaron_otro_establecimiento" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q15_derivaron_otro_establecimiento" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>16. Si derivaron a la paciente, ¿cuánto tiempo pasó hasta que logró ser atendida? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="1–2 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–2 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="3–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q16_tiempo_atencion_derivacion" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>17. ¿La derivación implicó pasar de un sistema de atención a otro? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q17_cambio_sistema" value="Público → privado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Público → privado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q17_cambio_sistema" value="Privado → público" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Privado → público</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q17_cambio_sistema" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q17_cambio_sistema" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q17_cambio_sistema" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- PASO 3: BIOPSIA Y ESTADIO                                  -->
            <!-- ========================================================== -->
            <div class="obs-wizard-step" data-step="3">
                <div class="obs-step-heading">
                    <h3>SECCIÓN 3: BIOPSIA, SUBTIPO Y ESTADIO</h3>
                    <p class="obs-step-subinfo">Procedimiento de confirmación patológica, inmunohistoquímica y estadiaje clínico.</p>
                </div>

                <div class="obs-field-group">
                    <label>18. ¿Le indicaron una biopsia a la paciente para confirmar el diagnóstico? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q18_indicaron_biopsia" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q18_indicaron_biopsia" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>19. Desde que le indicaron la biopsia a la paciente hasta que se realizó, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="1–2 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–2 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="3–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q19_tiempo_hasta_realizar_biopsia" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>20. Desde que se realizó la biopsia hasta que la paciente recibió el resultado, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="1–2 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–2 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="3–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q20_tiempo_resultado_biopsia" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>21. ¿En qué tipo de establecimiento se realizó principalmente la biopsia? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q21_establecimiento_biopsia" value="Público" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Público</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q21_establecimiento_biopsia" value="Privado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Privado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q21_establecimiento_biopsia" value="En ambos" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">En ambos</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q21_establecimiento_biopsia" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>22. ¿Le informaron a la paciente qué subtipo de cáncer de mama tenía? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q22_informaron_subtipo" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q22_informaron_subtipo" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q22_informaron_subtipo" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group obs-conditional-block" id="block-q23" style="display: none;">
                    <label>23. Si le informaron el subtipo, ¿cuál le indicaron? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q23_subtipo_indicado" value="Luminal A/B u hormonal"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Luminal A/B u hormonal</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q23_subtipo_indicado" value="HER2 positivo"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">HER2 positivo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q23_subtipo_indicado" value="Triple negativo"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Triple negativo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q23_subtipo_indicado" value="No me lo informaron"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No me lo informaron</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>24. ¿En qué estadio le detectaron a la paciente el cáncer de mama? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q24_estadio_detectado" value="Estadio I" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio I</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q24_estadio_detectado" value="Estadio II" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio II</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q24_estadio_detectado" value="Estadio III" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio III</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q24_estadio_detectado" value="Estadio IV" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio IV</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q24_estadio_detectado" value="No me informaron" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No me informaron</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>25. ¿Cuánto tiempo pasó desde la confirmación del diagnóstico hasta que se completaron los estudios para determinar el estadio? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="1–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q25_tiempo_estudios_estadio" value="No corresponde" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>26. Cuándo inició el primer tratamiento de la paciente, ¿le informaron de un estadio diferente al que le habían indicado inicialmente? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q26_estadio_diferente" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q26_estadio_diferente" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q26_estadio_diferente" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q26_estadio_diferente" value="Todavía no he iniciado tratamiento" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Todavía no he iniciado tratamiento</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group obs-conditional-block" id="block-q27" style="display: none;">
                    <label>27. Si respondiste “Sí”, ¿qué estadio le informaron al inicio del tratamiento? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="Estadio I"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio I</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="Estadio II"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio II</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="Estadio III"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio III</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="Estadio IV"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estadio IV</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="No recuerdo"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q27_estadio_inicio_tratamiento" value="No corresponde"><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No corresponde</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- PASO 4: TRATAMIENTO, EXPERIENCIA Y CONSENTIMIENTO          -->
            <!-- ========================================================== -->
            <div class="obs-wizard-step" data-step="4">
                <div class="obs-step-heading">
                    <h3>SECCIÓN 4: INICIO DEL TRATAMIENTO Y BARRERAS</h3>
                    <p class="obs-step-subinfo">Información sobre el inicio terapéutico, mayores tiempos de espera y barreras enfrentadas.</p>
                </div>

                <div class="obs-field-group">
                    <label>28. ¿Qué tratamiento le indicaron a la paciente inicialmente? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Cirugía" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Cirugía</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Quimioterapia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Quimioterapia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Radioterapia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Radioterapia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Terapia hormonal" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Terapia hormonal</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Terapia dirigida" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Terapia dirigida</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Inmunoterapia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Inmunoterapia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Combinación de tratamientos" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Combinación de tratamientos</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="Otro" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otro</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q28_tratamiento_inicial" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>29. Desde que recibió la paciente la confirmación del diagnóstico hasta que le indicaron el tratamiento, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="1–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q29_tiempo_confirmacion_a_indicacion" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>30. Desde que le indicaron el tratamiento a la paciente hasta que efectivamente lo inició, ¿cuánto tiempo pasó? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="Menos de 1 semana" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 semana</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="1–4 semanas" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–4 semanas</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="2–3 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">2–3 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="4–6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">4–6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="Más de 6 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 6 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="Más de 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="Todavía no lo he iniciado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Todavía no lo he iniciado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q30_tiempo_indicacion_a_inicio" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>31. ¿Dónde inició la paciente principalmente su tratamiento? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q31_lugar_inicio_tratamiento" value="Público" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Público</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q31_lugar_inicio_tratamiento" value="Privado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Privado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q31_lugar_inicio_tratamiento" value="En ambos" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">En ambos</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q31_lugar_inicio_tratamiento" value="Todavía no lo he iniciado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Todavía no lo he iniciado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q31_lugar_inicio_tratamiento" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>32. ¿En qué etapa la paciente sintió que tuvo que esperar más? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Primera consulta" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Primera consulta</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Exámenes" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Exámenes</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Biopsia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Biopsia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Resultado de la biopsia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Resultado de la biopsia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Estudios para determinar el estadio" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Estudios para determinar el estadio</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Indicación del tratamiento" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Indicación del tratamiento</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="Inicio del tratamiento" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Inicio del tratamiento</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="No tuve una espera importante" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No tuve una espera importante</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q32_etapa_mayor_espera" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>33. ¿Cuál fue la principal dificultad que la paciente enfrentó durante el recorrido? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Conseguir una cita" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Conseguir una cita</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Conseguir una derivación" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Conseguir una derivación</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Realizar los exámenes" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Realizar los exámenes</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Acceder a la biopsia" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Acceder a la biopsia</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Esperar los resultados" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Esperar los resultados</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Obtener autorizaciones" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Obtener autorizaciones</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Acceder al tratamiento o medicamentos" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Acceder al tratamiento o medicamentos</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Costo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Costo</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Distancia o traslado" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Distancia o traslado</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="No tuve dificultades" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No tuve dificultades</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q33_principal_dificultad_recorrido" value="Otra" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Otra</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>34. ¿En algún momento la paciente tuvo que acudir a un establecimiento privado para poder avanzar más rápido en alguna etapa? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q34_acudio_privado_rapidez" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q34_acudio_privado_rapidez" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q34_acudio_privado_rapidez" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>35. Durante el recorrido, ¿la paciente sintió que la demora en la atención retrasó el inicio de su tratamiento? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q35_demora_retraso_tratamiento" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q35_demora_retraso_tratamiento" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q35_demora_retraso_tratamiento" value="No estoy segura" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No estoy segura</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q35_demora_retraso_tratamiento" value="Todavía no inicio tratamiento" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Todavía no inicio tratamiento</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>36. Pensando en todo el recorrido de la paciente, desde la primera señal hasta el inicio de su primer tratamiento, ¿cuánto tiempo pasó aproximadamente? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="Menos de 1 mes" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Menos de 1 mes</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="1–2 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">1–2 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="3–4 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">3–4 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="5–7 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">5–7 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="8 - 9 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">8 - 9 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="10 a 12 meses" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">10 a 12 meses</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="Más de 1 año" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Más de 1 año</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="Todavía no inicio tratamiento" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Todavía no inicio tratamiento</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q36_tiempo_total_recorrido" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>37. Durante el recorrido de la paciente, ¿recibió la paciente información clara sobre cuál era el siguiente paso de su atención? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-2">
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="Siempre" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Siempre</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="Casi siempre" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Casi siempre</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="Algunas veces" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Algunas veces</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="Pocas veces" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Pocas veces</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="Nunca" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Nunca</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q37_informacion_clara_siguiente_paso" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <div class="obs-field-group">
                    <label>38. ¿Algún médico le indicó a la paciente que existe un tratamiento innovador que el sistema no cubre o que no cubre su seguro? <span class="req">*</span></label>
                    <div class="obs-options-grid cols-3">
                        <label class="obs-choice-card"><input type="radio" name="q38_tratamiento_innovador_no_cubierto" value="Sí" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">Sí</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q38_tratamiento_innovador_no_cubierto" value="No" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No</span></span></label>
                        <label class="obs-choice-card"><input type="radio" name="q38_tratamiento_innovador_no_cubierto" value="No recuerdo" required><span class="obs-choice-content"><span class="obs-radio-circle"></span><span class="obs-choice-label">No recuerdo</span></span></label>
                    </div>
                    <span class="obs-field-error"></span>
                </div>

                <!-- Bloque de Consentimiento y Términos -->
                <div class="obs-consent-card">
                    <p class="obs-consent-text">
                        <strong>*AUTORIZO AL OBSERVATORIO DE POR UN PERÚ SIN CÁNCER</strong> incorporar los datos brindados en el presente formulario en sus registros de análisis sobre demoras en la atención oncológica y falta de medicamentos en el sistema de salud. La información será utilizada de manera <strong>anonimizada</strong>, exclusivamente para fines estadísticos, de estudio, análisis y elaboración de propuestas orientadas a la mejora de la atención oncológica. (*)
                    </p>
                    <label class="obs-checkbox-container">
                        <input type="checkbox" name="consent_accepted" id="consent_accepted" value="1" required>
                        <span class="obs-custom-checkbox"></span>
                        <span class="obs-checkbox-label"><strong>Autorizo</strong> <span class="req">*</span></span>
                    </label>
                    <span class="obs-field-error"></span>
                </div>
            </div>

            <!-- Navegación y Botones del Formulario -->
            <div class="obs-form-actions">
                <button type="button" class="obs-btn obs-btn-prev" id="obs-btn-prev" style="display: none;">
                    <span class="obs-btn-icon">←</span> ATRÁS
                </button>
                <button type="button" class="obs-btn obs-btn-next" id="obs-btn-next">
                    SIGUIENTE <span class="obs-btn-icon">→</span>
                </button>
                <button type="submit" class="obs-btn obs-btn-submit" id="obs-btn-submit" style="display: none;">
                    <span class="obs-spinner" style="display: none;"></span>
                    <span class="obs-btn-text">ENVIAR</span>
                </button>
            </div>

            <div class="obs-global-status" id="obs-global-status" style="display: none;"></div>
        </form>

        <!-- Pantalla de Éxito / Agradecimiento -->
        <div class="obs-success-view" id="obs-success-view" style="display: none;">
            <div class="obs-confetti-container" id="obs-confetti-container" aria-hidden="true"></div>
            <div class="obs-success-icon-circle">
                <svg viewBox="0 0 52 52" class="obs-checkmark">
                    <circle cx="26" cy="26" r="25" fill="none"/>
                    <path fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <span class="obs-checkpoint-badge obs-badge-success">ENCUESTA REGISTRADA CON ÉXITO</span>
            <h2 class="obs-success-title">¡Muchas gracias por compartir tu experiencia!</h2>
            <p class="obs-success-message">
                Tus respuestas son fundamentales para visibilizar los tiempos de espera y promover mejoras urgentes en el sistema oncológico del Perú.
            </p>

            <div class="obs-share-card">
                <p class="obs-share-title">💜 Ayúdanos a llegar a más pacientes</p>
                <p class="obs-share-subtitle">Comparte este enlace para que más voces se sumen a la iniciativa:</p>
                <a href="#" id="obs-share-whatsapp-btn" target="_blank" rel="noopener noreferrer" class="obs-btn obs-btn-whatsapp">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" class="obs-wa-svg">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.854 2.796.855 3.18 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.766-5.768-5.766zm9.969 5.766c0 5.514-4.486 10-10 10-1.758 0-3.41-.458-4.856-1.258l-5.144 1.347 1.373-5.022c-.872-1.503-1.373-3.245-1.373-5.067 0-5.514 4.486-10 10-10s10 4.486 10 10z"/>
                    </svg>
                    Compartir por WhatsApp
                </a>
            </div>

            <div class="obs-success-actions">
                <a href="/" class="obs-btn obs-btn-outline">Volver al Inicio</a>
            </div>
        </div>
    </div>
</div>
