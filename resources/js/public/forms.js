export function init() {
    document.querySelectorAll('[data-sign-cta]').forEach(function (el) {
        el.addEventListener('click', function () {
            if (typeof window.gtag === 'function') {
                window.gtag('event', 'sign_cta_click', { variant: el.dataset.cached === '1' ? 'stateless' : 'session' });
            }
        });
    });

    document.querySelectorAll('form').forEach(function (form) {
        if (form.dataset.fcLoadingBound === '1') return;
        if (!form.querySelector('[data-loading]')) return;
        form.dataset.fcLoadingBound = '1';

        form.addEventListener('submit', function (event) {
            window.setTimeout(function () {
                if (event.defaultPrevented) return;

                form.querySelectorAll('[data-loading]').forEach(function (el) {
                    if (el.dataset.fcLoadingActive === '1') return;
                    el.dataset.fcLoadingActive = '1';

                    if (typeof el.disabled === 'boolean') {
                        el.disabled = true;
                    }
                    el.setAttribute('aria-busy', 'true');

                    const label = el.querySelector('.fcui-btn__label');
                    const busyText = el.dataset.loadingText;
                    if (label && busyText) {
                        label.textContent = busyText;
                    }
                });
            }, 0);
        });
    });

    document.querySelectorAll('[data-busy-submit]').forEach(function (btn) {
        const form = btn.closest('form');
        if (!form) return;
        form.addEventListener('submit', function () {
            btn.disabled = true;
        });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        if (form.dataset.fcValidateBound === '1') return;
        form.dataset.fcValidateBound = '1';
        const msgRequired = form.dataset.msgRequired || 'This field is required';
        const msgEmail = form.dataset.msgEmail || 'Enter a valid email';
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const fields = form.querySelectorAll('input[name], textarea[name], select[name]');
        fields.forEach(function (field) {
            const name = field.getAttribute('name');
            if (!name) return;
            const errorEl = form.querySelector('[data-error-for="' + name + '"]');

            const setError = function (message) {
                if (message) {
                    field.classList.add('fcui-field-invalid');
                    if (errorEl) {
                        errorEl.textContent = message;
                        errorEl.hidden = false;
                    }
                } else {
                    field.classList.remove('fcui-field-invalid');
                    if (errorEl) {
                        errorEl.textContent = '';
                        errorEl.hidden = true;
                    }
                }
            };

            const validate = function () {
                const value = (field.value || '').trim();
                if (field.hasAttribute('required') && value === '') {
                    setError(msgRequired);
                    return;
                }
                const type = (field.getAttribute('type') || '').toLowerCase();
                if (type === 'email' && value !== '' && !emailPattern.test(value)) {
                    setError(msgEmail);
                    return;
                }
                setError('');
            };

            field.addEventListener('blur', validate);
            field.addEventListener('input', validate);
        });
    });

    document.querySelectorAll('[data-faq-search-input]').forEach(function (input) {
        const root = document.querySelector('[data-faq-search-root]');
        if (!root) return;

        const empty = document.querySelector('[data-faq-search-empty]');

        const run = function () {
            const query = input.value.trim().toLowerCase();
            let visible = 0;

            root.querySelectorAll('.accordion-item').forEach(function (item) {
                const match = query === '' || (item.textContent || '').toLowerCase().indexOf(query) !== -1;
                item.hidden = !match;
                if (match) visible++;
            });

            root.querySelectorAll('.accordion').forEach(function (accordion) {
                const any = Array.prototype.some.call(accordion.querySelectorAll('.accordion-item'), function (item) {
                    return !item.hidden;
                });
                const heading = accordion.previousElementSibling;

                if (heading && heading.tagName === 'H2') heading.hidden = !any;
                accordion.hidden = !any;
            });

            if (empty) empty.hidden = visible !== 0;
        };

        input.addEventListener('input', window.fcDebounce ? window.fcDebounce(run, 150) : run);
        run();
    });

    document.querySelectorAll('form[data-live-search]').forEach(function (form) {
        const input = form.querySelector('input[name="q"]');
        if (!input) return;

        const skeleton = document.querySelector('[data-skeleton]');
        const results = document.querySelector('[data-results]');

        const busy = function () {
            if (skeleton) skeleton.removeAttribute('hidden');
            if (results) results.setAttribute('aria-busy', 'true');
        };

        const submit = window.fcDebounce(function () {
            busy();
            form.submit();
        }, 450);

        input.addEventListener('input', function () {
            submit();
        });

        form.addEventListener('submit', function () {
            busy();
        });

        window.addEventListener('pageshow', function () {
            if (skeleton) skeleton.setAttribute('hidden', 'hidden');
            if (results) results.removeAttribute('aria-busy');
        });
    });

    const eyeOpenSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeOffSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.hasAttribute('data-no-toggle')) return;
        if (input.dataset.fcPwToggle === '1') return;
        input.dataset.fcPwToggle = '1';

        const parent = input.parentElement;
        if (!parent) return;
        let wrapper;
        if (parent.classList.contains('fcui-pwwrap')) {
            wrapper = parent;
        } else {
            wrapper = document.createElement('span');
            wrapper.className = 'fcui-pwwrap';
            parent.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'fcui-pwtoggle';
        btn.setAttribute('aria-label', 'Show password');
        btn.setAttribute('aria-pressed', 'false');
        btn.innerHTML = eyeOpenSvg;
        wrapper.appendChild(btn);

        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.innerHTML = show ? eyeOffSvg : eyeOpenSvg;
        });
    });

    const strengthFallbackLabels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['#cc1f1f', '#f97316', '#f6c343', '#84cc16', '#2f9e44'];

    document.querySelectorAll('input[type="password"][data-strength]').forEach(function (input) {
        if (input.dataset.fcStrength === '1') return;
        input.dataset.fcStrength = '1';

        let labels = strengthFallbackLabels;
        if (input.dataset.strengthLabels) {
            try {
                const parsed = JSON.parse(input.dataset.strengthLabels);
                if (Array.isArray(parsed) && parsed.length === 5) {
                    labels = parsed;
                }
            } catch (error) {
                labels = strengthFallbackLabels;
            }
        }

        const meter = document.createElement('div');
        meter.className = 'fcui-pwmeter';
        const barWrap = document.createElement('div');
        barWrap.className = 'fcui-pwmeter__track';
        const bar = document.createElement('div');
        bar.className = 'fcui-pwmeter__bar';
        barWrap.appendChild(bar);
        const text = document.createElement('span');
        text.className = 'fcui-pwmeter__label';
        meter.appendChild(barWrap);
        meter.appendChild(text);
        const meterAnchor = input.closest('.fcui-pwwrap') || input;
        meterAnchor.insertAdjacentElement('afterend', meter);

        const score = function (value) {
            if (!value) return -1;
            let s = 0;
            if (value.length >= 8) s++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) s++;
            if (/\d/.test(value)) s++;
            if (/[^A-Za-z0-9]/.test(value)) s++;
            if (value.length >= 12 && s >= 3) s++;
            if (value.length < 6) s = Math.min(s, 1);
            return Math.max(0, Math.min(4, s));
        };

        const update = function () {
            const value = input.value || '';
            const idx = score(value);
            if (idx < 0) {
                bar.style.width = '0%';
                bar.style.backgroundColor = 'transparent';
                text.textContent = '';
                return;
            }
            bar.style.width = ((idx + 1) / 5 * 100) + '%';
            bar.style.backgroundColor = strengthColors[idx];
            text.textContent = labels[idx];
            text.style.color = strengthColors[idx];
        };

        input.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('textarea[data-maxcount], input[data-maxcount]').forEach(function (field) {
        if (field.dataset.fcCharcount === '1') return;
        const max = Number(field.dataset.maxcount);
        if (!Number.isFinite(max) || max <= 0) return;
        field.dataset.fcCharcount = '1';

        const counter = document.createElement('div');
        counter.className = 'fcui-charcount';
        field.insertAdjacentElement('afterend', counter);

        const update = function () {
            const used = (field.value || '').length;
            counter.textContent = used + '/' + max;
            if (used >= max) {
                counter.classList.add('is-over');
            } else {
                counter.classList.remove('is-over');
            }
        };

        field.addEventListener('input', update);
        update();
    });

    const emailProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'aol.com', 'protonmail.com'];
    const levenshtein = function (a, b) {
        const m = a.length;
        const n = b.length;
        if (m === 0) return n;
        if (n === 0) return m;
        const row = [];
        for (let j = 0; j <= n; j++) row[j] = j;
        for (let i = 1; i <= m; i++) {
            let prev = row[0];
            row[0] = i;
            for (let j = 1; j <= n; j++) {
                const temp = row[j];
                row[j] = Math.min(
                    row[j] + 1,
                    row[j - 1] + 1,
                    prev + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1)
                );
                prev = temp;
            }
        }
        return row[n];
    };

    document.querySelectorAll('input[type="email"][data-email-suggest]').forEach(function (input) {
        if (input.dataset.fcEmailSuggest === '1') return;
        input.dataset.fcEmailSuggest = '1';

        const box = document.createElement('div');
        box.className = 'fcui-email-suggest';
        box.setAttribute('data-email-suggest-box', '');
        box.hidden = true;
        input.insertAdjacentElement('afterend', box);

        const hide = function () {
            box.hidden = true;
            box.innerHTML = '';
        };

        input.addEventListener('blur', function () {
            const value = (input.value || '').trim();
            const at = value.lastIndexOf('@');
            if (at < 1 || at === value.length - 1) {
                hide();
                return;
            }
            const local = value.slice(0, at);
            const domain = value.slice(at + 1).toLowerCase();
            if (emailProviders.indexOf(domain) !== -1) {
                hide();
                return;
            }
            let best = null;
            let bestDist = 3;
            emailProviders.forEach(function (provider) {
                const dist = levenshtein(domain, provider);
                if (dist > 0 && dist <= 2 && dist < bestDist) {
                    bestDist = dist;
                    best = provider;
                }
            });
            if (!best) {
                hide();
                return;
            }
            const suggestion = local + '@' + best;
            box.innerHTML = '';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'fcui-email-suggest__btn';
            btn.innerHTML = (input.dataset.emailSuggestPrefix || 'Did you mean') + ' <strong>' + suggestion.replace(/[<>&]/g, '') + '</strong>?';
            btn.addEventListener('click', function () {
                input.value = suggestion;
                hide();
                input.focus();
            });
            box.appendChild(btn);
            box.hidden = false;
        });

        input.addEventListener('input', hide);
    });

    const contactForm = document.getElementById('contactForm');
    if (!contactForm || !window.clientToast) return;

    contactForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = contactForm.querySelector('button[type="submit"], input[type="submit"]');
        const original = btn.value;
        btn.disabled = true;
        btn.value = contactForm.dataset.sending;

        fetch(contactForm.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(contactForm),
        })
            .then(function (res) {
                if (!res.ok) {
                    return res.json().catch(function () {
                        return {};
                    }).then(function (data) {
                        const msg = data.message
                            || (data.errors && Object.values(data.errors)[0]?.[0])
                            || contactForm.dataset.failed;
                        throw new Error(msg);
                    });
                }
                return res.json();
            })
            .then(function () {
                contactForm.reset();

                const success = document.getElementById('contactSuccess');
                if (success) {
                    contactForm.hidden = true;
                    success.hidden = false;
                    success.focus?.();
                    return;
                }

                window.clientToast.success(contactForm.dataset.sent);
            })
            .catch(function (err) {
                window.clientToast.error(err.message || contactForm.dataset.failed);
            })
            .finally(function () {
                btn.disabled = false;
                btn.value = original;
            });
    });

    const contactReset = document.querySelector('[data-contact-reset]');
    const contactSuccess = document.getElementById('contactSuccess');

    if (contactReset && contactSuccess && contactForm) {
        contactReset.addEventListener('click', function () {
            contactSuccess.hidden = true;
            contactForm.hidden = false;
            contactForm.querySelector('input, textarea, select')?.focus();
        });
    }
}
