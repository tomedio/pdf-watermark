<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

class TextWatermarkConfig extends AbstractWatermarkConfig
{
    private string $text;
    private int $fontSize = 24;
    private string $textColor = '000000'; // Hex color
    private float $textOpacity = 1.0;
    private string $backgroundColor = 'ffffff'; // Hex color
    private float $backgroundOpacity = 0.0; // Default to transparent background
    private int $padding = 5;
    private string $fontName = 'Helvetica';
    private string $fontStyle = '';

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
        $this->textColor = sprintf('%02x%02x%02x', $r, $g, $b);
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
        $this->backgroundColor = sprintf('%02x%02x%02x', $r, $g, $b);
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
     * @param string $fontStyle Font style (e.g., 'B' for bold, 'I' for italic)
     * @return $this
     */
    public function setFontStyle(string $fontStyle): self
    {
        $this->fontStyle = $fontStyle;
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
     * Get the text color of the watermark as RGB array
     *
     * @return array [r, g, b] values (0-255)
     */
    public function getTextColor(): array
    {
        // Convert hex color to RGB array
        $hex = ltrim($this->textColor, '#');
        if (strlen($hex) === 6) {
            return [
                hexdec(substr($hex, 0, 2)), // r
                hexdec(substr($hex, 2, 2)), // g
                hexdec(substr($hex, 4, 2))  // b
            ];
        }

        // Default to black if invalid
        return [0, 0, 0];
    }

    /**
     * Get the text opacity of the watermark
     */
    public function getTextOpacity(): float
    {
        return $this->textOpacity;
    }

    /**
     * Get the background color of the watermark in hex format
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
     * Get the font style of the watermark
     */
    public function getFontStyle(): string
    {
        return $this->fontStyle;
    }

    /**
     * Get the font to use for the watermark
     * This is a convenience method that returns the font name
     */
    public function getFont(): string
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
}
