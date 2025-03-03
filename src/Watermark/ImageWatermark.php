<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark;

use PdfWatermark\Watermark\Config\ImageWatermarkConfig;

class ImageWatermark extends AbstractWatermark
{
    /**
     * Constructor
     * 
     * @param ImageWatermarkConfig $config Image watermark configuration
     */
    public function __construct(ImageWatermarkConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get the image watermark configuration
     */
    public function getConfig(): ImageWatermarkConfig
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePostScript(array $pageInfo): string
    {
        $pageWidth = $pageInfo['width'];
        $pageHeight = $pageInfo['height'];
        
        $config = $this->getConfig();
        $imagePath = $config->getImagePath();
        
        // Get image dimensions
        list($originalWidth, $originalHeight, $imageType) = getimagesize($imagePath);
        
        // Calculate final dimensions
        list($finalWidth, $finalHeight) = $this->calculateFinalDimensions($originalWidth, $originalHeight);
        
        // Calculate position
        list($x, $y) = $this->calculatePosition($pageWidth, $pageHeight, $finalWidth, $finalHeight);
        
        // Generate PostScript code
        $ps = [];
        $ps[] = "% Image watermark";
        $ps[] = "gsave";
        
        // Set position and apply rotation
        $ps[] = sprintf("%.2f %.2f translate", $x, $y);
        if ($config->getAngle() != 0) {
            $ps[] = sprintf("%.2f rotate", $config->getAngle());
        }
        
        // Set opacity
        if ($config->getOpacity() < 1.0) {
            $ps[] = sprintf("%.2f .setopacityalpha", $config->getOpacity());
        }
        
        // Include the image
        $ps[] = sprintf("%.2f %.2f scale", $finalWidth, $finalHeight);
        $ps[] = "1 dict begin";
        $ps[] = "/img { <</ImageType 1 /Width $originalWidth /Height $originalHeight /BitsPerComponent 8";
        
        // Different handling based on image type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $ps[] = "/Decode [0 1 0 1 0 1] /DataSource currentfile /DCTDecode filter /ImageMatrix [1 0 0 -1 0 $originalHeight]>> image } def";
                break;
            case IMAGETYPE_PNG:
                $ps[] = "/Decode [0 1 0 1 0 1 0 1] /DataSource currentfile /FlateDecode filter /ImageMatrix [1 0 0 -1 0 $originalHeight]>> image } def";
                break;
            default:
                throw new \RuntimeException('Unsupported image type. Only JPEG and PNG are supported.');
        }
        
        // This is a placeholder - in a real implementation, we would embed the image data here
        // For simplicity, we're just indicating where it would go
        $ps[] = "% Image data would be embedded here";
        $ps[] = "img";
        $ps[] = "end";
        
        $ps[] = "grestore";
        
        return implode("\n", $ps);
    }
    
    /**
     * Calculate the final dimensions of the image
     * 
     * @param int $originalWidth Original width of the image
     * @param int $originalHeight Original height of the image
     * @return array [width, height]
     */
    private function calculateFinalDimensions(int $originalWidth, int $originalHeight): array
    {
        $aspectRatio = $originalWidth / $originalHeight;
        $config = $this->getConfig();
        
        if ($config->getScale() !== null) {
            return [
                $originalWidth * $config->getScale(),
                $originalHeight * $config->getScale()
            ];
        } elseif ($config->getWidth() !== null) {
            return [
                $config->getWidth(),
                $config->getWidth() / $aspectRatio
            ];
        } elseif ($config->getHeight() !== null) {
            return [
                $config->getHeight() * $aspectRatio,
                $config->getHeight()
            ];
        } else {
            // Default scale is 1.0
            return [$originalWidth, $originalHeight];
        }
    }
    
    /**
     * Calculate the position of the watermark based on the selected position constant
     * 
     * @param float $pageWidth Width of the page
     * @param float $pageHeight Height of the page
     * @param float $imageWidth Width of the image
     * @param float $imageHeight Height of the image
     * @return array [x, y] coordinates
     */
    private function calculatePosition(float $pageWidth, float $pageHeight, float $imageWidth, float $imageHeight): array
    {
        $margin = 20; // Margin from the edge of the page
        $position = $this->getPosition();
        
        switch ($position) {
            case self::POSITION_TOP_LEFT:
                return [$margin, $pageHeight - $margin - $imageHeight];
                
            case self::POSITION_TOP_CENTER:
                return [($pageWidth - $imageWidth) / 2, $pageHeight - $margin - $imageHeight];
                
            case self::POSITION_TOP_RIGHT:
                return [$pageWidth - $margin - $imageWidth, $pageHeight - $margin - $imageHeight];
                
            case self::POSITION_MIDDLE_LEFT:
                return [$margin, ($pageHeight - $imageHeight) / 2];
                
            case self::POSITION_CENTER:
                return [($pageWidth - $imageWidth) / 2, ($pageHeight - $imageHeight) / 2];
                
            case self::POSITION_MIDDLE_RIGHT:
                return [$pageWidth - $margin - $imageWidth, ($pageHeight - $imageHeight) / 2];
                
            case self::POSITION_BOTTOM_LEFT:
                return [$margin, $margin];
                
            case self::POSITION_BOTTOM_CENTER:
                return [($pageWidth - $imageWidth) / 2, $margin];
                
            case self::POSITION_BOTTOM_RIGHT:
            default:
                return [$pageWidth - $margin - $imageWidth, $margin];
        }
    }
}
