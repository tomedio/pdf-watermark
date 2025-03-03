<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

class ImageWatermarkConfig extends AbstractWatermarkConfig
{
    private string $imagePath;
    private ?float $scale = null;
    private ?int $width = null;
    private ?int $height = null;

    public function __construct(string $imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException(sprintf('Image file "%s" does not exist', $imagePath));
        }

        $this->imagePath = $imagePath;
    }

    /**
     * Set the scale of the image
     *
     * @param float $scale Scale factor (1.0 = original size)
     * @return $this
     */
    public function setScale(float $scale): self
    {
        if ($scale <= 0) {
            throw new \InvalidArgumentException('Scale must be greater than 0');
        }

        $this->scale = $scale;
        $this->width = null;
        $this->height = null;
        return $this;
    }

    /**
     * Set the width of the image (height will be calculated to maintain aspect ratio)
     *
     * @param int $width Width in points
     * @return $this
     */
    public function setWidth(int $width): self
    {
        if ($width <= 0) {
            throw new \InvalidArgumentException('Width must be greater than 0');
        }

        $this->width = $width;
        $this->height = null;
        $this->scale = null;
        return $this;
    }

    /**
     * Set the height of the image (width will be calculated to maintain aspect ratio)
     *
     * @param int $height Height in points
     * @return $this
     */
    public function setHeight(int $height): self
    {
        if ($height <= 0) {
            throw new \InvalidArgumentException('Height must be greater than 0');
        }

        $this->height = $height;
        $this->width = null;
        $this->scale = null;
        return $this;
    }

    /**
     * Get the image path
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * Get the scale of the image
     */
    public function getScale(): ?float
    {
        return $this->scale;
    }

    /**
     * Get the width of the image
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Get the height of the image
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }
}
