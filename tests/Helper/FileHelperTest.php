<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;
use Platine\Lang\Lang;
use Platine\Upload\Exception\UploadException;
use Platine\Upload\File\UploadFileInfo;

class FileHelperTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstance(Config::class);
        $o = new FileHelper($config, $filesystem, $lang);

        $this->assertInstanceOf(FileHelper::class, $o);
    }

    public function testIsUploadedFailed(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstance(Config::class);
        $o = new FileHelper($config, $filesystem, $lang);

        $this->assertFalse($o->isUploaded('file'));
    }

    public function testIsUploadedSuccess(): void
    {
        $_FILES['file'] = [
            'name' => 'tmp.txt',
            'tmp_name' => 'tmp.txt',
            'type' => 'text/plain',
            'size' => 4677,
            'error' => 0,
        ];
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstance(Config::class);
        $o = new FileHelper($config, $filesystem, $lang);

        $this->assertTrue($o->isUploaded('file'));
    }

    public function testDeleteUploadFile(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'getPath' => 'report_file',
        ]);

        $info = $this->getMockInstance(UploadFileInfo::class);
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $config = $this->getMockInstance(Config::class);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'file');
        $this->expectMethodCallCount($file, 'exists');
        $this->expectMethodCallCount($file, 'delete');
        $o->deleteFile($info);
    }

    public function testExists(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'exists');
        $this->assertFalse($o->exists('my_filename', 'folder'));
    }

    public function testExistImage(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_image_path', null, 'data_image_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'exists');
        $this->assertFalse($o->existImage('my_filename', 'folder'));
    }

    public function testExistPublicImage(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.public_image_path', null, 'public_image_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'exists');
        $this->assertFalse($o->existPublicImage('my_filename', 'folder'));
    }

    public function testDelete(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'delete');
        $this->assertTrue($o->delete('my_filename', 'folder'));
    }

    public function testDeleteFileNotFound(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->assertTrue($o->delete('my_filename', 'folder'));
    }

    public function testDeleteUploadPublicImage(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.public_image_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'delete');
        $this->assertTrue($o->deletePublicImage('my_filename'));
    }

    public function testDeleteUploadImage(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_image_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->expectMethodCallCount($file, 'delete');
        $this->assertTrue($o->deleteImage('my_filename', 'folder'));
    }

    public function testDeleteUploadImageFileNotFound(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_image_path', null, 'attachment_path'],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectMethodCallCount($filesystem, 'get');
        $this->assertTrue($o->deleteImage('my_filename'));
    }

    /**
     *
     * @dataProvider uploadImageDataProvider
     *
     * @param string $path
     * @param bool $isPublic
     * @return void
     */
    public function testUploadImage(string $path, bool $isPublic): void
    {
        $imageContent = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAIAAADYYG7QAAAAAXNSR0IArs4c6QAAAA'
                . 'RnQU1BAACxjwv8YQUAAAAJcEhZcwAAFiUAABYlAUlSJPAAAABDSURBVFhH'
                . '7c4xAQAwDASh+jf9lcCa4VDA2zGFpJAUkkJSSApJISkkhaSQFJJCUkgKSS'
                . 'EpJIWkkBSSQlJICkkhORbaPoBi5ofwSUznAAAAAElFTkSuQmCC'
        );

        $vfsRoot = vfsStream::setup();

        global $mock_realpath_to_value,
                $mock_tempnam_to_value;

        $mock_realpath_to_value = true;

        $uploadPath = $this->createVfsDirectory('upload', $vfsRoot);
        $tmpPath = $this->createVfsDirectory('upload_tmp', $vfsRoot);
        $tmpUploadFile = $this->createVfsFile('tmp.png', $uploadPath, $imageContent);

        $mock_tempnam_to_value = $tmpPath->url();

        $_FILES['file'] = [
            'name' => 'tmp.png',
            'tmp_name' => $tmpUploadFile->url(),
            'type' => 'image/png',
            'size' => $tmpUploadFile->size(),
            'error' => 0,
        ];

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [$path, null, $uploadPath->url()],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        if ($isPublic) {
            $res = $o->uploadPublicImage('file');
        } else {
            $res = $o->uploadImage('file', '');
        }
        $this->assertInstanceOf(UploadFileInfo::class, $res);
        $this->assertEquals(174, $res->getSize());
        $this->assertEquals('png', $res->getExtension());
        $this->assertEquals('image/png', $res->getMimeType());
    }

    public function testUploadAttachment(): void
    {
        $vfsRoot = vfsStream::setup();

        global $mock_realpath_to_value,
                $mock_tempnam_to_value;

        $mock_realpath_to_value = true;

        $uploadPath = $this->createVfsDirectory('upload', $vfsRoot);
        $myfolder = $this->createVfsDirectory('myfolder', $uploadPath);
        $tmpPath = $this->createVfsDirectory('upload_tmp', $vfsRoot);
        $tmpUploadFile = $this->createVfsFile('tmp.txt', $uploadPath, 'foocontent');

        $mock_tempnam_to_value = $tmpPath->url();

        $_FILES['file'] = [
            'name' => 'tmp.txt',
            'tmp_name' => $tmpUploadFile->url(),
            'type' => 'text/plain',
            'size' => $tmpUploadFile->size(),
            'error' => 0,
        ];

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, $uploadPath->url()],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $res = $o->uploadAttachment('file', $myfolder->getName());
        $this->assertInstanceOf(UploadFileInfo::class, $res);
        $this->assertEquals(10, $res->getSize());
        $this->assertEquals('txt', $res->getExtension());
        $this->assertEquals('text/plain', $res->getMimeType());
    }

    public function testUploadAttachmentNoFileUploaded(): void
    {
        $vfsRoot = vfsStream::setup();

        global $mock_realpath_to_value,
                $mock_tempnam_to_value;

        $mock_realpath_to_value = true;

        $uploadPath = $this->createVfsDirectory('upload', $vfsRoot);
        $tmpPath = $this->createVfsDirectory('upload_tmp', $vfsRoot);

        $mock_tempnam_to_value = $tmpPath->url();

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, $uploadPath->url()],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectException(UploadException::class);
        $o->uploadAttachment('file_not_found');
    }

    public function testUploadAttachmentValidationFailed(): void
    {
        $vfsRoot = vfsStream::setup();

        global $mock_realpath_to_value,
                $mock_tempnam_to_value;

        $mock_realpath_to_value = true;

        $uploadPath = $this->createVfsDirectory('upload', $vfsRoot);
        $tmpPath = $this->createVfsDirectory('upload_tmp', $vfsRoot);
        $tmpUploadFile = $this->createVfsFile('tmp.ext', $uploadPath, 'foocontent');

        $mock_tempnam_to_value = $tmpPath->url();

        $_FILES['file'] = [
            'name' => 'tmp.ext',
            'tmp_name' => $tmpUploadFile->url(),
            'type' => 'text/plain',
            'size' => $tmpUploadFile->size(),
            'error' => 0,
        ];

        $lang = $this->getMockInstance(Lang::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => null,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_attachment_path', null, $uploadPath->url()],
            ],
        ]);
        $o = new FileHelper($config, $filesystem, $lang);
        $this->expectException(UploadException::class);
        $o->uploadAttachment('file');
    }

    /**
     * Data provider for "testUploadImage"
     * @return array
     */
    public function uploadImageDataProvider(): array
    {
        return [
            ['platform.data_image_path', false],
            ['platform.public_image_path', true],
        ];
    }
}
