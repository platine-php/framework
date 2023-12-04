<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\VendorPublishCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class VendorPublishCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {

        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);

        $this->createTestPackage($rootDir, $vendorDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'lang.translation_path',
                    null,
                    $rootDir->url()
                ],
                [
                    'database.migration.path',
                    'migrations',
                    $rootDir->url()
                ],
                [
                    'template.template_dir',
                    'templates',
                    $rootDir->url()
                ]
            ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-a']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();
        $expected = 'PUBLISH OF PACKAGE [test/foo]

Name: test/foo
Description: Test package
Version: v1.0.0
Type: library
Path: vfs://root/vendor/test/foo

Publish of package configuration
Package configuration [testconfig.php] publish successfully
Publish of package migration
Package migration [migrations/] publish successfully
Publish of package language
Package language [lang/] publish successfully
Publish of package template
Package template [templates/] publish successfully

Command finished successfully
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecutePublishOnlySomeFiles(): void
    {

        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);

        $this->createTestPackage($rootDir, $vendorDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
        'get' => [
            [
                'lang.translation_path',
                null,
                $rootDir->url()
            ],
            [
                'database.migration.path',
                'migrations',
                $rootDir->url()
            ],
            [
                'template.template_dir',
                'templates',
                $rootDir->url()
            ]

        ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-cmlt']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();
        $expected = 'PUBLISH OF PACKAGE [test/foo]

Name: test/foo
Description: Test package
Version: v1.0.0
Type: library
Path: vfs://root/vendor/test/foo

Publish of package configuration
Package configuration [testconfig.php] publish successfully
Publish of package migration
Package migration [migrations/] publish successfully
Publish of package language
Package language [lang/] publish successfully
Publish of package template
Package template [templates/] publish successfully

Command finished successfully
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecutePackageNotFound(): void
    {
        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);
        $this->createVfsFile('composer.lock', $rootDir, '{}');
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'lang.translation_path',
                    null,
                    $rootDir->url()
                ],
                [
                    'database.migration.path',
                    'migrations',
                    $rootDir->url()
                ],
                [
                    'template.template_dir',
                    'templates',
                    $rootDir->url()
                ]
            ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-a']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();
        $expected = 'PUBLISH OF PACKAGE [test/foo]

Can not find the composer package [test/foo].
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecutePackageFileToPublishMissing(): void
    {
        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);
        $this->createTestPackageMissingFileToPublish($rootDir, $vendorDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'lang.translation_path',
                    null,
                    $rootDir->url()
                ],
                [
                    'database.migration.path',
                    'migrations',
                    $rootDir->url()
                ],
                [
                    'template.template_dir',
                    'templates',
                    $rootDir->url()
                ]
            ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-c']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();
        $expected = 'PUBLISH OF PACKAGE [test/foo]

Name: test/foo
Description: Test package
Version: v1.0.0
Type: library
Path: vfs://root/vendor/test/foo

Publish of package configuration
Can not find the package file configuration [vfs://root/vendor/test/foo/testconfig.php].

Command finished successfully
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecutePackageDestinationFileExistsNoOverwrite(): void
    {
        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);
        $this->createTestPackageDestinationFileAlreadyExists($rootDir, $vendorDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'lang.translation_path',
                    null,
                    $rootDir->url()
                ],
                [
                    'database.migration.path',
                    'migrations',
                    $rootDir->url()
                ],
                [
                    'template.template_dir',
                    'templates',
                    $rootDir->url()
                ]
            ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-c']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'testconfig.php'
            ]
        );

        $expected = <<<E
PUBLISH OF PACKAGE [test/foo]

Name: test/foo
Description: Test package
Version: v1.0.0
Type: library
Path: vfs://root/vendor/test/foo

Publish of package configuration
Package configuration [testconfig.php] publish successfully
File [$classPath] already exist, if you want to overwrite use option "--overwrite".
Package configuration [testconfig.php] publish successfully

