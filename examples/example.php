<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\AbstractWatermark;

// Path to your input and output files
$inputFile = __DIR__ . '/input.pdf';
$outputFile = __DIR__ . '/output.pdf';

// Create a factory (optionally specify the path to Ghostscript)
$factory = new PdfWatermarkerFactory();

// Example 1: Add a simple text watermark
echo "Example 1: Adding a simple text watermark\n";

// Create and configure a text watermark config
$textConfig = $factory->createTextWatermarkConfig('CONFIDENTIAL');
$textConfig
    ->setPosition(AbstractWatermark::POSITION_CENTER)
    ->setAngle(45)
    ->setOpacity(0.5)
    ->setFontSize(72)
    ->setTextColor(255, 0, 0)  // Red text
    ->setBackgroundColor(255, 255, 255)  // White background
    ->setBackgroundOpacity(0.0)  // Transparent background
    ->setPages('all');  // Apply to all pages

// Create a watermarker with the text watermark
$watermarker = $factory->createWithTextWatermark($textConfig);

$watermarker->apply($inputFile, $outputFile);
echo "Watermark applied. Output saved to: $outputFile\n\n";

// Example 2: Add multiple watermarks
echo "Example 2: Adding multiple watermarks\n";
$watermarker = $factory->create();

// Add a text watermark at the bottom
$pageNumberConfig = $factory->createTextWatermarkConfig('Page %d');
$pageNumberConfig
    ->setPosition(AbstractWatermark::POSITION_BOTTOM_CENTER)
    ->setFontSize(10)
    ->setTextColor(100, 100, 100)  // Gray text
    ->setOpacity(0.8);

$textWatermark = $factory->createTextWatermark($pageNumberConfig);
$watermarker->addWatermark($textWatermark);

// Add an image watermark
$logoPath = __DIR__ . '/logo.png';  // Path to your logo
if (file_exists($logoPath)) {
    $logoConfig = $factory->createImageWatermarkConfig($logoPath);
    $logoConfig
        ->setPosition(AbstractWatermark::POSITION_TOP_RIGHT)
        ->setOpacity(0.3)
        ->setScale(0.2);  // Scale to 20% of original size

    $imageWatermark = $factory->createImageWatermark($logoConfig);
    $watermarker->addWatermark($imageWatermark);
}

$watermarker->apply($inputFile, $outputFile);
echo "Multiple watermarks applied. Output saved to: $outputFile\n\n";

// Example 3: Add watermarks to specific pages
echo "Example 3: Adding watermarks to specific pages\n";
$watermarker = $factory->create();

// Add a "DRAFT" watermark to the first page only
$draftConfig = $factory->createTextWatermarkConfig('DRAFT');
$draftConfig
    ->setPosition(AbstractWatermark::POSITION_CENTER)
    ->setAngle(45)
    ->setOpacity(0.5)
    ->setFontSize(72)
    ->setTextColor(255, 0, 0)
    ->setPages(1);  // Only the first page

$draftWatermark = $factory->createTextWatermark($draftConfig);
$watermarker->addWatermark($draftWatermark);

// Add a "CONFIDENTIAL" watermark to pages 2-5
$confidentialConfig = $factory->createTextWatermarkConfig('CONFIDENTIAL');
$confidentialConfig
    ->setPosition(AbstractWatermark::POSITION_CENTER)
    ->setAngle(45)
    ->setOpacity(0.5)
    ->setFontSize(72)
    ->setTextColor(0, 0, 255)  // Blue text
    ->setPages('2-5');  // Pages 2 to 5

$confidentialWatermark = $factory->createTextWatermark($confidentialConfig);
$watermarker->addWatermark($confidentialWatermark);

// Add a "FINAL" watermark to the last page
$finalConfig = $factory->createTextWatermarkConfig('FINAL');
$finalConfig
    ->setPosition(AbstractWatermark::POSITION_CENTER)
    ->setAngle(45)
    ->setOpacity(0.5)
    ->setFontSize(72)
    ->setTextColor(0, 128, 0)  // Green text
    ->setPages('last');  // Only the last page

$finalWatermark = $factory->createTextWatermark($finalConfig);
$watermarker->addWatermark($finalWatermark);

$watermarker->apply($inputFile, $outputFile);
echo "Page-specific watermarks applied. Output saved to: $outputFile\n";
