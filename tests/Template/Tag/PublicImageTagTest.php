<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Template\Tag\PublicImageTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class PublicImageTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $this->expectException(ParseException::class);
        $b = new PublicImageTag('', $tokens, $parser);
    }


    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_filesystem_object,
               $mock_app_filehelper_object,
               $mock_app_config_items;

        $mock_app_to_instance = true;

        $mock_app_config_items = [
            'platform.public_image_path' => 'myconfig',
        ];

        $file = $this->getMockInstance(File::class, [
            'exists' => true,
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
        $b = new PublicImageTag('filename root:false width:199', $tokens, $parser);

        $c = new Context(['filename' => 'myfile.png']);
        $res = $b->render($c);
        $this->assertEquals(
            '<img src="fooroot/myfile.png" width="199" height="50"/>',
            $res
        );
    }
}
