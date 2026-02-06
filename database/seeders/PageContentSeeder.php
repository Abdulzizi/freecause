<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageContent;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // HERO
            ['page' => 'home', 'locale' => 'en', 'key' => 'hero_h1', 'value' => 'Change the World'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'hero_h2', 'value' => 'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate spot to kick off your online petition—let’s make some waves!'],

            ['page' => 'home', 'locale' => 'fr', 'key' => 'hero_h1', 'value' => 'Changeons le monde'],
            ['page' => 'home', 'locale' => 'fr', 'key' => 'hero_h2', 'value' => 'Bienvenue sur <span class="red">FreeCause - Pétition en ligne</span>, la plateforme idéale pour lancer votre pétition et faire bouger les choses.'],

            ['page' => 'home', 'locale' => 'it', 'key' => 'hero_h1', 'value' => 'Cambiamo il mondo'],
            ['page' => 'home', 'locale' => 'it', 'key' => 'hero_h2', 'value' => 'Benvenuto su <span class="red">FreeCause - Petizioni Online</span>, il posto giusto per lanciare la tua petizione e fare la differenza.'],

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
        ];

        foreach ($rows as $r) {
            PageContent::updateOrCreate(
                ['page' => $r['page'], 'locale' => $r['locale'], 'key' => $r['key']],
                ['value' => $r['value']]
            );
        }
    }
}
