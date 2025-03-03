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
     * Get the text content of the watermark
     * 
     * @return string
     */
    public function getText(): string
    {
        $text = $this->getConfig()->getText();
        
        // Handle page number placeholder
        if (strpos($text, '%d') !== false) {
            // This will be handled in the PdfWatermarker class
            return $text;
        }
        
        return $text;
    }
}
