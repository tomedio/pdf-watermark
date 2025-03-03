<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

interface WatermarkConfigInterface
{
    /**
     * Get the position of the watermark
     */
    public function getPosition(): string;

    /**
     * Get the opacity of the watermark
     */
    public function getOpacity(): float;

    /**
     * Get the rotation angle of the watermark
     */
    public function getAngle(): float;

    /**
     * Get the pages to apply the watermark to
     */
    public function getPages(): array;
}
