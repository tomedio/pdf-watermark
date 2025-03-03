<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark;

use PdfWatermark\Watermark\Config\WatermarkConfigInterface;

abstract class AbstractWatermark
{
    public const POSITION_TOP_LEFT = 'top-left';
    public const POSITION_TOP_CENTER = 'top-center';
    public const POSITION_TOP_RIGHT = 'top-right';
    public const POSITION_MIDDLE_LEFT = 'middle-left';
    public const POSITION_CENTER = 'center';
    public const POSITION_MIDDLE_RIGHT = 'middle-right';
    public const POSITION_BOTTOM_LEFT = 'bottom-left';
    public const POSITION_BOTTOM_CENTER = 'bottom-center';
    public const POSITION_BOTTOM_RIGHT = 'bottom-right';

    protected WatermarkConfigInterface $config;

    /**
     * Constructor
     * 
     * @param WatermarkConfigInterface $config Watermark configuration
     */
    public function __construct(WatermarkConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get the position of the watermark
     */
    public function getPosition(): string
    {
        return $this->config->getPosition();
    }

    /**
     * Get the opacity of the watermark
     */
    public function getOpacity(): float
    {
        return $this->config->getOpacity();
    }

    /**
     * Get the rotation angle of the watermark
     */
    public function getAngle(): float
    {
        return $this->config->getAngle();
    }

    /**
     * Get the pages to apply the watermark to
     */
    public function getPages(): array
    {
        return $this->config->getPages();
    }

    /**
     * Get the configuration object
     */
    public function getConfig(): WatermarkConfigInterface
    {
        return $this->config;
    }
}
