<?php

declare(strict_types=1);

namespace PdfWatermark\Watermark\Config;

use PdfWatermark\Watermark\AbstractWatermark;

abstract class AbstractWatermarkConfig implements WatermarkConfigInterface
{
    protected string $position = AbstractWatermark::POSITION_BOTTOM_RIGHT;
    protected float $opacity = 1.0;
    protected float $angle = 0.0;
    protected array $pages = ['all'];

    /**
     * @param string $position One of the POSITION_* constants
     * @return $this
     * @throws \InvalidArgumentException If position is not valid or if angle is not 0 and position is not center
     */
    public function setPosition(string $position): self
    {
        $validPositions = [
            AbstractWatermark::POSITION_TOP_LEFT,
            AbstractWatermark::POSITION_TOP_CENTER,
            AbstractWatermark::POSITION_TOP_RIGHT,
            AbstractWatermark::POSITION_MIDDLE_LEFT,
            AbstractWatermark::POSITION_CENTER,
            AbstractWatermark::POSITION_MIDDLE_RIGHT,
            AbstractWatermark::POSITION_BOTTOM_LEFT,
            AbstractWatermark::POSITION_BOTTOM_CENTER,
            AbstractWatermark::POSITION_BOTTOM_RIGHT,
        ];

        if (!in_array($position, $validPositions)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid position "%s". Valid positions are: %s', $position, implode(', ', $validPositions))
            );
        }

        if ($this->angle != 0 && $position !== AbstractWatermark::POSITION_CENTER) {
            throw new \InvalidArgumentException(
                'Cannot set position other than center when angle is not 0'
            );
        }

        $this->position = $position;
        return $this;
    }

    /**
     * @param float $opacity Value between 0.0 and 1.0
     * @return $this
     */
    public function setOpacity(float $opacity): self
    {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new \InvalidArgumentException('Opacity must be between 0.0 and 1.0');
        }

        $this->opacity = $opacity;
        return $this;
    }

    /**
     * @param float $angle Rotation angle in degrees
     * @return $this
     * @throws \InvalidArgumentException If angle is not 0 and position is not center
     */
    public function setAngle(float $angle): self
    {
        if ($angle != 0 && $this->position !== AbstractWatermark::POSITION_CENTER) {
            throw new \InvalidArgumentException(
                'Rotation angle can only be used with the center position'
            );
        }
        
        $this->angle = $angle;
        return $this;
    }

    /**
     * Set pages to apply the watermark to
     *
     * @param array|string|int $pages Can be:
     *                           - 'all' for all pages
     *                           - 'last' for the last page
     *                           - A specific page number (e.g., 1)
     *                           - A range (e.g., '2-5')
     *                           - A range to the end (e.g., '2-last')
     *                           - A comma-separated list of any of the above (e.g., '1-2,4')
     *                           - An array of any of the above
     * @return $this
     */
    public function setPages(array|string|int $pages): self
    {
        if (is_int($pages)) {
            $pages = [(string)$pages];
        } elseif (is_string($pages)) {
            // Handle comma-separated values
            if (strpos($pages, ',') !== false) {
                $pages = array_map('trim', explode(',', $pages));
            } else {
                $pages = [$pages];
            }
        }

        $this->pages = $pages;
        return $this;
    }

    /**
     * Get the position of the watermark
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Get the opacity of the watermark
     */
    public function getOpacity(): float
    {
        return $this->opacity;
    }

    /**
     * Get the rotation angle of the watermark
     */
    public function getAngle(): float
    {
        return $this->angle;
    }

    /**
     * Get the pages to apply the watermark to
     */
    public function getPages(): array
    {
        return $this->pages;
    }
}
