<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageDocument;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📄 Création des documents requis pour chaque package...');

        $packages = Package::all();

        foreach ($packages as $package) {
            // Documents communs à tous les packages immigration
            $commonDocs = [
                ['nom' => 'Passeport', 'description' => 'Copie du passeport (pages identité et visas)', 'requis' => true, 'ordre' => 1],
                ['nom' => 'Photos d\'identité', 'description' => 'Photos format passeport (35x45mm)', 'requis' => true, 'ordre' => 2],
                ['nom' => 'Acte de naissance', 'description' => 'Copie certifiée conforme', 'requis' => true, 'ordre' => 3],
            ];

            // Documents spécifiques selon le type de package
            $specificDocs = $this->getSpecificDocuments($package->name);

            $allDocs = array_merge($commonDocs, $specificDocs);

            foreach ($allDocs as $doc) {
                PackageDocument::create([
                    'package_id' => $package->id,
                    'nom' => $doc['nom'],
                    'description' => $doc['description'] ?? null,
                    'requis' => $doc['requis'],
                    'ordre' => $doc['ordre'],
                ]);
            }

            $this->command->info("  ✓ {$package->name}: " . count($allDocs) . " documents");
        }

        $this->command->info('✅ Documents requis créés avec succès!');
    }

    /**
     * Get specific documents based on package name
     */
    private function getSpecificDocuments(string $packageName): array
    {
        if (str_contains(strtolower($packageName), 'études')) {
            return [
                ['nom' => 'Diplômes', 'description' => 'Copies certifiées de tous les diplômes', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Relevés de notes', 'description' => 'Bulletins scolaires et universitaires', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Lettre de motivation', 'description' => 'Lettre expliquant le projet d\'études', 'requis' => true, 'ordre' => 6],
                ['nom' => 'CV', 'description' => 'Curriculum vitae actualisé', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Preuve financière', 'description' => 'Relevés bancaires (3 derniers mois)', 'requis' => true, 'ordre' => 8],
                ['nom' => 'Test de langue', 'description' => 'TEF/TCF ou IELTS selon le cas', 'requis' => false, 'ordre' => 9],
            ];
        }

        if (str_contains(strtolower($packageName), 'travail')) {
            return [
                ['nom' => 'CV détaillé', 'description' => 'Curriculum vitae professionnel', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Lettre d\'emploi', 'description' => 'Offre d\'emploi ou LMIA', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Certificats professionnels', 'description' => 'Attestations de travail et formations', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Diplômes', 'description' => 'Copies certifiées des diplômes', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Test de langue', 'description' => 'TEF/TCF ou IELTS', 'requis' => false, 'ordre' => 8],
            ];
        }

        if (str_contains(strtolower($packageName), 'csq') || str_contains(strtolower($packageName), 'québec')) {
            return [
                ['nom' => 'CV détaillé', 'description' => 'Curriculum vitae professionnel', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Diplômes', 'description' => 'Copies certifiées de tous les diplômes', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Certificats de travail', 'description' => 'Attestations employeurs', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Test de français', 'description' => 'TEF Canada ou TCF Canada', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Preuve financière', 'description' => 'Fonds de subsistance', 'requis' => true, 'ordre' => 8],
            ];
        }

        if (str_contains(strtolower($packageName), 'visiteur') || str_contains(strtolower($packageName), 'tourisme')) {
            return [
                ['nom' => 'Réservation vol', 'description' => 'Billet aller-retour ou itinéraire', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Réservation hôtel', 'description' => 'Confirmation hébergement', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Preuve financière', 'description' => 'Relevés bancaires récents', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Lettre d\'invitation', 'description' => 'Si visite familiale/amis', 'requis' => false, 'ordre' => 7],
            ];
        }

        if (str_contains(strtolower($packageName), 'super visa')) {
            return [
                ['nom' => 'Lettre d\'invitation', 'description' => 'De l\'enfant/petit-enfant au Canada', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Preuve citoyenneté enfant', 'description' => 'Copie citoyenneté/RP de l\'enfant', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Assurance médicale', 'description' => 'Couverture minimum 100 000 CAD', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Preuve financière sponsor', 'description' => 'Revenu minimum de l\'enfant', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Certificat médical', 'description' => 'Examen médical', 'requis' => false, 'ordre' => 8],
            ];
        }

        if (str_contains(strtolower($packageName), 'parrainage')) {
            return [
                ['nom' => 'Certificat de mariage', 'description' => 'Si parrainage conjoint', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Preuves de relation', 'description' => 'Photos, communications, voyages ensemble', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Preuve citoyenneté sponsor', 'description' => 'Copie carte citoyenneté/RP', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Déclaration revenus', 'description' => 'Avis de cotisation (3 ans)', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Certificats de police', 'description' => 'Certificats de bonne conduite', 'requis' => true, 'ordre' => 8],
                ['nom' => 'Examen médical', 'description' => 'Formulaires IMM et résultats', 'requis' => true, 'ordre' => 9],
            ];
        }

        if (str_contains(strtolower($packageName), 'citoyenneté')) {
            return [
                ['nom' => 'Carte RP', 'description' => 'Copie recto-verso', 'requis' => true, 'ordre' => 4],
                ['nom' => 'Preuve résidence', 'description' => 'Documents prouvant présence physique', 'requis' => true, 'ordre' => 5],
                ['nom' => 'Déclaration revenus', 'description' => 'Avis de cotisation (5 ans)', 'requis' => true, 'ordre' => 6],
                ['nom' => 'Test de langue', 'description' => 'Preuve niveau CLB 4+', 'requis' => true, 'ordre' => 7],
                ['nom' => 'Photos citoyenneté', 'description' => 'Format spécifique IRCC', 'requis' => true, 'ordre' => 8],
            ];
        }

        // Documents par défaut si type non reconnu
        return [
            ['nom' => 'Justificatif de domicile', 'description' => 'Facture récente (eau, électricité)', 'requis' => true, 'ordre' => 4],
            ['nom' => 'Preuve financière', 'description' => 'Relevés bancaires', 'requis' => true, 'ordre' => 5],
        ];
    }
}
