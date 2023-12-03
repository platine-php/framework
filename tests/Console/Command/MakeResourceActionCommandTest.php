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
use Platine\Framework\Console\Command\MakeResourceActionCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyValidator;

/*
 * @group core
 * @group framework
 */
class MakeResourceActionCommandTest extends BaseCommandTestCase
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

        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent(Application::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent('');

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

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'actions',
                'MyAction.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the form parameter full class name: Class does not exists, please enter the form parameter full class name: Enter the form validator full class name: Class does not exists, please enter the form validator full class name: Enter the entity full class name: Class does not exists, please enter the entity full class name: Enter the repository full class name: Class does not exists, please enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Property full class name: The class [Foo\Bar\Not\Found] does not exists
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: $classPath
Namespace: MyApp\actions

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
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
        $this->createInputContent('');
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

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName, '-i=name:name,description', '-c=name:name,description', '-o=name:desc,description']);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'actions',
                'MyAction.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: $classPath
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
        //$this->assertEquals($this->getExpectedCommandContent(), $this->runPrivateProtectedMethod($o, 'createClass', []));
    }

    public function testExecuteCreateFromJsonConfig(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $json = $this->createVfsFile('config.json', $dir, "{\"message_create\": \"Category created\"}");
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
        $this->createInputContent('');
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

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName, '-j=' . $json->url()]);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'actions',
                'MyAction.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: $classPath
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testGetPropertyNameNotExist(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeResourceActionCommand($app, $filesystem);

        $this->assertEmpty($this->runPrivateProtectedMethod($o, 'getPropertyName', ['not_found_prop']));
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeResourceActionCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }


    protected function getExpectedCommandContent(): string
    {
        return <<<E
        <?php
        
        declare(strict_types=1);
        
        namespace MyApp\actions;

        use Exception;
        use Platine\Http\ResponseInterface;
        use Platine\Http\ServerRequestInterface;
        use Platine\Framework\Http\RequestData;
        use Platine\Framework\Http\Response\TemplateResponse;
        use Platine\Framework\Http\Response\RedirectResponse;
        use Platine\Lang\Lang; 
        use Platine\Pagination\Pagination; 
        use Platine\Template\Template; 
        use Platine\Framework\Helper\Flash; 
        use Platine\Framework\Http\RouteHelper; 
        use Platine\Logger\LoggerInterface; 
        use Platine\Framework\Auth\Repository\UserRepository; 
        use Platine\Framework\Auth\Entity\User; 
        use Platine\Test\Framework\Fixture\MyParam; 
        use Platine\Test\Framework\Fixture\MyValidator; 


        /**
        * @class MyAction
        * @package MyApp\actions
        */
        class MyAction
        {
            
            /**
            * The Lang instance
            * @var Lang
            */
            protected Lang \$lang;

            /**
            * The Pagination instance
            * @var Pagination
            */
            protected Pagination \$pagination;

            /**
            * The Template instance
            * @var Template
            */
            protected Template \$template;

            /**
            * The Flash instance
            * @var Flash
            */
            protected Flash \$flash;

            /**
            * The RouteHelper instance
            * @var RouteHelper
            */
            protected RouteHelper \$routeHelper;

            /**
            * The LoggerInterface instance
            * @var LoggerInterface
            */
            protected LoggerInterface \$logger;

            /**
            * The UserRepository instance
            * @var UserRepository
            */
            protected UserRepository \$userRepository;

            

            /**
            * Create new instance
            * @param Lang \$lang 
            * @param Pagination \$pagination 
            * @param Template \$template 
            * @param Flash \$flash 
            * @param RouteHelper \$routeHelper 
            * @param LoggerInterface \$logger 
            * @param UserRepository \$userRepository 
            */
            public function __construct(
               Lang \$lang,
               Pagination \$pagination,
               Template \$template,
               Flash \$flash,
               RouteHelper \$routeHelper,
               LoggerInterface \$logger,
               UserRepository \$userRepository
            ){
                \$this->lang = \$lang;
                \$this->pagination = \$pagination;
                \$this->template = \$template;
                \$this->flash = \$flash;
                \$this->routeHelper = \$routeHelper;
                \$this->logger = \$logger;
                \$this->userRepository = \$userRepository;
            }

            /**
            * List all entities
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function index(ServerRequestInterface \$request): ResponseInterface
            {
                \$context = [];
                \$param = new RequestData(\$request);
                \$totalItems = \$this->userRepository->query()
                                                       ->count('id');

                \$currentPage = (int) \$param->get('page', 1);

                \$this->pagination->setTotalItems(\$totalItems)
                                ->setCurrentPage(\$currentPage);

                \$limit = \$this->pagination->getItemsPerPage();
                \$offset = \$this->pagination->getOffset();

                \$results = \$this->userRepository->query()
                                                    ->offset(\$offset)
                                                    ->limit(\$limit)
                                                    ->orderBy('name', 'DESC')
        \t\t\t\t\t    ->orderBy('description', 'ASC')
                                                    ->all();
                
                \$context['list'] = \$results;
                \$context['pagination'] = \$this->pagination->render();


                return new TemplateResponse(
                    \$this->template,
                    'my/list',
                    \$context
                );
            }

            /**
            * List entity detail
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function detail(ServerRequestInterface \$request): ResponseInterface
            {
                \$context = [];
                \$id = (int) \$request->getAttribute('id');

                /** @var User|null \$entity */
                \$entity = \$this->userRepository->find(\$id);

                if (\$entity === null) {
                    \$this->flash->setError(\$this->lang->tr('This record doesn\'t exist'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                }
                \$context['entity'] = \$entity;
                        
                return new TemplateResponse(
                    \$this->template,
                    'my/detail',
                    \$context
                );
            }

            /**
            * Create new entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function create(ServerRequestInterface \$request): ResponseInterface
            {
                \$context = [];
                \$param = new RequestData(\$request);
                
                \$formParam = new MyParam(\$param->posts());
                \$context['param'] = \$formParam;
                
                if (\$request->getMethod() === 'GET') {
                    return new TemplateResponse(
                        \$this->template,
                        'my/create',
                        \$context
                    );
                }
                
                \$validator = new MyValidator(\$formParam, \$this->lang);
                if (\$validator->validate() === false) {
                    \$context['errors'] = \$validator->getErrors();

                    return new TemplateResponse(
                        \$this->template,
                        'my/create',
                        \$context
                    );
                }
                
                \$entityExist = \$this->userRepository->findBy([
                                                       'name' => \$formParam->getName(),
        \t\t\t\t\t       'description' => \$formParam->getDescription(),
                                                   ]);
                
                if(\$entityExist !== null){
                    \$this->flash->setError(\$this->lang->tr('This record already exist'));

                    return new TemplateResponse(
                        \$this->template,
                        'my/create',
                        \$context
                    );
                }

                /** @var User \$entity */
                \$entity = \$this->userRepository->create([
                   'name' => \$formParam->getName(),
        \t   'description' => \$formParam->getDescription(),
                ]);
                
                try {
                    \$this->userRepository->save(\$entity);

                    \$this->flash->setSuccess(\$this->lang->tr('Data successfully created'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when saved the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('Data processing error'));

                    return new TemplateResponse(
                        \$this->template,
                        'my/create',
                        \$context
                    );
                }
            }

            /**
            * Update existing entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function update(ServerRequestInterface \$request): ResponseInterface
            {
                \$context = [];
                \$param = new RequestData(\$request);
                
                \$id = (int) \$request->getAttribute('id');

                /** @var User|null \$entity */
                \$entity = \$this->userRepository->find(\$id);

                if (\$entity === null) {
                    \$this->flash->setError(\$this->lang->tr('This record doesn\'t exist'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                }
                \$context['entity'] = \$entity;
                \$context['param'] = (new MyParam())->fromEntity(\$entity);
                if (\$request->getMethod() === 'GET') {
                    return new TemplateResponse(
                        \$this->template,
                        'my/update',
                        \$context
                    );
                }
                \$formParam = new MyParam(\$param->posts());
                \$context['param'] = \$formParam;
                
                \$validator = new MyValidator(\$formParam, \$this->lang);
                if (\$validator->validate() === false) {
                    \$context['errors'] = \$validator->getErrors();

                    return new TemplateResponse(
                        \$this->template,
                        'my/update',
                        \$context
                    );
                }
                
                \$entityExist = \$this->userRepository->findBy([
                                                       'name' => \$formParam->getName(),
        \t\t\t\t\t       'description' => \$formParam->getDescription(),
                                                   ]);
                
                if(\$entityExist !== null && \$entityExist->id !== \$id){
                    \$this->flash->setError(\$this->lang->tr('This record already exist'));

                    return new TemplateResponse(
                        \$this->template,
                        'my/update',
                        \$context
                    );
                }

                \$entity->name = \$formParam->getName();
        \t   \$entity->description = \$formParam->getDescription();
                
                try {
                    \$this->userRepository->save(\$entity);

                    \$this->flash->setSuccess(\$this->lang->tr('Data successfully updated'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_detail', ['id' => \$id])
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when saved the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('Data processing error'));

                    return new TemplateResponse(
                        \$this->template,
                        'my/update',
                        \$context
                    );
                }
            }

            /**
            * Delete the entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function delete(ServerRequestInterface \$request): ResponseInterface
            {
                \$id = (int) \$request->getAttribute('id');

                /** @var User|null \$entity */
                \$entity = \$this->userRepository->find(\$id);

                if (\$entity === null) {
                    \$this->flash->setError(\$this->lang->tr('This record doesn\'t exist'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                }

                try {
                    \$this->userRepository->delete(\$entity);

                    \$this->flash->setSuccess(\$this->lang->tr('Data successfully deleted'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when delete the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('Data processing error'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('my_list')
                    );
                }
            }
        }
        
        E;
    }
}
