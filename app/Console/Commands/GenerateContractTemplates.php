<?php

namespace App\Console\Commands;

use App\Services\ContractGenerationService;
use Illuminate\Console\Command;

class GenerateContractTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:generate-templates {--lang=both : Language (fr, en, or both)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate .docx contract templates from .txt files with ELI-VOYAGES header/footer';

    /**
     * Execute the console command.
     */
    public function handle(ContractGenerationService $contractService): int
    {
        $language = $this->option('lang');
        $languages = $language === 'both' ? ['fr', 'en'] : [$language];

        $this->info('🚀 Starting contract template generation...');
        $this->newLine();

        foreach ($languages as $lang) {
            $this->info("📄 Generating {$lang} templates...");
            
            $contractTypes = $contractService->getAvailableContractTypes($lang);

            $progressBar = $this->output->createProgressBar(count($contractTypes));
            $progressBar->start();

            foreach ($contractTypes as $contractType) {
                try {
                    $templatePath = $contractService->createDocxTemplate($contractType, $lang);
                    $this->newLine();
                    $this->line("  ✅ {$contractType} ({$lang}): {$templatePath}");
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("  ❌ {$contractType} ({$lang}): " . $e->getMessage());
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->newLine(2);
        }

        $this->info('✨ Contract template generation completed!');
        
        return Command::SUCCESS;
    }
}
