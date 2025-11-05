<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use GdImage;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Image;
use RuntimeException;

class ImageTest extends PlatineTestCase
{
    public function testConstructGdExtensionNotLoadedOrEnabled(): void
    {
        global $mock_extension_loaded_to_false;
        $mock_extension_loaded_to_false = true;
        $path = $this->getImagePath();
        $this->expectException(RuntimeException::class);
        $o = new Image($path);
    }

    public function testConstructPng(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);
        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
        $this->assertEquals($o->getMimetype(), 'image/png');
        $this->assertInstanceOf(GdImage::class, $o->getImage());

        // Todo
        $o->save($this->getSavePath());
    }

    public function testConstructGif(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);
        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
        $this->assertEquals($o->getMimetype(), 'image/gif');

        // Todo
        $o->save($this->getSavePath('gif'));
    }

    public function testConstructJpg(): void
    {
        $path = $this->getImagePath('jpg');
        $o = new Image($path);
        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
        $this->assertEquals($o->getMimetype(), 'image/jpeg');

        // Todo
        $o->save($this->getSavePath('jpg'));
    }

    public function testConstructImageFileNotFound(): void
    {
        $path = sprintf('%d-not-found-platine', time());
        $this->expectException(RuntimeException::class);
        $o = new Image($path);
    }

    public function testConstructGetimagesizeError(): void
    {
        global $mock_getimagesize_to_false;
        $mock_getimagesize_to_false = true;

        $path = $this->getImagePath();
        $this->expectException(RuntimeException::class);
        $o = new Image($path);
    }

    public function testConstructGetimagesizeMimetypeInvalid(): void
    {
        global $mock_getimagesize_to_value;
        $mock_getimagesize_to_value = [100, 100, 'mime' => 'platine/mime'];

        $path = $this->getImagePath();
        $this->expectException(RuntimeException::class);
        $o = new Image($path);
    }

    public function testConstructCreateImageFailed(): void
    {
        global $mock_imagecreatefrompng_to_false;
        $mock_imagecreatefrompng_to_false = true;

        $path = $this->getImagePath();
        $this->expectException(RuntimeException::class);
        $o = new Image($path);
    }

    public function testResizeWidthOrHeightLessThan1(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);
        $this->setPropertyValue(Image::class, $o, 'width', 0);
        $o->resize(0, 0);
        $this->assertEquals($o->getWidth(), 0);
        $this->assertEquals($o->getHeight(), 48);
        $this->assertEquals($o->getMimetype(), 'image/png');
        $this->assertInstanceOf(GdImage::class, $o->getImage());
    }

    public function testResizeComputedScaleIs1(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);
        $o->resize(48, 48);
        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testResize(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);
        $o->resize(10, 10);
        $this->assertEquals($o->getWidth(), 10);
        $this->assertEquals($o->getHeight(), 10);
        $this->assertEquals($o->getMimetype(), 'image/png');
        $this->assertInstanceOf(GdImage::class, $o->getImage());
    }

    public function testResizeUsingWidthScale(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);
        $o->resize(10, 10, true);
        $this->assertEquals($o->getWidth(), 10);
        $this->assertEquals($o->getHeight(), 10);
        $this->assertEquals($o->getMimetype(), 'image/png');
        $this->assertInstanceOf(GdImage::class, $o->getImage());
    }

    public function testResizeUsingHeightScale(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);
        $o->resize(10, 10, false);
        $this->assertEquals($o->getWidth(), 10);
        $this->assertEquals($o->getHeight(), 10);
        $this->assertEquals($o->getMimetype(), 'image/png');
        $this->assertInstanceOf(GdImage::class, $o->getImage());
    }

    public function testResizeColorAllocateAlphaPngFailed(): void
    {
        global $mock_imagecolorallocatealpha_to_false;
        $mock_imagecolorallocatealpha_to_false = true;
        $path = $this->getImagePath('png');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->resize(40, 40);
    }

    public function testResizeColorAllocateNotPngFailed(): void
    {
        global $mock_imagecolorallocate_to_false;
        $mock_imagecolorallocate_to_false = true;
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->resize(40, 40);
    }

    public function testWatermark(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);

        $w = clone $o;
        $o->watermark($w);
        $this->assertEquals($w->getWidth(), 48);
        $this->assertEquals($w->getHeight(), 48);
        $this->assertEquals($w->getMimetype(), 'image/png');
    }

    public function testCrop(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);

        $o->crop(0, 10, 40, 35);
        $this->assertEquals($o->getWidth(), 35);
        $this->assertEquals($o->getHeight(), 25);
    }

    public function testCropWidthOrHeightLess1(): void
    {
        $path = $this->getImagePath();
        $o = new Image($path);

        $o->crop(0, 1, 1, 1);
        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testRotateColorAllocateFailed(): void
    {
        global $mock_imagecolorallocate_to_false;
        $mock_imagecolorallocate_to_false = true;
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->rotate(45);
    }

    public function testRotateFailed(): void
    {
        global $mock_imagerotate_to_false;
        $mock_imagerotate_to_false = true;
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->rotate(45);
    }

    public function testRotateSuccess(): void
    {
        $path = $this->getImagePath('png');
        $o = new Image($path);

        $o->rotate(15);

        $this->assertEquals($o->getWidth(), 59);
        $this->assertEquals($o->getHeight(), 59);
    }

    public function testTextColorAllocateFailed(): void
    {
        global $mock_imagecolorallocate_to_false;
        $mock_imagecolorallocate_to_false = true;
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->text('Hello');
    }

    public function testTextSuccess(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $o->text('Hello');

        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testTextHexdecDefaultToZero(): void
    {
        global $mock_hexdec_to_value;
        $mock_hexdec_to_value = 256;

        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $o->text('Hello');

        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testTextRgbWithHash(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $o->text('Hello', 0, 0, 5, '#fff');

        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testTextInValidColorFormat(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);

        $this->expectException(RuntimeException::class);
        $o->text('Hello', 0, 0, 5, '#fffd');
    }

    public function testMerge(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);
        $m = clone $o;
        $o->merge($m);

        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    public function testFilter(): void
    {
        $path = $this->getImagePath('gif');
        $o = new Image($path);
        $o->filter(IMG_FILTER_GRAYSCALE);



        $this->assertEquals($o->getWidth(), 48);
        $this->assertEquals($o->getHeight(), 48);
    }

    private function getImagePath(string $type = 'png'): string
    {
        if ($type === 'png') {
            $imageContent = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAIAAADYYG7QAAAAAXNSR0IArs4c6QAAAA'
                    . 'RnQU1BAACxjwv8YQUAAAAJcEhZcwAAFiUAABYlAUlSJPAAAABDSURBVFhH'
                    . '7c4xAQAwDASh+jf9lcCa4VDA2zGFpJAUkkJSSApJISkkhaSQFJJCUkgKSS'
                    . 'EpJIWkkBSSQlJICkkhORbaPoBi5ofwSUznAAAAAElFTkSuQmCC'
            );
        } elseif ($type === 'gif') {
            $imageContent = base64_decode(
                'R0lGODlhMAAwAHAAACH5BAEAAPwALAAAAAAwADAAhwAAAAAAMwAAZgAAmQAAzAAA/'
                    . 'wArAAArMwArZgArmQArzAAr/wBVAABVMwBVZgBVmQBVzABV/wCAAACAMw'
                    . 'CAZgCAmQCAzACA/wCqAACqMwCqZgCqmQCqzACq/wDVAADVMwDVZgDVm'
                    . 'QDVzADV/wD/AAD/MwD/ZgD/mQD/zAD//zMAADMAMzMAZjMAmTMAzDMA'
                    . '/zMrADMrMzMrZjMrmTMrzDMr/zNVADNVMzNVZjNVmTNVzDNV/zOAAD'
                    . 'OAMzOAZjOAmTOAzDOA/zOqADOqMzOqZjOqmTOqzDOq/zPVADPVMzPV'
                    . 'ZjPVmTPVzDPV/zP/ADP/MzP/ZjP/mTP/zDP//2YAAGYAM2YAZmYAmWY'
                    . 'AzGYA/2YrAGYrM2YrZmYrmWYrzGYr/2ZVAGZVM2ZVZmZVmWZVzGZV/2'
                    . 'aAAGaAM2aAZmaAmWaAzGaA/2aqAGaqM2aqZmaqmWaqzGaq/2bVAGbV'
                    . 'M2bVZmbVmWbVzGbV/2b/AGb/M2b/Zmb/mWb/zGb//5kAAJkAM5kAZp'
                    . 'kAmZkAzJkA/5krAJkrM5krZpkrmZkrzJkr/5lVAJlVM5lVZplVmZlVz'
                    . 'JlV/5mAAJmAM5mAZpmAmZmAzJmA/5mqAJmqM5mqZpmqmZmqzJmq/5nV'
                    . 'AJnVM5nVZpnVmZnVzJnV/5n/AJn/M5n/Zpn/mZn/zJn//8wAAMwAM8w'
                    . 'AZswAmcwAzMwA/8wrAMwrM8wrZswrmcwrzMwr/8xVAMxVM8xVZsxVm'
                    . 'cxVzMxV/8yAAMyAM8yAZsyAmcyAzMyA/8yqAMyqM8yqZsyqmcyqzMy'
                    . 'q/8zVAMzVM8zVZszVmczVzMzV/8z/AMz/M8z/Zsz/mcz/zMz///8A'
                    . 'AP8AM/8AZv8Amf8AzP8A//8rAP8rM/8rZv8rmf8rzP8r//9VAP9VM/9'
                    . 'VZv9Vmf9VzP9V//+AAP+AM/+AZv+Amf+AzP+A//+qAP+qM/+qZv+qmf'
                    . '+qzP+q///VAP/VM//VZv/Vmf/VzP/V////AP//M///Zv//mf//zP///'
                    . 'wAAAAAAAAAAAAAAAAhPAPcJHEiwoMGDCBMqXMiwocOHECNKnEixosWLG'
                    . 'DNq3Mixo8ePIEOKHEmypMmTKFOqXMmypcuXMGPKnEmzps2bOHPq3Mmzp'
                    . '8+fQIMKHaoxIAA7'
            );
        } else {
             $imageContent = base64_decode(
                 '/9j/4AAQSkZJRgABAQEAkACQAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwY'
                     . 'EBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDA'
                     . 'z/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDA'
                     . 'wMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAAwADADASIAA'
                     . 'hEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAA'
                     . 'AAAAAAAAAAAA/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAYJ/8QAFBEBAA'
                     . 'AAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AlWAj2hAAAAAAAAAAA'
                     . 'AAAAAD/2Q=='
             );
        }

        $vfsRoot = vfsStream::setup();
        $tmpPath = $this->createVfsDirectory('image_dir', $vfsRoot);
        $imageFile = $this->createVfsFile('tmp.png', $tmpPath, $imageContent);

        return $imageFile->url();
    }

    private function getSavePath(string $ext = 'png'): string
    {
        $vfsRoot = vfsStream::setup();
        $tmpPath = $this->createVfsDirectory('image_save_dir', $vfsRoot);

        return sprintf('%s/savefile.%s', $tmpPath->url(), $ext);
    }
}
