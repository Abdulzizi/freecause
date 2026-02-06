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
            ],
            [
                'en' => 'Business and Companies',
                'fr' => 'Entreprises et commerce',
                'it' => 'Aziende e imprese',
            ],
            [
                'en' => 'City Life',
                'fr' => 'Vie urbaine',
                'it' => 'Vita cittadina',
            ],
            [
                'en' => 'Culture and Society',
                'fr' => 'Culture et société',
                'it' => 'Cultura e società',
            ],
            [
                'en' => 'Education',
                'fr' => 'Éducation',
                'it' => 'Istruzione',
            ],
            [
                'en' => 'Environment',
                'fr' => 'Environnement',
                'it' => 'Ambiente',
            ],
            [
                'en' => 'Health and Wellness',
                'fr' => 'Santé et bien-être',
                'it' => 'Salute e benessere',
            ],
            [
                'en' => 'Human Rights',
                'fr' => 'Droits humains',
                'it' => 'Diritti umani',
            ],
            [
                'en' => 'International Affairs',
                'fr' => 'Affaires internationales',
                'it' => 'Affari internazionali',
            ],
            [
                'en' => 'Law and Justice',
                'fr' => 'Droit et justice',
                'it' => 'Legge e giustizia',
            ],
            [
                'en' => 'Media and Entertainment',
                'fr' => 'Médias et divertissement',
                'it' => 'Media e intrattenimento',
            ],
            [
                'en' => 'Politics',
                'fr' => 'Politique',
                'it' => 'Politica',
            ],
            [
                'en' => 'Religion and Spirituality',
                'fr' => 'Religion et spiritualité',
                'it' => 'Religione e spiritualità',
            ],
            [
                'en' => 'Science and Technology',
                'fr' => 'Science et technologie',
                'it' => 'Scienza e tecnologia',
            ],
            [
                'en' => 'Sports',
                'fr' => 'Sports',
                'it' => 'Sport',
            ],
            [
                'en' => 'Transportation',
                'fr' => 'Transports',
                'it' => 'Trasporti',
            ],
            [
                'en' => 'Travel and Tourism',
                'fr' => 'Voyage et tourisme',
                'it' => 'Viaggi e turismo',
            ],
            [
                'en' => 'Work and Employment',
                'fr' => 'Travail et emploi',
                'it' => 'Lavoro e occupazione',
            ],
            [
                'en' => 'Youth and Family',
                'fr' => 'Jeunesse et famille',
                'it' => 'Giovani e famiglia',
            ],
            [
                'en' => 'Food and Agriculture',
                'fr' => 'Alimentation et agriculture',
                'it' => 'Cibo e agricoltura',
            ],
            [
                'en' => 'Housing and Urban Development',
                'fr' => 'Logement et développement urbain',
                'it' => 'Edilizia e sviluppo urbano',
            ],
            [
                'en' => 'Energy and Resources',
                'fr' => 'Énergie et ressources',
                'it' => 'Energia e risorse',
            ],
            [
                'en' => 'Public Safety',
                'fr' => 'Sécurité publique',
                'it' => 'Sicurezza pubblica',
            ],
        ];

        foreach ($categories as $i => $names) {
            $category = Category::firstOrCreate(
                ['sort_order' => $i + 1],
                ['is_active' => true]
            );

            foreach (['en', 'fr', 'it'] as $locale) {
                $category->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $names[$locale],
                        'slug' => Str::slug($names[$locale]),
                    ]
                );
            }
        }
    }
}
