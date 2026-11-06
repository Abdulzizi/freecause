/* First-party preferences, shared by the site and magazine. No TCF claim. */
(function () {
    "use strict";
    const translations = {
    "en": [
        "Cookie settings",
        "Cookies and privacy",
        "Essential cookies keep FreeCause working. Analytics and Adsterra advertising are optional. Your choice applies to the site and magazine.",
        "Accept all",
        "Reject optional",
        "Preferences",
        "Save preferences",
        "Close",
        "Essential",
        "Login, security and your saved privacy choices. Always enabled.",
        "Analytics",
        "Google Analytics measures visits only if you agree.",
        "Advertising",
        "Adsterra may use cookies and device identifiers to display and measure ads. It loads only if you agree.",
        "Privacy policy"
    ],
    "it": [
        "Impostazioni cookie",
        "Cookie e privacy",
        "I cookie necessari fanno funzionare FreeCause. Statistiche e pubblicità Adsterra sono facoltative. La scelta vale per sito e magazine.",
        "Accetta tutti",
        "Rifiuta facoltativi",
        "Preferenze",
        "Salva preferenze",
        "Chiudi",
        "Necessari",
        "Accesso, sicurezza e preferenze privacy salvate. Sempre attivi.",
        "Statistiche",
        "Google Analytics misura le visite solo con il tuo consenso.",
        "Pubblicità",
        "Adsterra può usare cookie e identificatori del dispositivo per mostrare e misurare gli annunci. Si carica solo con il tuo consenso.",
        "Informativa privacy"
    ],
    "de": [
        "Cookie-Einstellungen",
        "Cookies und Datenschutz",
        "Notwendige Cookies ermöglichen FreeCause. Statistiken und Adsterra-Werbung sind freiwillig. Ihre Wahl gilt für Website und Magazin.",
        "Alle akzeptieren",
        "Optionale ablehnen",
        "Einstellungen",
        "Auswahl speichern",
        "Schließen",
        "Notwendig",
        "Anmeldung, Sicherheit und gespeicherte Datenschutzeinstellungen. Immer aktiv.",
        "Statistiken",
        "Google Analytics misst Besuche nur mit Ihrer Zustimmung.",
        "Werbung",
        "Adsterra kann Cookies und Gerätekennungen verwenden, um Anzeigen zu zeigen und zu messen. Der Dienst lädt nur mit Ihrer Zustimmung.",
        "Datenschutzerklärung"
    ],
    "fr": [
        "Paramètres des cookies",
        "Cookies et confidentialité",
        "Les cookies nécessaires font fonctionner FreeCause. Les statistiques et la publicité Adsterra sont facultatives. Votre choix vaut pour le site et le magazine.",
        "Tout accepter",
        "Refuser les facultatifs",
        "Préférences",
        "Enregistrer les choix",
        "Fermer",
        "Nécessaires",
        "Connexion, sécurité et choix de confidentialité enregistrés. Toujours actifs.",
        "Statistiques",
        "Google Analytics mesure les visites uniquement avec votre accord.",
        "Publicité",
        "Adsterra peut utiliser des cookies et des identifiants pour afficher et mesurer des publicités. Ce service ne charge qu’avec votre accord.",
        "Politique de confidentialité"
    ],
    "es": [
        "Configuración de cookies",
        "Cookies y privacidad",
        "Las cookies necesarias permiten usar FreeCause. Las estadísticas y la publicidad de Adsterra son opcionales. Tu elección se aplica al sitio y a la revista.",
        "Aceptar todas",
        "Rechazar opcionales",
        "Preferencias",
        "Guardar preferencias",
        "Cerrar",
        "Necesarias",
        "Inicio de sesión, seguridad y preferencias de privacidad guardadas. Siempre activas.",
        "Estadísticas",
        "Google Analytics mide las visitas solo con tu consentimiento.",
        "Publicidad",
        "Adsterra puede usar cookies e identificadores para mostrar y medir anuncios. Solo se carga con tu consentimiento.",
        "Política de privacidad"
    ],
    "pt": [
        "Definições de cookies",
        "Cookies e privacidade",
        "Os cookies necessários permitem utilizar o FreeCause. As estatísticas e a publicidade Adsterra são opcionais. A escolha aplica-se ao site e à revista.",
        "Aceitar todos",
        "Rejeitar opcionais",
        "Preferências",
        "Guardar preferências",
        "Fechar",
        "Necessários",
        "Início de sessão, segurança e preferências de privacidade guardadas. Sempre ativos.",
        "Estatísticas",
        "O Google Analytics mede visitas apenas com o seu consentimento.",
        "Publicidade",
        "A Adsterra pode utilizar cookies e identificadores para apresentar e medir anúncios. Só é carregada com o seu consentimento.",
        "Política de privacidade"
    ],
    "nl": [
        "Cookie-instellingen",
        "Cookies en privacy",
        "Noodzakelijke cookies laten FreeCause werken. Statistieken en Adsterra-advertenties zijn optioneel. Uw keuze geldt voor de website en het magazine.",
        "Alles accepteren",
        "Optionele weigeren",
        "Voorkeuren",
        "Voorkeuren opslaan",
        "Sluiten",
        "Noodzakelijk",
        "Inloggen, beveiliging en opgeslagen privacykeuzes. Altijd actief.",
        "Statistieken",
        "Google Analytics meet bezoeken alleen met uw toestemming.",
        "Advertenties",
        "Adsterra kan cookies en apparaat-ID’s gebruiken om advertenties te tonen en te meten. De dienst laadt alleen met uw toestemming.",
        "Privacybeleid"
    ],
    "da": [
        "Cookieindstillinger",
        "Cookies og privatliv",
        "Nødvendige cookies får FreeCause til at fungere. Statistik og Adsterra-annoncer er valgfrie. Dit valg gælder hjemmesiden og magasinet.",
        "Acceptér alle",
        "Afvis valgfrie",
        "Indstillinger",
        "Gem indstillinger",
        "Luk",
        "Nødvendige",
        "Login, sikkerhed og gemte privatlivsvalg. Altid aktive.",
        "Statistik",
        "Google Analytics måler kun besøg med dit samtykke.",
        "Annoncer",
        "Adsterra kan bruge cookies og enheds-id’er til at vise og måle annoncer. Tjenesten indlæses kun med dit samtykke.",
        "Privatlivspolitik"
    ],
    "sv": [
        "Cookieinställningar",
        "Cookies och integritet",
        "Nödvändiga cookies gör att FreeCause fungerar. Statistik och Adsterra-annonser är valfria. Ditt val gäller webbplatsen och magasinet.",
        "Godkänn alla",
        "Avvisa valfria",
        "Inställningar",
        "Spara inställningar",
        "Stäng",
        "Nödvändiga",
        "Inloggning, säkerhet och sparade integritetsval. Alltid aktiva.",
        "Statistik",
        "Google Analytics mäter besök endast med ditt samtycke.",
        "Annonser",
        "Adsterra kan använda cookies och enhetsidentifierare för att visa och mäta annonser. Tjänsten laddas endast med ditt samtycke.",
        "Integritetspolicy"
    ],
    "pl": [
        "Ustawienia plików cookie",
        "Pliki cookie i prywatność",
        "Niezbędne pliki cookie zapewniają działanie FreeCause. Statystyki i reklamy Adsterra są opcjonalne. Wybór dotyczy witryny i magazynu.",
        "Akceptuj wszystkie",
        "Odrzuć opcjonalne",
        "Preferencje",
        "Zapisz preferencje",
        "Zamknij",
        "Niezbędne",
        "Logowanie, bezpieczeństwo i zapisane wybory prywatności. Zawsze aktywne.",
        "Statystyki",
        "Google Analytics mierzy wizyty tylko za Twoją zgodą.",
        "Reklamy",
        "Adsterra może używać plików cookie i identyfikatorów urządzeń do wyświetlania i pomiaru reklam. Usługa ładuje się tylko za Twoją zgodą.",
        "Polityka prywatności"
    ],
    "ro": [
        "Setări cookie",
        "Cookie-uri și confidențialitate",
        "Cookie-urile necesare asigură funcționarea FreeCause. Statisticile și reclamele Adsterra sunt opționale. Alegerea se aplică site-ului și revistei.",
        "Acceptă toate",
        "Respinge opționalele",
        "Preferințe",
        "Salvează preferințele",
        "Închide",
        "Necesare",
        "Autentificare, securitate și preferințe de confidențialitate salvate. Mereu active.",
        "Statistici",
        "Google Analytics măsoară vizitele doar cu acordul tău.",
        "Publicitate",
        "Adsterra poate folosi cookie-uri și identificatori pentru a afișa și măsura reclame. Serviciul se încarcă doar cu acordul tău.",
        "Politica de confidențialitate"
    ],
    "ru": [
        "Настройки cookie",
        "Cookie и конфиденциальность",
        "Необходимые cookie обеспечивают работу FreeCause. Статистика и реклама Adsterra необязательны. Ваш выбор действует для сайта и журнала.",
        "Принять все",
        "Отклонить необязательные",
        "Настройки",
        "Сохранить настройки",
        "Закрыть",
        "Необходимые",
        "Вход, безопасность и сохранение настроек конфиденциальности. Всегда включены.",
        "Статистика",
        "Google Analytics измеряет посещения только с вашего согласия.",
        "Реклама",
        "Adsterra может использовать cookie и идентификаторы устройства для показа и измерения рекламы. Сервис загружается только с вашего согласия.",
        "Политика конфиденциальности"
    ],
    "el": [
        "Ρυθμίσεις cookie",
        "Cookie και απόρρητο",
        "Τα απαραίτητα cookie επιτρέπουν τη λειτουργία του FreeCause. Τα στατιστικά και οι διαφημίσεις Adsterra είναι προαιρετικά. Η επιλογή ισχύει για τον ιστότοπο και το περιοδικό.",
        "Αποδοχή όλων",
        "Απόρριψη προαιρετικών",
        "Προτιμήσεις",
        "Αποθήκευση προτιμήσεων",
        "Κλείσιμο",
        "Απαραίτητα",
        "Σύνδεση, ασφάλεια και αποθηκευμένες επιλογές απορρήτου. Πάντα ενεργά.",
        "Στατιστικά",
        "Το Google Analytics μετρά επισκέψεις μόνο με τη συγκατάθεσή σας.",
        "Διαφημίσεις",
        "Η Adsterra μπορεί να χρησιμοποιεί cookie και αναγνωριστικά για την προβολή και μέτρηση διαφημίσεων. Φορτώνεται μόνο με τη συγκατάθεσή σας.",
        "Πολιτική απορρήτου"
    ],
    "tr": [
        "Çerez ayarları",
        "Çerezler ve gizlilik",
        "Gerekli çerezler FreeCause’un çalışmasını sağlar. İstatistikler ve Adsterra reklamları isteğe bağlıdır. Seçiminiz site ve dergi için geçerlidir.",
        "Tümünü kabul et",
        "İsteğe bağlıları reddet",
        "Tercihler",
        "Tercihleri kaydet",
        "Kapat",
        "Gerekli",
        "Oturum açma, güvenlik ve kaydedilmiş gizlilik tercihleri. Her zaman etkin.",
        "İstatistikler",
        "Google Analytics ziyaretleri yalnızca onayınızla ölçer.",
        "Reklamlar",
        "Adsterra reklam göstermek ve ölçmek için çerezler ve cihaz tanımlayıcıları kullanabilir. Yalnızca onayınızla yüklenir.",
        "Gizlilik politikası"
    ]
};
    if (!window.CookieConsent || window.FCConsentLoaded) return;
    window.FCConsentLoaded = true;
    const cc = window.CookieConsent;
    const tag = document.querySelector('script[data-fc-consent-locale]');
    const requested = ((tag && tag.dataset.fcConsentLocale) || document.documentElement.lang || 'en').toLowerCase().split(/[-_]/)[0];
    const locale = translations[requested] ? requested : 'en';
    const t = translations[locale];
    const privacy = '/' + locale + '/privacy-policy';
    const allowed = (category, service) => cc.acceptedCategory(category) && cc.acceptedService(service, category);
    function syncFrames() {
        const ads = allowed('advertising', 'adsterra');
        document.documentElement.dataset.fcAds = ads ? 'on' : 'off';
        document.querySelectorAll('iframe[data-fc-ad-src]').forEach((frame) => {
            if (ads && !frame.hasAttribute('src')) frame.src = frame.dataset.fcAdSrc;
            if (!ads && frame.hasAttribute('src')) frame.removeAttribute('src');
        });
    }
    function clearAnalyticsCookies() {
        document.cookie.split(';').forEach((part) => {
            const name = part.trim().split('=')[0];
            if (!/^(_ga|_gid|_gat|_gcl_)/.test(name)) return;
            const host = location.hostname;
            const domains = ['', '; Domain=' + host];
            if (host === 'www.freecause.com' || host === 'freecause.com') domains.push('; Domain=freecause.com');
            domains.forEach((domain) => { document.cookie = name + '=; Max-Age=0; Path=/' + domain; });
        });
    }
    function syncConsent() {
        const analytics = allowed('analytics', 'googleAnalytics');
        window.FCAnalyticsAllowed = analytics;
        document.querySelectorAll('script').forEach((script) => {
            const src = script.getAttribute('data-src') || script.getAttribute('src') || '';
            if (!src.includes('googletagmanager.com/gtag/js')) return;
            const id = new URL(src, location.href).searchParams.get('id');
            if (id && /^G-[A-Z0-9]+$/.test(id)) window['ga-disable-' + id] = !analytics;
        });
        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {
                analytics_storage: analytics ? 'granted' : 'denied',
                ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
            });
        }
        if (!analytics) clearAnalyticsCookies();
        syncFrames();
    }
    document.querySelectorAll('[data-fc-cookie-settings]').forEach((button) => {
        button.textContent = t[0];
        button.setAttribute('aria-label', t[0]);
    });
    cc.run({
        revision: 1,
        hideFromBots: false,
        disablePageInteraction: false,
        cookie: { name: 'fc_cookie_consent', domain: location.hostname, path: '/', sameSite: 'Lax', secure: location.protocol === 'https:', expiresAfterDays: 180 },
        guiOptions: {
            consentModal: { layout: 'box inline', position: 'bottom left', equalWeightButtons: true },
            preferencesModal: { layout: 'box', equalWeightButtons: true },
        },
        onConsent: syncConsent,
        onChange: syncConsent,
        categories: {
            necessary: { enabled: true, readOnly: true },
            analytics: { services: { googleAnalytics: { label: 'Google Analytics' } } },
            advertising: { services: { adsterra: { label: 'Adsterra' } } },
        },
        language: {
            default: locale,
            translations: {
                [locale]: {
                    consentModal: {
                        title: t[1], description: t[2], acceptAllBtn: t[3], acceptNecessaryBtn: t[4], showPreferencesBtn: t[5],
                        footer: '<a href="' + privacy + '">' + t[14] + '</a>',
                    },
                    preferencesModal: {
                        title: t[0], acceptAllBtn: t[3], acceptNecessaryBtn: t[4], savePreferencesBtn: t[6], closeIconLabel: t[7], serviceCounterLabel: '[[count]]',
                        sections: [
                            { title: t[8], description: t[9], linkedCategory: 'necessary' },
                            { title: t[10], description: t[11] + ' <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Google Analytics</a>', linkedCategory: 'analytics' },
                            { title: t[12], description: t[13] + ' <a href="https://adsterra.com/privacy-policy/" target="_blank" rel="noopener noreferrer">Adsterra</a>', linkedCategory: 'advertising' },
                            { description: '<a href="' + privacy + '">' + t[14] + '</a>' },
                        ],
                    },
                },
            },
        },
    }).then(() => {
        syncConsent();
        new MutationObserver(syncFrames).observe(document.body, { childList: true, subtree: true });
    });
})();