Command finished successfully

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }


    public function testExecutePackageNothingToPublish(): void
    {
        $rootDir = $this->createVfsDirectory('app', $this->vfsRoot);
        $vendorDir = $this->createVfsDirectory('vendor', $this->vfsRoot);
        $this->createTestPackageNoPublish($rootDir, $vendorDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getConfigPath' => $rootDir->url(),
            'getRootPath' => $rootDir->url(),
            'getVendorPath' => $vendorDir->url(),
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'lang.translation_path',
                    null,
                    $rootDir->url()
                ],
                [
                    'database.migration.path',
                    'migrations',
                    $rootDir->url()
                ],
                [
                    'template.template_dir',
                    'templates',
                    $rootDir->url()
                ]
            ]
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new VendorPublishCommand($app, $filesystem, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'test/foo', '-a']);
        $this->assertEquals('vendor:publish', $o->getName());
        $o->execute();
        $expected = 'PUBLISH OF PACKAGE [test/foo]

Name: test/foo
Description: Test package
Version: v1.0.0
Type: library
Path: vfs://root/vendor/test/foo

NOTHING TO PUBLISH, COMMAND ENDED!
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    private function createTestPackage($rootDir, $vendorDir): void
    {
        $composerLock = '{
            "packages": [
                {
                    "name": "test/foo",
                    "version": "v1.0.0",
                    "type": "library",
                    "extra": {
                        "platine": {
                            "config": ["testconfig.php"],
                            "lang": ["lang/"],
                            "template": ["templates/"],
                            "migration": ["migrations/"]
                        }
                    },
                    "description": "Test package"
                }
            ]
        }';

        $this->createVfsFile('composer.lock', $rootDir, $composerLock);
        $packageRootDir = $this->createVfsDirectory('test', $vendorDir);
        $packageDir = $this->createVfsDirectory('foo', $packageRootDir);
        $this->createVfsDirectory('lang', $packageDir);
        $this->createVfsDirectory('migrations', $packageDir);
        $templateDir = $this->createVfsDirectory('templates', $packageDir);
        $this->createVfsFile('testconfig.php', $packageDir, '<?php');
        $this->createVfsFile('home.html', $templateDir, 'hello World');
    }

    private function createTestPackageDestinationFileAlreadyExists($rootDir, $vendorDir): void
    {
        $composerLock = '{
            "packages": [
                {
                    "name": "test/foo",
                    "version": "v1.0.0",
                    "type": "library",
                    "extra": {
                        "platine": {
                            "config": ["testconfig.php", "testconfig.php"],
                            "lang": ["lang/"],
                            "migration": ["migrations/"]
                        }
                    },
                    "description": "Test package"
                }
            ]
        }';

        $this->createVfsFile('composer.lock', $rootDir, $composerLock);
        $packageRootDir = $this->createVfsDirectory('test', $vendorDir);
        $packageDir = $this->createVfsDirectory('foo', $packageRootDir);
        $this->createVfsDirectory('lang', $packageDir);
        $this->createVfsDirectory('migrations', $packageDir);
        $this->createVfsFile('testconfig.php', $packageDir, '<?php');
    }

    private function createTestPackageMissingFileToPublish($rootDir, $vendorDir): void
    {
        $composerLock = '{
            "packages": [
                {
                    "name": "test/foo",
                    "version": "v1.0.0",
                    "type": "library",
                    "extra": {
                        "platine": {
                            "config": ["testconfig.php"]
                        }
                    },
                    "description": "Test package"
                }
            ]
        }';

        $this->createVfsFile('composer.lock', $rootDir, $composerLock);
        $packageRootDir = $this->createVfsDirectory('test', $vendorDir);
        $this->createVfsDirectory('foo', $packageRootDir);
    }

    private function createTestPackageNoPublish($rootDir, $vendorDir): void
    {
        $composerLock = '{
            "packages": [
                {
                    "name": "test/foo",
                    "version": "v1.0.0",
                    "type": "library",
                    "extra": {
                        "platine": {
                        }
                    },
                    "description": "Test package"
                }
            ]
        }';

        $this->createVfsFile('composer.lock', $rootDir, $composerLock);
        $packageRootDir = $this->createVfsDirectory('test', $vendorDir);
        $this->createVfsDirectory('foo', $packageRootDir);
    }
}
