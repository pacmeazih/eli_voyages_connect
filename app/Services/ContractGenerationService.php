<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Facades\Storage;
use Exception;

class ContractGenerationService
{
    /**
     * Template path for contracts with header and footer
     */
    protected string $templatePath;

    /**
     * Available contract types with their template files
     */
    protected array $contractTypes = [
        'etude' => 'Contrat_prestation_service_model_etude.txt',
        'etude_2e_3e_cycle' => 'Contrat_prestation_service_model_etude_2e_et_3e_cycle.txt',
        'etude_garant' => 'Contrat_prestation_service_model_etude_version_garant_et_beneficiaire.txt',
        'entree_express' => 'Contrat_prestation_service_model_entre_express.txt',
        'permis_travail' => 'Contrat_prestation_service_model_permis_travail.txt',
        'visa_visiteur' => 'Contrat_prestation_service_model_visa_visiteur.txt',
        'super_visa' => 'Contrat_prestation_service_model_super_visa.txt',
        'parrainage_familial' => 'Contrat_prestation_service_model_parrainage_familial.txt',
        'citoyennete' => 'Contrat_prestation_service_model_citoyennete.txt',
        'ave' => 'Contrat_prestation_service_model_ave.txt',
        'csq_quebec' => 'Contrat_prestation_service_model_csq_quebec.txt',
        'lmia' => 'Contrat_prestation_service_model_lmia.txt',
        'restauration_prolongation' => 'Contrat_prestation_service_model_restauration_prolongation.txt',
        'demande_asile' => 'Contrat_prestation_service_model_demande_asile.txt',
        'traduction_documents' => 'Contrat_prestation_service_model_traduction_documents.txt',
    ];

    /**
     * English contract types mapping
     */
    protected array $contractTypesEnglish = [
        'etude' => 'prestation_service_study_model_english version.txt',
        'permis_travail' => 'Service_agreement_work_permit_model_english.txt',
        'visa_visiteur' => 'Service_agreement_visitor_visa_model_english.txt',
        'super_visa' => 'Service_agreement_super_visa_model_english.txt',
        'parrainage_familial' => 'Service_agreement_family_sponsorship_model_english.txt',
        'citoyennete' => 'Service_agreement_citizenship_model_english.txt',
        'ave' => 'Service_agreement_eTA_model_english.txt',
        'csq_quebec' => 'Service_agreement_CSQ_Quebec_model_english.txt',
        'lmia' => 'Service_agreement_LMIA_model_english.txt',
        'restauration_prolongation' => 'Service_agreement_status_restoration_extension_model_english.txt',
        'demande_asile' => 'Service_agreement_asylum_claim_model_english.txt',
        'traduction_documents' => 'Service_agreement_document_translation_model_english.txt',
    ];

    public function __construct()
    {
        $this->templatePath = base_path('app/templates/contracts/En tete et pied de page model.docx');
    }

