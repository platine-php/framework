<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Console\Command\MakeCrudActionCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyValidator;

/*
 * @group core
 * @group framework
 */
class MakeCrudActionCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent('y');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
            'confirm',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeCrudActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:crud', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Generation of new crud class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testExecuteCreateSuccess(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent('y');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
            'confirm',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeCrudActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName, '-a=Foo', '-b=foo', '-i=name:name,description', '-c=name:name,description:foo', '-o=name:desc,description']);
        $this->assertEquals('make:crud', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Generation of new crud class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
        $this->assertEquals($this->getExpectedCommandContent(), $this->runPrivateProtectedMethod($o, 'createClass', []));
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeCrudActionCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }


    protected function getExpectedCommandContent(): string
    {
        return <<<E
        <?php

        declare(strict_types=1);

        namespace MyApp\actions;

        use Platine\Framework\Helper\Flash;
        use Platine\Framework\Http\Action\CrudAction;
        use Platine\Framework\Http\RouteHelper;
        use Platine\Lang\Lang;
        use Platine\Logger\LoggerInterface;
        use Platine\Pagination\Pagination;
        use Platine\Template\Template;
        use Platine\Framework\Auth\Entity\User; 
        use Platine\Test\Framework\Fixture\MyParam; 
        use Platine\Test\Framework\Fixture\MyValidator; 
        use Platine\Framework\Auth\Repository\UserRepository; 


        /**
        * @class MyAction
        * @package MyApp\actions
        * @extends CrudAction<User>
        */
        class MyAction extends CrudAction
        {
            
            /**
            * {@inheritdoc}
            */
            protected array \$fields = ['name', 'description' => 'foo'];

            /**
            * {@inheritdoc}
            */
            protected array \$orderFields = ['name' => 'DESC', 'description'];

            /**
            * {@inheritdoc}
            */
            protected array \$uniqueFields = ['name', 'description'];

            /**
            * {@inheritdoc}
            */
            protected string \$templatePrefix = 'my';

            /**
            * {@inheritdoc}
            */
            protected string \$routePrefix = 'my';

            /**
            * {@inheritdoc}
            */
            protected string \$entityContextName = 'foo';

            /**
            * {@inheritdoc}
            */
            protected string \$messageCreate = 'Foo';
    
        
            /**
            * {@inheritdoc}
            */
            protected string \$paramClass = MyParam::class;

            /**
            * {@inheritdoc}
            */
            protected string \$validatorClass = MyValidator::class;

            /**
            * Create new instance
            * {@inheritdoc}
            * @param UserRepository<User> \$repository
            */
            public function __construct(
                Lang \$lang,
                Pagination \$pagination,
                Template \$template,
                Flash \$flash,
                RouteHelper \$routeHelper,
                LoggerInterface \$logger,
                UserRepository \$repository
            ) {
                parent::__construct(
                    \$lang,
                    \$pagination,
                    \$template,
                    \$flash,
                    \$routeHelper,
                    \$logger
                );
                \$this->repository = \$repository;
            }
        }
        
        E;
    }
}
