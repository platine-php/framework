<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Framework\Console\PasswordGenerateCommand;
use Platine\Security\Hash\BcryptHash;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class PasswordGenerateCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $this->createInputContent('');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ]);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $hash = $this->getMockInstance(BcryptHash::class, [
            'hash' => 'hashed'
        ]);

        $password = 'my password';

        $o = new PasswordGenerateCommand($hash);
        $o->bind($consoleApp);
        $o->parse(['platine', $password]);
        $this->assertEquals('password:generate', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expectedTemplate = 'GENERATION OF PASSWORD%s%sPlain password: [my password]%sHashed password: hashed%s%sCommand finished successfully%s';
        
        $expected = sprintf($expectedTemplate, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL);
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteInputPassword(): void
    {
        $this->createInputContent('tnh');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $hash = $this->getMockInstance(BcryptHash::class, [
            'hash' => 'hashed'
        ]);

        $o = new PasswordGenerateCommand($hash);
        $o->bind($consoleApp);
        $o->parse(['platine']);
        $this->assertEquals('password:generate', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF PASSWORD

Enter the plain password to generate []: Plain password: [tnh]
Hashed password: hashed

Command finished successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
