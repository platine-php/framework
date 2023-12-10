<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Storage\ApcuStorage;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class ApcuStorageTest extends PlatineTestCase
{
    public function testConstructorExtensionIsNotLoaded(): void
    {
        global $mock_extension_loaded_to_false;

        $mock_extension_loaded_to_false = true;
        $this->expectException(RuntimeException::class);

        (new ApcuStorage());
    }

    public function testConstructorExtensionIstLoadedButNotEnabled(): void
    {
        global $mock_extension_loaded_to_true, $mock_ini_get_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_false = true;

        $this->expectException(RuntimeException::class);

        (new ApcuStorage());
    }

    public function testGet(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_fetch_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;

        $ac = new ApcuStorage();

        $mock_apcu_fetch_to_false = true;
        //Default value
        $this->assertEquals(0.0, $ac->get('not_found_key', 'bar'));

        $mock_apcu_fetch_to_false = false;
        //Return correct data
        $key = uniqid();

        $content = $ac->get($key);
        $this->assertEquals(6, $content);
    }

    public function testSetSimple(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_true;

        $key = uniqid();

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_true = true;


        $ac = new ApcuStorage();
        $result = $ac->set($key, 15, 100);
        $this->assertTrue($result);
    }

    public function testSetFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_false = true;

        $ac = new ApcuStorage();
        $result = $ac->set('key', 100, 100);
        $this->assertFalse($result);
    }

    public function testDeleteSuccess(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_delete_to_true;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_delete_to_true = true;

        $key = uniqid();

        $ac = new ApcuStorage();

        $this->assertTrue($ac->delete($key));
        $this->assertFalse($ac->exists($key));
    }

    public function testDeleteFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_delete_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_delete_to_false = true;

        $key = uniqid();

        $ac = new ApcuStorage();

        $this->assertFalse($ac->delete($key));
    }
}
