<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

class TextWatermarkConfig extends AbstractWatermarkConfig
{
    /**
     * Font style constants
     */
    public const FONT_STYLE_REGULAR = 0;
    public const FONT_STYLE_BOLD = 1;
    public const FONT_STYLE_ITALIC = 2;
    public const FONT_STYLE_UNDERLINE = 4;
    public const FONT_STYLE_STRIKETHROUGH = 8;
    
    /**
     * Native font constants
     */
    public const FONT_COURIER = 'Courier';
    public const FONT_HELVETICA = 'Helvetica';
    public const FONT_TIMES = 'Times';
    public const FONT_SYMBOL = 'Symbol';
    
    private string $text;
    private int $fontSize = 24; // Font size in pixels (will be converted to points internally)
    private string $textColor = '000000'; // Hex color
    private float $textOpacity = 1.0;
    private string $backgroundColor = 'ffffff'; // Hex color
    private float $backgroundOpacity = 0.0; // Default to transparent background
    private string $fontName = 'Helvetica';
    private int $fontStyle = self::FONT_STYLE_REGULAR;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param int $fontSize Font size in pixels
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
     * Set the font name to use for the watermark
     * 
     * You can use one of the predefined constants:
     * - TextWatermarkConfig::FONT_COURIER
     * - TextWatermarkConfig::FONT_HELVETICA
     * - TextWatermarkConfig::FONT_TIMES
     * - TextWatermarkConfig::FONT_SYMBOL
     * 
     * Or specify a custom font name that is available in your TCPDF installation.
     * 
     * @param string $fontName Name of the font to use
     * @return $this
     */
    public function setFontName(string $fontName): self
    {
        $this->fontName = $fontName;
        return $this;
    }

    /**
     * Set the font style
     * 
     * @param int $fontStyle Font style (use TextWatermarkConfig::FONT_STYLE_* constants)
     * @return $this
     * @throws \InvalidArgumentException If an invalid font style is provided
     */
    public function setFontStyle(int $fontStyle): self
    {
        // Validate that only valid bits are set
        $validBits = self::FONT_STYLE_BOLD | self::FONT_STYLE_ITALIC | 
                     self::FONT_STYLE_UNDERLINE | self::FONT_STYLE_STRIKETHROUGH;
        
        if (($fontStyle & ~$validBits) !== 0) {
            throw new \InvalidArgumentException(
                'Invalid font style. Use TextWatermarkConfig::FONT_STYLE_* constants.'
            );
        }
        
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
     * Get the font name of the watermark
     */
    public function getFontName(): string
    {
        return $this->fontName;
    }

    /**
     * Get the font style of the watermark
     * 
     * @return string Font style string for TCPDF
     */
    public function getFontStyle(): string
    {
        $style = '';
        
        if ($this->fontStyle & self::FONT_STYLE_BOLD) {
            $style .= 'B';
        }
        
        if ($this->fontStyle & self::FONT_STYLE_ITALIC) {
            $style .= 'I';
        }
        
        if ($this->fontStyle & self::FONT_STYLE_UNDERLINE) {
            $style .= 'U';
        }
        
        if ($this->fontStyle & self::FONT_STYLE_STRIKETHROUGH) {
            $style .= 'D';
        }
        
        return $style;
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
