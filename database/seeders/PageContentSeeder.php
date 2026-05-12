<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageContent;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // GLOBAL
            ['page' => 'global', 'locale' => 'en', 'key' => 'meta_title_suffix', 'value' => ' - FreeCause'],
            ['page' => 'global', 'locale' => 'en', 'key' => 'meta_description', 'value' => 'FreeCause - Online Petition Platform'],
            ['page' => 'global', 'locale' => 'en', 'key' => 'meta_keywords', 'value' => 'petitions, activism, freecause'],
            ['page' => 'global', 'locale' => 'en', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'global', 'locale' => 'en', 'key' => 'footer_additional_html', 'value' => ''],

            // ENGLISH
            ['page' => 'home', 'locale' => 'en', 'key' => 'h1', 'value' => 'Change the World'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'h2', 'value' => 'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate platform to launch your cause.'],
            [
                'page' => 'home',
                'locale' => 'en',
                'key' => 'text_index_left',
                'value' => '<p>An online petition lets anyone collect digital signatures to support a cause and push for real change.</p><ul><li>Reach thousands of supporters instantly, without barriers</li><li>Present verified signatures to decision-makers and authorities</li><li>Track your progress in real time and keep supporters engaged</li><li>Free to create, free to sign, free to share</li></ul><p>New to petitions? Our <a href="{FAQ_URL}">FAQ</a> has all the answers — or <a href="{CONTACTS_URL}">contact us</a> and we\'ll help you get started.</p>',
            ],
            [
                'page' => 'home',
                'locale' => 'en',
                'key' => 'text_index_right',
                'value' => '<p>Supercharge your cause and make your voice heard!</p><ul><li>The #1 platform to gather signatures online</li><li>Always free to use, no strings attached</li><li>Share your petition across all social platforms instantly</li><li>Download signatures in PDF or DOC format</li><li>Maximum visibility to boost your impact</li></ul><p>Join thousands of change-makers — <a href="{CREATE_PETITION_URL}">start your petition today</a> and make your voice count.</p>',
            ],
            ['page' => 'home', 'locale' => 'en', 'key' => 'meta_keywords', 'value' => 'petitions, activism, online petition, freecause'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'meta_description', 'value' => 'Create and support online petitions easily with FreeCause.'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'en', 'key' => 'exclude_most_read', 'value' => ''],

            // FRENCH
            ['page' => 'home', 'locale' => 'fr', 'key' => 'h1', 'value' => 'Changeons le monde'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'h2', 'value' => 'Bienvenue sur <span class="red">FreeCause</span>, la plateforme de pétitions en ligne.'],
            [
                'page' => 'home',
                'locale' => 'fr',
                'key' => 'text_index_left',
                'value' => '<p>Une pétition en ligne vous permet de collecter des signatures numériques pour soutenir une cause et obtenir un vrai changement.</p><ul><li>Atteignez des milliers de soutiens instantanément</li><li>Présentez des signatures vérifiées aux décideurs</li><li>Suivez vos progrès en temps réel</li><li>Gratuit pour créer, signer et partager</li></ul><p>Nouveau sur les pétitions ? Consultez notre <a href="{FAQ_URL}">FAQ</a> pour tout comprendre, ou <a href="{CONTACTS_URL}">contactez-nous</a> si vous avez besoin d'aide.</p>',
            ],
            [
                'page' => 'home',
                'locale' => 'fr',
                'key' => 'text_index_right',
                'value' => '<p>Donnez de la force à votre cause et faites entendre votre voix !</p><ul><li>La plateforme n°1 pour collecter des signatures en ligne</li><li>Toujours gratuit, sans engagement</li><li>Partagez facilement sur tous les réseaux sociaux</li><li>Téléchargez les signatures en PDF ou DOC</li><li>Visibilité maximale pour votre cause</li></ul><p>Unisciti a migliaia di attivisti — <a href="{CREATE_PETITION_URL}">avvia la tua petizione oggi</a> e fai sentire la tua voce.</p>',
            ],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'meta_keywords', 'value' => 'pétitions, activisme, pétition en ligne'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'meta_description', 'value' => 'Créez et soutenez des pétitions en ligne avec FreeCause.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'exclude_most_read', 'value' => ''],

            // ITALIAN
            ['page' => 'home', 'locale' => 'it', 'key' => 'h1', 'value' => 'Cambiamo il mondo'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'h2', 'value' => 'Benvenuto su <span class="red">FreeCause</span>, la piattaforma di petizioni online.'],
            [
                'page' => 'home',
                'locale' => 'it',
                'key' => 'text_index_left',
                'value' => '<p>Una petizione online ti permette di raccogliere firme digitali per sostenere una causa e ottenere un vero cambiamento.</p><ul><li>Raggiungi migliaia di sostenitori istantaneamente</li><li>Presenta firme verificate ai responsabili delle decisioni</li><li>Monitora i progressi in tempo reale</li><li>Gratuita da creare, firmare e condividere</li></ul><p>Prima volta con le petizioni? Leggi le nostre <a href="{FAQ_URL}">FAQ</a> per sapere come funziona, oppure <a href="{CONTACTS_URL}">contattaci</a> per ricevere supporto.</p>',
            ],
            [
                'page' => 'home',
                'locale' => 'it',
                'key' => 'text_index_right',
                'value' => '<p>Dai forza alla tua causa e fai sentire la tua voce!</p><ul><li>La piattaforma n.1 per raccogliere firme online</li><li>Sempre gratuita, senza vincoli</li><li>Condividi la tua petizione su tutti i social</li><li>Scarica le firme in PDF o DOC</li><li>Massima visibilità per la tua causa</li></ul><p>Unisciti a migliaia di attivisti — <a href="{CREATE_PETITION_URL}">avvia la tua petizione oggi</a> e fai sentire la tua voce.</p>',
            ],
            ['page' => 'home', 'locale' => 'it', 'key' => 'meta_keywords', 'value' => 'petizioni, attivismo, petizione online'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'meta_description', 'value' => 'Crea e sostieni petizioni online con FreeCause.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'it', 'key' => 'exclude_most_read', 'value' => ''],

            // GERMAN
            ['page' => 'home', 'locale' => 'de', 'key' => 'h1', 'value' => 'Verändere die Welt'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'h2', 'value' => 'Willkommen bei <span class="red">FreeCause</span>, der Plattform für Online-Petitionen.'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'what_title', 'value' => 'Was ist eine Online-Petition'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'create_box_title', 'value' => 'PETITION ERSTELLEN'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'text_index_left', 'value' => '<p>Eine Online-Petition ermöglicht es jedem, digitale Unterschriften für eine gute Sache zu sammeln und echten Wandel zu bewirken.</p><ul><li>Sofort Tausende von Unterstützern erreichen</li><li>Verifizierte Unterschriften an Entscheidungsträger übergeben</li><li>Fortschritt in Echtzeit verfolgen</li><li>Kostenlos erstellen, unterzeichnen und teilen</li></ul><p>Neu bei Online-Petitionen? Lies unsere <a href="{FAQ_URL}">FAQ</a> oder <a href="{CONTACTS_URL}">kontaktiere uns</a> — wir helfen dir gerne weiter.</p>'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'text_index_right', 'value' => '<p>Stärke deine Sache und mach deine Stimme hörbar!</p><ul><li>Die #1-Plattform zum Sammeln von Unterschriften online</li><li>Immer kostenlos, ohne Bedingungen</li><li>Einfaches Teilen auf allen sozialen Plattformen</li><li>Unterschriften als PDF oder DOC herunterladen</li><li>Maximale Sichtbarkeit für deine Wirkung</li></ul><p>Werde Teil von tausenden Aktivisten — <a href="{CREATE_PETITION_URL}">starte heute deine Petition</a> und mach einen Unterschied.</p>'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'btn_create_petition', 'value' => 'Petition erstellen'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'tab_featured', 'value' => 'Empfohlene Petition'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'tab_recent', 'value' => 'Aktuelle Aktivitäten'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'categories_title', 'value' => 'Kategorien durchsuchen'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'featured_badge', 'value' => 'Empfohlene Petition'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'featured_read_more', 'value' => 'mehr lesen'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'featured_none_title', 'value' => 'noch keine empfohlene Petition'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'featured_none_sub', 'value' => 'keine Petitionen für diese Sprache'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'recent_has_signed', 'value' => 'hat unterzeichnet'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'recent_empty', 'value' => 'noch keine aktuelle Aktivität'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'meta_keywords', 'value' => 'Petitionen, Aktivismus, Online-Petition'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'meta_description', 'value' => 'Online-Petitionen einfach erstellen und unterstützen mit FreeCause.'],
            ['page' => 'home', 'locale' => 'de', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'de', 'key' => 'exclude_most_read', 'value' => ''],

            // SPANISH
            ['page' => 'home', 'locale' => 'es', 'key' => 'h1', 'value' => 'Cambia el Mundo'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'h2', 'value' => 'Bienvenido a <span class="red">FreeCause</span>, la plataforma de peticiones en línea.'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'what_title', 'value' => '¿Qué es una petición en línea?'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'create_box_title', 'value' => 'CREAR PETICIÓN'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'text_index_left', 'value' => '<p>Una petición en línea te permite recopilar firmas digitales para apoyar una causa y lograr un cambio real.</p><ul><li>Llega a miles de personas de forma instantánea</li><li>Presenta firmas verificadas a los responsables</li><li>Sigue tu progreso en tiempo real</li><li>Gratis para crear, firmar y compartir</li></ul><p>¿Primera vez con peticiones? Lee nuestras <a href="{FAQ_URL}">preguntas frecuentes</a> o <a href="{CONTACTS_URL}">contáctanos</a> y te ayudamos a empezar.</p>'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'text_index_right', 'value' => '<p>¡Impulsa tu causa y haz que tu voz sea escuchada!</p><ul><li>La plataforma #1 para recopilar firmas en línea</li><li>Siempre gratuita, sin compromisos</li><li>Comparte tu petición en todas las redes sociales</li><li>Descarga firmas en PDF o DOC</li><li>Máxima visibilidad para tu causa</li></ul><p>Únete a miles de personas comprometidas — <a href="{CREATE_PETITION_URL}">inicia tu petición hoy</a> y haz que tu voz importe.</p>'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'btn_create_petition', 'value' => 'Crear petición'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'tab_featured', 'value' => 'Petición destacada'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'tab_recent', 'value' => 'Actividad reciente'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'categories_title', 'value' => 'Explorar categorías'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'featured_badge', 'value' => 'Petición destacada'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'featured_read_more', 'value' => 'leer más'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'featured_none_title', 'value' => 'sin petición destacada aún'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'featured_none_sub', 'value' => 'sin peticiones para este idioma'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'recent_has_signed', 'value' => 'ha firmado'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'recent_empty', 'value' => 'sin actividad reciente aún'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'meta_keywords', 'value' => 'peticiones, activismo, petición en línea'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'meta_description', 'value' => 'Crea y apoya peticiones en línea fácilmente con FreeCause.'],
            ['page' => 'home', 'locale' => 'es', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'es', 'key' => 'exclude_most_read', 'value' => ''],

            // PORTUGUESE
            ['page' => 'home', 'locale' => 'pt', 'key' => 'h1', 'value' => 'Mude o Mundo'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'h2', 'value' => 'Bem-vindo ao <span class="red">FreeCause</span>, a plataforma de petições online.'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'what_title', 'value' => 'O que é uma petição online'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'create_box_title', 'value' => 'CRIAR PETIÇÃO'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'text_index_left', 'value' => '<p>Uma petição online permite que qualquer pessoa colete assinaturas digitais para apoiar uma causa e promover mudanças reais.</p><ul><li>Alcance milhares de apoiadores instantaneamente</li><li>Apresente assinaturas verificadas aos responsáveis</li><li>Acompanhe seu progresso em tempo real</li><li>Grátis para criar, assinar e compartilhar</li></ul><p>Nunca criou uma petição? Leia nossas <a href="{FAQ_URL}">perguntas frequentes</a> ou <a href="{CONTACTS_URL}">fale conosco</a> — estamos aqui para ajudar.</p>'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'text_index_right', 'value' => '<p>Impulsione sua causa e faça sua voz ser ouvida!</p><ul><li>A plataforma #1 para coletar assinaturas online</li><li>Sempre gratuita, sem restrições</li><li>Compartilhe sua petição em todas as redes sociais</li><li>Baixe assinaturas em PDF ou DOC</li><li>Máxima visibilidade para seu impacto</li></ul><p>Junte-se a milhares de pessoas engajadas — <a href="{CREATE_PETITION_URL}">inicie sua petição hoje</a> e faça sua voz ser ouvida.</p>'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'btn_create_petition', 'value' => 'Criar petição'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'tab_featured', 'value' => 'Petição em destaque'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'tab_recent', 'value' => 'Atividades recentes'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'categories_title', 'value' => 'Explorar categorias'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'featured_badge', 'value' => 'Petição em destaque'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'featured_read_more', 'value' => 'leia mais'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'featured_none_title', 'value' => 'nenhuma petição em destaque ainda'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'featured_none_sub', 'value' => 'nenhuma petição para este idioma'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'recent_has_signed', 'value' => 'assinou'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'recent_empty', 'value' => 'nenhuma atividade recente ainda'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'meta_keywords', 'value' => 'petições, ativismo, petição online'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'meta_description', 'value' => 'Crie e apoie petições online facilmente com FreeCause.'],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'pt', 'key' => 'exclude_most_read', 'value' => ''],

            // DUTCH
            ['page' => 'home', 'locale' => 'nl', 'key' => 'h1', 'value' => 'Verander de Wereld'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'h2', 'value' => 'Welkom bij <span class="red">FreeCause</span>, het platform voor online petities.'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'what_title', 'value' => 'Wat is een online petitie'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'create_box_title', 'value' => 'PETITIE STARTEN'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'text_index_left', 'value' => '<p>Een online petitie stelt iedereen in staat digitale handtekeningen te verzamelen voor een goed doel en echte verandering te bewerkstelligen.</p><ul><li>Bereik direct duizenden supporters</li><li>Presenteer geverifieerde handtekeningen aan beslissers</li><li>Volg je voortgang in realtime</li><li>Gratis aanmaken, ondertekenen en delen</li></ul><p>Nieuw met petities? Lees onze <a href="{FAQ_URL}">FAQ</a> om te begrijpen hoe het werkt, of <a href="{CONTACTS_URL}">neem contact op</a> voor hulp.</p>'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'text_index_right', 'value' => '<p>Versterk je zaak en laat je stem horen!</p><ul><li>Het #1 platform voor het verzamelen van handtekeningen online</li><li>Altijd gratis, zonder verplichtingen</li><li>Deel je petitie eenvoudig op alle sociale platforms</li><li>Download handtekeningen als PDF of DOC</li><li>Maximale zichtbaarheid voor je impact</li></ul><p>Sluit je aan bij duizenden activisten — <a href="{CREATE_PETITION_URL}">start vandaag je petitie</a> en laat je stem tellen.</p>'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'btn_create_petition', 'value' => 'Petitie starten'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'tab_featured', 'value' => 'Uitgelichte petitie'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'tab_recent', 'value' => 'Recente activiteiten'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'categories_title', 'value' => 'Categorieën bekijken'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'featured_badge', 'value' => 'Uitgelichte petitie'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'featured_read_more', 'value' => 'lees meer'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'featured_none_title', 'value' => 'nog geen uitgelichte petitie'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'featured_none_sub', 'value' => 'geen petities voor deze taal'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'recent_has_signed', 'value' => 'heeft getekend'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'recent_empty', 'value' => 'nog geen recente activiteit'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'meta_keywords', 'value' => 'petities, activisme, online petitie'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'meta_description', 'value' => 'Maak en ondersteun online petities eenvoudig met FreeCause.'],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'nl', 'key' => 'exclude_most_read', 'value' => ''],

            // POLISH
            ['page' => 'home', 'locale' => 'pl', 'key' => 'h1', 'value' => 'Zmień Świat'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'h2', 'value' => 'Witamy w <span class="red">FreeCause</span>, platformie petycji online.'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'what_title', 'value' => 'Czym jest petycja online'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'create_box_title', 'value' => 'UTWÓRZ PETYCJĘ'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'text_index_left', 'value' => '<p>Petycja online umożliwia każdemu zbieranie cyfrowych podpisów w celu wsparcia sprawy i wprowadzenia realnych zmian.</p><ul><li>Dotrzyj do tysięcy zwolenników natychmiast</li><li>Przekaż zweryfikowane podpisy decydentom</li><li>Śledź postępy w czasie rzeczywistym</li><li>Bezpłatne tworzenie, podpisywanie i udostępnianie</li></ul><p>Pierwszy raz z petycją? Sprawdź nasze <a href="{FAQ_URL}">FAQ</a> albo <a href="{CONTACTS_URL}">skontaktuj się z nami</a> — chętnie pomożemy.</p>'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'text_index_right', 'value' => '<p>Wzmocnij swoją sprawę i spraw, by twój głos był słyszany!</p><ul><li>Platforma #1 do zbierania podpisów online</li><li>Zawsze bezpłatna, bez zobowiązań</li><li>Łatwe udostępnianie na wszystkich platformach społecznościowych</li><li>Pobierz podpisy w formacie PDF lub DOC</li><li>Maksymalna widoczność dla twojej sprawy</li></ul><p>Dołącz do tysięcy aktywistów — <a href="{CREATE_PETITION_URL}">uruchom swoją petycję dziś</a> i spraw, by twój głos był słyszany.</p>'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'btn_create_petition', 'value' => 'Utwórz petycję'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'tab_featured', 'value' => 'Wyróżniona petycja'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'tab_recent', 'value' => 'Ostatnie aktywności'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'categories_title', 'value' => 'Przeglądaj kategorie'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'featured_badge', 'value' => 'Wyróżniona petycja'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'featured_read_more', 'value' => 'czytaj więcej'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'featured_none_title', 'value' => 'brak wyróżnionej petycji'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'featured_none_sub', 'value' => 'brak petycji dla tego języka'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'recent_has_signed', 'value' => 'podpisał(a)'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'recent_empty', 'value' => 'brak ostatnich aktywności'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'meta_keywords', 'value' => 'petycje, aktywizm, petycja online'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'meta_description', 'value' => 'Twórz i wspieraj petycje online z łatwością dzięki FreeCause.'],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'pl', 'key' => 'exclude_most_read', 'value' => ''],

            // ROMANIAN
            ['page' => 'home', 'locale' => 'ro', 'key' => 'h1', 'value' => 'Schimbă Lumea'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'h2', 'value' => 'Bine ai venit la <span class="red">FreeCause</span>, platforma de petiții online.'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'what_title', 'value' => 'Ce este o petiție online'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'create_box_title', 'value' => 'CREEAZĂ PETIȚIE'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'text_index_left', 'value' => '<p>O petiție online permite oricui să colecteze semnături digitale pentru a susține o cauză și a genera schimbări reale.</p><ul><li>Atinge mii de susținători instantaneu</li><li>Prezintă semnături verificate factorilor de decizie</li><li>Urmărește progresul în timp real</li><li>Gratuit pentru a crea, semna și distribui</li></ul><p>Prima dată cu o petiție? Citește <a href="{FAQ_URL}">întrebările frecvente</a> sau <a href="{CONTACTS_URL}">contactează-ne</a> — suntem aici să te ajutăm.</p>'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'text_index_right', 'value' => '<p>Amplifică-ți cauza și fă-ți vocea auzită!</p><ul><li>Platforma #1 pentru colectarea semnăturilor online</li><li>Întotdeauna gratuită, fără restricții</li><li>Distribuie petiția pe toate platformele sociale</li><li>Descarcă semnăturile în format PDF sau DOC</li><li>Vizibilitate maximă pentru impactul tău</li></ul><p>Alătură-te miilor de oameni activi — <a href="{CREATE_PETITION_URL}">lansează petiția ta astăzi</a> și fă-ți vocea auzită.</p>'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'btn_create_petition', 'value' => 'Creează petiție'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'tab_featured', 'value' => 'Petiție recomandată'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'tab_recent', 'value' => 'Activități recente'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'categories_title', 'value' => 'Explorează categoriile'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'featured_badge', 'value' => 'Petiție recomandată'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'featured_read_more', 'value' => 'citește mai mult'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'featured_none_title', 'value' => 'nicio petiție recomandată încă'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'featured_none_sub', 'value' => 'nicio petiție pentru această limbă'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'recent_has_signed', 'value' => 'a semnat'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'recent_empty', 'value' => 'nicio activitate recentă încă'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'meta_keywords', 'value' => 'petiții, activism, petiție online'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'meta_description', 'value' => 'Creează și susține petiții online ușor cu FreeCause.'],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'ro', 'key' => 'exclude_most_read', 'value' => ''],

            // RUSSIAN
            ['page' => 'home', 'locale' => 'ru', 'key' => 'h1', 'value' => 'Измени Мир'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'h2', 'value' => 'Добро пожаловать на <span class="red">FreeCause</span> — платформу онлайн-петиций.'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'what_title', 'value' => 'Что такое онлайн-петиция'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'create_box_title', 'value' => 'СОЗДАТЬ ПЕТИЦИЮ'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'text_index_left', 'value' => '<p>Онлайн-петиция позволяет каждому собирать цифровые подписи в поддержку своей инициативы и добиваться реальных изменений.</p><ul><li>Мгновенно достучаться до тысяч сторонников</li><li>Представить проверенные подписи лицам, принимающим решения</li><li>Отслеживать прогресс в режиме реального времени</li><li>Бесплатно создавать, подписывать и делиться</li></ul><p>Впервые на платформе? Ознакомьтесь с нашим <a href="{FAQ_URL}">разделом FAQ</a> или <a href="{CONTACTS_URL}">свяжитесь с нами</a> — мы поможем разобраться.</p>'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'text_index_right', 'value' => '<p>Усильте своё дело и сделайте так, чтобы ваш голос был услышан!</p><ul><li>Платформа №1 для сбора подписей онлайн</li><li>Всегда бесплатно, без ограничений</li><li>Лёгкий обмен на всех социальных платформах</li><li>Скачивайте подписи в формате PDF или DOC</li><li>Максимальная видимость для вашей инициативы</li></ul><p>Присоединяйтесь к тысячам активистов — <a href="{CREATE_PETITION_URL}">запустите свою петицию сегодня</a> и добейтесь перемен.</p>'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'btn_create_petition', 'value' => 'Создать петицию'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'tab_featured', 'value' => 'Рекомендуемая петиция'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'tab_recent', 'value' => 'Последние действия'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'categories_title', 'value' => 'Просмотр категорий'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'featured_badge', 'value' => 'Рекомендуемая петиция'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'featured_read_more', 'value' => 'читать далее'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'featured_none_title', 'value' => 'рекомендуемых петиций пока нет'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'featured_none_sub', 'value' => 'петиций для этого языка пока нет'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'recent_has_signed', 'value' => 'подписал(а)'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'recent_empty', 'value' => 'последних действий пока нет'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'meta_keywords', 'value' => 'петиции, активизм, онлайн-петиция'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'meta_description', 'value' => 'Создавайте и поддерживайте онлайн-петиции легко с FreeCause.'],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'ru', 'key' => 'exclude_most_read', 'value' => ''],

            // DANISH
            ['page' => 'home', 'locale' => 'da', 'key' => 'h1', 'value' => 'Forandre Verden'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'h2', 'value' => 'Velkommen til <span class="red">FreeCause</span>, platformen for online underskriftindsamlinger.'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'what_title', 'value' => 'Hvad er en online underskriftindsamling'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'create_box_title', 'value' => 'OPRET UNDERSKRIFTINDSAMLING'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'text_index_left', 'value' => '<p>En online underskriftindsamling giver alle mulighed for at samle digitale underskrifter til støtte for en sag og skabe reel forandring.</p><ul><li>Nå tusindvis af støtter øjeblikkeligt</li><li>Præsenter verificerede underskrifter til beslutningstagere</li><li>Følg din fremgang i realtid</li><li>Gratis at oprette, underskrive og dele</li></ul><p>Ny til underskriftindsamlinger? Læs vores <a href="{FAQ_URL}">FAQ</a> for at forstå, hvordan det virker, eller <a href="{CONTACTS_URL}">kontakt os</a> for hjælp.</p>'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'text_index_right', 'value' => '<p>Styrk din sag og lad din stemme blive hørt!</p><ul><li>#1 platform til indsamling af underskrifter online</li><li>Altid gratis, ingen betingelser</li><li>Del nemt på alle sociale platforme</li><li>Download underskrifter som PDF eller DOC</li><li>Maksimal synlighed for din sag</li></ul><p>Bliv en del af tusindvis af aktivister — <a href="{CREATE_PETITION_URL}">start din indsamling i dag</a> og lad din stemme tælle.</p>'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'btn_create_petition', 'value' => 'Opret indsamling'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'tab_featured', 'value' => 'Fremhævet indsamling'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'tab_recent', 'value' => 'Seneste aktiviteter'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'categories_title', 'value' => 'Gennemse kategorier'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'featured_badge', 'value' => 'Fremhævet indsamling'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'featured_read_more', 'value' => 'læs mere'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'featured_none_title', 'value' => 'ingen fremhævet indsamling endnu'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'featured_none_sub', 'value' => 'ingen indsamlinger for dette sprog'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'recent_has_signed', 'value' => 'har underskrevet'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'recent_empty', 'value' => 'ingen seneste aktivitet endnu'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'meta_keywords', 'value' => 'underskriftindsamlinger, aktivisme, online underskriftindsamling'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'meta_description', 'value' => 'Opret og støt online underskriftindsamlinger nemt med FreeCause.'],
            ['page' => 'home', 'locale' => 'da', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'da', 'key' => 'exclude_most_read', 'value' => ''],

            // SWEDISH
            ['page' => 'home', 'locale' => 'sv', 'key' => 'h1', 'value' => 'Förändra Världen'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'h2', 'value' => 'Välkommen till <span class="red">FreeCause</span>, plattformen för namninsamlingar online.'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'what_title', 'value' => 'Vad är en namninsamling online'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'create_box_title', 'value' => 'STARTA NAMNINSAMLING'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'text_index_left', 'value' => '<p>En namninsamling online låter vem som helst samla digitala underskrifter för att stödja en sak och driva fram verklig förändring.</p><ul><li>Nå tusentals supportrar omedelbart</li><li>Presentera verifierade underskrifter för beslutsfattare</li><li>Följ din framgång i realtid</li><li>Gratis att skapa, signera och dela</li></ul><p>Ny på namninsamlingar? Läs vår <a href="{FAQ_URL}">FAQ</a> för att förstå hur det fungerar, eller <a href="{CONTACTS_URL}">kontakta oss</a> för hjälp.</p>'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'text_index_right', 'value' => '<p>Stärk din sak och gör din röst hörd!</p><ul><li>#1 plattformen för att samla underskrifter online</li><li>Alltid gratis, inga villkor</li><li>Dela enkelt på alla sociala plattformar</li><li>Ladda ner underskrifter som PDF eller DOC</li><li>Maximal synlighet för din påverkan</li></ul><p>Gå med tusentals aktivister — <a href="{CREATE_PETITION_URL}">starta din namninsamling idag</a> och gör din röst hörd.</p>'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'btn_create_petition', 'value' => 'Starta namninsamling'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'tab_featured', 'value' => 'Utvald namninsamling'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'tab_recent', 'value' => 'Senaste aktiviteter'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'categories_title', 'value' => 'Bläddra bland kategorier'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'featured_badge', 'value' => 'Utvald namninsamling'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'featured_read_more', 'value' => 'läs mer'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'featured_none_title', 'value' => 'ingen utvald namninsamling än'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'featured_none_sub', 'value' => 'inga namninsamlingar för detta språk'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'recent_has_signed', 'value' => 'har skrivit på'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'recent_empty', 'value' => 'ingen senaste aktivitet än'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'meta_keywords', 'value' => 'namninsamlingar, aktivism, online namninsamling'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'meta_description', 'value' => 'Skapa och stöd namninsamlingar online enkelt med FreeCause.'],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'sv', 'key' => 'exclude_most_read', 'value' => ''],

            // TURKISH
            ['page' => 'home', 'locale' => 'tr', 'key' => 'h1', 'value' => 'Dünyayı Değiştir'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'h2', 'value' => '<span class="red">FreeCause</span>\'a hoş geldiniz, çevrimiçi dilekçe platformu.'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'what_title', 'value' => 'Çevrimiçi dilekçe nedir'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'create_box_title', 'value' => 'DİLEKÇE OLUŞTUR'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'text_index_left', 'value' => '<p>Çevrimiçi bir dilekçe, herkese dijital imza toplayarak bir davayı destekleme ve gerçek değişim yaratma imkânı sunar.</p><ul><li>Anında binlerce destekçiye ulaşın</li><li>Doğrulanmış imzaları karar vericilere sunun</li><li>İlerlemenizi gerçek zamanlı takip edin</li><li>Oluşturmak, imzalamak ve paylaşmak ücretsiz</li></ul><p>Dilekçeler konusunda yeni misiniz? <a href="{FAQ_URL}">SSS sayfamıza</a> bakın ya da <a href="{CONTACTS_URL}">bize ulaşın</a> — yardımcı olmaktan memnuniyet duyarız.</p>'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'text_index_right', 'value' => '<p>Davanızı güçlendirin ve sesinizi duyurun!</p><ul><li>Çevrimiçi imza toplamak için #1 platform</li><li>Her zaman ücretsiz, koşulsuz</li><li>Dilekçenizi tüm sosyal platformlarda kolayca paylaşın</li><li>İmzaları PDF veya DOC olarak indirin</li><li>Etkinizi artırmak için maksimum görünürlük</li></ul><p>Binlerce aktiviste katılın — <a href="{CREATE_PETITION_URL}">dilekçenizi bugün başlatın</a> ve sesinizi duyurun.</p>'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'btn_create_petition', 'value' => 'Dilekçe oluştur'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'tab_featured', 'value' => 'Öne çıkan dilekçe'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'tab_recent', 'value' => 'Son aktiviteler'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'categories_title', 'value' => 'Kategorilere göz at'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'featured_badge', 'value' => 'Öne çıkan dilekçe'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'featured_read_more', 'value' => 'daha fazla oku'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'featured_none_title', 'value' => 'henüz öne çıkan dilekçe yok'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'featured_none_sub', 'value' => 'bu dil için henüz dilekçe yok'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'recent_has_signed', 'value' => 'imzaladı'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'recent_empty', 'value' => 'henüz son aktivite yok'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'meta_keywords', 'value' => 'dilekçeler, aktivizm, çevrimiçi dilekçe'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'meta_description', 'value' => 'FreeCause ile çevrimiçi dilekçeleri kolayca oluşturun ve destekleyin.'],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'tr', 'key' => 'exclude_most_read', 'value' => ''],

            // GREEK
            ['page' => 'home', 'locale' => 'el', 'key' => 'h1', 'value' => 'Άλλαξε τον Κόσμο'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'h2', 'value' => 'Καλώς ήρθατε στο <span class="red">FreeCause</span>, την πλατφόρμα διαδικτυακών αναφορών.'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'what_title', 'value' => 'Τι είναι μια διαδικτυακή αναφορά'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'create_box_title', 'value' => 'ΔΗΜΙΟΥΡΓΙΑ ΑΝΑΦΟΡΑΣ'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'text_index_left', 'value' => '<p>Μια διαδικτυακή αναφορά επιτρέπει σε οποιονδήποτε να συλλέγει ψηφιακές υπογραφές για να υποστηρίξει μια υπόθεση και να επιφέρει πραγματική αλλαγή.</p><ul><li>Φτάστε άμεσα σε χιλιάδες υποστηρικτές</li><li>Παρουσιάστε επαληθευμένες υπογραφές σε υπεύθυνους λήψης αποφάσεων</li><li>Παρακολουθήστε την πρόοδό σας σε πραγματικό χρόνο</li><li>Δωρεάν δημιουργία, υπογραφή και κοινοποίηση</li></ul><p>Νέος στις αναφορές; Διαβάστε τις <a href="{FAQ_URL}">Συχνές Ερωτήσεις</a> μας ή <a href="{CONTACTS_URL}">επικοινωνήστε μαζί μας</a> — είμαστε εδώ για να βοηθήσουμε.</p>'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'text_index_right', 'value' => '<p>Ενισχύστε την υπόθεσή σας και κάντε τη φωνή σας να ακουστεί!</p><ul><li>Η πλατφόρμα #1 για συλλογή υπογραφών online</li><li>Πάντα δωρεάν, χωρίς δεσμεύσεις</li><li>Εύκολη κοινοποίηση σε όλες τις κοινωνικές πλατφόρμες</li><li>Λήψη υπογραφών σε μορφή PDF ή DOC</li><li>Μέγιστη ορατότητα για τον αντίκτυπό σας</li></ul><p>Ενώστε δυνάμεις με χιλιάδες ακτιβιστές — <a href="{CREATE_PETITION_URL}">ξεκινήστε την αναφορά σας σήμερα</a> και κάντε τη διαφορά.</p>'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'btn_create_petition', 'value' => 'Δημιουργία αναφοράς'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'tab_featured', 'value' => 'Προτεινόμενη αναφορά'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'tab_recent', 'value' => 'Πρόσφατες δραστηριότητες'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'categories_title', 'value' => 'Περιήγηση κατηγοριών'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'featured_badge', 'value' => 'Προτεινόμενη αναφορά'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'featured_read_more', 'value' => 'διαβάστε περισσότερα'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'featured_none_title', 'value' => 'καμία προτεινόμενη αναφορά ακόμα'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'featured_none_sub', 'value' => 'καμία αναφορά για αυτή τη γλώσσα'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'recent_has_signed', 'value' => 'υπέγραψε'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'recent_empty', 'value' => 'καμία πρόσφατη δραστηριότητα ακόμα'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'meta_keywords', 'value' => 'αναφορές, ακτιβισμός, διαδικτυακή αναφορά'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'meta_description', 'value' => 'Δημιουργήστε και υποστηρίξτε διαδικτυακές αναφορές εύκολα με το FreeCause.'],
            ['page' => 'home', 'locale' => 'el', 'key' => 'head_additional_html', 'value' => ''],
            ['page' => 'home', 'locale' => 'el', 'key' => 'exclude_most_read', 'value' => ''],

            // BUTTONS / TABS
            ['page' => 'home', 'locale' => 'en', 'key' => 'btn_create_petition', 'value' => 'Create Petition'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'tab_featured', 'value' => 'Featured Petition'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'tab_recent', 'value' => 'Recent activities'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'btn_create_petition', 'value' => 'Créer une pétition'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'tab_featured', 'value' => 'Pétition à la une'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'tab_recent', 'value' => 'Activités récentes'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'btn_create_petition', 'value' => 'Crea una petizione'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'tab_featured', 'value' => 'Petizione in evidenza'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'tab_recent', 'value' => 'Attività recenti'],

            // FEATURED BOX
            ['page' => 'home', 'locale' => 'en', 'key' => 'featured_badge', 'value' => 'Featured Petition'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'featured_read_more', 'value' => 'read more'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'featured_none_title', 'value' => 'no featured petition yet'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'featured_none_sub', 'value' => 'no petitions yet for this locale'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'featured_badge', 'value' => 'Pétition à la une'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'featured_read_more', 'value' => 'lire la suite'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'featured_none_title', 'value' => 'aucune pétition mise en avant'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'featured_none_sub', 'value' => 'aucune pétition pour cette langue'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'featured_badge', 'value' => 'Petizione in evidenza'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'featured_read_more', 'value' => 'leggi di più'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'featured_none_title', 'value' => 'nessuna petizione in evidenza'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'featured_none_sub', 'value' => 'nessuna petizione per questa lingua'],

            // RECENT ACTIVITY
            ['page' => 'home', 'locale' => 'en', 'key' => 'recent_has_signed', 'value' => 'has signed'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'recent_empty', 'value' => 'no recent activity yet'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'recent_has_signed', 'value' => 'a signé'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'recent_empty', 'value' => 'aucune activité récente'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'recent_has_signed', 'value' => 'ha firmato'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'recent_empty', 'value' => 'nessuna attività recente'],

            // ONLINE PETITION SECTION
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_title', 'value' => 'What is online petition'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_p1', 'value' => 'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate platform for launching your online petitions. Champion your cause and make your voice heard!'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_p2', 'value' => 'At <span class="red">FreeCause - Online Petition</span>, we believe real change starts with individuals like you—bold enough to share your ideas and inspire others to take action.'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_p3', 'value' => 'Without a space to champion our causes, no matter how small or everyday they may seem, true freedom feels out of reach.'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_p4', 'value' => 'That’s why we built <span class="red">FreeCause - Online Petition</span>—free, independent, and made for you.'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'what_link', 'value' => 'Learn how to start your petition »'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_title', 'value' => 'Qu’est-ce qu’une pétition en ligne'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_p1', 'value' => 'Bienvenue sur <span class="red">FreeCause - Pétition en ligne</span>, la plateforme pour lancer vos pétitions et défendre vos causes.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_p2', 'value' => 'Nous croyons que le changement commence avec des individus prêts à partager leurs idées et à mobiliser les autres.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_p3', 'value' => 'Sans espace pour s’exprimer, même les causes les plus simples restent invisibles.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_p4', 'value' => 'C’est pourquoi FreeCause est gratuit, indépendant et conçu pour vous.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'what_link', 'value' => 'Apprendre à créer une pétition »'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'what_title', 'value' => 'Cos’è una petizione online'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'what_p1', 'value' => 'Benvenuto su <span class="red">FreeCause - Petizioni Online</span>, la piattaforma per lanciare petizioni e sostenere le tue cause.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'what_p2', 'value' => 'Crediamo che il cambiamento inizi da persone pronte a condividere idee e coinvolgere gli altri.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'what_p3', 'value' => 'Senza uno spazio per esprimersi, anche le cause più semplici restano invisibili.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'what_p4', 'value' => 'Per questo FreeCause è gratuito, indipendente e pensato per te.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'what_link', 'value' => 'Scopri come creare una petizione »'],

            // CREATE PETITION BOX
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_title', 'value' => 'CREATE PETITION'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_p1', 'value' => 'Supercharge your cause!'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li1', 'value' => 'The #1 platform to gather signatures'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li2', 'value' => 'Always free to use, no strings attached'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li3', 'value' => 'Easily share your petition across all social platforms'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li4', 'value' => 'Download signatures in PDF or DOC format—perfect for printing or delivering in person'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li5', 'value' => 'Get maximum visibility to boost your impact'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_li6', 'value' => 'Ethical Code'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_p2', 'value' => 'Let’s build change together from the ground up!'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'create_box_link', 'value' => 'Launch your first petition now »'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_title', 'value' => 'CRÉER UNE PÉTITION'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_p1', 'value' => 'Donnez de la force à votre cause !'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li1', 'value' => 'La plateforme n°1 pour collecter des signatures'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li2', 'value' => 'Toujours gratuit, sans engagement'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li3', 'value' => 'Partage facile sur les réseaux sociaux'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li4', 'value' => 'Téléchargez les signatures en PDF ou DOC'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li5', 'value' => 'Visibilité maximale pour votre pétition'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_li6', 'value' => 'Code éthique'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_p2', 'value' => 'Construisons le changement ensemble.'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'create_box_link', 'value' => 'Lancer votre première pétition »'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_title', 'value' => 'CREA UNA PETIZIONE'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_p1', 'value' => 'Dai forza alla tua causa!'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li1', 'value' => 'La piattaforma n.1 per raccogliere firme'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li2', 'value' => 'Sempre gratuita, senza vincoli'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li3', 'value' => 'Condivisione facile sui social'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li4', 'value' => 'Scarica le firme in PDF o DOC'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li5', 'value' => 'Massima visibilità per la tua petizione'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_li6', 'value' => 'Codice etico'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_p2', 'value' => 'Costruiamo il cambiamento insieme.'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'create_box_link', 'value' => 'Lancia la tua prima petizione »'],

            // CATEGORIES + BLOG
            ['page' => 'home', 'locale' => 'en', 'key' => 'categories_title', 'value' => 'Browse categories'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'blog_title', 'value' => 'Latest from Freecause magazine'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'blog_subtitle', 'value' => 'Stay updated with our latest insights and news'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'blog_read_more', 'value' => 'Read More'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'categories_title', 'value' => 'Parcourir les catégories'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'blog_title', 'value' => 'Derniers articles du magazine Freecause'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'blog_subtitle', 'value' => 'Actualités et analyses récentes'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'blog_read_more', 'value' => 'Lire la suite'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'categories_title', 'value' => 'Sfoglia le categorie'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'blog_title', 'value' => 'Ultime notizie dal magazine Freecause'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'blog_subtitle', 'value' => 'Aggiornamenti e approfondimenti recenti'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'blog_read_more', 'value' => 'Leggi di più'],

            // NAVBAR
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_explore',  'value' => 'Explore petitions'],
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_magazine', 'value' => 'Magazine'],
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_help',     'value' => 'Help'],
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_login',    'value' => 'Login'],
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_logout',   'value' => 'Logout'],
            ['page' => 'navbar', 'locale' => 'en', 'key' => 'nav_startfree', 'value' => 'Start Free'],

            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_explore',  'value' => 'Explorer les pétitions'],
            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_magazine', 'value' => 'Magazine'],
            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_help',     'value' => 'Aide'],
            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_login',    'value' => 'Connexion'],
            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_logout',   'value' => 'Déconnexion'],
            ['page' => 'navbar', 'locale' => 'fr', 'key' => 'nav_startfree', 'value' => 'Commencer gratuitement'],

            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_explore',  'value' => 'Esplora petizioni'],
            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_magazine', 'value' => 'Magazine'],
            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_help',     'value' => 'Aiuto'],
            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_login',    'value' => 'Accedi'],
            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_logout',   'value' => 'Esci'],
            ['page' => 'navbar', 'locale' => 'it', 'key' => 'nav_startfree', 'value' => 'Inizia gratis'],

            // PETITION SIGN PAGE
            ['page' => 'petition_sign', 'locale' => 'en', 'key' => 'title',    'value' => 'Sign - :title'],
            ['page' => 'petition_sign', 'locale' => 'en', 'key' => 'h2_line1', 'value' => 'Support and share your cause.'],
            ['page' => 'petition_sign', 'locale' => 'en', 'key' => 'h2_line2', 'value' => 'Please click "like" button and sign the petition'],
            ['page' => 'petition_sign', 'locale' => 'en', 'key' => 'btn_sign', 'value' => 'Sign'],

            ['page' => 'petition_sign', 'locale' => 'fr', 'key' => 'title',    'value' => 'Signer - :title'],
            ['page' => 'petition_sign', 'locale' => 'fr', 'key' => 'h2_line1', 'value' => 'Soutenez et partagez votre cause.'],
            ['page' => 'petition_sign', 'locale' => 'fr', 'key' => 'h2_line2', 'value' => 'Veuillez cliquer sur le bouton "J’aime" et signer la pétition'],
            ['page' => 'petition_sign', 'locale' => 'fr', 'key' => 'btn_sign', 'value' => 'Signer'],

            ['page' => 'petition_sign', 'locale' => 'it', 'key' => 'title',    'value' => 'Firma - :title'],
            ['page' => 'petition_sign', 'locale' => 'it', 'key' => 'h2_line1', 'value' => 'Sostieni e condividi la tua causa.'],
            ['page' => 'petition_sign', 'locale' => 'it', 'key' => 'h2_line2', 'value' => 'Clicca sul pulsante "Mi piace" e firma la petizione'],
            ['page' => 'petition_sign', 'locale' => 'it', 'key' => 'btn_sign', 'value' => 'Firma'],

            // PETITION THANKS PAGE
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'title_created',      'value' => 'Thanks! - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'title_signed',       'value' => 'Thank you for having signed - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'h1_created',         'value' => 'Thanks!'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'h1_signed',          'value' => 'Thank you for having signed:'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'p_created',          'value' => 'Your petition has been created successfully. You can open it now using the link above.'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'p_signed',           'value' => 'Registration has been successful, however you still have to activate your account by clicking a link you\'ll receive soon at the supplied email address.'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'suggestions_h2',      'value' => 'Petitions you might like'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'suggestions_empty',   'value' => 'No suggestions yet.'],
            ['page' => 'petition_thanks', 'locale' => 'en', 'key' => 'invite_btn',          'value' => 'Invite friends from your address book »'],

            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'title_created',      'value' => 'Merci ! - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'title_signed',       'value' => 'Merci d’avoir signé - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'h1_created',         'value' => 'Merci !'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'h1_signed',          'value' => 'Merci d’avoir signé :'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'p_created',          'value' => 'Votre pétition a été créée avec succès. Vous pouvez l’ouvrir maintenant via le lien ci-dessus.'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'p_signed',           'value' => 'Votre inscription a bien été prise en compte. Vous devez toutefois activer votre compte en cliquant sur le lien que vous recevrez bientôt à l’adresse e-mail indiquée.'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'suggestions_h2',      'value' => 'Des pétitions qui pourraient vous plaire'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'suggestions_empty',   'value' => 'Pas encore de suggestions.'],
            ['page' => 'petition_thanks', 'locale' => 'fr', 'key' => 'invite_btn',          'value' => 'Inviter des amis depuis votre carnet d’adresses »'],

            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'title_created',      'value' => 'Grazie! - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'title_signed',       'value' => 'Grazie per aver firmato - FreeCause'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'h1_created',         'value' => 'Grazie!'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'h1_signed',          'value' => 'Grazie per aver firmato:'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'p_created',          'value' => 'La tua petizione è stata creata con successo. Puoi aprirla ora usando il link qui sopra.'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'p_signed',           'value' => 'La registrazione è avvenuta con successo, tuttavia devi ancora attivare il tuo account cliccando su un link che riceverai presto all’indirizzo email fornito.'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'suggestions_h2',      'value' => 'Petizioni che potrebbero piacerti'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'suggestions_empty',   'value' => 'Nessun suggerimento al momento.'],
            ['page' => 'petition_thanks', 'locale' => 'it', 'key' => 'invite_btn',          'value' => 'Invita amici dalla tua rubrica »'],

            // PETITION SHOW PAGE
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'title', 'value' => ':title - FreeCause'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'btn_sign_now', 'value' => 'Sign Now'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_sign_title', 'value' => 'Sign The Petition'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'google_continue', 'value' => 'Continue with Google'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'or', 'value' => 'OR'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'auth_hint_split', 'value' => 'If you already have an account <a class="red" href=":login_url">please sign in</a>, otherwise <strong>register an account</strong> for free then sign the petition filling the fields below.<br>Email and password will be your account data, you will be able to sign other petitions after logging in.'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'auth_hint_stack', 'value' => 'If you already have an account <a class="red" href=":login_url"><em>please sign in</em></a>'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_shoutbox', 'value' => 'Shoutbox'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_goal', 'value' => 'Goal'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'goal_signatures', 'value' => ':count signatures'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'goal_label', 'value' => 'Goal: :count'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_latest', 'value' => 'Latest Signatures'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'latest_empty', 'value' => 'no signatures yet'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'latest_browse_all', 'value' => 'browse all the signatures »'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_information', 'value' => 'Information'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'info_by', 'value' => 'By:'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'info_in', 'value' => 'In:'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'info_target', 'value' => 'Petition target:'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_tags', 'value' => 'Tags'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'tags_empty', 'value' => 'No tags'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_embed', 'value' => 'Embed Codes'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'embed_direct', 'value' => 'direct link'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'embed_html', 'value' => 'link for html'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'embed_forum_no_title', 'value' => 'link for forum without title'],
            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'embed_forum_with_title', 'value' => 'link for forum with title'],

            ['page' => 'petition_show', 'locale' => 'en', 'key' => 'box_widgets', 'value' => 'Widgets'],

            // FR
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'title', 'value' => ':title - FreeCause'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'btn_sign_now', 'value' => 'Signer'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_sign_title', 'value' => 'Signer la pétition'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'google_continue', 'value' => 'Continuer avec Google'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'or', 'value' => 'OU'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'auth_hint_split', 'value' => 'Si vous avez déjà un compte <a class="red" href=":login_url">connectez-vous</a>, sinon <strong>créez un compte</strong> gratuitement puis signez la pétition en remplissant les champs ci-dessous.<br>L’email et le mot de passe seront vos identifiants et vous pourrez signer d’autres pétitions après connexion.'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'auth_hint_stack', 'value' => 'Si vous avez déjà un compte <a class="red" href=":login_url"><em>connectez-vous</em></a>'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_shoutbox', 'value' => 'Shoutbox'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_goal', 'value' => 'Objectif'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'goal_signatures', 'value' => ':count signatures'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'goal_label', 'value' => 'Objectif : :count'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_latest', 'value' => 'Dernières signatures'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'latest_empty', 'value' => 'aucune signature pour le moment'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'latest_browse_all', 'value' => 'voir toutes les signatures »'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_information', 'value' => 'Informations'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'info_by', 'value' => 'Par :'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'info_in', 'value' => 'Dans :'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'info_target', 'value' => 'Cible de la pétition :'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_tags', 'value' => 'Tags'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'tags_empty', 'value' => 'Aucun tag'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_embed', 'value' => 'Codes d’intégration'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'embed_direct', 'value' => 'lien direct'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'embed_html', 'value' => 'lien pour html'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'embed_forum_no_title', 'value' => 'lien forum sans titre'],
            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'embed_forum_with_title', 'value' => 'lien forum avec titre'],

            ['page' => 'petition_show', 'locale' => 'fr', 'key' => 'box_widgets', 'value' => 'Widgets'],

            // IT
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'title', 'value' => ':title - FreeCause'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'btn_sign_now', 'value' => 'Firma ora'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_sign_title', 'value' => 'Firma la petizione'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'google_continue', 'value' => 'Continua con Google'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'or', 'value' => 'OPPURE'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'auth_hint_split', 'value' => 'Se hai già un account <a class="red" href=":login_url">accedi</a>, altrimenti <strong>registrati gratuitamente</strong> e poi firma la petizione compilando i campi qui sotto.<br>Email e password saranno i tuoi dati di accesso, potrai firmare altre petizioni dopo aver effettuato l’accesso.'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'auth_hint_stack', 'value' => 'Se hai già un account <a class="red" href=":login_url"><em>accedi</em></a>'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_shoutbox', 'value' => 'Shoutbox'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_goal', 'value' => 'Obiettivo'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'goal_signatures', 'value' => ':count firme'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'goal_label', 'value' => 'Obiettivo: :count'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_latest', 'value' => 'Ultime firme'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'latest_empty', 'value' => 'nessuna firma ancora'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'latest_browse_all', 'value' => 'vedi tutte le firme »'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_information', 'value' => 'Informazioni'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'info_by', 'value' => 'Da:'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'info_in', 'value' => 'In:'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'info_target', 'value' => 'Destinatario della petizione:'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_tags', 'value' => 'Tag'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'tags_empty', 'value' => 'Nessun tag'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_embed', 'value' => 'Codici di incorporamento'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'embed_direct', 'value' => 'link diretto'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'embed_html', 'value' => 'link per html'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'embed_forum_no_title', 'value' => 'link forum senza titolo'],
            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'embed_forum_with_title', 'value' => 'link forum con titolo'],

            ['page' => 'petition_show', 'locale' => 'it', 'key' => 'box_widgets', 'value' => 'Widget'],

            // PETITION SIGN FORM PARTIAL
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'signed_already', 'value' => 'You signed this petition.'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'signed_hint', 'value' => 'Support and share your cause. Please click "like" button and sign the petition'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'btn_sign', 'value' => 'Sign'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'btn_sign_arrow', 'value' => '»'],

            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_name', 'value' => 'Name (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_surname', 'value' => 'Surname (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_email', 'value' => 'Email (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_password', 'value' => 'Choose a password (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_city', 'value' => 'City (optional)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_nickname', 'value' => 'Nickname (optional)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'ph_comment', 'value' => 'Comment'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'default_comment', 'value' => 'I support this petition'],

            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_name', 'value' => 'Name (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_surname', 'value' => 'Surname (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_email', 'value' => 'Email (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_password', 'value' => 'Choose a password (mandatory)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_city', 'value' => 'City (optional)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_nickname', 'value' => 'Nickname (optional)'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'lbl_comment', 'value' => 'Comment'],

            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'privacy_hint', 'value' => 'Privacy in the search engines? You can use a nickname:'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'email_warning', 'value' => 'Attention, the email address you supply must be valid in order to validate the signature, otherwise it will be deleted.'],

            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'agree1_title', 'value' => 'I confirm registration and I agree to <a class="red" href="#">Usage and Limitations of Services</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'agree2_title', 'value' => 'I confirm that I have read the <a class="red" href="#">Privacy Policy</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'agree3_title', 'value' => 'I agree to the <a class="red" href="#">Personal Data Processing</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'agree_yes', 'value' => 'I agree'],
            ['page' => 'petition_sign_form', 'locale' => 'en', 'key' => 'agree_no', 'value' => 'I do not agree'],

            // FR
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'signed_already', 'value' => 'Vous avez signé cette pétition.'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'signed_hint', 'value' => 'Soutenez et partagez votre cause. Cliquez sur "J’aime" et signez la pétition.'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'btn_sign', 'value' => 'Signer'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'btn_sign_arrow', 'value' => '»'],

            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_name', 'value' => 'Prénom (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_surname', 'value' => 'Nom (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_email', 'value' => 'Email (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_password', 'value' => 'Choisissez un mot de passe (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_city', 'value' => 'Ville (optionnel)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_nickname', 'value' => 'Pseudo (optionnel)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'ph_comment', 'value' => 'Commentaire'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'default_comment', 'value' => 'Je soutiens cette pétition'],

            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_name', 'value' => 'Prénom (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_surname', 'value' => 'Nom (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_email', 'value' => 'Email (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_password', 'value' => 'Choisissez un mot de passe (obligatoire)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_city', 'value' => 'Ville (optionnel)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_nickname', 'value' => 'Pseudo (optionnel)'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'lbl_comment', 'value' => 'Commentaire'],

            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'privacy_hint', 'value' => 'Confidentialité sur les moteurs de recherche ? Vous pouvez utiliser un pseudo :'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'email_warning', 'value' => 'Attention, l’adresse email doit être valide pour valider la signature, sinon elle sera supprimée.'],

            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'agree1_title', 'value' => 'Je confirme l’inscription et j’accepte les <a class="red" href="#">Conditions d’utilisation</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'agree2_title', 'value' => 'Je confirme avoir lu la <a class="red" href="#">Politique de confidentialité</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'agree3_title', 'value' => 'J’accepte le <a class="red" href="#">Traitement des données personnelles</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'agree_yes', 'value' => 'J’accepte'],
            ['page' => 'petition_sign_form', 'locale' => 'fr', 'key' => 'agree_no', 'value' => 'Je n’accepte pas'],

            // IT
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'signed_already', 'value' => 'Hai firmato questa petizione.'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'signed_hint', 'value' => 'Sostieni e condividi la tua causa. Clicca su "Mi piace" e firma la petizione.'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'btn_sign', 'value' => 'Firma'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'btn_sign_arrow', 'value' => '»'],

            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_name', 'value' => 'Nome (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_surname', 'value' => 'Cognome (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_email', 'value' => 'Email (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_password', 'value' => 'Scegli una password (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_city', 'value' => 'Città (opzionale)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_nickname', 'value' => 'Nickname (opzionale)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'ph_comment', 'value' => 'Commento'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'default_comment', 'value' => 'Sostengo questa petizione'],

            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_name', 'value' => 'Nome (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_surname', 'value' => 'Cognome (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_email', 'value' => 'Email (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_password', 'value' => 'Scegli una password (obbligatorio)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_city', 'value' => 'Città (opzionale)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_nickname', 'value' => 'Nickname (opzionale)'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'lbl_comment', 'value' => 'Commento'],

            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'privacy_hint', 'value' => 'Privacy nei motori di ricerca? Puoi usare un nickname:'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'email_warning', 'value' => 'Attenzione: l’indirizzo email deve essere valido per convalidare la firma, altrimenti verrà eliminata.'],

            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'agree1_title', 'value' => 'Confermo la registrazione e accetto le <a class="red" href="#">Condizioni d’uso</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'agree2_title', 'value' => 'Confermo di aver letto la <a class="red" href="#">Privacy Policy</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'agree3_title', 'value' => 'Accetto il <a class="red" href="#">Trattamento dei dati personali</a>'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'agree_yes', 'value' => 'Accetto'],
            ['page' => 'petition_sign_form', 'locale' => 'it', 'key' => 'agree_no', 'value' => 'Non accetto'],

            [
                'page' => 'layout',
                'locale' => 'en',
                'key' => 'footer_about',
                'value' => '
                    <div class="footer-logo">
                        <img src="/legacy/images/logo7footer.png" alt="Freecause">
                    </div>

                    <p>
                        Freecause Magazine is your go-to resource for stories that inspire action and empower change.
                        We bring you the latest insights on advocacy, community movements, and global causes,
                        along with expert advice on how to make a difference through petitions and grassroots campaigns.
                    </p>

                    <p>
                        Our content spans a variety of topics, including social justice, environmental advocacy,
                        civil rights, and emerging technologies shaping modern activism.
                    </p>

                    <p>
                        For inquiries, feedback, or to report inaccuracies,
                        please reach out at <a href="mailto:hello@freecause.com">hello@freecause.com</a>.
                    </p>'
            ],

            [
                'page' => 'layout',
                'locale' => 'en',
                'key' => 'footer_links',
                'value' => '
                    {PRIVACY_POLICY_ROOT_LINK}
                    {ETHICAL_CODE_LINK}
                    {CONTACTS_LINK}
                    {TOS_LINK}'
            ],

            [
                'page' => 'layout',
                'locale' => 'en',
                'key' => 'footer_bottom',
                'value' => '© 2026 Freecause – Freedom in Sharing™ – Freecause LLC, Albuquerque, NM, USA – All rights reserved'
            ]
        ];

        foreach ($rows as $r) {
            PageContent::updateOrCreate(
                ['page' => $r['page'], 'locale' => $r['locale'], 'key' => $r['key']],
                ['value' => $r['value']]
            );
        }
    }
}
