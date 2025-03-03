<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark;

use PdfWatermark\Watermark\Config\TextWatermarkConfig;

class TextWatermark extends AbstractWatermark
{
    /**
     * Constructor
     * 
     * @param TextWatermarkConfig $config Text watermark configuration
     */
    public function __construct(TextWatermarkConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get the text watermark configuration
     */
    public function getConfig(): TextWatermarkConfig
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePostScript(array $pageInfo): string
    {
        $width = $pageInfo['width'];
        $height = $pageInfo['height'];
        
        $config = $this->getConfig();
        
        // Calculate text width and height (approximate)
        $textWidth = $config->getFontSize() * strlen($config->getText()) * 0.6; // Approximate width
        $textHeight = $config->getFontSize() * 1.2; // Approximate height
        
        // Add padding
        $boxWidth = $textWidth + (2 * $config->getPadding());
        $boxHeight = $textHeight + (2 * $config->getPadding());
        
        // Calculate position based on the selected position constant
        list($x, $y) = $this->calculatePosition($width, $height, $boxWidth, $boxHeight);
        
        // Generate PostScript code
        $ps = [];
        $ps[] = "% Text watermark";
        $ps[] = "gsave";
        
        // Set position and apply rotation
        $ps[] = sprintf("%.2f %.2f translate", $x, $y);
        if ($config->getAngle() != 0) {
            $ps[] = sprintf("%.2f rotate", $config->getAngle());
        }
        
        // Draw background if opacity > 0
        if ($config->getBackgroundOpacity() > 0) {
            $ps[] = sprintf("%s setrgbcolor", $config->getBackgroundColor());
            $ps[] = sprintf("%.2f %.2f %.2f %.2f rectfill", 
                -$config->getPadding(), 
                -$config->getPadding(), 
                $boxWidth, 
                $boxHeight
            );
        }
        
        // Set font and draw text
        $ps[] = sprintf("/%s findfont %.2f scalefont setfont", $config->getFontName(), $config->getFontSize());
        $ps[] = sprintf("%s setrgbcolor", $config->getTextColor());
        $ps[] = sprintf("%.2f %.2f moveto", 0, $config->getFontSize() * 0.3); // Adjust for baseline
        $ps[] = sprintf("(%s) show", $this->escapePostScriptString($config->getText()));
        
        $ps[] = "grestore";
        
        return implode("\n", $ps);
    }
    
    /**
     * Calculate the position of the watermark based on the selected position constant
     * 
     * @param float $pageWidth Width of the page
     * @param float $pageHeight Height of the page
     * @param float $boxWidth Width of the watermark box
     * @param float $boxHeight Height of the watermark box
     * @return array [x, y] coordinates
     */
    private function calculatePosition(float $pageWidth, float $pageHeight, float $boxWidth, float $boxHeight): array
    {
        $margin = 20; // Margin from the edge of the page
        $position = $this->getPosition();
        
        switch ($position) {
            case self::POSITION_TOP_LEFT:
                return [$margin, $pageHeight - $margin - $boxHeight];
                
            case self::POSITION_TOP_CENTER:
                return [($pageWidth - $boxWidth) / 2, $pageHeight - $margin - $boxHeight];
                
            case self::POSITION_TOP_RIGHT:
                return [$pageWidth - $margin - $boxWidth, $pageHeight - $margin - $boxHeight];
                
            case self::POSITION_MIDDLE_LEFT:
                return [$margin, ($pageHeight - $boxHeight) / 2];
                
            case self::POSITION_CENTER:
                return [($pageWidth - $boxWidth) / 2, ($pageHeight - $boxHeight) / 2];
                
            case self::POSITION_MIDDLE_RIGHT:
                return [$pageWidth - $margin - $boxWidth, ($pageHeight - $boxHeight) / 2];
                
            case self::POSITION_BOTTOM_LEFT:
                return [$margin, $margin];
                
            case self::POSITION_BOTTOM_CENTER:
                return [($pageWidth - $boxWidth) / 2, $margin];
                
            case self::POSITION_BOTTOM_RIGHT:
            default:
                return [$pageWidth - $margin - $boxWidth, $margin];
        }
    }
    
    /**
     * Escape special characters in PostScript strings
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapePostScriptString(string $text): string
    {
        return str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $text);
    }
}
