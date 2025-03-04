<?php

declare(strict_types=1);

namespace PdfWatermark;

use PdfWatermark\Watermark\AbstractWatermark;
use PdfWatermark\Watermark\TextWatermark;
use PdfWatermark\Watermark\ImageWatermark;
use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PdfWatermarker
{
    private string $pdftk;
    private array $watermarks = [];
    private array $pageInfo = [];
    private int $totalPages = 0;
    private ?string $tempDir;

    /**
     * Constructor
     *
     * @param string|null $pdftk Path to the pdftk executable (defaults to 'pdftk')
     * @param string|null $tempDir Path to the temporary directory (defaults to system temp directory)
     */
    public function __construct(?string $pdftk = null, ?string $tempDir = null)
    {
        $this->pdftk = $pdftk ?? 'pdftk';
        $this->tempDir = $tempDir;
    }

    /**
     * Add a watermark to be applied to the PDF
     *
     * @param AbstractWatermark $watermark The watermark to add
     * @return $this
     */
    public function addWatermark(AbstractWatermark $watermark): self
    {
        $this->watermarks[] = $watermark;
        return $this;
    }

    /**
     * Apply watermarks to a PDF file
     *
     * @param string $inputFile Path to the input PDF file
     * @param string $outputFile Path to the output PDF file
     * @throws \InvalidArgumentException If the input file doesn't exist
     * @throws \RuntimeException If processing fails
     */
    public function apply(string $inputFile, string $outputFile): void
    {
        if (!file_exists($inputFile)) {
            throw new \InvalidArgumentException(sprintf('Input file "%s" does not exist', $inputFile));
        }

        if (empty($this->watermarks)) {
            throw new \InvalidArgumentException('No watermarks have been added');
        }

        // Create a temporary directory for intermediate files
        $baseDir = $this->tempDir ?? sys_get_temp_dir();
        $tempDir = $baseDir . '/pdf_watermark_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            // Try to process the PDF directly
            try {
                $this->processPdf($inputFile, $outputFile);
            } catch (CrossReferenceException | PdfParserException $e) {
                // If we get a parser exception, try the pdftk workaround
                $this->processPdfWithPdftk($inputFile, $outputFile, $tempDir);
            }
        } finally {
            // Clean up temporary files
            $this->cleanupTempDir($tempDir);
        }
    }

    /**
     * Process a PDF file directly using FPDI
     *
     * @param string $inputFile Path to the input PDF file
     * @param string $outputFile Path to the output PDF file
     * @throws PdfParserException If the PDF cannot be parsed
     */
    private function processPdf(string $inputFile, string $outputFile): void
    {
        // Extract page information
        $this->extractPageInfo($inputFile);

        // Create a new PDF document
        $pdf = new Fpdi();

        // Disable auto page break to prevent unexpected new pages
        $pdf->SetAutoPageBreak(false);

        // Disable default border drawing
        $pdf->SetDrawColor(255, 255, 255, 0); // Transparent
        $pdf->SetLineWidth(0); // Set line width to 0

        // Disable headers and footers
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setHeaderMargin(0);
        $pdf->setFooterMargin(0);

        // Set the source file
        $pdf->setSourceFile($inputFile);

        // Process each page
        for ($pageNo = 1; $pageNo <= $this->totalPages; $pageNo++) {
            // Import the page
            $templateId = $pdf->importPage($pageNo, 'MediaBox');

            // Get the page size
            $size = $pdf->getTemplateSize($templateId);

            // Add a page with the same size and orientation
            if ($size['width'] > $size['height']) {
                $pdf->AddPage('L', [$size['width'], $size['height']]);
            } else {
                $pdf->AddPage('P', [$size['width'], $size['height']]);
            }

            // Ensure no borders are drawn
            $pdf->SetLineWidth(0);
            $pdf->SetDrawColor(255, 255, 255, 0);

            // Use the imported page as a template - specify position explicitly
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], true);

            // Apply watermarks to this page
            foreach ($this->watermarks as $watermark) {
                $this->applyWatermarkToPage($pdf, $watermark, $pageNo, $size);
            }
        }

        // Output the PDF - ensure the path is properly formatted
        $pdf->Output($outputFile, 'F');
    }

    /**
     * Process a PDF file using the pdftk workaround for compressed PDFs
     *
     * @param string $inputFile Path to the input PDF file
     * @param string $outputFile Path to the output PDF file
     * @param string $tempDir Temporary directory for intermediate files
     * @throws \RuntimeException If pdftk fails
     */
    private function processPdfWithPdftk(string $inputFile, string $outputFile, string $tempDir): void
    {
        // Uncompress the PDF using pdftk
        $uncompressedFile = $tempDir . '/uncompressed.pdf';
        $this->runPdftk($inputFile, $uncompressedFile, 'uncompress');

        // Process the uncompressed PDF
        $processedFile = $tempDir . '/processed.pdf';
        $this->processPdf($uncompressedFile, $processedFile);

        // Recompress the PDF
        $this->runPdftk($processedFile, $outputFile, 'compress');
    }

    /**
     * Run pdftk command
     *
     * @param string $inputFile Input file path
     * @param string $outputFile Output file path
     * @param string $operation Operation to perform (compress or uncompress)
     * @throws \RuntimeException If pdftk fails
     */
    private function runPdftk(string $inputFile, string $outputFile, string $operation): void
    {
        $process = new Process([
            $this->pdftk,
            $inputFile,
            'output',
            $outputFile,
            $operation
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Extract page information from the PDF
     *
     * @param string $inputFile Path to the input PDF file
     */
    private function extractPageInfo(string $inputFile): void
    {
        $this->pageInfo = [];

        // Create a temporary FPDI instance to get page information
        $pdf = new Fpdi();
        $this->totalPages = $pdf->setSourceFile($inputFile);

        for ($pageNo = 1; $pageNo <= $this->totalPages; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $this->pageInfo[$pageNo] = [
                'width' => $size['width'],
                'height' => $size['height'],
                'orientation' => $size['width'] > $size['height'] ? 'landscape' : 'portrait',
            ];
        }
    }

    /**
     * Apply a watermark to a specific page
     *
     * @param Fpdi $pdf The PDF document
     * @param AbstractWatermark $watermark The watermark to apply
     * @param int $pageNo The page number
     * @param array $size The page size information
     */
    private function applyWatermarkToPage(Fpdi $pdf, AbstractWatermark $watermark, int $pageNo, array $size): void
    {
        // Check if this page should be watermarked
        if (!$this->shouldWatermarkPage($watermark->getPages(), $pageNo)) {
            return;
        }

        // Apply the watermark based on its type
        if ($watermark instanceof TextWatermark) {
            $this->applyTextWatermark($pdf, $watermark, $size);
        } elseif ($watermark instanceof ImageWatermark) {
            $this->applyImageWatermark($pdf, $watermark, $size);
        }
    }

    /**
     * Apply a text watermark to the current page
     *
     * @param Fpdi $pdf The PDF document
     * @param TextWatermark $watermark The text watermark to apply
     * @param array $size The page size information
     */
    private function applyTextWatermark(Fpdi $pdf, TextWatermark $watermark, array $size): void
    {
        $config = $watermark->getConfig();
        $text = $watermark->getText();
        $position = $watermark->getPosition();

        // Adjust font size for middle-right position
        $fontSize = $config->getFontSize();

        // Set font with potentially adjusted size
        $pdf->SetFont($config->getFont(), $config->getFontStyle(), $config->getFontSize());

        // Get text dimensions
        $textWidth = $watermark->getAngle() != 0 ? $pdf->GetStringWidth($text) : ceil($pdf->GetStringWidth($text)) + 2;

        // Calculate rectangle dimensions - consistent for all positions
        $rectWidth = $textWidth + 2.0;

        // Calculate font height based on TCPDF's font metrics
        // For text watermarks, we want a much tighter fit around the text
        // Adjust the font height to be proportional to the font size
        $fontHeight = $fontSize * 0.4; // Significantly reduce the height to better fit the text
        // Set rectangle height to match text height with minimal padding
        $rectHeight = $fontHeight + 0.5; // Minimal padding

        // Initialize rectangle and text positions
        $rectX = 0;
        $rectY = 0;
        $textX = 0;
        $textY = 0;

        // Calculate positions based on alignment
        // Horizontal positioning
        if (strpos($position, 'left') !== false) {
            // Left alignment - stick to left border
            $rectX = 0;
            $textX = $rectX + 2.0; // Text aligned with rectangle
        } elseif (strpos($position, 'right') !== false) {
            // Right alignment - stick to right border
            $rectX = $size['width'] - $rectWidth;
            $textX = $rectX + 2.0; // Text aligned with rectangle
        } else {
            // Center alignment
            $rectX = ($size['width'] - $rectWidth) / 2;
            $textX = $rectX + 2.0; // Text aligned with rectangle
        }

        // Calculate position using the same method as for image watermarks
        list($rectX, $rectY) = $this->calculatePosition(
            $position,
            $size,
            $rectWidth,
            $rectHeight
        );

        // Update textX to match the new rectX
        $textX = $rectX + 2.0;

        // Calculate textY based on rectY
        $textY = max($rectY + ($rectHeight - $fontHeight) / 2 - ($fontSize * 0.05), 0.0);

        // Apply rotation if angle is not 0 (only allowed for center position)
        $angle = $watermark->getAngle();
        if ($angle != 0) {
            $pdf->StartTransform();
            // Rotate around the center of the rectangle
            $pdf->Rotate($angle, $rectX + ($rectWidth / 2), $rectY + ($rectHeight / 2));
        }

        // Get background color and opacity
        $bgOpacity = $config->getBackgroundOpacity();

        // Draw background if opacity is greater than 0
        if ($bgOpacity > 0) {
            // Convert hex background color to RGB
            $bgColorHex = $config->getBackgroundColor();
            $r = hexdec(substr($bgColorHex, 0, 2));
            $g = hexdec(substr($bgColorHex, 2, 2));
            $b = hexdec(substr($bgColorHex, 4, 2));

            // Set background color
            $pdf->SetFillColor($r, $g, $b);

            // Set background opacity
            $this->setOpacity($pdf, $bgOpacity);

            // Draw background rectangle - consistent for all positions
            $pdf->Rect(
                $rectX,
                $rectY,
                $rectWidth,
                $rectHeight,
                'F'
            );
        }

        // Set text color and opacity
        $textColor = $config->getTextColor();
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $this->setOpacity($pdf, $config->getTextOpacity());

        // Draw text - centered in the rectangle
        $pdf->Text($textX, $textY, $text);

        // Reset transformation
        if ($watermark->getAngle() != 0) {
            $pdf->StopTransform();
        }
    }

    /**
     * Apply an image watermark to the current page
     *
     * @param Fpdi $pdf The PDF document
     * @param ImageWatermark $watermark The image watermark to apply
     * @param array $size The page size information
     */
    private function applyImageWatermark(Fpdi $pdf, ImageWatermark $watermark, array $size): void
    {
        $config = $watermark->getConfig();
        $imagePath = $config->getImagePath();
        $position = $watermark->getPosition();

        // Get image dimensions
        list($imgWidth, $imgHeight) = getimagesize($imagePath);

        // Calculate appropriate scale based on page size
        $pageWidth = $size['width'];
        $pageHeight = $size['height'];

        // Get scale from config or calculate a default scale
        $scale = ($config->getScale() ?? 1.0) * 0.15;

        // Apply scaling
        $imgWidth *= $scale;
        $imgHeight *= $scale;

        // Check if image exceeds page size and scale down if necessary
        if ($imgWidth > $pageWidth || $imgHeight > $pageHeight) {
            // Calculate scale factors for width and height
            $scaleWidth = $pageWidth / $imgWidth;
            $scaleHeight = $pageHeight / $imgHeight;

            // Use the smaller scale factor to ensure image fits within page
            $scaleFactor = floor(min($scaleWidth, $scaleHeight) * 100) / 100;

            // Apply the scale factor to maintain aspect ratio
            $imgWidth *= $scaleFactor;
            $imgHeight *= $scaleFactor;
        }

        // Calculate position
        list($x, $y) = $this->calculatePosition(
            $position,
            $size,
            $imgWidth,
            $imgHeight
        );

        // Apply rotation if angle is not 0 (only allowed for center position)
        $angle = $watermark->getAngle();
        if ($angle != 0) {
            $pdf->StartTransform();
            $pdf->Rotate($angle, $x + ($imgWidth / 2), $y + ($imgHeight / 2));
        }

        // Set opacity
        $this->setOpacity($pdf, $watermark->getOpacity());

        // Adjust y-coordinate based on position
        // In TCPDF, the y-coordinate for Image is the top-left corner
        switch ($position) {
            case AbstractWatermark::POSITION_MIDDLE_LEFT:
            case AbstractWatermark::POSITION_CENTER:
            case AbstractWatermark::POSITION_MIDDLE_RIGHT:
                // For middle positioning, adjust y to center the image vertically
                $y = $y - ($imgHeight / 2);
                break;
                
            // For top and bottom positions, no adjustment needed as calculatePosition already returns the correct coordinates
        }

        // Add image
        @$pdf->Image($imagePath, $x, $y, $imgWidth, $imgHeight);

        // Reset transformation if rotation was applied
        if ($angle != 0) {
            $pdf->StopTransform();
        }
    }

    /**
     * Set the opacity for the next drawing operation
     *
     * @param Fpdi $pdf The PDF document
     * @param float $opacity The opacity value (0-1)
     */
    private function setOpacity(Fpdi $pdf, float $opacity): void
    {
        // Create opacity ExtGState
        $opacityName = sprintf('Opacity%.2F', $opacity * 100);

        // TCPDF has a method to set alpha which handles the ExtGState internally
        $pdf->setAlpha($opacity, 'Normal');
    }

    /**
     * Calculate the position for a watermark based on its alignment
     *
     * @param string $position Position identifier (e.g., 'center', 'top-left')
     * @param array $pageSize Page dimensions
     * @param float $width Watermark width
     * @param float $height Watermark height
     * @return array [x, y] coordinates
     */
    private function calculatePosition(string $position, array $pageSize, float $width, float $height): array
    {
        $x = 0;
        $y = 0;

        // Handle horizontal position based on the exact position constant
        switch ($position) {
            case AbstractWatermark::POSITION_TOP_LEFT:
            case AbstractWatermark::POSITION_MIDDLE_LEFT:
            case AbstractWatermark::POSITION_BOTTOM_LEFT:
                // Left alignment
                $x = 0.0;
                break;
            
            case AbstractWatermark::POSITION_TOP_RIGHT:
            case AbstractWatermark::POSITION_MIDDLE_RIGHT:
            case AbstractWatermark::POSITION_BOTTOM_RIGHT:
                // Right alignment
                $x = $pageSize['width'] - $width;
                break;
            
            case AbstractWatermark::POSITION_TOP_CENTER:
            case AbstractWatermark::POSITION_CENTER:
            case AbstractWatermark::POSITION_BOTTOM_CENTER:
            default:
                // Center alignment
                $x = ($pageSize['width'] - $width) / 2;
                break;
        }

        // Handle vertical position based on the exact position constant
        switch ($position) {
            case AbstractWatermark::POSITION_TOP_LEFT:
            case AbstractWatermark::POSITION_TOP_CENTER:
            case AbstractWatermark::POSITION_TOP_RIGHT:
                // Top alignment
                $y = 0.45; // Add a small margin for top positions to ensure visibility
                break;
            
            case AbstractWatermark::POSITION_BOTTOM_LEFT:
            case AbstractWatermark::POSITION_BOTTOM_CENTER:
            case AbstractWatermark::POSITION_BOTTOM_RIGHT:
                // Bottom alignment
                $y = $pageSize['height'] - $height;
                break;
            
            case AbstractWatermark::POSITION_MIDDLE_LEFT:
            case AbstractWatermark::POSITION_CENTER:
            case AbstractWatermark::POSITION_MIDDLE_RIGHT:
            default:
                // Middle alignment
                $y = ($pageSize['height'] / 2) - ($height / 2);
                break;
        }

        return [$x, $y];
    }

    /**
     * Check if a page should be watermarked
     *
     * @param array $pages Pages to watermark
     * @param int $pageNo Current page number
     * @return bool True if the page should be watermarked
     */
    private function shouldWatermarkPage(array $pages, int $pageNo): bool
    {
        if (in_array('all', $pages)) {
            return true;
        }

        if (in_array('last', $pages) && $pageNo == $this->totalPages) {
            return true;
        }

        if (in_array((string) $pageNo, $pages)) {
            return true;
        }

        foreach ($pages as $page) {
            if (is_string($page) && strpos($page, '-') !== false) {
                list($start, $end) = explode('-', $page, 2);

                if ($end === 'last') {
                    $end = $this->totalPages;
                }

                if (is_numeric($start) && is_numeric($end)) {
                    $startPage = (int) $start;
                    $endPage = (int) $end;

                    if ($pageNo >= $startPage && $pageNo <= $endPage) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Clean up a temporary directory and all its contents
     *
     * @param string $dir Path to the directory
     */
    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->cleanupTempDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
