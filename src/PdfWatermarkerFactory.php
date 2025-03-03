<?php

declare(strict_types=1);

namespace PdfWatermark;

use PdfWatermark\Watermark\TextWatermark;
use PdfWatermark\Watermark\ImageWatermark;
use PdfWatermark\Watermark\Config\TextWatermarkConfig;
use PdfWatermark\Watermark\Config\ImageWatermarkConfig;

class PdfWatermarkerFactory
{
    private ?string $pdftk;
    private ?string $tempDir;

    /**
     * Constructor
     *
     * @param string|null $pdftk Path to the pdftk executable (defaults to 'pdftk')
     * @param string|null $tempDir Path to the temporary directory (defaults to system temp directory)
     */
    public function __construct(?string $pdftk = null, ?string $tempDir = null)
    {
        $this->pdftk = $pdftk;
        $this->tempDir = $tempDir;
    }

    /**
     * Create a new PdfWatermarker instance
     *
     * @return PdfWatermarker
     */
    public function create(): PdfWatermarker
    {
        return new PdfWatermarker($this->pdftk, $this->tempDir);
    }

    /**
     * Create a new text watermark configuration
     *
     * @param string $text Text to use for the watermark
     * @return TextWatermarkConfig
     */
    public function createTextWatermarkConfig(string $text): TextWatermarkConfig
    {
        return new TextWatermarkConfig($text);
    }

    /**
     * Create a new image watermark configuration
     *
     * @param string $imagePath Path to the image file
     * @return ImageWatermarkConfig
     */
    public function createImageWatermarkConfig(string $imagePath): ImageWatermarkConfig
    {
        return new ImageWatermarkConfig($imagePath);
    }

    /**
     * Create a new text watermark
     *
     * @param TextWatermarkConfig $config Text watermark configuration
     * @return TextWatermark
     */
    public function createTextWatermark(TextWatermarkConfig $config): TextWatermark
    {
        return new TextWatermark($config);
    }

    /**
     * Create a new image watermark
     *
     * @param ImageWatermarkConfig $config Image watermark configuration
     * @return ImageWatermark
     */
    public function createImageWatermark(ImageWatermarkConfig $config): ImageWatermark
    {
        return new ImageWatermark($config);
    }

    /**
     * Create a PdfWatermarker with a text watermark
     *
     * @param TextWatermarkConfig $config Text watermark configuration
     * @return PdfWatermarker
     */
    public function createWithTextWatermark(TextWatermarkConfig $config): PdfWatermarker
    {
        $watermarker = $this->create();
        $watermark = $this->createTextWatermark($config);
        $watermarker->addWatermark($watermark);
        return $watermarker;
    }

    /**
     * Create a PdfWatermarker with an image watermark
     *
     * @param ImageWatermarkConfig $config Image watermark configuration
     * @return PdfWatermarker
     */
    public function createWithImageWatermark(ImageWatermarkConfig $config): PdfWatermarker
    {
        $watermarker = $this->create();
        $watermark = $this->createImageWatermark($config);
        $watermarker->addWatermark($watermark);
        return $watermarker;
    }
}
