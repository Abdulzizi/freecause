<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            [
                'en' => 'Animals',
                'fr' => 'Animaux',
                'it' => 'Animali',
                'de' => 'Tiere',
                'es' => 'Animales',
            ],

            [
                'en' => 'Business and Companies',
                'fr' => 'Entreprises et commerce',
                'it' => 'Aziende e imprese',
                'de' => 'Unternehmen und Wirtschaft',
                'es' => 'Empresas y negocios',
            ],

            [
                'en' => 'City Life',
                'fr' => 'Vie urbaine',
                'it' => 'Vita cittadina',
                'de' => 'Stadtleben',
                'es' => 'Vida urbana',
            ],

            [
                'en' => 'Culture and Society',
                'fr' => 'Culture et société',
                'it' => 'Cultura e società',
                'de' => 'Kultur und Gesellschaft',
                'es' => 'Cultura y sociedad',
            ],

            [
                'en' => 'Education',
                'fr' => 'Éducation',
                'it' => 'Istruzione',
                'de' => 'Bildung',
                'es' => 'Educación',
            ],

            [
                'en' => 'Environment',
                'fr' => 'Environnement',
                'it' => 'Ambiente',
                'de' => 'Umwelt',
                'es' => 'Medio ambiente',
            ],

            [
                'en' => 'Health and Wellness',
                'fr' => 'Santé et bien-être',
                'it' => 'Salute e benessere',
                'de' => 'Gesundheit und Wohlbefinden',
                'es' => 'Salud y bienestar',
            ],

            [
                'en' => 'Human Rights',
                'fr' => 'Droits humains',
                'it' => 'Diritti umani',
                'de' => 'Menschenrechte',
                'es' => 'Derechos humanos',
            ],

            [
                'en' => 'Politics',
                'fr' => 'Politique',
                'it' => 'Politica',
                'de' => 'Politik',
                'es' => 'Política',
            ],

            [
                'en' => 'Science and Technology',
                'fr' => 'Science et technologie',
                'it' => 'Scienza e tecnologia',
                'de' => 'Wissenschaft und Technologie',
                'es' => 'Ciencia y tecnología',
            ],

            [
                'en' => 'Sports',
                'fr' => 'Sports',
                'it' => 'Sport',
                'de' => 'Sport',
                'es' => 'Deportes',
            ],

            [
                'en' => 'Travel and Tourism',
                'fr' => 'Voyage et tourisme',
                'it' => 'Viaggi e turismo',
                'de' => 'Reisen und Tourismus',
                'es' => 'Viajes y turismo',
            ],

            [
                'en' => 'Transportation',
                'fr' => 'Transports',
                'it' => 'Trasporti',
                'de' => 'Transport',
                'es' => 'Transporte',
            ],

            [
                'en' => 'Work and Employment',
                'fr' => 'Travail et emploi',
                'it' => 'Lavoro e occupazione',
                'de' => 'Arbeit und Beschäftigung',
                'es' => 'Trabajo y empleo',
            ],

            [
                'en' => 'Youth and Family',
                'fr' => 'Jeunesse et famille',
                'it' => 'Giovani e famiglia',
                'de' => 'Jugend und Familie',
                'es' => 'Juventud y familia',
            ],

        ];

        foreach ($categories as $index => $translations) {

            $category = Category::firstOrCreate(
                ['sort_order' => $index + 1],
                ['is_active' => true]
            );

            foreach ($translations as $locale => $name) {

                $category->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $name,
                        'slug' => Str::slug($name),
                    ]
                );
            }
        }
    }
}
