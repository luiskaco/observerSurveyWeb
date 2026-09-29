/**
 * Observatorio Survey — Wizard interactivo Multi-Paso
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('obs-survey-form');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.obs-wizard-step'));
    const pills = Array.from(document.querySelectorAll('.obs-step-pill'));
    const btnPrev = document.getElementById('obs-btn-prev');
    const btnNext = document.getElementById('obs-btn-next');
    const btnSubmit = document.getElementById('obs-btn-submit');
    const progressFill = document.querySelector('.obs-progress-fill');
    const progressText = document.querySelector('.obs-step-title-display');
    const progressPercent = document.querySelector('.obs-step-percentage');
    const globalStatus = document.getElementById('obs-global-status');
    const successView = document.getElementById('obs-success-view');

    let currentStep = 1;
    const totalSteps = steps.length;

    // Utilidad para convertir texto a Title Case
    const toTitleCase = (str) => {
        if (!str) return '';
        return str.toLowerCase().replace(/(?:^|\s|[-'])\S/g, char => char.toUpperCase());
    };

    const stepTitles = {
        1: 'Paso 1 de 6: Antecedentes',
        2: 'Paso 2 de 6: Lugar de Diagnóstico y Derivación',
        3: 'Paso 3 de 6: Primera Señal y Consulta',
        4: 'Paso 4 de 6: Exámenes y Derivación',
        5: 'Paso 5 de 6: Biopsia y Estadio',
        6: 'Paso 6 de 6: Tratamiento y Experiencia',
    };

    // --- Lógica Condicional ---
    function setupConditionalLogic() {
        // Región de diagnóstico y derivación
        const q1Radios = form.querySelectorAll('input[name="q1_diag_place"]');
        const blockRegionDeriv = document.getElementById('block-region-derivacion');
        const q2Radios = form.querySelectorAll('input[name="q2_derivada_lima"]');
        const blockEtapaLima = document.getElementById('block-etapa-lima');
        const blockTiempoLima = document.getElementById('block-tiempo-lima');

        q1Radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'Otra región del Perú') {
                    blockRegionDeriv.style.display = 'block';
                } else {
                    blockRegionDeriv.style.display = 'none';
                    clearInputsInside(blockRegionDeriv);
                    if (blockEtapaLima) blockEtapaLima.style.display = 'none';
                    if (blockTiempoLima) blockTiempoLima.style.display = 'none';
                }
            });
        });

        q2Radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'Si') {
                    if (blockEtapaLima) blockEtapaLima.style.display = 'block';
                    if (blockTiempoLima) blockTiempoLima.style.display = 'block';
                } else {
                    if (blockEtapaLima) {
                        blockEtapaLima.style.display = 'none';
                        clearInputsInside(blockEtapaLima);
                    }
                    if (blockTiempoLima) {
                        blockTiempoLima.style.display = 'none';
                        clearInputsInside(blockTiempoLima);
                    }
                }
            });
        });

        // Subtipo de cáncer (Q22 -> Q23)
        const q22Radios = form.querySelectorAll('input[name="q22_informaron_subtipo"]');
        const blockQ23 = document.getElementById('block-q23');
        q22Radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'Sí') {
                    if (blockQ23) blockQ23.style.display = 'block';
                } else {
                    if (blockQ23) {
                        blockQ23.style.display = 'none';
                        clearInputsInside(blockQ23);
                    }
                }
            });
        });

        // Estadio diferente (Q26 -> Q27)
        const q26Radios = form.querySelectorAll('input[name="q26_estadio_diferente"]');
        const blockQ27 = document.getElementById('block-q27');
        q26Radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'Sí') {
                    if (blockQ27) blockQ27.style.display = 'block';
                } else {
                    if (blockQ27) {
                        blockQ27.style.display = 'none';
                        clearInputsInside(blockQ27);
                    }
                }
            });
        });
    }

    function clearInputsInside(container) {
        if (!container) return;
        const inputs = container.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });
    }

    // --- Máscaras y Restricciones de Tipo en Tiempo Real ---
    function setupInputMasks() {
        const ageInput = document.getElementById('age');
        const phoneInput = document.getElementById('phone');
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');


        // Campos numéricos (Solo dígitos)
        const applyNumericOnly = (input, maxLength) => {
            if (!input) return;

            // Bloquear teclas no numéricas
            input.addEventListener('keydown', (e) => {
                const allowedKeys = ['Backspace', 'Tab', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'Enter'];
                if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey) {
                    return;
                }
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Limpiar pegado o autocompletado con caracteres inválidos
            input.addEventListener('input', function () {
                let clean = this.value.replace(/\D/g, '');
                if (maxLength && clean.length > maxLength) {
                    clean = clean.slice(0, maxLength);
                }
                this.value = clean;
            });
        };

        // Teléfono: separación en bloques de 3 dígitos (ej: 333 333 333)
        const applyPhoneMask = (input) => {
            if (!input) return;

            input.addEventListener('keydown', (e) => {
                const allowedKeys = ['Backspace', 'Tab', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'Enter'];
                if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey) {
                    return;
                }
                if (!/^[0-9\s]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('input', function () {
                let digits = this.value.replace(/\D/g, '').slice(0, 9);
                let formatted = '';
                for (let i = 0; i < digits.length; i++) {
                    if (i > 0 && i % 3 === 0) {
                        formatted += ' ';
                    }
                    formatted += digits[i];
                }
                this.value = formatted;
            });
        };

        applyNumericOnly(ageInput, 3);
        applyPhoneMask(phoneInput);

        // Nombres y Apellidos (Solo letras, espacios, tildes y guiones) + Auto Title Case
        const applyTextOnly = (input) => {
            if (!input) return;
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]/g, '');
            });
            input.addEventListener('blur', function () {
                this.value = toTitleCase(this.value.trim());
            });
        };

        applyTextOnly(firstNameInput);
        applyTextOnly(lastNameInput);
    }

    // --- Validación de Paso ---
    function validateStep(stepNumber) {
        const currentStepEl = form.querySelector(`.obs-wizard-step[data-step="${stepNumber}"]`);
        if (!currentStepEl) return true;

        let isValid = true;
        let firstErrorGroup = null;
        const fieldGroups = currentStepEl.querySelectorAll('.obs-field-group, .obs-consent-card');

        fieldGroups.forEach(group => {
            // Ignorar grupos dentro de bloques condicionales ocultos
            const isHidden = group.style.display === 'none' || 
                             (window.getComputedStyle(group).display === 'none') || 
                             group.closest('.obs-conditional-block[style*="none"]') ||
                             (group.closest('.obs-conditional-block') && window.getComputedStyle(group.closest('.obs-conditional-block')).display === 'none');

            if (isHidden) {
                clearFieldError(group);
                return;
            }

            const errorSpan = group.querySelector('.obs-field-error');
            const inputs = group.querySelectorAll('input, select, textarea');
            let groupValid = true;
            let errorMessage = 'Este campo es obligatorio.';

            if (inputs.length === 1 && (inputs[0].type === 'text' || inputs[0].type === 'number' || inputs[0].type === 'email' || inputs[0].type === 'tel' || inputs[0].tagName === 'SELECT')) {
                const input = inputs[0];
                const val = input.value.trim();

                if (input.hasAttribute('required') && !val) {
                    groupValid = false;
                } else if (input.type === 'email' && val && !validateEmail(val)) {
                    groupValid = false;
                    errorMessage = 'Ingresa un correo electrónico válido (ej. usuario@dominio.com).';
                } else if (input.id === 'age' && val) {
                    const ageNum = parseInt(val, 10);
                    if (isNaN(ageNum) || ageNum < 10 || ageNum > 120) {
                        groupValid = false;
                        errorMessage = 'Ingresa una edad válida (entre 10 y 120 años).';
                    }
                } else if (input.id === 'phone' && val) {
                    const phoneDigits = val.replace(/\D/g, '');
                    if (phoneDigits.length < 9) {
                        groupValid = false;
                        errorMessage = 'El número de celular debe tener 9 dígitos.';
                    }
                } else if ((input.id === 'first_name' || input.id === 'last_name') && val) {
                    if (val.length < 2) {
                        groupValid = false;
                        errorMessage = 'Por favor ingresa un nombre válido.';
                    }
                }
            } else if (inputs.length > 0 && inputs[0].type === 'radio') {
                const isRequired = Array.from(inputs).some(r => r.hasAttribute('required'));
                if (isRequired) {
                    const isChecked = Array.from(inputs).some(r => r.checked);
                    if (!isChecked) {
                        groupValid = false;
                        errorMessage = 'Por favor selecciona una opción.';
                    }
                }
            } else if (group.classList.contains('obs-consent-card')) {
                const consentCheckbox = group.querySelector('input[name="consent_accepted"]');
                if (consentCheckbox && !consentCheckbox.checked) {
                    groupValid = false;
                    errorMessage = 'Debes autorizar el uso anónimo de los datos para continuar.';
                }
            }

            if (!groupValid) {
                isValid = false;
                group.classList.add('has-error');
                if (errorSpan) errorSpan.textContent = errorMessage;
                if (!firstErrorGroup) {
                    firstErrorGroup = group;
                }
            } else {
                clearFieldError(group);
            }
        });

        if (!isValid && firstErrorGroup) {
            firstErrorGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    function clearFieldError(group) {
        group.classList.remove('has-error');
        const errorSpan = group.querySelector('.obs-field-error');
        if (errorSpan) errorSpan.textContent = '';
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Limpiar errores en cambio de valor
    form.addEventListener('input', (e) => {
        const group = e.target.closest('.obs-field-group, .obs-consent-card');
        if (group) clearFieldError(group);
        saveDraft();
    });

    form.addEventListener('change', (e) => {
        const group = e.target.closest('.obs-field-group, .obs-consent-card');
        if (group) clearFieldError(group);
        saveDraft();
    });

    // --- Navegación del Wizard ---
    function updateWizardUI() {
        steps.forEach((step, index) => {
            const stepNum = index + 1;
            if (stepNum === currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });

        pills.forEach((pill, index) => {
            const stepNum = index + 1;
            pill.classList.remove('active', 'completed');
            if (stepNum === currentStep) {
                pill.classList.add('active');
            } else if (stepNum < currentStep) {
                pill.classList.add('completed');
            }
        });

        // Botones
        if (currentStep === 1) {
            btnPrev.style.display = 'none';
        } else {
            btnPrev.style.display = 'inline-flex';
        }

        if (currentStep === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-flex';
        } else {
            btnNext.style.display = 'inline-flex';
            btnSubmit.style.display = 'none';
        }

        // Progreso
        const percent = Math.round((currentStep / totalSteps) * 100);
        if (progressFill) progressFill.style.width = `${percent}%`;
        if (progressPercent) progressPercent.textContent = `${percent}%`;
        if (progressText) progressText.textContent = stepTitles[currentStep] || `Paso ${currentStep}`;

        // Scroll al formulario
        const appCard = document.querySelector('.obs-survey-card');
        if (appCard) {
            const rect = appCard.getBoundingClientRect();
            if (rect.top < 0) {
                appCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    // --- Persistencia de Borrador y Autosave Progresivo ---
    const STORAGE_KEY = 'obs_survey_mama_draft';
    const SUBMISSION_ID_KEY = 'obs_survey_submission_id';
    let currentSubmissionId = localStorage.getItem(SUBMISSION_ID_KEY) || null;

    function saveDraft() {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((val, key) => {
            if (key !== 'hp_field') {
                data[key] = val;
            }
        });
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (err) {}
    }

    function loadDraft() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const data = JSON.parse(raw);
            Object.keys(data).forEach(key => {
                const fields = form.querySelectorAll(`[name="${key}"]`);
                if (fields.length === 1 && (fields[0].type === 'text' || fields[0].type === 'number' || fields[0].type === 'email' || fields[0].type === 'tel' || fields[0].tagName === 'SELECT')) {
                    fields[0].value = data[key];
                } else if (fields.length > 0 && fields[0].type === 'radio') {
                    fields.forEach(radio => {
                        if (radio.value === data[key]) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change'));
                        }
                    });
                } else if (fields.length === 1 && fields[0].type === 'checkbox') {
                    fields[0].checked = (data[key] === '1' || data[key] === true);
                }
            });
        } catch (err) {}
    }

    /**
     * Guarda el progreso del paso completado en segundo plano sin bloquear la UI
     */
    function saveStepProgress(stepNumber) {
        const formData = new FormData(form);
        const payload = {
            survey_type: 'cancer_mama_journey',
            step: stepNumber,
            submission_id: currentSubmissionId ? parseInt(currentSubmissionId, 10) : null,
            hp_field: formData.get('hp_field') || '',
            first_name: toTitleCase((formData.get('first_name') || '').trim()),
            last_name: toTitleCase((formData.get('last_name') || '').trim()),
            age: formData.get('age') || '',
            phone: (formData.get('phone') || '').trim(),
            email: formData.get('email') || '',
            region: formData.get('region') || '',
            is_current_patient: formData.get('is_current_patient') || '',
            health_system: formData.get('health_system') || '',
            age_diagnosis: formData.get('age_diagnosis') || '',
            responses: {}
        };

        formData.forEach((val, key) => {
            if (![
                'hp_field', 'first_name', 'last_name', 'age', 'phone', 
                'email', 'region', 'is_current_patient', 'health_system', 
                'age_diagnosis', 'consent_accepted'
            ].includes(key)) {
                payload.responses[key] = val;
            }
        });

        const stepSaveUrl = window.obsSurveyConfig && window.obsSurveyConfig.stepSaveUrl 
            ? window.obsSurveyConfig.stepSaveUrl 
            : '/wp-json/observatorio/v1/survey/step-save';
        const nonce = window.obsSurveyConfig ? window.obsSurveyConfig.nonce : '';

        const headers = { 'Content-Type': 'application/json' };
        if (nonce) headers['X-WP-Nonce'] = nonce;

        fetch(stepSaveUrl, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'success' && data.submission_id) {
                currentSubmissionId = data.submission_id;
                try {
                    localStorage.setItem(SUBMISSION_ID_KEY, data.submission_id);
                } catch (e) {}
            }
        })
        .catch(err => {
            console.warn('[Observatorio Survey Autosave] Step save error (silently recovered):', err);
        });
    }

    // --- Banner de Hito Motivacional Integrado (Top of Step) ---
    const MILESTONES_DATA = {
        2: {
            iconSvg: `<svg viewBox="0 0 24 24" class="obs-ms-svg" fill="none" stroke="#d81b60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>`,
            title: '¡Paso 1 completado! 💜',
            desc: 'Tu experiencia ayuda a que más mujeres conozcan la importancia de una detección temprana y oportuna.'
        },
        3: {
            iconSvg: `<svg viewBox="0 0 24 24" class="obs-ms-svg" fill="none" stroke="#381e72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <path d="M9 12l2 2 4-4"></path>
            </svg>`,
            title: '¡Excelente avance!',
            desc: 'Comprender los tiempos de derivación permite documentar las dificultades en cada región.'
        },
        4: {
            iconSvg: `<svg viewBox="0 0 24 24" class="obs-ms-svg" fill="none" stroke="#d81b60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="7"></circle>
                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
            </svg>`,
            title: '¡Vas a más de la mitad! 🌟',
            desc: 'Cada respuesta que brindas es clave para exigir que no haya demoras en las consultas médicas.'
        },
        5: {
            iconSvg: `<svg viewBox="0 0 24 24" class="obs-ms-svg" fill="none" stroke="#381e72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>`,
            title: '¡Ya falta muy poco!',
            desc: 'Agradecemos tu valentía y generosidad al compartir este recorrido. Estamos cerca de finalizar.'
        },
        6: {
            iconSvg: `<svg viewBox="0 0 24 24" class="obs-ms-svg" fill="none" stroke="#d81b60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>`,
            title: '¡Último esfuerzo para finalizar! 🎉',
            desc: 'Solo unos datos finales para registrar formalmente tu valioso testimonio.'
        }
    };

    const milestoneBanner = document.getElementById('obs-milestone-banner');
    const msIcon = document.getElementById('obs-ms-icon');
    const msTitle = document.getElementById('obs-ms-title');
    const msDesc = document.getElementById('obs-ms-desc');
    const msClose = document.getElementById('obs-ms-close');
    let milestoneTimer = null;

    function showMilestoneBanner(stepNumber) {
        const data = MILESTONES_DATA[stepNumber];
        if (!data || !milestoneBanner) {
            hideMilestoneBanner(true);
            return;
        }

        if (msIcon) msIcon.innerHTML = data.iconSvg;
        if (msTitle) msTitle.textContent = data.title;
        if (msDesc) msDesc.textContent = data.desc;

        milestoneBanner.classList.remove('fading');
        milestoneBanner.style.display = 'flex';

        clearTimeout(milestoneTimer);
        milestoneTimer = setTimeout(() => {
            hideMilestoneBanner();
        }, 7000);
    }

    function hideMilestoneBanner(instant = false) {
        clearTimeout(milestoneTimer);
        if (!milestoneBanner || milestoneBanner.style.display === 'none') return;
        if (instant) {
            milestoneBanner.style.display = 'none';
            milestoneBanner.classList.remove('fading');
        } else {
            milestoneBanner.classList.add('fading');
            milestoneTimer = setTimeout(() => {
                milestoneBanner.style.display = 'none';
                milestoneBanner.classList.remove('fading');
            }, 400);
        }
    }

    if (msClose) {
        msClose.addEventListener('click', () => {
            hideMilestoneBanner();
        });
    }

    btnNext.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            const stepCompleted = currentStep;
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardUI();
                saveStepProgress(stepCompleted);
                showMilestoneBanner(currentStep);
            }
        }
    });

    btnPrev.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateWizardUI();
            hideMilestoneBanner(true);
        }
    });

    function triggerSuccessCelebration() {
        const confettiContainer = document.getElementById('obs-confetti-container');
        if (confettiContainer) {
            confettiContainer.innerHTML = '';
            const colors = ['#381e72', '#d81b60', '#10b981', '#7b63bf', '#f59e0b', '#ec4899'];
            for (let i = 0; i < 45; i++) {
                const piece = document.createElement('div');
                piece.className = 'obs-confetti-piece';
                piece.style.left = Math.random() * 100 + '%';
                piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                piece.style.animationDelay = (Math.random() * 1.5) + 's';
                piece.style.animationDuration = (2.5 + Math.random() * 2) + 's';
                confettiContainer.appendChild(piece);
            }
        }

        const waBtn = document.getElementById('obs-share-whatsapp-btn');
        if (waBtn) {
            const shareUrl = window.location.href.split('?')[0];
            const shareText = encodeURIComponent(
                "🌸 Hola, te invito a sumarte a la encuesta nacional del Observatorio sobre los tiempos de atención y diagnóstico del cáncer de mama en el Perú. ¡Cada testimonio ayuda a salvar vidas! Puedes llenarla aquí: " + shareUrl
            );
            waBtn.href = `https://api.whatsapp.com/send?text=${shareText}`;
        }
    }

    // --- Envío del Formulario (AJAX / REST API) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateStep(currentStep)) {
            return;
        }

        // Preparar payload
        const formData = new FormData(form);
        const payload = {
            survey_type: 'cancer_mama_journey',
            submission_id: currentSubmissionId ? parseInt(currentSubmissionId, 10) : null,
            hp_field: formData.get('hp_field') || '',
            first_name: toTitleCase((formData.get('first_name') || '').trim()),
            last_name: toTitleCase((formData.get('last_name') || '').trim()),
            age: formData.get('age') || '',
            phone: (formData.get('phone') || '').trim(),
            email: formData.get('email') || '',
            region: formData.get('region') || '',
            is_current_patient: formData.get('is_current_patient') || '',
            health_system: formData.get('health_system') || '',
            age_diagnosis: formData.get('age_diagnosis') || '',
            consent_accepted: formData.get('consent_accepted') ? 1 : 0,
            responses: {}
        };

        formData.forEach((val, key) => {
            if (![
                'hp_field', 'first_name', 'last_name', 'age', 'phone', 
                'email', 'region', 'is_current_patient', 'health_system', 
                'age_diagnosis', 'consent_accepted'
            ].includes(key)) {
                payload.responses[key] = val;
            }
        });

        // UI Loading State
        btnSubmit.disabled = true;
        const spinner = btnSubmit.querySelector('.obs-spinner');
        const btnText = btnSubmit.querySelector('.obs-btn-text');
        if (spinner) spinner.style.display = 'inline-block';
        if (btnText) btnText.textContent = 'ENVIANDO...';
        if (globalStatus) globalStatus.style.display = 'none';

        try {
            const apiUrl = window.obsSurveyConfig && window.obsSurveyConfig.apiUrl 
                ? window.obsSurveyConfig.apiUrl 
                : '/wp-json/observatorio/v1/survey/submit';
            const nonce = window.obsSurveyConfig ? window.obsSurveyConfig.nonce : '';

            const headers = {
                'Content-Type': 'application/json'
            };
            if (nonce) {
                headers['X-WP-Nonce'] = nonce;
            }

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                // Borrar borrador y submission_id
                try {
                    localStorage.removeItem(STORAGE_KEY);
                    localStorage.removeItem(SUBMISSION_ID_KEY);
                } catch (e) {}
                currentSubmissionId = null;

                // Ocultar form y mostrar éxito
                form.style.display = 'none';
                const progressWrapper = document.querySelector('.obs-progress-bar-wrapper');
                const notice = document.querySelector('.obs-mandatory-notice');
                if (progressWrapper) progressWrapper.style.display = 'none';
                if (notice) notice.style.display = 'none';
                if (successView) {
                    successView.style.display = 'block';
                    triggerSuccessCelebration();
                }

                successView.scrollIntoView({ behavior: 'smooth' });
            } else {
                if (data.errors && typeof data.errors === 'object') {
                    const firstErrorMsg = Object.values(data.errors)[0];
                    throw new Error(firstErrorMsg || data.message || 'Por favor completa todos los campos requeridos.');
                }
                throw new Error(data.message || 'Ocurrió un error al enviar el formulario.');
            }
        } catch (error) {
            if (globalStatus) {
                globalStatus.textContent = error.message || 'Error de conexión. Por favor intenta nuevamente.';
                globalStatus.className = 'obs-global-status error';
                globalStatus.style.display = 'block';
                globalStatus.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } finally {
            btnSubmit.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (btnText) btnText.textContent = 'ENVIAR ENCUESTA';
        }
    });

    // Iniciar
    setupConditionalLogic();
    setupInputMasks();
    loadDraft();
    updateWizardUI();
});

