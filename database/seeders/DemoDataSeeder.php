<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Package;
use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création des données de démonstration...');

        // Create Roles
        $this->createRoles();
        
        // Create Users
        $users = $this->createUsers();
        
        // Create Clients
        $clients = $this->createClients();
        
        // Create Packages
        $packages = $this->createPackages();
        
        // Create Dossiers
        $dossiers = $this->createDossiers($clients, $packages, $users);
        
        // Create Documents
        $this->createDocuments($dossiers, $users);
        
        $this->command->info('');
        $this->command->info('✅ Données de démonstration créées avec succès !');
        $this->command->info('');
        $this->command->info('📊 Statistiques :');
        $this->command->info('   - Utilisateurs: ' . User::count());
        $this->command->info('   - Clients: ' . Client::count());
        $this->command->info('   - Packages: ' . Package::count());
        $this->command->info('   - Dossiers: ' . Dossier::count());
        $this->command->info('   - Documents: ' . Document::count());
    }

    private function createRoles()
    {
        Role::firstOrCreate(['name' => 'SuperAdmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Agent']);
        Role::firstOrCreate(['name' => 'Client']);
    }

    private function createUsers()
    {
        $this->command->info('👥 Création des utilisateurs...');

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@eli-voyages.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('SuperAdmin');

        $admin = User::firstOrCreate(
            ['email' => 'koffi@eli-voyages.com'],
            [
                'name' => 'AZIH Koffi Pacôme',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        $agent1 = User::firstOrCreate(
            ['email' => 'agent@eli-voyages.com'],
            [
                'name' => 'Agent Principal',
                'password' => Hash::make('agent123'),
                'email_verified_at' => now(),
            ]
        );
        $agent1->assignRole('Agent');

        $agent2 = User::firstOrCreate(
            ['email' => 'marie.dupont@eli-voyages.com'],
            [
                'name' => 'Marie Dupont',
                'password' => Hash::make('agent123'),
                'email_verified_at' => now(),
            ]
        );
        $agent2->assignRole('Agent');

        return collect([$superAdmin, $admin, $agent1, $agent2]);
    }

    private function createClients()
    {
        $this->command->info('👤 Création des clients...');

        $clients = [];

        // Client 1 - Demande d'études
        $clients[] = Client::create([
            'civilite' => 'M.',
            'nom' => 'KOUASSI',
            'prenom' => 'Jean-Baptiste',
            'adresse' => '15 Rue de la Paix, Abidjan, Côte d\'Ivoire',
            'telephone' => '+225 07 08 09 10 11',
            'email' => 'jb.kouassi@example.com',
            'date_naissance' => '1995-03-15',
            'lieu_naissance' => 'Abidjan',
            'nationalite' => 'Ivoirienne',
            'profession' => 'Ingénieur informatique',
            'passeport_numero' => 'CI9876543',
            'passeport_date_emission' => '2022-01-10',
            'passeport_date_expiration' => '2027-01-10',
        ]);

        // Client 2 - Demande de permis de travail
        $clients[] = Client::create([
            'civilite' => 'Mme',
            'nom' => 'DIALLO',
            'prenom' => 'Fatou',
            'adresse' => '28 Avenue du Commerce, Conakry, Guinée',
            'telephone' => '+224 62 55 44 33 22',
            'email' => 'fatou.diallo@example.com',
            'date_naissance' => '1990-07-22',
            'lieu_naissance' => 'Conakry',
            'nationalite' => 'Guinéenne',
            'profession' => 'Infirmière',
            'passeport_numero' => 'GN5544332',
            'passeport_date_emission' => '2021-05-15',
            'passeport_date_expiration' => '2026-05-15',
        ]);

        // Client 3 - Demande de visa visiteur
        $clients[] = Client::create([
            'civilite' => 'M.',
            'nom' => 'MENSAH',
            'prenom' => 'Kojo',
            'adresse' => '42 Liberation Road, Lomé, Togo',
            'telephone' => '+228 90 12 34 56',
            'email' => 'kojo.mensah@example.com',
            'date_naissance' => '1988-11-05',
            'lieu_naissance' => 'Lomé',
            'nationalite' => 'Togolaise',
            'profession' => 'Entrepreneur',
            'passeport_numero' => 'TG1122334',
            'passeport_date_emission' => '2023-02-20',
            'passeport_date_expiration' => '2028-02-20',
        ]);

        // Client 4 - Demande CSQ
        $clients[] = Client::create([
            'civilite' => 'Mme',
            'nom' => 'TRAORÉ',
            'prenom' => 'Aminata',
            'adresse' => 'Quartier Hippodrome, Bamako, Mali',
            'telephone' => '+223 76 88 99 00',
            'email' => 'aminata.traore@example.com',
            'date_naissance' => '1992-09-18',
            'lieu_naissance' => 'Bamako',
            'nationalite' => 'Malienne',
            'profession' => 'Développeuse web',
            'passeport_numero' => 'ML8899776',
            'passeport_date_emission' => '2022-08-10',
            'passeport_date_expiration' => '2027-08-10',
        ]);

        // Client 5 - Parrainage familial
        $clients[] = Client::create([
            'civilite' => 'M.',
            'nom' => 'NKRUMAH',
            'prenom' => 'Kwame',
            'adresse' => 'East Legon, Accra, Ghana',
            'telephone' => '+233 24 555 6677',
            'email' => 'kwame.nkrumah@example.com',
            'date_naissance' => '1985-04-12',
            'lieu_naissance' => 'Accra',
            'nationalite' => 'Ghanéenne',
            'profession' => 'Comptable',
            'passeport_numero' => 'GH3344556',
            'passeport_date_emission' => '2020-11-25',
            'passeport_date_expiration' => '2025-11-25',
        ]);

        return collect($clients);
    }

    private function createPackages()
    {
        $this->command->info('📦 Création des packages...');

        $packages = [];

        $packages[] = Package::create([
            'name' => 'Package Études - 1er cycle',
            'description' => 'Accompagnement complet pour admission universitaire au Canada (Baccalauréat)',
            'price' => 2500000,
            'duration' => '6-9 mois',
            'services' => json_encode([
                'Évaluation du profil',
                'Recherche d\'établissements',
                'Préparation dossier admission',
                'Demande de permis d\'études',
                'Assistance visa',
                'Suivi post-arrivée'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Études - 2e/3e cycle',
            'description' => 'Accompagnement pour Master et Doctorat',
            'price' => 3000000,
            'duration' => '8-12 mois',
            'services' => json_encode([
                'Évaluation académique avancée',
                'Recherche superviseur',
                'Demande bourses',
                'Préparation dossier complet',
                'Permis d\'études',
                'Support recherche financement'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Permis de Travail',
            'description' => 'Obtention permis de travail canadien',
            'price' => 1800000,
            'duration' => '4-6 mois',
            'services' => json_encode([
                'Évaluation admissibilité',
                'Recherche employeur (si nécessaire)',
                'Préparation LMIA',
                'Demande permis travail',
                'Assistance installation'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package CSQ Québec',
            'description' => 'Certificat de sélection du Québec',
            'price' => 2200000,
            'duration' => '10-14 mois',
            'services' => json_encode([
                'Évaluation profil',
                'Test français (préparation)',
                'Dossier CSQ complet',
                'Suivi MIFI',
                'Assistance RP fédérale'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Visa Visiteur',
            'description' => 'Visa de visiteur/tourisme Canada',
            'price' => 800000,
            'duration' => '2-3 mois',
            'services' => json_encode([
                'Préparation dossier complet',
                'Lettre invitation (si nécessaire)',
                'Preuves financières',
                'Soumission demande',
                'Suivi biométrie'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Super Visa',
            'description' => 'Super Visa parents/grands-parents',
            'price' => 1200000,
            'duration' => '3-5 mois',
            'services' => json_encode([
                'Dossier complet',
                'Assurance médicale',
                'Preuves financières sponsor',
                'Lettre invitation',
                'Suivi demande'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Parrainage Familial',
            'description' => 'Parrainage conjoint/enfants/parents',
            'price' => 2800000,
            'duration' => '12-18 mois',
            'services' => json_encode([
                'Évaluation éligibilité',
                'Dossier sponsor complet',
                'Documents personne parrainée',
                'Preuves relation',
                'Suivi IRCC'
            ]),
        ]);

        $packages[] = Package::create([
            'name' => 'Package Citoyenneté',
            'description' => 'Demande de citoyenneté canadienne',
            'price' => 1500000,
            'duration' => '8-12 mois',
            'services' => json_encode([
                'Vérification admissibilité',
                'Calcul présence physique',
                'Préparation test',
                'Dossier complet',
                'Accompagnement cérémonie'
            ]),
        ]);

        return collect($packages);
    }

    private function createDossiers($clients, $packages, $users)
    {
        $this->command->info('📁 Création des dossiers...');

        $dossiers = [];
        $statuses = ['draft', 'pending', 'in_progress', 'approved', 'completed'];
        $agents = $users->filter(fn($u) => $u->hasRole('Agent') || $u->hasRole('Admin'));

        // Dossier 1 - Études en cours
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2025-001',
            'title' => 'Demande d\'admission - Université de Montréal',
            'description' => 'Baccalauréat en Génie Informatique',
            'status' => 'in_progress',
            'client_id' => $clients[0]->id,
            'package_id' => $packages[0]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(30),
        ]);

        // Dossier 2 - Permis travail approuvé
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2025-002',
            'title' => 'Permis de travail - Infirmière Toronto',
            'description' => 'Permis de travail fermé avec LMIA positive',
            'status' => 'approved',
            'client_id' => $clients[1]->id,
            'package_id' => $packages[2]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(60),
        ]);

        // Dossier 3 - Visa visiteur pending
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2025-003',
            'title' => 'Visa visiteur - Voyage d\'affaires',
            'description' => 'Visite commerciale 3 semaines Vancouver',
            'status' => 'pending',
            'client_id' => $clients[2]->id,
            'package_id' => $packages[4]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(15),
        ]);

        // Dossier 4 - CSQ en cours
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2025-004',
            'title' => 'CSQ - Programme Régulier des Travailleurs Qualifiés',
            'description' => 'Développeuse web avec 5 ans d\'expérience',
            'status' => 'in_progress',
            'client_id' => $clients[3]->id,
            'package_id' => $packages[3]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(90),
        ]);

        // Dossier 5 - Parrainage draft
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2025-005',
            'title' => 'Parrainage conjoint',
            'description' => 'Réunification familiale - Conjoint au Canada',
            'status' => 'draft',
            'client_id' => $clients[4]->id,
            'package_id' => $packages[6]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(7),
        ]);

        // Dossier 6 - Études 2e cycle completed
        $dossiers[] = Dossier::create([
            'reference' => 'DOS-2024-088',
            'title' => 'Master en Administration - McGill University',
            'description' => 'MBA accepté, permis d\'études obtenu',
            'status' => 'completed',
            'client_id' => $clients[0]->id,
            'package_id' => $packages[1]->id,
            'assigned_to' => $agents->random()->id,
            'created_at' => now()->subDays(180),
            'updated_at' => now()->subDays(30),
        ]);

        return collect($dossiers);
    }

    private function createDocuments($dossiers, $users)
    {
        $this->command->info('📄 Création des documents...');

        $documentTypes = ['passport', 'diploma', 'transcript', 'photo', 'contract', 'letter', 'proof'];
        $uploader = $users->first();

        foreach ($dossiers as $dossier) {
            // 2-5 documents par dossier
            $docCount = rand(2, 5);
            
            for ($i = 0; $i < $docCount; $i++) {
                $type = $documentTypes[array_rand($documentTypes)];
                $filename = Str::random(20) . '.pdf';
                
                Document::create([
                    'dossier_id' => $dossier->id,
                    'type' => $type,
                    'name' => $this->getDocumentName($type, $i),
                    'original_filename' => $this->getDocumentName($type, $i) . '.pdf',
                    'path' => 'documents/' . $dossier->reference . '/' . $filename,
                    'mime_type' => 'application/pdf',
                    'size' => rand(100000, 5000000),
                    'uploaded_by' => $uploader->id,
                    'created_at' => $dossier->created_at->addDays(rand(1, 10)),
                ]);
            }
        }
    }

    private function getDocumentName($type, $index)
    {
        $names = [
            'passport' => ['Passeport - Page identité', 'Passeport - Page visa'],
            'diploma' => ['Diplôme de Baccalauréat', 'Diplôme de Licence', 'Attestation de formation'],
            'transcript' => ['Relevé de notes 2023', 'Relevé de notes 2024', 'Bulletin académique'],
            'photo' => ['Photo d\'identité', 'Photo passeport format'],
            'contract' => ['Contrat de prestation signé', 'Avenant au contrat'],
            'letter' => ['Lettre de motivation', 'Lettre d\'acceptation', 'Lettre d\'invitation'],
            'proof' => ['Preuve financière', 'Attestation d\'emploi', 'Relevé bancaire'],
        ];

        $typeNames = $names[$type] ?? ['Document'];
        return $typeNames[$index % count($typeNames)];
    }
}
