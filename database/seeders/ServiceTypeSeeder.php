<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Permis d\'études',
                'name_en' => 'Study Permit',
                'slug' => 'study-permit',
                'description' => 'Services pour l\'obtention d\'un permis d\'études canadien pour étudiants internationaux',
                'description_en' => 'Services for obtaining a Canadian study permit for international students',
                'sort_order' => 1,
            ],
            [
                'name' => 'Permis de travail',
                'name_en' => 'Work Permit',
                'slug' => 'work-permit',
                'description' => 'Assistance pour l\'obtention d\'un permis de travail canadien',
                'description_en' => 'Assistance for obtaining a Canadian work permit',
                'sort_order' => 2,
            ],
            [
                'name' => 'Visa visiteur',
                'name_en' => 'Visitor Visa',
                'slug' => 'visitor-visa',
                'description' => 'Visa de résidence temporaire pour visiter le Canada',
                'description_en' => 'Temporary resident visa to visit Canada',
                'sort_order' => 3,
            ],
            [
                'name' => 'Super Visa',
                'name_en' => 'Super Visa',
                'slug' => 'super-visa',
                'description' => 'Visa multi-entrées pour parents et grands-parents',
                'description_en' => 'Multi-entry visa for parents and grandparents',
                'sort_order' => 4,
            ],
            [
                'name' => 'Parrainage familial',
                'name_en' => 'Family Sponsorship',
                'slug' => 'family-sponsorship',
                'description' => 'Parrainage de membres de la famille pour la résidence permanente',
                'description_en' => 'Sponsorship of family members for permanent residence',
                'sort_order' => 5,
            ],
            [
                'name' => 'Citoyenneté',
                'name_en' => 'Citizenship',
                'slug' => 'citizenship',
                'description' => 'Demande de citoyenneté canadienne',
                'description_en' => 'Canadian citizenship application',
                'sort_order' => 6,
            ],
            [
                'name' => 'AVE (Autorisation de voyage électronique)',
                'name_en' => 'eTA (Electronic Travel Authorization)',
                'slug' => 'eta',
                'description' => 'Autorisation de voyage électronique pour voyages aériens',
                'description_en' => 'Electronic travel authorization for air travel',
                'sort_order' => 7,
            ],
            [
                'name' => 'CSQ Québec',
                'name_en' => 'Quebec CSQ',
                'slug' => 'csq',
                'description' => 'Certificat de sélection du Québec',
                'description_en' => 'Quebec Selection Certificate',
                'sort_order' => 8,
            ],
            [
                'name' => 'LMIA',
                'name_en' => 'LMIA',
                'slug' => 'lmia',
                'description' => 'Étude d\'impact sur le marché du travail',
                'description_en' => 'Labour Market Impact Assessment',
                'sort_order' => 9,
            ],
            [
                'name' => 'Restauration de statut',
                'name_en' => 'Status Restoration',
                'slug' => 'status-restoration',
                'description' => 'Restauration de statut d\'immigration',
                'description_en' => 'Immigration status restoration',
                'sort_order' => 10,
            ],
            [
                'name' => 'Demande d\'asile',
                'name_en' => 'Asylum Application',
                'slug' => 'asylum',
                'description' => 'Demande de protection au Canada',
                'description_en' => 'Protection claim in Canada',
                'sort_order' => 11,
            ],
            [
                'name' => 'Services de traduction',
                'name_en' => 'Translation Services',
                'slug' => 'translation',
                'description' => 'Traduction certifiée de documents',
                'description_en' => 'Certified document translation',
                'sort_order' => 12,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::firstOrCreate(
                ['slug' => $serviceType['slug']],
                $serviceType
            );
        }

        $this->command->info('✅ ' . count($serviceTypes) . ' types de services créés!');
    }
}
