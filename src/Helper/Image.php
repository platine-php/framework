<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Platine\Framework\Helper;

use GdImage;
use RuntimeException;

/**
 * @class Image
 * @package Platine\Framework\Helper
 */
class Image
{
    /**
     * The current width of the image
     * @var int
     */
    protected int $width = 0;

    /**
     * The current height of the image
     * @var int
     */
    protected int $height = 0;

    /**
     * The image bits information
     * @var int|null
     */
    protected ?int $bits = null;

    /**
     * The image mime type
     * @var string
     */
    protected string $mimetype = '';

    /**
     * The GD image resource instance
     * @var GdImage
     */
    protected GdImage $image;

    /**
     * Create new instance
     * @param string $filePath
     */
    public function __construct(protected string $filePath)
    {
        if (extension_loaded('gd') === false) {
            throw new RuntimeException('PHP GD extension is not installed or enabled');
        }

        // Now load image
        $this->loadImage($filePath);
    }

    /**
     * Resize the current image
     * @param int $width
     * @param int $height
     * @param bool|null $useWidth if "true" will use the width as scale factor, if "false"
     * will use the height if "null" will use the minimum scale between width and height.
     * @return void
     */
    public function resize(int $width = 0, int $height = 0, ?bool $useWidth = null): void
    {
        if ($width <= 0) {
            $width = $this->width;
        }

        if ($height <= 0) {
            $height = $this->height;
        }

        // imagecreatetruecolor need minimum width and height to be >= 1
        if ($width < 1 || $height < 1) {
            return;
        }

        $scale = 1;
        $scaleWidth = (int)($width / $this->width);
        $scaleHeight = (int)($height / $this->height);
        if ($useWidth) {
            $scale = $scaleWidth;
        } elseif ($useWidth === false) {
            $scale = $scaleHeight;
        } else {
            $scale = (int) min($scaleWidth, $scaleHeight);
        }

        if (
            $scale === 1 &&
            $scaleWidth === $scaleHeight &&
            $this->mimetype !== 'image/png'
        ) {
            return;
        }

        $newWidth = (int)($this->width * $scale);
        $newHeight = (int)($this->height * $scale);
        $xpos = (int)(($width - $newWidth) / 2);
        $ypos = (int)(($height - $newHeight) / 2);
        $oldImage = $this->image;
        $this->image = imagecreatetruecolor($width, $height);
        if ($this->mimetype === 'image/png') {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, false);
            $background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
            if ($background === false) {
                throw new RuntimeException('Can not allocate color alpha for PNG image');
            }
            imagecolortransparent($this->image, $background);
        } else {
            $background = imagecolorallocate($this->image, 255, 255, 255);
            if ($background === false) {
                throw new RuntimeException('Can not allocate color for image resize');
            }
        }
        imagefilledrectangle($this->image, 0, 0, $width, $height, $background);
        imagecopyresampled(
            $this->image,
            $oldImage,
            $xpos,
            $ypos,
            0,
            0,
            $newWidth,
            $newHeight,
            $this->width,
            $this->height
        );
        imagedestroy($oldImage);
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Write the watermark in the given image
     * @param Image $watermark
     * @param string $position
     * @return void
     */
    public function watermark(Image $watermark, string $position = 'bottomright'): void
    {
        $watermarkPosX = 0;
        $watermarkPosY = 0;

        $positionMaps = [
            'topleft' => [
                0,
                0
            ],
            'topcenter' => [
                intval(($this->width - $watermark->getWidth()) / 2),
                0
            ],
            'topright' => [
                $this->width - $watermark->getWidth(),
                0
            ],
            'middleleft' => [
                0,
                intval(($this->height - $watermark->getHeight()) / 2)
            ],
            'middlecenter' => [
                intval(($this->width - $watermark->getWidth()) / 2),
                intval(($this->height - $watermark->getHeight()) / 2)
            ],
            'middleright' => [
                $this->width - $watermark->getWidth(),
                intval(($this->height - $watermark->getHeight()) / 2)
            ],
            'bottomleft' => [
                0,
                $this->height - $watermark->getHeight()
            ],
            'bottomcenter' => [
                intval(($this->width - $watermark->getWidth()) / 2),
                $this->height - $watermark->getHeight()
            ],
            'bottomright' => [
                $this->width - $watermark->getWidth(),
                $this->height - $watermark->getHeight()
            ],
        ];

        if (isset($positionMaps[$position])) {
            $watermarkPosX = $positionMaps[$position][0];
            $watermarkPosY = $positionMaps[$position][1];
        }
        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
        imagecopy(
            $this->image,
            $watermark->getImage(),
            $watermarkPosX,
            $watermarkPosY,
            0,
            0,
            $watermark->getWidth(),
            $watermark->getHeight()
        );
        imagedestroy($watermark->getImage());
    }

