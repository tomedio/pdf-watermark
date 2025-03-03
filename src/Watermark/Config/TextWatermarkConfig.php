<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

class TextWatermarkConfig extends AbstractWatermarkConfig
{
    private string $text;
    private int $fontSize = 24;
    private string $textColor = '0 0 0'; // RGB values (0-1)
    private float $textOpacity = 1.0;
    private string $backgroundColor = '1 1 1'; // RGB values (0-1)
    private float $backgroundOpacity = 0.0; // Default to transparent background
    private int $padding = 5;
    private string $fontName = 'Helvetica';

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param int $fontSize Font size in points
     * @return $this
     */
    public function setFontSize(int $fontSize): self
    {
        if ($fontSize <= 0) {
            throw new \InvalidArgumentException('Font size must be greater than 0');
        }

        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     * Set the text color in RGB format
     * 
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @return $this
     */
    public function setTextColor(int $r, int $g, int $b): self
    {
        $this->validateRgbValues($r, $g, $b);
        $this->textColor = $this->rgbToPostScript($r, $g, $b);
        return $this;
    }

    /**
     * @param float $opacity Value between 0.0 and 1.0
     * @return $this
     */
    public function setTextOpacity(float $opacity): self
    {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new \InvalidArgumentException('Text opacity must be between 0.0 and 1.0');
        }

        $this->textOpacity = $opacity;
        return $this;
    }

    /**
     * Set the background color in RGB format
     * 
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @return $this
     */
    public function setBackgroundColor(int $r, int $g, int $b): self
    {
        $this->validateRgbValues($r, $g, $b);
        $this->backgroundColor = $this->rgbToPostScript($r, $g, $b);
        return $this;
    }

    /**
     * @param float $opacity Value between 0.0 and 1.0
     * @return $this
     */
    public function setBackgroundOpacity(float $opacity): self
    {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new \InvalidArgumentException('Background opacity must be between 0.0 and 1.0');
        }

        $this->backgroundOpacity = $opacity;
        return $this;
    }

    /**
     * @param int $padding Padding in points
     * @return $this
     */
    public function setPadding(int $padding): self
    {
        if ($padding < 0) {
            throw new \InvalidArgumentException('Padding must be greater than or equal to 0');
        }

        $this->padding = $padding;
        return $this;
    }

    /**
     * @param string $fontName Name of the font to use
     * @return $this
     */
    public function setFontName(string $fontName): self
    {
        $this->fontName = $fontName;
        return $this;
    }

    /**
     * Get the text of the watermark
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the font size of the watermark
     */
    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * Get the text color of the watermark in PostScript format
     */
    public function getTextColor(): string
    {
        return $this->textColor;
    }

    /**
     * Get the text opacity of the watermark
     */
    public function getTextOpacity(): float
    {
        return $this->textOpacity;
    }

    /**
     * Get the background color of the watermark in PostScript format
     */
    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * Get the background opacity of the watermark
     */
    public function getBackgroundOpacity(): float
    {
        return $this->backgroundOpacity;
    }

    /**
     * Get the padding of the watermark
     */
    public function getPadding(): int
    {
        return $this->padding;
    }

    /**
     * Get the font name of the watermark
     */
    public function getFontName(): string
    {
        return $this->fontName;
    }

    /**
     * Validate RGB values
     * 
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @throws \InvalidArgumentException If any value is out of range
     */
    private function validateRgbValues(int $r, int $g, int $b): void
    {
        if ($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255) {
            throw new \InvalidArgumentException('RGB values must be between 0 and 255');
        }
    }
    
    /**
     * Convert RGB values to PostScript RGB format (0-1)
     * 
     * @param int $r Red component (0-255)
     * @param int $g Green component (0-255)
     * @param int $b Blue component (0-255)
     * @return string PostScript RGB string
     */
    private function rgbToPostScript(int $r, int $g, int $b): string
    {
        return sprintf("%.3f %.3f %.3f", $r / 255, $g / 255, $b / 255);
    }
}
