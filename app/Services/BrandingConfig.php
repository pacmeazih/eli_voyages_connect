<?php

namespace App\Services;

/**
 * ELI-VOYAGES Branding Configuration
 * Charte graphique et configuration visuelle de l'entreprise
 */
class BrandingConfig
{
    /**
     * Couleurs de la marque
     */
    public const COLORS = [
        'primary' => '1F497D',      // Bleu corporate principal
        'secondary' => 'FFD700',    // Or/Jaune accent
        'text_dark' => '333333',    // Texte principal
        'text_light' => '666666',   // Texte secondaire
        'accent' => '0066CC',       // Bleu accent pour liens
        'success' => '28A745',      // Vert succès
        'warning' => 'FFC107',      // Orange avertissement
        'danger' => 'DC3545',       // Rouge erreur
    ];

    /**
     * Tailles de police (en points)
     */
    public const FONT_SIZES = [
        'company_name' => 14,       // Nom de l'entreprise
        'title' => 16,              // Titre principal
        'subtitle' => 12,           // Sous-titre
        'article' => 11,            // Titre d'article
        'normal' => 10,             // Texte normal
        'small' => 9,               // Petit texte (footer, notes)
        'tiny' => 8,                // Très petit (mentions légales)
    ];

    /**
     * Styles de paragraphe
     */
    public const PARAGRAPH_SPACING = [
        'before_title' => 200,      // Espacement avant titre
        'after_title' => 150,       // Espacement après titre
        'before_article' => 150,    // Espacement avant article
        'after_article' => 100,     // Espacement après article
        'normal' => 100,            // Espacement normal
        'tight' => 50,              // Espacement serré
    ];

    /**
     * Marges de page (en twips: 1440 = 1 pouce)
     */
    public const PAGE_MARGINS = [
        'left' => 1134,             // ~2cm
        'right' => 1134,            // ~2cm
        'top' => 1700,              // ~3cm (pour header avec logo)
        'bottom' => 1134,           // ~2cm
    ];

    /**
     * Dimensions du logo
     */
    public const LOGO = [
        'width' => 120,             // Largeur en points
        'height' => 60,             // Hauteur en points
        'path' => 'templates/branding/Eli-Voyages LOGO.png',
        'icon_path' => 'templates/branding/Eli-Voyages icon.png',
    ];

    /**
     * Informations de l'entreprise
     */
    public const COMPANY_INFO = [
        'name' => 'ELI-VOYAGES SARL U',
        'address' => 'Adidogomé-Kohé, Lomé (Togo)',
        'phone' => '+1 (416) 276-8269',
        'email' => 'contact@eli-voyages.com',
        'website' => 'www.eli-voyages.com',
        'slogan_fr' => 'Votre partenaire pour une immigration réussie',
        'slogan_en' => 'Your partner for successful immigration',
    ];

    /**
     * Configuration des en-têtes
     */
    public static function getHeaderConfig(): array
    {
        return [
            'logo' => [
                'width' => self::LOGO['width'],
                'height' => self::LOGO['height'],
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ],
            'company_name' => [
                'text' => self::COMPANY_INFO['name'],
                'font' => [
                    'bold' => true,
                    'size' => self::FONT_SIZES['company_name'],
                    'color' => self::COLORS['primary'],
                ],
                'paragraph' => [
                    'alignment' => 'center',
                    'spaceAfter' => self::PARAGRAPH_SPACING['tight'],
                ],
            ],
            'contact_info' => [
                'text' => self::COMPANY_INFO['address'] . ' | Tél: ' . self::COMPANY_INFO['phone'],
                'font' => [
                    'size' => self::FONT_SIZES['small'],
                    'color' => self::COLORS['text_light'],
                ],
                'paragraph' => [
                    'alignment' => 'center',
                ],
            ],
        ];
    }

    /**
     * Configuration des pieds de page
     */
    public static function getFooterConfig(): array
    {
        return [
            'page_numbers' => [
                'text' => 'Page {PAGE} / {NUMPAGES}',
                'font' => [
                    'size' => self::FONT_SIZES['small'],
                    'color' => self::COLORS['text_light'],
                ],
                'paragraph' => [
                    'alignment' => 'center',
                ],
            ],
        ];
    }

    /**
     * Configuration des titres d'article
     */
    public static function getArticleTitleStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => self::FONT_SIZES['article'],
                'color' => self::COLORS['primary'],
            ],
            'paragraph' => [
                'spaceBefore' => self::PARAGRAPH_SPACING['before_article'],
                'spaceAfter' => self::PARAGRAPH_SPACING['after_article'],
            ],
        ];
    }

    /**
     * Configuration du texte normal
     */
    public static function getNormalTextStyle(): array
    {
        return [
            'font' => [
                'size' => self::FONT_SIZES['normal'],
                'color' => self::COLORS['text_dark'],
            ],
            'paragraph' => [
                'spaceAfter' => self::PARAGRAPH_SPACING['normal'],
                'alignment' => 'both', // Justifié
            ],
        ];
    }

    /**
     * Configuration des listes à puces
     */
    public static function getListItemStyle(): array
    {
        return [
            'font' => [
                'size' => self::FONT_SIZES['normal'],
                'color' => self::COLORS['text_dark'],
            ],
            'paragraph' => [
                'spaceAfter' => self::PARAGRAPH_SPACING['tight'],
            ],
        ];
    }

    /**
     * Obtient le chemin complet du logo
     */
    public static function getLogoPath(): string
    {
        return app_path(self::LOGO['path']);
    }

    /**
     * Obtient le chemin complet de l'icône
     */
    public static function getIconPath(): string
    {
        return app_path(self::LOGO['icon_path']);
    }

    /**
     * Vérifie si le logo existe
     */
    public static function logoExists(): bool
    {
        return file_exists(self::getLogoPath());
    }
}