    /**
     * Crop the current image
     * @param int $topX
     * @param int $topY
     * @param int $bottomX
     * @param int $bottomY
     * @return void
     */
    public function crop(int $topX, int $topY, int $bottomX, int $bottomY): void
    {
        $oldImage = $this->image;
        $width = $bottomY - $topX;
        $height = $bottomY - $topY;

        // imagecreatetruecolor need minimum width and height to be >= 1
        if ($width < 1 || $height < 1) {
            return;
        }

        $this->image = imagecreatetruecolor($width, $height);
        imagecopy(
            $this->image,
            $oldImage,
            0,
            0,
            $topX,
            $topY,
            $this->width,
            $this->height
        );
        imagedestroy($oldImage);
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Rotate the current image
     * @param float $degree
     * @param string $color
     * @return void
     */
    public function rotate(float $degree, string $color = 'ffffff'): void
    {
        [$red, $green, $blue] = self::htmlToRGBColor($color);
        $background = imagecolorallocate($this->image, $red, $green, $blue);
        if ($background === false) {
            throw new RuntimeException('Can not allocate color for image rotation');
        }
        $image = imagerotate($this->image, $degree, $background);
        if ($image === false) {
            throw new RuntimeException('Can not rotate the current image');
        }
        $this->image = $image;
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     * Write text in the current image
     * @param string $text
     * @param int $x
     * @param int $y
     * @param int $size
     * @param string $color
     * @return void
     */
    public function text(
        string $text,
        int $x = 0,
        int $y = 0,
        int $size = 5,
        string $color = '000000'
    ): void {
        [$red, $green, $blue] = self::htmlToRGBColor($color);
        $background = imagecolorallocate($this->image, $red, $green, $blue);
        if ($background === false) {
            throw new RuntimeException('Can not allocate color for image text');
        }
        imagestring($this->image, $size, $x, $y, $text, $background);
    }

    public function merge(
        Image $merge,
        int $x = 0,
        int $y = 0,
        int $opacity = 100
    ): void {
        imagecopymerge(
            $this->image,
            $merge->getImage(),
            $x,
            $y,
            0,
            0,
            $merge->getWidth(),
            $merge->getHeight(),
            $opacity
        );
    }

    /**
     * Apply filter to the current image
     * @param int $filter
     * @param array<mixed>|int|float|bool ...$args
     * @return void
     */
    public function filter(int $filter, array|int|float|bool ...$args): void
    {
        imagefilter($this->image, $filter, $args);
    }

    /**
     * Save the current image into the given path
     * @param string $filePath
     * @param int $quality only used for JPEG image
     * @return void
     */
    public function save(string $filePath, int $quality = 90): void
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (in_array($extension, ['jpeg', 'jpg'])) {
            imagejpeg($this->image, $filePath, $quality);
        } elseif ($extension === 'png') {
            imagepng($this->image, $filePath);
        } elseif ($extension === 'gif') {
            imagegif($this->image, $filePath);
        }

        imagedestroy($this->image);
    }

    /**
     * Return the current width
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Return the current height
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Return the image mime type
     * @return string
     */
    public function getMimetype(): string
    {
        return $this->mimetype;
    }


    /**
     * Return the current image instance
     * @return GdImage
     */
    public function getImage(): GdImage
    {
        return $this->image;
    }

    /**
     * Convert the HTML color to RGB
     * @param string $color
     * @return array{0: int<0, 255>, 1:int<0, 255>, 2:int<0, 255>} the color with each index R, G, B
     */
    public static function htmlToRGBColor(string $color): array
    {
        if (isset($color[0]) && $color[0] === '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) === 6) { // like ff34dq
            [$r, $g, $b] = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5],
            ];
        } elseif (strlen($color) === 3) { // like f4c => ff44cc
            [$r, $g, $b] = [
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2],
            ];
        } else {
            throw new RuntimeException(sprintf('Invalid HTML color [%s] provided', $color));
        }

        $r = (int) hexdec($r);
        if ($r < 0 || $r > 255) {
            $r = 0;
        }

        $g = (int) hexdec($g);
        if ($g < 0 || $g > 255) {
            $g = 0;
        }

        $b = (int) hexdec($b);
        if ($b < 0 || $b > 255) {
            $b = 0;
        }

        return [$r, $g, $b];
    }

    /**
     * Load the image into GD instance
     * @param string $filePath
     * @return void
     */
    protected function loadImage(string $filePath): void
    {
        if (file_exists($filePath) === false) {
            throw new RuntimeException(sprintf(
                'The image file [%s] to load does not exist',
                $filePath
            ));
        }
        $info = getimagesize($filePath);
        if ($info === false) {
            throw new RuntimeException(sprintf(
                'Can not load image file [%s]',
                $filePath
            ));
        }
        $this->width = $info[0];
        $this->height = $info[1];
        $this->bits = $info['bits'] ?? null;
        $this->mimetype = $info['mime'];

        if ($this->mimetype === 'image/gif') {
            $image = imagecreatefromgif($filePath);
        } elseif ($this->mimetype === 'image/png') {
            $image = imagecreatefrompng($filePath);
        } elseif ($this->mimetype === 'image/jpeg' || $this->mimetype === 'image/jpg') {
            $image = imagecreatefromjpeg($filePath);
        } else {
            throw new RuntimeException('The image MIME type cannot be determined');
        }

        if ($image === false) {
            throw new RuntimeException(sprintf('Can not create image using file [%s]', $filePath));
        }
        $this->image = $image;
    }
}
