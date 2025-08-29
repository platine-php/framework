<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Template\Tag\ImageTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class ImageTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $this->expectException(ParseException::class);
        $b = new ImageTag('', $tokens, $parser);
    }

    public function testRenderEmptyFilename(): void
    {
        $this->render(true, false);
    }

    public function testRenderFileNotFound(): void
    {
        $this->render(false, true);
    }

    public function testRenderSuccess(): void
    {
        $this->render(false, false);
    }

    protected function render(bool $emptyFilename, bool $fileNotFound): void
    {
        global $mock_app_to_instance,
               $mock_app_filesystem_object,
               $mock_app_filehelper_object,
               $mock_app_config_items;

        $mock_app_to_instance = true;

        $mock_app_config_items = [
            'platform.data_image_path' => 'myconfig',
        ];

        $file = $this->getMockInstance(File::class, [
            'exists' => $fileNotFound === false,
            'getExtension' => 'png',
            'read' => 'myimagecontent',
        ]);

        $mock_app_filesystem_object = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $mock_app_filehelper_object = $this->getMockInstance(FileHelper::class, [
            'getRootPath' => 'fooroot',
        ]);

        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new ImageTag('filename root:false', $tokens, $parser);

        $c = new Context(['filename' => $emptyFilename ? '' : 'myfile.png']);
        $res = $b->render($c);
        if ($emptyFilename || $fileNotFound) {
            $this->assertEmpty($res);
        } else {
            $this->assertEquals(
                '<img src="data:image/png;base64,bXlpbWFnZWNvbnRlbnQ=" width="50" height="50"/>',
                $res
            );
        }
    }
}