    /**
     * Generate a contract from template with variable replacement
     *
     * @param string $contractType Contract type slug
     * @param array $variables Associative array of variables to replace
     * @param string $language Language code ('fr' or 'en')
     * @return string Path to the generated contract
     * @throws Exception
     */
    public function generateContract(string $contractType, array $variables, string $language = 'fr'): string
    {
        // Validate contract type
        $contractFiles = $language === 'en' ? $this->contractTypesEnglish : $this->contractTypes;
        
        if (!isset($contractFiles[$contractType])) {
            throw new Exception("Contract type '{$contractType}' not found");
        }

        // Load contract text template
        $textTemplatePath = base_path("models_contrat/{$contractFiles[$contractType]}");
        
        if (!file_exists($textTemplatePath)) {
            throw new Exception("Contract text template not found: {$textTemplatePath}");
        }

        $contractText = file_get_contents($textTemplatePath);

        // Replace variables in contract text
        foreach ($variables as $key => $value) {
            $contractText = str_replace('${' . $key . '}', $value, $contractText);
        }

        // Create PHPWord document from template
        $phpWord = new PhpWord();

        // Load the template with header and footer
        if (!file_exists($this->templatePath)) {
            throw new Exception("Template with header/footer not found: {$this->templatePath}");
        }

        // Create a new section with branding
        $section = $phpWord->addSection([
            'marginLeft' => BrandingConfig::PAGE_MARGINS['left'],
            'marginRight' => BrandingConfig::PAGE_MARGINS['right'],
            'marginTop' => BrandingConfig::PAGE_MARGINS['top'],
            'marginBottom' => BrandingConfig::PAGE_MARGINS['bottom'],
        ]);

        // Add header with logo and branding
        $header = $section->addHeader();
        $headerConfig = BrandingConfig::getHeaderConfig();
        
        // Add logo (centered)
        if (BrandingConfig::logoExists()) {
            $header->addImage(
                BrandingConfig::getLogoPath(),
                $headerConfig['logo']
            );
        }
        
        $header->addText(
            $headerConfig['company_name']['text'],
            $headerConfig['company_name']['font'],
            $headerConfig['company_name']['paragraph']
        );
        $header->addText(
            $headerConfig['contact_info']['text'],
            $headerConfig['contact_info']['font'],
            $headerConfig['contact_info']['paragraph']
        );

        // Add footer with branding
        $footer = $section->addFooter();
        $footerConfig = BrandingConfig::getFooterConfig();
        $footer->addText(
            $footerConfig['page_numbers']['text'],
            $footerConfig['page_numbers']['font'],
            $footerConfig['page_numbers']['paragraph']
        );

        // Add contract content
        $this->addFormattedText($section, $contractText);

        // Generate unique filename
        $filename = $contractType . '_' . date('Y-m-d_His') . '_' . uniqid() . '.docx';
        $outputPath = storage_path("app/contracts/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Save the document
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($outputPath);

        return $outputPath;
    }

    /**
     * Add formatted text to section, preserving line breaks and structure
     * Uses BrandingConfig for consistent styling
     *
     * @param \PhpOffice\PhpWord\Element\Section $section
     * @param string $text
     */
    protected function addFormattedText($section, string $text): void
    {
        $lines = explode("\n", $text);
        $articleStyle = BrandingConfig::getArticleTitleStyle();
        $normalStyle = BrandingConfig::getNormalTextStyle();
        $listStyle = BrandingConfig::getListItemStyle();
        
        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                $section->addTextBreak();
                continue;
            }

            // Article titles (with branding colors)
            if (preg_match('/^ARTICLE\s+\d+\s*[–-]/', $line)) {
                $section->addText(
                    $line,
                    $articleStyle['font'],
                    $articleStyle['paragraph']
                );
                continue;
            }

            // Main sections (all caps, bold with primary color)
            if (preg_match('/^[A-Z\s]+:$/', $line)) {
                $section->addText(
                    $line,
                    $articleStyle['font'],
                    $articleStyle['paragraph']
                );
                continue;
            }

            // Bullet points
            if (preg_match('/^[•\-]\s*(.+)$/', $line, $matches)) {
                $section->addListItem(
                    $matches[1],
                    0,
                    $listStyle['font'],
                    $listStyle['paragraph']
                );
                continue;
            }

            // Regular text with justification
            $section->addText(
                $line,
                $normalStyle['font'],
                $normalStyle['paragraph']
            );
        }
    }

    /**
     * Generate a contract using the PHPWord template processor (alternative method)
     * This requires a .docx template with ${variable} placeholders
     *
     * @param string $templateDocxPath Path to .docx template
     * @param array $variables Variables to replace
     * @return string Path to generated contract
     */
    public function generateFromDocxTemplate(string $templateDocxPath, array $variables): string
    {
        if (!file_exists($templateDocxPath)) {
            throw new Exception("Template not found: {$templateDocxPath}");
        }

        $templateProcessor = new TemplateProcessor($templateDocxPath);

        // Replace all variables
        foreach ($variables as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // Generate filename
        $filename = 'contract_' . date('Y-m-d_His') . '_' . uniqid() . '.docx';
        $outputPath = storage_path("app/contracts/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        $templateProcessor->saveAs($outputPath);

        return $outputPath;
    }

    /**
     * Convert .txt contract templates to .docx templates with header/footer
     * This should be run once to create the .docx templates
     *
     * @param string $contractType
     * @param string $language
     * @return string Path to created .docx template
     */
    public function createDocxTemplate(string $contractType, string $language = 'fr'): string
    {
        $contractFiles = $language === 'en' ? $this->contractTypesEnglish : $this->contractTypes;
        
        if (!isset($contractFiles[$contractType])) {
            throw new Exception("Contract type '{$contractType}' not found");
        }

        $textTemplatePath = base_path("models_contrat/{$contractFiles[$contractType]}");
        
        if (!file_exists($textTemplatePath)) {
            throw new Exception("Contract text template not found: {$textTemplatePath}");
        }

        $contractText = file_get_contents($textTemplatePath);

        // Create PHPWord document
        $phpWord = new PhpWord();
        
        $section = $phpWord->addSection([
            'marginLeft' => BrandingConfig::PAGE_MARGINS['left'],
            'marginRight' => BrandingConfig::PAGE_MARGINS['right'],
            'marginTop' => BrandingConfig::PAGE_MARGINS['top'],
            'marginBottom' => BrandingConfig::PAGE_MARGINS['bottom'],
        ]);

        // Add header with logo and branding
        $header = $section->addHeader();
        $headerConfig = BrandingConfig::getHeaderConfig();
        
        // Add logo (centered)
        if (BrandingConfig::logoExists()) {
            $header->addImage(
                BrandingConfig::getLogoPath(),
                $headerConfig['logo']
            );
        }
        
        // Company name
        $header->addText(
            $headerConfig['company_name']['text'],
            $headerConfig['company_name']['font'],
            $headerConfig['company_name']['paragraph']
        );
        
        // Contact info
        $header->addText(
            $headerConfig['contact_info']['text'],
            $headerConfig['contact_info']['font'],
            $headerConfig['contact_info']['paragraph']
        );

        // Add footer with branding
        $footer = $section->addFooter();
        $footerConfig = BrandingConfig::getFooterConfig();
        $footer->addText(
            $footerConfig['page_numbers']['text'],
            $footerConfig['page_numbers']['font'],
            $footerConfig['page_numbers']['paragraph']
        );

        // Add contract content
        $this->addFormattedText($section, $contractText);

        // Save as template
        $templateName = $contractType . '_' . $language . '_template.docx';
        $templatePath = storage_path("app/templates/contracts/{$templateName}");

        // Ensure directory exists
        if (!file_exists(dirname($templatePath))) {
            mkdir(dirname($templatePath), 0755, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($templatePath);

        return $templatePath;
    }

    /**
     * Get list of available contract types
     *
     * @param string $language
     * @return array
     */
    public function getAvailableContractTypes(string $language = 'fr'): array
    {
        return array_keys($language === 'en' ? $this->contractTypesEnglish : $this->contractTypes);
    }

    /**
     * Get contract template filename for a contract type
     *
     * @param string $contractType
     * @param string $language
     * @return string|null
     */
    public function getContractTemplateFilename(string $contractType, string $language = 'fr'): ?string
    {
        $contractFiles = $language === 'en' ? $this->contractTypesEnglish : $this->contractTypes;
        return $contractFiles[$contractType] ?? null;
    }
}
