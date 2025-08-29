<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Tool\Database;

use Platine\Database\Connection;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Tool\Database\NullDumpDriver;

/*
 * @group core
 * @group framework
 */
class NullDumpDriverTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $cnx = $this->getMockInstance(Connection::class);
        $o = new NullDumpDriver($cnx);

        $this->assertInstanceOf(NullDumpDriver::class, $o);
        $this->assertEmpty($o->startBackup('foodb', [], []));
        $this->assertEmpty($o->endBackup('foodb'));
        $this->assertEmpty($o->dumpTable('footable', 1, false));

        // Fake
        $o->restore('filename', '');
    }
}
