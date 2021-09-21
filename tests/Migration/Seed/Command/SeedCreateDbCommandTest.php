<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Database\Query\Query;
use Platine\Database\QueryBuilder;
use Platine\Database\ResultSet;
use Platine\Database\Schema;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\Command\SeedCreateDbCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class SeedCreateDbCommandTest extends BaseCommandTestCase
{

    public function testExecuteTableNotExists(): void
    {
        $seedDir = $this->createVfsDirectory('seed', $this->vfsPath);
        $seedPath = $seedDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $schema =  $this->getMockInstance(Schema::class, [
            'hasTable' => false
        ]);
        $queryBuilder =  $this->getMockInstance(QueryBuilder::class);
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user seed',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedCreateDbCommand($application, $config, $filesystem, $schema, $queryBuilder);
        $o->bind($app);
        $o->parse(['platine', 'mytable']);
        $this->assertEquals('seed:createdb', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $seedFilename = 'AddUserSeed.php';
        $seedFile = $seedPath . '/' . $seedFilename;
        $expected = 'SEED GENERATION USING EXISTING DATA

Seed detail: 
Name: add user seed
Table : mytable
Class name: AddUserSeed
Filename: AddUserSeed.php
Path: ' . $seedFile . '

Database table [mytable] does not exist';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteTableExistsButEmpty(): void
    {
        $seedDir = $this->createVfsDirectory('seed', $this->vfsPath);
        $seedPath = $seedDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $schema =  $this->getMockInstance(Schema::class, [
            'hasTable' => true
        ]);

        $resulSet = $this->getMockInstance(ResultSet::class, [
            'all' => []
        ]);
        $resulSet->expects($this->any())
                ->method('fetchAssoc')
                ->will($this->returnSelf());

        $from = $this->getMockInstance(Query::class, [
            'select' => $resulSet
        ]);
        $queryBuilder =  $this->getMockInstance(QueryBuilder::class, [
            'from' => $from
        ]);
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user seed',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedCreateDbCommand($application, $config, $filesystem, $schema, $queryBuilder);
        $o->bind($app);
        $o->parse(['platine', 'mytable']);
        $this->assertEquals('seed:createdb', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $seedFilename = 'AddUserSeed.php';
        $seedFile = $seedPath . '/' . $seedFilename;
        $expected = 'SEED GENERATION USING EXISTING DATA

Seed detail: 
Name: add user seed
Table : mytable
Class name: AddUserSeed
Filename: AddUserSeed.php
Path: ' . $seedFile . '

Seed [add user seed] generated successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
        $this->assertTrue($seedDir->hasChild($seedFilename));
    }

    public function testExecuteSuccess(): void
    {
        $seedDir = $this->createVfsDirectory('seed', $this->vfsPath);
        $seedPath = $seedDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $schema =  $this->getMockInstance(Schema::class, [
            'hasTable' => true
        ]);
        $resulSet = $this->getMockInstance(ResultSet::class, [
            'all' => [['a' => 1], ['a' => 2]]
        ]);
        $resulSet->expects($this->any())
                ->method('fetchAssoc')
                ->will($this->returnSelf());

        $from = $this->getMockInstance(Query::class, [
            'select' => $resulSet
        ]);
        $queryBuilder =  $this->getMockInstance(QueryBuilder::class, [
            'from' => $from
        ]);
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user seed',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedCreateDbCommand($application, $config, $filesystem, $schema, $queryBuilder);
        $o->bind($app);
        $o->parse(['platine', 'mytable']);
        $this->assertEquals('seed:createdb', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $seedFilename = 'AddUserSeed.php';
        $seedFile = $seedPath . '/' . $seedFilename;
        $expected = 'SEED GENERATION USING EXISTING DATA

Seed detail: 
Name: add user seed
Table : mytable
Class name: AddUserSeed
Filename: AddUserSeed.php
Path: ' . $seedFile . '

Seed [add user seed] generated successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
        $this->assertTrue($seedDir->hasChild($seedFilename));
    }
}
