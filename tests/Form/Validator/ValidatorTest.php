<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Form\Validator;

use Platine\Dev\PlatineTestCase;
use Platine\Lang\Lang;
use Platine\Orm\Entity;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyValidator;

/*
 * @group core
 * @group framework
 */
class ValidatorTest extends PlatineTestCase
{
    public function testValidate(): void
    {
        $param = new MyParam([
            'name' => 'foo',
            'status' => '1'
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $o = new MyValidator($param, $lang);

        $this->assertTrue($o->validate());
    }
}
