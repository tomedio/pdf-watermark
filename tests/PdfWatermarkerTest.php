<?php

declare(strict_types=1);

namespace PdfWatermark\Tests;

use PHPUnit\Framework\TestCase;
use PdfWatermark\PdfWatermarker;
use PdfWatermark\PdfWatermarkerFactory;
use PdfWatermark\Watermark\TextWatermark;
use PdfWatermark\Watermark\ImageWatermark;
use PdfWatermark\Watermark\AbstractWatermark;

class PdfWatermarkerTest extends TestCase
{
    private string $testPdfPath;
    private string $outputPdfPath;
    private string $testImagePath;

    protected function setUp(): void
    {
        // Create paths for test files
        $this->testPdfPath = sys_get_temp_dir() . '/test.pdf';
        $this->outputPdfPath = sys_get_temp_dir() . '/output.pdf';
        $this->testImagePath = sys_get_temp_dir() . '/test.png';

        // Create a simple test PDF file if it doesn't exist
        if (!file_exists($this->testPdfPath)) {
            $this->createTestPdf();
        }

        // Create a simple test image if it doesn't exist
        if (!file_exists($this->testImagePath)) {
            $this->createTestImage();
        }
    }

    protected function tearDown(): void
    {
        // Clean up output file
        if (file_exists($this->outputPdfPath)) {
            unlink($this->outputPdfPath);
        }
    }

    public function testTextWatermark(): void
    {
        // Skip test if Ghostscript is not available
        if (!$this->isGhostscriptAvailable()) {
            $this->markTestSkipped('Ghostscript is not available');
        }

        $factory = new PdfWatermarkerFactory(null, sys_get_temp_dir());

        // Create and configure a text watermark config
        $textConfig = $factory->createTextWatermarkConfig('TEST');
        $textConfig
            ->setPosition(AbstractWatermark::POSITION_CENTER)
            ->setAngle(45)
            ->setOpacity(0.5)
            ->setFontSize(72)
            ->setTextColor(255, 0, 0)
            ->setBackgroundOpacity(1.0)
            ->setPages('all');

        $watermarker = $factory->createWithTextWatermark($textConfig);

        $watermarker->apply($this->testPdfPath, $this->outputPdfPath);

        $this->assertFileExists($this->outputPdfPath);
        $this->assertGreaterThan(0, filesize($this->outputPdfPath));
    }

    public function testImageWatermark(): void
    {
        // Skip test if Ghostscript is not available
        if (!$this->isGhostscriptAvailable()) {
            $this->markTestSkipped('Ghostscript is not available');
        }

        $factory = new PdfWatermarkerFactory(null, sys_get_temp_dir());

        // Create and configure an image watermark config
        $imageConfig = $factory->createImageWatermarkConfig($this->testImagePath);
        $imageConfig
            ->setPosition(AbstractWatermark::POSITION_BOTTOM_RIGHT)
            ->setOpacity(0.3)
            ->setScale(0.2)
            ->setPages('all');

        $watermarker = $factory->createWithImageWatermark($imageConfig);

        $watermarker->apply($this->testPdfPath, $this->outputPdfPath);

        $this->assertFileExists($this->outputPdfPath);
        $this->assertGreaterThan(0, filesize($this->outputPdfPath));
    }

    public function testMultipleWatermarks(): void
    {
        // Skip test if Ghostscript is not available
        if (!$this->isGhostscriptAvailable()) {
            $this->markTestSkipped('Ghostscript is not available');
        }

        $factory = new PdfWatermarkerFactory(null, sys_get_temp_dir());
        $watermarker = $factory->create();

        // Create and configure a text watermark
        $textConfig = $factory->createTextWatermarkConfig('TEST');
        $textConfig
            ->setPosition(AbstractWatermark::POSITION_CENTER)
            ->setAngle(45)
            ->setOpacity(0.5)
            ->setFontSize(72)
            ->setTextColor(255, 0, 0)
            ->setPages('all');

        $textWatermark = $factory->createTextWatermark($textConfig);

        // Create and configure an image watermark
        $imageConfig = $factory->createImageWatermarkConfig($this->testImagePath);
        $imageConfig
            ->setPosition(AbstractWatermark::POSITION_BOTTOM_RIGHT)
            ->setOpacity(0.3)
            ->setScale(0.2)
            ->setPages('all');

        $imageWatermark = $factory->createImageWatermark($imageConfig);

        $watermarker->addWatermark($textWatermark);
        $watermarker->addWatermark($imageWatermark);

        $watermarker->apply($this->testPdfPath, $this->outputPdfPath);

        $this->assertFileExists($this->outputPdfPath);
        $this->assertGreaterThan(0, filesize($this->outputPdfPath));
    }

