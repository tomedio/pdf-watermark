<?php

declare(strict_types=1);

namespace PdfWatermark;

use PdfWatermark\Watermark\AbstractWatermark;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PdfWatermarker
{
    private string $ghostscriptPath;
    private array $watermarks = [];
    private array $pageInfo = [];
    private int $totalPages = 0;

    /**
     * Constructor
     * 
     * @param string|null $ghostscriptPath Path to the Ghostscript executable (defaults to 'gs')
     */
    public function __construct(?string $ghostscriptPath = null)
    {
        $this->ghostscriptPath = $ghostscriptPath ?? 'gs';
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
     * @throws \RuntimeException If Ghostscript fails
     */
    public function apply(string $inputFile, string $outputFile): void
    {
        if (!file_exists($inputFile)) {
            throw new \InvalidArgumentException(sprintf('Input file "%s" does not exist', $inputFile));
        }

        if (empty($this->watermarks)) {
            throw new \InvalidArgumentException('No watermarks have been added');
        }

        // Extract page information from the PDF
        $this->extractPageInfo($inputFile);

        // Create a temporary PostScript file for the watermarks
        $tempFile = $this->createTempFile();
        $this->generateWatermarkPostScript($tempFile);

        // Apply the watermarks using Ghostscript
        $this->runGhostscript($inputFile, $outputFile, $tempFile);

        // Clean up
        unlink($tempFile);
    }

    /**
     * Extract page information from the PDF
     * 
     * @param string $inputFile Path to the input PDF file
     * @throws \RuntimeException If Ghostscript fails
     */
    private function extractPageInfo(string $inputFile): void
    {
        $this->pageInfo = [];
        $this->totalPages = 0;

        // Use Ghostscript to extract page information
        $process = new Process([
            $this->ghostscriptPath,
            '-q',
            '-dNODISPLAY',
            '-dNOSAFER',
            '-dBATCH',
            '-dNOPAUSE',
            '-c',
            "(" . $inputFile . ") (r) file runpdfbegin pdfpagecount = quit",
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->totalPages = (int) trim($process->getOutput());

        // Extract page sizes
        for ($i = 1; $i <= $this->totalPages; $i++) {
            $process = new Process([
                $this->ghostscriptPath,
                '-q',
                '-dNODISPLAY',
                '-dNOSAFER',
                '-dBATCH',
                '-dNOPAUSE',
                '-c',
                "(" . $inputFile . ") (r) file runpdfbegin " . $i . " pdfgetpage /MediaBox get == quit",
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = trim($process->getOutput());
            preg_match('/\[(.*?)\]/', $output, $matches);

            if (isset($matches[1])) {
                $dimensions = explode(' ', $matches[1]);
                $this->pageInfo[$i] = [
                    'width' => (float) ($dimensions[2] ?? 0),
                    'height' => (float) ($dimensions[3] ?? 0),
                    'orientation' => isset($dimensions[2], $dimensions[3]) && $dimensions[2] > $dimensions[3] ? 'landscape' : 'portrait',
                ];
            } else {
                // Default to A4 if we can't extract the dimensions
                $this->pageInfo[$i] = [
                    'width' => 595.0,
                    'height' => 842.0,
                    'orientation' => 'portrait',
                ];
            }
        }
    }

    /**
     * Generate PostScript code for the watermarks
     * 
     * @param string $outputFile Path to the output PostScript file
     */
    private function generateWatermarkPostScript(string $outputFile): void
    {
        $ps = [];
        $ps[] = "%!PS-Adobe-3.0";
        $ps[] = "% Watermark PostScript file";
        $ps[] = "";
        $ps[] = "% Define watermark procedure";
        $ps[] = "/WatermarkPage {";
        $ps[] = "  /PageNo exch def";
        $ps[] = "  /PageDict exch def";
        $ps[] = "";

        // Add watermarks
        foreach ($this->watermarks as $index => $watermark) {
            $ps[] = "  % Watermark " . ($index + 1);
            $ps[] = "  PageNo PageDict " . $this->getWatermarkProcName($index) . " exec";
        }

        $ps[] = "} def";
        $ps[] = "";

        // Define watermark procedures
        foreach ($this->watermarks as $index => $watermark) {
            $ps[] = "% Watermark " . ($index + 1) . " procedure";
            $ps[] = "/" . $this->getWatermarkProcName($index) . " {";
            $ps[] = "  /PageDict exch def";
            $ps[] = "  /PageNo exch def";
            $ps[] = "";
            $ps[] = "  % Check if this page should be watermarked";
            $ps[] = "  " . $this->generatePageCheckCode($watermark->getPages());
            $ps[] = "  {";
            
            // Generate watermark code for each page size/orientation
            foreach ($this->pageInfo as $pageNo => $info) {
                $ps[] = "    % Page " . $pageNo . " (" . $info['orientation'] . ")";
                $ps[] = "    PageNo " . $pageNo . " eq {";
                $ps[] = "      " . str_replace("\n", "\n      ", $watermark->generatePostScript($info));
                $ps[] = "    } if";
            }
            
            $ps[] = "  } if";
            $ps[] = "} def";
            $ps[] = "";
        }

        // Write to file
        file_put_contents($outputFile, implode("\n", $ps));
    }

    /**
     * Generate PostScript code to check if a page should be watermarked
     * 
     * @param array $pages Pages to watermark
     * @return string PostScript code
     */
    private function generatePageCheckCode(array $pages): string
    {
        $conditions = [];

        foreach ($pages as $page) {
            if ($page === 'all') {
                return 'true';
            } elseif ($page === 'last') {
                $conditions[] = "PageNo " . $this->totalPages . " eq";
            } elseif (is_numeric($page)) {
                $conditions[] = "PageNo " . (int) $page . " eq";
            } elseif (is_string($page) && strpos($page, '-') !== false) {
                list($start, $end) = explode('-', $page, 2);
                
                if ($end === 'last') {
                    $end = $this->totalPages;
                }
                
                if (is_numeric($start) && is_numeric($end)) {
                    $conditions[] = "PageNo " . (int) $start . " ge PageNo " . (int) $end . " le and";
                }
            }
        }

        return empty($conditions) ? 'false' : implode(" ", $conditions);
    }

    /**
     * Run Ghostscript to apply the watermarks
     * 
     * @param string $inputFile Path to the input PDF file
     * @param string $outputFile Path to the output PDF file
     * @param string $watermarkFile Path to the watermark PostScript file
     * @throws \RuntimeException If Ghostscript fails
     */
    private function runGhostscript(string $inputFile, string $outputFile, string $watermarkFile): void
    {
        $process = new Process([
            $this->ghostscriptPath,
            '-q',
            '-dBATCH',
            '-dNOPAUSE',
            '-sDEVICE=pdfwrite',
            '-dPDFSETTINGS=/prepress',
            '-dCompatibilityLevel=1.4',
            '-dAutoRotatePages=/None',
            '-sOutputFile=' . $outputFile,
            '-f',
            $watermarkFile,
            $inputFile,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Create a temporary file
     * 
     * @return string Path to the temporary file
     */
    private function createTempFile(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'watermark');
        
        if ($tempFile === false) {
            throw new \RuntimeException('Failed to create temporary file');
        }
        
        return $tempFile;
    }

    /**
     * Get a unique procedure name for a watermark
     * 
     * @param int $index Watermark index
     * @return string Procedure name
     */
    private function getWatermarkProcName(int $index): string
    {
        return 'Watermark' . ($index + 1);
    }
}
