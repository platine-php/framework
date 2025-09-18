<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Tool\Database;

use Exception;
use Platine\Database\Connection;
use Platine\Database\Driver\Driver;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Tool\Database\DatabaseDump;
use Platine\Framework\Tool\Database\MySQLDump;
use Platine\Stdlib\Helper\Str;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class MySQLDumpTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $cnx = $this->getMockInstance(Connection::class);
        $o = new MySQLDump($cnx);

        $this->assertInstanceOf(MySQLDump::class, $o);
    }

    public function testStartBackupEmptyTables(): void
    {
        $cnx = $this->getMockInstance(Connection::class);
        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'exec');
        $res = $o->startBackup('foodb', [], []);
        $this->assertEmpty($res);
    }

    public function testStartBackup(): void
    {
        $cnx = $this->getMockInstance(Connection::class);
        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'exec', 2);
        $res = $o->startBackup('foodb', ['foo'], []);
        $this->assertNotEmpty($res);
    }

    public function testEndBackup(): void
    {
        $cnx = $this->getMockInstance(Connection::class);
        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'exec', 1);
        $res = $o->endBackup('foodb');
        $this->assertNotEmpty($res);
    }

    public function testDumpTableNoData(): void
    {
        $rsMiddle = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'Create Table' => 'Create Table footable',
            ],
        ]);
        $rs = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $rsMiddle,
        ]);
        $cnx = $this->getMockInstance(Connection::class, [
            'query' => $rs,
        ]);
        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'query', 1);
        $res = $o->dumpTable('footable', DatabaseDump::CREATE | DatabaseDump::DROP, false);
        $this->assertNotEmpty($res);
    }

    public function testDumpTableWithData(): void
    {
        $rsMiddle1 = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'Create Table' => 'Create Table footable',
            ],
        ]);
        $rs1 = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $rsMiddle1,
        ]);

        $rsMiddle2 = $this->getMockInstance(ResultSet::class, [
            'all' => [
                [
                    'Field' => 'id',
                    'Type' => 'int',
                ],
                [
                    'Field' => 'name',
                    'Type' => 'varchar',
                ]
            ],
        ]);
        $rs2 = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $rsMiddle2,
        ]);

        $rsMiddle3 = $this->getMockInstance(ResultSet::class, [
            'all' => [
                [
                    'id' => 1,
                    'name' => Str::repeat('a', (int) 1e6),
                ],
                [
                    'id' => 2,
                    'name' => null,
                ],
                [
                    'id' => 3,
                    'name' => 'Foo',
                ],
            ],
        ]);
        $rs3 = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $rsMiddle3,
        ]);

        $driver = $this->getMockInstance(Driver::class, [], ['quote']);
        $cnx = $this->getMockInstance(Connection::class, [
            'getDriver' => $driver,
        ]);

        $cnx->expects($this->any())
             ->method('query')
             ->willReturnOnConsecutiveCalls(...[$rs1, $rs2, $rs3]);

        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'query', 3);
        $res = $o->dumpTable('footable', DatabaseDump::ALL, false);
        $this->assertNotEmpty($res);
    }

    public function testRestore(): void
    {
        $cnx = $this->getMockInstance(Connection::class, [
        ]);

        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'exec', 2);
        $fn = fn($count, $percent) => $count . '|' . $percent;

        $content = "DELIMITER ;\n-- Foo comment\n SELECT * FROM footable;\nSELECT * FROM\n footable1;";
        $o->restore('filename', $content, $fn);
    }

    public function testRestoreError(): void
    {
        $cnx = $this->getMockInstance(Connection::class, [
        ]);

        $cnx->expects($this->any())
             ->method('exec')
             ->willThrowException(new Exception());

        $o = new MySQLDump($cnx);

        $this->expectMethodCallCount($cnx, 'exec', 1);
        $fn = fn($count, $percent) => $count . '|' . $percent;

        $content = "DELIMITER ;\n-- Foo comment\n SELECT * FROM footable;\nSELECT * FROM\n footable1;";
        $this->expectException(RuntimeException::class);
        $o->restore('filename', $content, $fn);
    }
}
