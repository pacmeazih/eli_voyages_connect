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
        
        // Create Activities
        $this->createActivities($dossiers, $users);
        
        $this->command->info('');
        $this->command->info('✅ Données de démonstration créées avec succès !');
        $this->command->info('');
        $this->command->info('📊 Statistiques :');
        $this->command->info('   - Utilisateurs: ' . User::count());
        $this->command->info('   - Clients: ' . Client::count());
        $this->command->info('   - Packages: ' . Package::count());
        $this->command->info('   - Dossiers: ' . Dossier::count());
        $this->command->info('   - Documents: ' . Document::count());
        $this->command->info('   - Activités: ' . \Spatie\Activitylog\Models\Activity::count());
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

        // Create a client user for demo
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Jean-Baptiste KOUASSI',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $client->assignRole('Client');

        return collect([$superAdmin, $admin, $agent1, $agent2, $client]);
    }

    private function createClients()
    {
        $this->command->info('👤 Création du client pour l\'utilisateur de démo...');

        // Create a client matching the demo user email
        $demoClient = Client::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'civilite' => 'M.',
                'nom' => 'KOUASSI',
                'prenom' => 'Jean-Baptiste',
                'adresse' => '15 Rue de la Paix, Abidjan, Côte d\'Ivoire',
                'telephone' => '+225 07 08 09 10 11',
                'date_naissance' => '1995-03-15',
                'lieu_naissance' => 'Abidjan',
                'nationalite' => 'Ivoirienne',
                'profession' => 'Ingénieur Informatique',
                'passeport_numero' => 'CI1234567',
                'passeport_date_emission' => '2023-01-15',
                'passeport_date_expiration' => '2028-01-15',
            ]
        );

        // Get existing clients from factory
        $clients = Client::where('email', '!=', 'client@example.com')->get();
        
        if ($clients->isEmpty()) {
            $this->command->warn('Aucun autre client trouvé. Création de clients supplémentaires...');
            // Create demo clients if none exist
            $clients = collect();
            
            $clients->push(Client::firstOrCreate(
                ['email' => 'jb.kouassi@example.com'],
                [
                    'civilite' => 'M.',
                    'nom' => 'KOUASSI',
                    'prenom' => 'Jean-Baptiste',
                    'adresse' => '15 Rue de la Paix, Abidjan, Côte d\'Ivoire',
                    'telephone' => '+225 07 08 09 10 11',
                    'date_naissance' => '1995-03-15',
                    'lieu_naissance' => 'Abidjan',
                    'nationalite' => 'Ivoirienne',
                    'profession' => 'Ingénieur informatique',
                    'passeport_numero' => 'CI9876543',
                    'passeport_date_emission' => '2022-01-10',
                    'passeport_date_expiration' => '2027-01-10',
                ]
            ));
        }

        // Add the demo client to the collection
        $clients->prepend($demoClient);

        return $clients;
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
            // 3-6 documents par dossier
            $docCount = rand(3, 6);
            
            for ($i = 0; $i < $docCount; $i++) {
                $type = $documentTypes[array_rand($documentTypes)];
                $documentName = $this->getDocumentName($type, $i);
                $filename = Str::slug($documentName) . '-' . Str::random(8) . '.pdf';
                
                // 30% de chance que le document soit manquant (path vide ou null)
                $isMissing = rand(1, 100) <= 30;
                
                Document::create([
                    'dossier_id' => $dossier->id,
                    'name' => $documentName,
                    'type' => $type,
                    'original_filename' => $isMissing ? $documentName . '.pdf' : $filename,
                    'path' => $isMissing ? '' : 'documents/' . $dossier->reference . '/' . $filename,
                    'mime_type' => $isMissing ? null : 'application/pdf',
                    'size' => $isMissing ? null : rand(100000, 5000000),
                    'description' => 'Document requis pour le traitement du dossier',
                    'uploaded_by' => $isMissing ? null : $uploader->id,
                    'version' => 1,
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

    private function createActivities($dossiers, $users)
    {
        $this->command->info('📝 Création des activités de timeline...');

        foreach ($dossiers as $dossier) {
            $causer = $users->random();
            
            // Activity 1: Dossier créé
            activity()
                ->performedOn($dossier)
                ->causedBy($causer)
                ->withProperties(['status' => 'created'])
                ->log('Dossier créé');

            // Activity 2: Document uploadé
            if ($dossier->created_at->diffInDays(now()) > 2) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'document_uploaded'])
                    ->createdAt($dossier->created_at->addDays(2))
                    ->log('Document téléchargé - Passeport');
            }

            // Activity 3: Vérification documents
            if ($dossier->status !== 'draft' && $dossier->created_at->diffInDays(now()) > 5) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'verification'])
                    ->createdAt($dossier->created_at->addDays(5))
                    ->log('Vérification des documents en cours');
            }

            // Activity 4: Documents approuvés
            if (in_array($dossier->status, ['in_progress', 'approved', 'completed']) && $dossier->created_at->diffInDays(now()) > 8) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'approved'])
                    ->createdAt($dossier->created_at->addDays(8))
                    ->log('Documents approuvés');
            }

            // Activity 5: Contrat généré
            if (in_array($dossier->status, ['in_progress', 'approved', 'completed']) && $dossier->created_at->diffInDays(now()) > 10) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'contract_generated'])
                    ->createdAt($dossier->created_at->addDays(10))
                    ->log('Contrat de prestation généré');
            }

            // Activity 6: En cours de traitement
            if (in_array($dossier->status, ['in_progress', 'approved', 'completed']) && $dossier->created_at->diffInDays(now()) > 15) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'processing'])
                    ->createdAt($dossier->created_at->addDays(15))
                    ->log('Dossier en cours de traitement');
            }

            // Activity 7: Dossier approuvé
            if (in_array($dossier->status, ['approved', 'completed']) && $dossier->created_at->diffInDays(now()) > 20) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'approved'])
                    ->createdAt($dossier->created_at->addDays(20))
                    ->log('Dossier approuvé par les autorités');
            }

            // Activity 8: Dossier finalisé
            if ($dossier->status === 'completed' && $dossier->created_at->diffInDays(now()) > 25) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy($causer)
                    ->withProperties(['status' => 'completed'])
                    ->createdAt($dossier->created_at->addDays(25))
                    ->log('Dossier finalisé avec succès');
            }
        }
    }
}
