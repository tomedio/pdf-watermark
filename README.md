# PDF Watermark

A PHP library for adding text and image watermarks to PDF files using FPDI.

## Features

- Add text watermarks with customizable:
  - Font size, color, and opacity
  - Font style (bold, italic, etc.)
  - Background color and opacity
  - Rotation angle
  - Position (top-left, center, bottom-right, etc.)
  - Pages to apply the watermark to (specific pages, ranges, last page, all pages)
  - Page number placeholder support (%d)

- Add image watermarks with customizable:
  - Opacity
  - Rotation angle
  - Scaling or explicit dimensions
  - Position
  - Pages to apply the watermark to

- Works with compressed PDF files and PDFs with versions higher than 1.4
- Recognizes page sizes and orientations
- Modifies existing pages without adding new ones
- Configuration-based approach
- Configurable temporary directory for processing files

## Requirements

- PHP 8.1 or higher
- pdftk (optional, but recommended for handling compressed PDFs with versions higher than 1.4)
- Ghostscript (optional, used for testing)

## Installation

### 1. Install pdftk (optional but recommended)

pdftk is used as a workaround for handling compressed PDFs with versions higher than 1.4.

#### On Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install pdftk
```

#### On macOS (using Homebrew):

```bash
brew install pdftk-java
```

#### On Windows:

Download and install pdftk from the [official website](https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/).

### 2. Install the library via Composer

```bash
composer require tomedio/pdf-watermark
```

## Basic Usage

### Adding a Text Watermark

```php
use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\AbstractWatermark;

// Create a factory (optionally specify the path to pdftk and a custom temp directory)
$factory = new PdfWatermarkerFactory('pdftk', '/path/to/custom/temp/dir');

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

### Custom Temporary Directory

You can specify a custom temporary directory for processing files:

```php
// Specify a custom temp directory in the factory
$factory = new PdfWatermarkerFactory('pdftk', '/path/to/custom/temp/dir');

// Or directly in the PdfWatermarker constructor
$watermarker = new PdfWatermarker('pdftk', '/path/to/custom/temp/dir');
```

If no temporary directory is specified, the system's default temporary directory (`sys_get_temp_dir()`) will be used.

## API Documentation

### PdfWatermarkerFactory

The factory class provides methods to create watermarkers, watermark configurations, and watermarks.

#### Methods

- `__construct(?string $pdftk = null, ?string $tempDir = null)`: Constructor
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

- `__construct(?string $pdftk = null, ?string $tempDir = null)`: Constructor
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

#### Constants

- `FONT_STYLE_REGULAR`: Regular font style (no formatting)
- `FONT_STYLE_BOLD`: Bold font style
- `FONT_STYLE_ITALIC`: Italic font style
- `FONT_STYLE_UNDERLINE`: Underlined font style
- `FONT_STYLE_STRIKETHROUGH`: Strikethrough font style

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
- `setFontName(string $fontName): self`: Set the font name
- `setFontStyle(int $fontStyle): self`: Set the font style using constants (e.g., `TextWatermarkConfig::FONT_STYLE_BOLD`, `TextWatermarkConfig::FONT_STYLE_ITALIC`)

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

## PDF Version Compatibility

This library uses FPDI to process PDF files, which has a limitation with compressed PDFs that have versions higher than 1.4. To overcome this limitation, the library implements a workaround using pdftk:

1. When a PDF cannot be processed directly with FPDI (typically due to being a compressed PDF with version > 1.4), the library automatically falls back to using pdftk.
2. pdftk is used to uncompress the PDF, making it compatible with FPDI.
3. The watermarks are applied to the uncompressed PDF using FPDI.
4. The resulting PDF is recompressed using pdftk.

This approach allows the library to work with a wide range of PDF files, including those with higher versions, without requiring the paid version of FPDI.

## Advanced Usage

### Using Page Number Placeholder

You can include the page number in your text watermark using the `%d` placeholder:

```php
// Create a text watermark with page number
$textConfig = $factory->createTextWatermarkConfig('Page %d');
$textConfig
    ->setPosition(AbstractWatermark::POSITION_BOTTOM_CENTER)
    ->setOpacity(0.5)
    ->setFontSize(10)
    ->setTextColor(0, 0, 0);

$watermarker = $factory->createWithTextWatermark($textConfig);
$watermarker->apply('input.pdf', 'output.pdf');
```

### Setting Font Name and Style

#### Font Name

You can set the font name for text watermarks using predefined constants or custom font names:

```php
use PdfWatermark\Watermark\Config\TextWatermarkConfig;

$textConfig = $factory->createTextWatermarkConfig('CONFIDENTIAL');

// Using predefined font constants
$textConfig->setFontName(TextWatermarkConfig::FONT_HELVETICA);
$textConfig->setFontName(TextWatermarkConfig::FONT_COURIER);
$textConfig->setFontName(TextWatermarkConfig::FONT_TIMES);
$textConfig->setFontName(TextWatermarkConfig::FONT_SYMBOL);

// Or using a string directly
$textConfig->setFontName('Helvetica');

// You can also use custom fonts if they are available in your TCPDF installation
$textConfig->setFontName('YourCustomFont');
```

#### Font Style

You can set the font style for text watermarks using constants:

```php
$textConfig
    ->setFontName(TextWatermarkConfig::FONT_HELVETICA)
    ->setFontStyle(TextWatermarkConfig::FONT_STYLE_BOLD)  // Use constants for font styles
    ->setFontSize(24);
```

You can also combine multiple font styles using the bitwise OR operator:

```php
// Apply both bold and italic styles
$textConfig->setFontStyle(
    TextWatermarkConfig::FONT_STYLE_BOLD | TextWatermarkConfig::FONT_STYLE_ITALIC
);

// Apply bold, underline, and strikethrough
$textConfig->setFontStyle(
    TextWatermarkConfig::FONT_STYLE_BOLD | 
    TextWatermarkConfig::FONT_STYLE_UNDERLINE | 
    TextWatermarkConfig::FONT_STYLE_STRIKETHROUGH
);
```

### Setting Explicit Dimensions for Image Watermarks

Instead of scaling, you can set explicit dimensions for image watermarks:

```php
$imageConfig = $factory->createImageWatermarkConfig('logo.png');
$imageConfig
    ->setPosition(AbstractWatermark::POSITION_BOTTOM_RIGHT)
    ->setWidth(200);  // Set width to 200 points, height will be calculated automatically

// Or set height instead
$imageConfig
    ->setHeight(100);  // Set height to 100 points, width will be calculated automatically
```


## Development

### Available Makefile Commands

The library includes a Makefile with several useful commands:

```bash
# Install dependencies
make install

# Run tests
make test

# Check coding standards
make cs

# Fix coding standards
make cs-fix

# Install Ghostscript (Ubuntu/Debian)
make install-gs-debian

# Install Ghostscript (macOS)
make install-gs-mac

# Run example
make example
```

## License

This library is licensed under the MIT License.
