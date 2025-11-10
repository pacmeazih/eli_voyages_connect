<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Service pour générer des contrats à partir de templates Word
 */
class ContractGeneratorService
{
    /**
     * Générer un contrat à partir d'un template Word
     *
     * @param string $templateName Nom du template (ex: 'service', 'reservation', 'payment')
     * @param array $variables Variables à remplacer dans le template
     * @param string $outputFormat Format de sortie ('docx', 'pdf')
     * @return string Chemin du fichier généré
     */
    public function generateContract(string $templateName, array $variables, string $outputFormat = 'pdf'): string
    {
        try {
            // Chemin du template
            $templatePath = storage_path("app/templates/contracts/{$templateName}.docx");
            
            if (!file_exists($templatePath)) {
                throw new \Exception("Template not found: {$templatePath}");
            }

            // Charger le template
            $templateProcessor = new TemplateProcessor($templatePath);

            // Remplacer toutes les variables
            foreach ($variables as $key => $value) {
                // Gérer les valeurs null
                $value = $value ?? '';
                
                // Remplacer la variable dans le template
                $templateProcessor->setValue($key, $value);
            }

            // Générer un nom de fichier unique
            $filename = "contract_{$templateName}_" . time() . '_' . uniqid();
            
            if ($outputFormat === 'pdf') {
                return $this->saveToPDF($templateProcessor, $filename);
            } else {
                return $this->saveToDocx($templateProcessor, $filename);
            }

        } catch (\Exception $e) {
            Log::error('Contract generation failed', [
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sauvegarder le document en DOCX
     */
    protected function saveToDocx(TemplateProcessor $templateProcessor, string $filename): string
    {
        $docxPath = storage_path("app/temp/{$filename}.docx");
        $templateProcessor->saveAs($docxPath);

        // Déplacer vers storage public
        $finalPath = "contracts/{$filename}.docx";
        Storage::put($finalPath, file_get_contents($docxPath));
        
        // Supprimer le fichier temporaire
        unlink($docxPath);

        return $finalPath;
    }

    /**
     * Sauvegarder le document en PDF
     */
    protected function saveToPDF(TemplateProcessor $templateProcessor, string $filename): string
    {
        // Sauvegarder d'abord en DOCX temporaire
        $docxPath = storage_path("app/temp/{$filename}.docx");
        $templateProcessor->saveAs($docxPath);

        // Convertir en PDF avec DomPDF
        $pdfPath = storage_path("app/temp/{$filename}.pdf");
        
        // Configuration pour la conversion PDF
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

        // Charger le document Word
        $phpWord = IOFactory::load($docxPath);
        
        // Sauvegarder en PDF
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
        $pdfWriter->save($pdfPath);

        // Déplacer vers storage public
        $finalPath = "contracts/{$filename}.pdf";
        Storage::put($finalPath, file_get_contents($pdfPath));
        
        // Supprimer les fichiers temporaires
        unlink($docxPath);
        unlink($pdfPath);

        return $finalPath;
    }

    /**
     * Obtenir les variables disponibles pour un type de contrat
     */
    public function getAvailableVariables(string $contractType): array
    {
        $commonVariables = [
            'date_generation' => 'Date de génération du contrat',
            'annee_courante' => 'Année en cours',
            'mois_courant' => 'Mois en cours',
        ];

        $specificVariables = [
            'service' => [
                // Client
                'client_civilite' => 'Civilité (M., Mme, Mlle)',
                'client_nom' => 'Nom du client',
                'client_prenom' => 'Prénom du client',
                'client_nom_complet' => 'Nom complet du client',
                'client_email' => 'Email du client',
                'client_telephone' => 'Téléphone du client',
                'client_adresse' => 'Adresse complète du client',
                'client_ville' => 'Ville du client',
                'client_code_postal' => 'Code postal du client',
                'client_pays' => 'Pays du client',
                'client_date_naissance' => 'Date de naissance',
                'client_lieu_naissance' => 'Lieu de naissance',
                'client_nationalite' => 'Nationalité',
                'client_numero_passeport' => 'Numéro de passeport',
                
                // Dossier
                'dossier_reference' => 'Référence du dossier (ex: DOS-2025-001)',
                'dossier_statut' => 'Statut du dossier',
                'dossier_type' => 'Type de dossier',
                'dossier_date_creation' => 'Date de création du dossier',
                
                // Voyage
                'destination' => 'Destination du voyage',
                'pays_destination' => 'Pays de destination',
                'date_depart' => 'Date de départ',
                'date_retour' => 'Date de retour',
                'duree_sejour' => 'Durée du séjour',
                'type_visa' => 'Type de visa',
                'motif_voyage' => 'Motif du voyage',
                
                // Financier
                'montant_total_ttc' => 'Montant total TTC',
                'montant_total_ht' => 'Montant total HT',
                'montant_tva' => 'Montant TVA',
                'montant_acompte' => 'Montant de l\'acompte',
                'montant_solde' => 'Montant du solde',
                'devise' => 'Devise (EUR, USD, MAD...)',
                'modalites_paiement' => 'Modalités de paiement',
                'echeances_paiement' => 'Échéances de paiement',
                
                // Garant (optionnel)
                'guarantor_nom' => 'Nom du garant',
                'guarantor_prenom' => 'Prénom du garant',
                'guarantor_nom_complet' => 'Nom complet du garant',
                'guarantor_email' => 'Email du garant',
                'guarantor_telephone' => 'Téléphone du garant',
                'guarantor_adresse' => 'Adresse du garant',
                'guarantor_relation' => 'Relation avec le client',
                
                // Agence
                'agence_nom' => 'Nom de l\'agence',
                'agence_adresse' => 'Adresse de l\'agence',
                'agence_telephone' => 'Téléphone de l\'agence',
                'agence_email' => 'Email de l\'agence',
                'agence_siret' => 'Numéro SIRET',
                'agence_rc' => 'Registre du commerce',
            ],
            'reservation' => [
                // Hérite des variables de 'service' + spécifiques
                'hotel_nom' => 'Nom de l\'hôtel',
                'hotel_adresse' => 'Adresse de l\'hôtel',
                'hotel_categorie' => 'Catégorie de l\'hôtel',
                'type_chambre' => 'Type de chambre',
                'nombre_nuits' => 'Nombre de nuits',
                'pension' => 'Type de pension',
                'vol_numero' => 'Numéro de vol',
                'vol_compagnie' => 'Compagnie aérienne',
                'vol_classe' => 'Classe de voyage',
            ],
            'payment' => [
                // Plan de paiement
                'echeance_1_date' => 'Date échéance 1',
                'echeance_1_montant' => 'Montant échéance 1',
                'echeance_2_date' => 'Date échéance 2',
                'echeance_2_montant' => 'Montant échéance 2',
                'echeance_3_date' => 'Date échéance 3',
                'echeance_3_montant' => 'Montant échéance 3',
                'mode_paiement' => 'Mode de paiement',
                'iban' => 'IBAN de l\'agence',
                'bic' => 'BIC de l\'agence',
            ],
        ];

        return array_merge($commonVariables, $specificVariables[$contractType] ?? []);
    }

    /**
     * Préparer les variables à partir des données du dossier
     */
    public function prepareVariables($dossier, $client, $package = null, $guarantor = null): array
    {
        $now = now();
        
        $variables = [
            // Dates système
            'date_generation' => $now->format('d/m/Y'),
            'annee_courante' => $now->format('Y'),
            'mois_courant' => $now->translatedFormat('F'),
            
            // Client
            'client_civilite' => $client->civilite ?? '',
            'client_nom' => strtoupper($client->nom ?? ''),
            'client_prenom' => ucfirst($client->prenom ?? ''),
            'client_nom_complet' => $client->nom_complet ?? ($client->prenom . ' ' . $client->nom),
            'client_email' => $client->email ?? '',
            'client_telephone' => $client->telephone ?? '',
            'client_adresse' => $client->adresse ?? '',
            'client_ville' => $client->ville ?? '',
            'client_code_postal' => $client->code_postal ?? '',
            'client_pays' => $client->pays ?? '',
            'client_date_naissance' => $client->date_naissance ? \Carbon\Carbon::parse($client->date_naissance)->format('d/m/Y') : '',
            'client_lieu_naissance' => $client->lieu_naissance ?? '',
            'client_nationalite' => $client->nationalite ?? '',
            'client_numero_passeport' => $client->numero_passeport ?? '',
            
            // Dossier
            'dossier_reference' => $dossier->reference ?? '',
            'dossier_statut' => $dossier->status ?? '',
            'dossier_type' => $dossier->type ?? '',
            'dossier_date_creation' => $dossier->created_at ? $dossier->created_at->format('d/m/Y') : '',
            
            // Agence (depuis config)
            'agence_nom' => config('app.agency_name', 'ELI-VOYAGES SARL U'),
            'agence_adresse' => config('app.agency_address', ''),
            'agence_telephone' => config('app.agency_phone', ''),
            'agence_email' => config('app.agency_email', ''),
            'agence_siret' => config('app.agency_siret', ''),
            'agence_rc' => config('app.agency_rc', ''),
        ];

        // Ajouter les données du package si disponible
        if ($package) {
            $variables = array_merge($variables, [
                'destination' => $package->destination ?? '',
                'pays_destination' => $package->pays ?? '',
                'date_depart' => $package->date_depart ? \Carbon\Carbon::parse($package->date_depart)->format('d/m/Y') : '',
                'date_retour' => $package->date_retour ? \Carbon\Carbon::parse($package->date_retour)->format('d/m/Y') : '',
                'duree_sejour' => $package->duree ?? '',
                'montant_total_ttc' => number_format($package->prix ?? 0, 2, ',', ' ') . ' €',
                'montant_total_ht' => number_format(($package->prix ?? 0) / 1.2, 2, ',', ' ') . ' €',
                'montant_tva' => number_format(($package->prix ?? 0) - (($package->prix ?? 0) / 1.2), 2, ',', ' ') . ' €',
                'devise' => $package->devise ?? 'EUR',
            ]);
        }

        // Ajouter les données du garant si disponible
        if ($guarantor) {
            $variables = array_merge($variables, [
                'guarantor_nom' => strtoupper($guarantor->nom ?? ''),
                'guarantor_prenom' => ucfirst($guarantor->prenom ?? ''),
                'guarantor_nom_complet' => $guarantor->nom_complet ?? ($guarantor->prenom . ' ' . $guarantor->nom),
                'guarantor_email' => $guarantor->email ?? '',
                'guarantor_telephone' => $guarantor->telephone ?? '',
                'guarantor_adresse' => $guarantor->adresse ?? '',
                'guarantor_relation' => $guarantor->relation ?? 'Garant',
            ]);
        }

        return $variables;
    }
}
