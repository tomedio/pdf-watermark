<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark;

use PdfWatermark\Watermark\Config\ImageWatermarkConfig;

class ImageWatermark extends AbstractWatermark
{
    /**
     * Constructor
     * 
     * @param ImageWatermarkConfig $config Image watermark configuration
     */
    public function __construct(ImageWatermarkConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get the image watermark configuration
     */
    public function getConfig(): ImageWatermarkConfig
    {
        return $this->config;
    }
}
