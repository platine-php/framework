<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\CsrfTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class CsrfTagTest extends PlatineTestCase
{
    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_config_items,
               $mock_sha1_foo;

        $mock_sha1_foo = true;
        $mock_app_to_instance = true;

        $mock_app_config_items = [
            'security.csrf' => ['expire' => 400, 'key' => 'csrf'],
            'security.csrf.key' => 'csrf',
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = ['tnh', '{% endcapture %}'];
        $o = new CsrfTag('myname', $tokens, $parser);

        $this->assertEquals('<input type = "hidden" name = "csrf" value = "foo" />', $o->render($context));
    }
}