    public function testPageSelection(): void
    {
        // Skip test if Ghostscript is not available
        if (!$this->isGhostscriptAvailable()) {
            $this->markTestSkipped('Ghostscript is not available');
        }

        $factory = new PdfWatermarkerFactory(null, sys_get_temp_dir());
        $watermarker = $factory->create();

        // Create and configure a watermark for the first page
        $firstPageConfig = $factory->createTextWatermarkConfig('FIRST');
        $firstPageConfig
            ->setPosition(AbstractWatermark::POSITION_CENTER)
            ->setAngle(45)
            ->setOpacity(0.5)
            ->setFontSize(72)
            ->setTextColor(255, 0, 0)
            ->setPages(1);

        $firstPageWatermark = $factory->createTextWatermark($firstPageConfig);

        // Create and configure a watermark for the last page
        $lastPageConfig = $factory->createTextWatermarkConfig('LAST');
        $lastPageConfig
            ->setPosition(AbstractWatermark::POSITION_CENTER)
            ->setAngle(45)
            ->setOpacity(0.5)
            ->setFontSize(72)
            ->setTextColor(0, 0, 255)
            ->setPages('last');

        $lastPageWatermark = $factory->createTextWatermark($lastPageConfig);

        $watermarker->addWatermark($firstPageWatermark);
        $watermarker->addWatermark($lastPageWatermark);

        $watermarker->apply($this->testPdfPath, $this->outputPdfPath);

        $this->assertFileExists($this->outputPdfPath);
        $this->assertGreaterThan(0, filesize($this->outputPdfPath));
    }

    /**
     * Test custom temp directory
     */
    public function testCustomTempDirectory(): void
    {
        // Skip test if Ghostscript is not available
        if (!$this->isGhostscriptAvailable()) {
            $this->markTestSkipped('Ghostscript is not available');
        }

        // Create a custom temp directory
        $customTempDir = sys_get_temp_dir() . '/pdf_watermark_test_' . uniqid();
        if (!is_dir($customTempDir)) {
            mkdir($customTempDir, 0755, true);
        }

        try {
            $factory = new PdfWatermarkerFactory(null, $customTempDir);

            // Create and configure a text watermark config
            $textConfig = $factory->createTextWatermarkConfig('TEST');
            $textConfig
                ->setPosition(AbstractWatermark::POSITION_CENTER)
                ->setAngle(45)
                ->setOpacity(0.5)
                ->setFontSize(72)
                ->setTextColor(255, 0, 0)
                ->setPages('all');

            $watermarker = $factory->createWithTextWatermark($textConfig);

            $watermarker->apply($this->testPdfPath, $this->outputPdfPath);

            $this->assertFileExists($this->outputPdfPath);
            $this->assertGreaterThan(0, filesize($this->outputPdfPath));
        } finally {
            // Clean up the custom temp directory
            if (is_dir($customTempDir)) {
                $this->removeDirectory($customTempDir);
            }
        }
    }

    /**
     * Remove a directory and all its contents recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Check if Ghostscript is available
     */
    private function isGhostscriptAvailable(): bool
    {
        exec('which gs', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Create a simple test PDF file using PHP's FPDF library
     * Note: In a real test, you would use FPDF or another library to create a test PDF
     */
    private function createTestPdf(): void
    {
        // This is a minimal PDF file
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n";
        $pdf .= "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n";
        $pdf .= "3 0 obj\n<</Type /Page /Parent 2 0 R ";
        $pdf .= "/MediaBox [0 0 595 842] /Contents 4 0 R>>\nendobj\n";
        $pdf .= "4 0 obj\n<</Length 44>>\nstream\n";
        $pdf .= "BT /F1 12 Tf 100 700 Td (Test PDF File) Tj ET\nendstream\nendobj\n";
        $pdf .= "xref\n0 5\n0000000000 65535 f\n0000000010 00000 n\n";
        $pdf .= "0000000056 00000 n\n0000000111 00000 n\n0000000191 00000 n\n";
        $pdf .= "trailer\n<</Size 5 /Root 1 0 R>>\nstartxref\n284\n%%EOF";

        file_put_contents($this->testPdfPath, $pdf);
    }

    /**
     * Create a simple test image
     */
    private function createTestImage(): void
    {
        $image = imagecreatetruecolor(100, 100);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, 100, 100, $bgColor);
        imagestring($image, 5, 10, 40, 'Test', $textColor);

        imagepng($image, $this->testImagePath);
        imagedestroy($image);
    }
}
