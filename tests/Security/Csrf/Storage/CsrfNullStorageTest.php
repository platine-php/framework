<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Csrf\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\Csrf\Storage\CsrfNullStorage;

/*
 * @group core
 * @group security
 */
class CsrfNullStorageTest extends PlatineTestCase
{
    public function testGetNull(): void
    {
        $o = new CsrfNullStorage();
        
        $this->assertNull($o->get('token'));
    }
    
    public function testAll(): void
    {
        $o = new CsrfNullStorage();
        
        $this->assertNull($o->get('token'));
        
        $o->set('token', [
            'expire' => 100,
            'value' => 'foobar',
        ]);
        
        $res = $o->get('token');
        $this->assertIsArray($res);
        $this->assertCount(2, $res);
        $this->assertArrayHasKey('expire', $res);
        $this->assertArrayHasKey('value', $res);
        $this->assertEquals('foobar', $res['value']);
        $this->assertEquals(100, $res['expire']);
        $o->delete('token');
        
        $this->assertNull($o->get('token'));
        
        $o->clear();
        $this->assertNull($o->get('token'));
    }
    

    
}
