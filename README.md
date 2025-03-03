# PDF Watermark

A PHP library for adding text and image watermarks to PDF files using Ghostscript.

## Features

- Add text watermarks with customizable:
  - Font size, color, and opacity
  - Background color and opacity
  - Rotation angle
  - Position (top-left, center, bottom-right, etc.)
  - Pages to apply the watermark to (specific pages, ranges, last page, all pages)

- Add image watermarks with customizable:
  - Opacity
  - Rotation angle
  - Scaling
  - Position
  - Pages to apply the watermark to

- Works with compressed PDF files
- Recognizes page sizes and orientations
- Modifies existing pages without adding new ones
- Configuration-based approach

## Requirements

- PHP 8.1 or higher
- Ghostscript (9.0 or higher recommended)

## Installation

### 1. Install Ghostscript

#### On Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install ghostscript
```

#### On macOS (using Homebrew):

```bash
brew install ghostscript
```

#### On Windows:

Download and install Ghostscript from the [official website](https://www.ghostscript.com/download/gsdnld.html).

### 2. Install the library via Composer

```bash
composer require tomedio/pdf-watermark
```

## Basic Usage

### Adding a Text Watermark

```php
use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\AbstractWatermark;

// Create a factory
$factory = new PdfWatermarkerFactory();

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

// Apply the watermark
$watermarker->apply('input.pdf', 'output.pdf');
```

### Adding an Image Watermark

```php
use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\AbstractWatermark;

// Create a factory
$factory = new PdfWatermarkerFactory();

// Create and configure an image watermark config
$imageConfig = $factory->createImageWatermarkConfig('logo.png');
$imageConfig
    ->setPosition(AbstractWatermark::POSITION_BOTTOM_RIGHT)
    ->setOpacity(0.3)
    ->setAngle(0)
    ->setScale(0.2)  // Scale to 20% of original size
    ->setPages('all');

// Create a watermarker with the image watermark
$watermarker = $factory->createWithImageWatermark($imageConfig);

// Apply the watermark
$watermarker->apply('input.pdf', 'output.pdf');
```

### Adding Multiple Watermarks

```php
use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\AbstractWatermark;

// Create a factory
$factory = new PdfWatermarkerFactory();

// Create a watermarker
$watermarker = $factory->create();

// Create and configure a text watermark
$textConfig = $factory->createTextWatermarkConfig('CONFIDENTIAL');
$textConfig
    ->setPosition(AbstractWatermark::POSITION_CENTER)
    ->setAngle(45)
    ->setOpacity(0.5)
    ->setFontSize(72)
    ->setTextColor(255, 0, 0);

$textWatermark = $factory->createTextWatermark($textConfig);
$watermarker->addWatermark($textWatermark);

// Create and configure an image watermark
$imageConfig = $factory->createImageWatermarkConfig('logo.png');
$imageConfig
    ->setPosition(AbstractWatermark::POSITION_BOTTOM_RIGHT)
    ->setOpacity(0.3)
    ->setScale(0.2);

$imageWatermark = $factory->createImageWatermark($imageConfig);
$watermarker->addWatermark($imageWatermark);

// Apply the watermarks
$watermarker->apply('input.pdf', 'output.pdf');
```

### Page Selection

You can specify which pages to apply the watermark to:

```php
// Apply to all pages (default)
$config->setPages('all');

// Apply to a specific page
$config->setPages(1);  // First page only

// Apply to a range of pages
$config->setPages('2-5');  // Pages 2 to 5

// Apply to a range of pages to the end
$config->setPages('2-last');  // From page 2 to the last page

// Apply to the last page only
$config->setPages('last');

// Apply to multiple specific pages or ranges
$config->setPages([1, '3-5', 'last']);
```

## API Documentation

### PdfWatermarkerFactory

The factory class provides methods to create watermarkers, watermark configurations, and watermarks.

#### Methods

- `__construct(?string $ghostscriptPath = null)`: Constructor
- `create(): PdfWatermarker`: Create a new PdfWatermarker instance
- `createTextWatermarkConfig(string $text): TextWatermarkConfig`: Create a new text watermark configuration
- `createImageWatermarkConfig(string $imagePath): ImageWatermarkConfig`: Create a new image watermark configuration
- `createTextWatermark(TextWatermarkConfig $config): TextWatermark`: Create a new text watermark
- `createImageWatermark(ImageWatermarkConfig $config): ImageWatermark`: Create a new image watermark
- `createWithTextWatermark(TextWatermarkConfig $config): PdfWatermarker`: Create a PdfWatermarker with a text watermark
- `createWithImageWatermark(ImageWatermarkConfig $config): PdfWatermarker`: Create a PdfWatermarker with an image watermark

### PdfWatermarker

The main class for applying watermarks to PDF files.

#### Methods

- `__construct(?string $ghostscriptPath = null)`: Constructor
- `addWatermark(AbstractWatermark $watermark): self`: Add a watermark to be applied
- `apply(string $inputFile, string $outputFile): void`: Apply watermarks to a PDF file

### AbstractWatermark

The base class for all watermarks.

#### Constants

- `POSITION_TOP_LEFT`: Position at the top-left corner
- `POSITION_TOP_CENTER`: Position at the top-center
- `POSITION_TOP_RIGHT`: Position at the top-right corner
- `POSITION_MIDDLE_LEFT`: Position at the middle-left
- `POSITION_CENTER`: Position at the center
- `POSITION_MIDDLE_RIGHT`: Position at the middle-right
- `POSITION_BOTTOM_LEFT`: Position at the bottom-left corner
- `POSITION_BOTTOM_CENTER`: Position at the bottom-center
- `POSITION_BOTTOM_RIGHT`: Position at the bottom-right corner

### TextWatermarkConfig

Configuration class for text watermarks.

#### Methods

- `__construct(string $text)`: Constructor
- `setPosition(string $position): self`: Set the position of the watermark
- `setOpacity(float $opacity): self`: Set the opacity of the watermark (0.0 to 1.0)
- `setAngle(float $angle): self`: Set the rotation angle of the watermark in degrees
- `setPages(array|string $pages): self`: Set pages to apply the watermark to
- `setFontSize(int $fontSize): self`: Set the font size in points
- `setTextColor(int $r, int $g, int $b): self`: Set the text color in RGB format (0-255)
- `setTextOpacity(float $opacity): self`: Set the text opacity (0.0 to 1.0)
- `setBackgroundColor(int $r, int $g, int $b): self`: Set the background color in RGB format (0-255)
- `setBackgroundOpacity(float $opacity): self`: Set the background opacity (0.0 to 1.0)
- `setPadding(int $padding): self`: Set the padding around the text in points
- `setFontName(string $fontName): self`: Set the font name

### ImageWatermarkConfig

Configuration class for image watermarks.

#### Methods

- `__construct(string $imagePath)`: Constructor
- `setPosition(string $position): self`: Set the position of the watermark
- `setOpacity(float $opacity): self`: Set the opacity of the watermark (0.0 to 1.0)
- `setAngle(float $angle): self`: Set the rotation angle of the watermark in degrees
- `setPages(array|string $pages): self`: Set pages to apply the watermark to
- `setScale(float $scale): self`: Set the scale of the image (1.0 = original size)
- `setWidth(int $width): self`: Set the width of the image (height will be calculated to maintain aspect ratio)
- `setHeight(int $height): self`: Set the height of the image (width will be calculated to maintain aspect ratio)

## Examples

See the `examples` directory for more examples.

## License

This library is licensed under the MIT License.
