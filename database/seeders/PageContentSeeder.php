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
        ];

        foreach ($rows as $r) {
            PageContent::updateOrCreate(
                ['page' => $r['page'], 'locale' => $r['locale'], 'key' => $r['key']],
                ['value' => $r['value']]
            );
        }
    }
}
