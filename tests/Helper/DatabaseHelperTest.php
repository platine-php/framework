<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\DatabaseHelper;
use Platine\Framework\Tool\Database\DatabaseDump;

class DatabaseHelperTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $dump = $this->getMockInstance(DatabaseDump::class);
        $o = new DatabaseHelper($dump);

        $this->assertInstanceOf(DatabaseHelper::class, $o);
    }

    public function testBackup(): void
    {
        $dumpSetTable = $this->getMockInstance(DatabaseDump::class);
        $dumpCompress = $this->getMockInstance(DatabaseDump::class, [
            'setTables' => $dumpSetTable
        ]);
        $dump = $this->getMockInstance(DatabaseDump::class, [
            'setCompress' => $dumpCompress,
        ]);

        $o = new DatabaseHelper($dump);

        $this->expectMethodCallCount($dump, 'setCompress');
        $this->expectMethodCallCount($dumpSetTable, 'backup');
        $o->backup('filename', true, []);
    }

    public function testRestore(): void
    {
        $dumpCompress = $this->getMockInstance(DatabaseDump::class);
        $dump = $this->getMockInstance(DatabaseDump::class, [
            'setCompress' => $dumpCompress,
        ]);

        $o = new DatabaseHelper($dump);

        $this->expectMethodCallCount($dump, 'setCompress');
        $this->expectMethodCallCount($dumpCompress, 'restore');
        $o->restore('filename', true);
    }
}
