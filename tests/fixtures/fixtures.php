<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Fixture;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Event\EventInterface;
use Platine\Event\Listener\ListenerInterface;
use Platine\Event\SubscriberInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Form\Param\BaseParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\Action\BaseAction;
use Platine\Framework\Http\Action\BaseConfigurationAction;
use Platine\Framework\Http\Action\BaseResourceAction;
use Platine\Framework\Http\Action\CrudAction;
use Platine\Framework\Http\Maintenance\MaintenanceDriverInterface;
use Platine\Framework\Http\Response\JsonResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Framework\Service\ServiceProvider;
use Platine\Framework\Task\TaskInterface;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Http\Uri;
use Platine\Http\UriInterface;
use Platine\Lang\Lang;
use Platine\Logger\Configuration;
use Platine\Logger\Formatter\DefaultFormatter;
use Platine\Logger\Logger;
use Platine\Logger\LoggerFormatterInterface;
use Platine\Logger\LoggerInterface;
use Platine\OAuth2\Entity\Client;
use Platine\OAuth2\Entity\TokenOwnerInterface;
use Platine\OAuth2\Grant\BaseGrant;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Repository;
use Platine\Pagination\Pagination;
use Platine\Route\RouteCollection;
use Platine\Route\Router;
use Platine\Session\Session;
use Platine\Template\Template;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Traversable;

function getTestMaintenanceDriver(bool $exception = false, bool $active = false, bool $dataException = false, array $excludes = []): MaintenanceDriverInterface
{
    return new class ($exception, $active, $dataException, $excludes) implements  MaintenanceDriverInterface{
        protected bool $exception = true;
        protected bool $active = true;
        protected bool $dataException = true;
        protected array $excludes = [];

        public function __construct(bool $exception = true, bool $active = true, bool $dataException = true, array $excludes = [])
        {
            $this->exception = $exception;
            $this->active = $active;
            $this->dataException = $dataException;
            $this->excludes = $excludes;
        }

        public function activate($data): void
        {
            if ($this->exception) {
                throw new Exception('Maintenance activate error');
            }
        }

        public function active(): bool
        {
            return $this->active;
        }

        public function data(): array
        {
            if ($this->dataException) {
                throw new Exception('Maintenance data error');
            }

            $data = [
                'except' => [],
                'template' => 'maintenance',
                'retry' => 1080,
                'refresh' => 15,
                'secret' => '08685bd7-594b-4ce1-9a6b-f5d168ecdb05',
                'status' => 503,
                'message' => 'Please the system is upgrading',
            ];

            foreach ($this->excludes as $key) {
                unset($data[$key]);
            }

            return $data;
        }

        public function deactivate(): void
        {
            if ($this->exception) {
                throw new Exception('Maintenance deactivate error');
            }
        }
    };
}

class MyBaseAction extends BaseAction
{
    public function respond(): ResponseInterface
    {
        $this->setView('foo_view');
        $this->addContext('foo', 'bar');
        $this->addContexts(['name' => 'Tony']);

        $this->addSidebar('', 'Add user', 'user_create');

        return $this->viewResponse();
    }

    protected function getIgnoreDateFilters(): array
    {
        return ['status'];
    }
}

class MyBaseAction2 extends BaseAction
{
    public function respond(): ResponseInterface
    {
        return $this->viewResponse();
    }
}

class MyBaseResourceAction extends BaseResourceAction
{
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['foo' => 'bar']);
    }
}

class MyBaseConfigurationAction extends BaseConfigurationAction
{
    protected function getModuleName(): string
    {
        return 'app';
    }

    protected function getParamDefinitions(): array
    {
        return [
            'name' => [
                'type' => 'integer',
                'comment' => 'Integer value',
            ],
            'array' => [
                'type' => 'array',
                'comment' => 'Array value',
            ],
            'callable' => [
                'type' => 'callable',
                'comment' => 'Callable value',
                'callable' => [$this, 'myCallable'],
            ],
        ];
    }

    protected function myCallable(): int
    {
        return 123;
    }

    protected function getParamName(): string
    {
        return MyDbConfigParam::class;
    }

    protected function getValidatorName(): string
    {
        return MyDbConfigValidator::class;
    }
}

class MyCrudAction extends CrudAction
{
    /**
     * {@inheritdoc}
     */
    protected array $fields = ['name', 'status'];

    /**
     * {@inheritdoc}
     */
    protected array $uniqueFields = ['name'];

    /**
     * {@inheritdoc}
     */
    protected array $orderFields = ['name', 'description' => 'DESC'];

    /**
     * {@inheritdoc}
     */
    protected string $templatePrefix = 'category';

    /**
     * {@inheritdoc}
     */
    protected string $routePrefix = 'category';

    /**
     * {@inheritdoc}
     */
    protected string $paramClass = MyParam::class;

    /**
     * {@inheritdoc}
     */
    protected string $validatorClass = MyValidator::class;

    public function __construct(
        Lang $lang,
        Pagination $pagination,
        Template $template,
        Flash $flash,
        RouteHelper $routeHelper,
        LoggerInterface $logger,
        MyRepository $repository
    ) {
        parent::__construct(
            $lang,
            $pagination,
            $template,
            $flash,
            $routeHelper,
            $logger
        );
        $this->repository = $repository;
    }
}

class MyOAuthGrant extends BaseGrant
{
    public function allowPublicClients(): bool
    {
        return true;
    }

    public function createAuthorizationResponse(ServerRequestInterface $request, Client $client, ?TokenOwnerInterface $owner = null): ResponseInterface
    {
        return new Response();
    }

    public function createTokenResponse(ServerRequestInterface $request, ?Client $client = null, ?TokenOwnerInterface $owner = null): ResponseInterface
    {
        return new Response();
    }
}

class MyRepository extends Repository
{
}

class MyEntity extends Entity
{
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
    }
}

class MyLang extends Lang
{
    protected array $mockMethods = [];

    public function __construct($mockMethods = [])
    {
        $this->mockMethods = (array) $mockMethods;
    }

    public function tr(string $message, $args = []): string
    {
        return $this->mockMethods['tr'] ?? '';
    }
}

class MyRouteHelper extends RouteHelper
{
    protected array $mockMethods = [];

    public function __construct($mockMethods = [])
    {
        $this->mockMethods = (array) $mockMethods;
    }

    public function generateUrl(string $name, array $parameters = []): string
    {
        return $this->mockMethods['generateUrl'] ?? '';
    }
}

class MyServerRequest extends ServerRequest
{
    protected array $mockMethods = [];

    public function __construct($mockMethods = [])
    {
        $this->mockMethods = (array) $mockMethods;
    }

    public function getUri(): UriInterface
    {
        $url = $this->mockMethods['getUri'] ?? '';

        return new Uri($url);
    }

    public function getParsedBody(): array|object|null
    {
        return $this->mockMethods['getParsedBody'] ?? [];
    }
}

class MyRouter extends Router
{
    protected array $mockMethods = [];

    public function __construct($mockMethods = [])
    {
        $this->mockMethods = (array) $mockMethods;
    }

    public function routes(): RouteCollection
    {
        $routes = $this->mockMethods['routes'] ?? [];

        return new RouteCollection($routes);
    }
}

class MyApp extends Application
{
    public function __construct(string $basePath = '')
    {
        parent::__construct($basePath);
        //Most of binding use config, logger
        $this->registerLogger();
        $this->registerConfiguration();
        $this->registerDb();
    }

    protected function registerLogger(): void
    {
        $this->bind(Configuration::class);
        $this->bind(LoggerInterface::class, Logger::class);
        $this->bind(LoggerFormatterInterface::class, DefaultFormatter::class);
    }

    protected function registerDb(): void
    {
        $this->bind(Configuration::class);
    }
}

class MyDatabaseConfigLoader extends DatabaseConfigLoader
{
    public function __construct()
    {
    }
}

class MySession extends Session
{
    protected array $has = [];
    protected array $items = [];
    protected array $flash = [];

    public function __construct($has = [], $items = [], $flash = [])
    {
        $this->has = (array) $has;
        $this->items = (array) $items;
        $this->flash = (array) $flash;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return isset($this->items[$key])
                ? $this->items[$key]
                : $default;
    }

    public function has(string $key): bool
    {
        return isset($this->has[$key]);
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return isset($this->flash[$key])
                ? $this->flash[$key]
                : $default;
    }
}

class MyConfig extends Config
{
    protected array $config = [
      'database.migration.table' => 'table',
      'security.encryption.key' => 'foosecret',
      'mail.smtp.username' => 'user',
      'mail.smtp.password' => 'foosecret',
    ];

    public function __construct($items = [])
    {
        $this->config = array_merge($this->config, (array) $items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->config)
               ? $this->config[$key]
                : $default;
    }
}

class MyNullMaillerConfig extends Config
{
    protected array $config = [
    ];

    public function __construct($items = [])
    {
        $this->config = array_merge($this->config, (array) $items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return null;
    }
}

class MyCsrfManager extends CsrfManager
{
    public function __construct($items = [])
    {
        parent::__construct(new MyConfig($items));
    }
}

class MyMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $request = $request->withAttribute(
            __CLASS__,
            sprintf('%s::%s', __CLASS__, __METHOD__)
        );
        return $handler->handle($request);
    }
}

class MyIterableObject implements IteratorAggregate
{
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }
}

class MyDefaultPhpErrorRequestHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        trigger_error('my error', E_USER_ERROR);
    }
}

class MyResponse extends Response
{
    public function __construct()
    {
        parent::__construct(300);
    }
}

class MyParam extends BaseParam
{
    protected string $name = '';
    protected string $status = '';

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function fromEntity(Entity $entity): self
    {
        $this->name = $entity->name;
        $this->status = $entity->status;

        return $this;
    }
}

class MyDbConfigParam extends BaseParam
{
    protected string $name = '';
    protected string $status = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function fromConfig(AppDatabaseConfig $cfg): self
    {
        $this->name = $cfg->get('app.name', 'foo');

        return $this;
    }
}

class MyParam2 extends BaseParam
{
    protected string $name;
    protected string $status;
}

class MyParam3 extends BaseParam
{
    protected string $name;
    protected string $fooBar;
}

class MyParam4 extends BaseParam
{
    protected object $obj;
    protected $name;
    protected ?int $age;
}

class MyValidator extends AbstractValidator
{
    protected MyParam $param;

    public function __construct(MyParam $param, Lang $lang)
    {
        parent::__construct($lang);
        $this->param = $param;
    }

    public function setValidationData(): void
    {
        $this->addData('name', $this->param->getName());
        $this->addData('status', $this->param->getStatus());
    }

    public function setValidationRules(): void
    {
        $this->addRules('name', [
            new NotEmpty(),
            new MinLength(2)
        ]);

        $this->addRules('status', [
            new NotEmpty()
        ]);
    }
}

class MyDbConfigValidator extends AbstractValidator
{
    public function __construct(protected MyDbConfigParam $param, Lang $lang)
    {
        parent::__construct($lang);
    }

    public function setValidationData(): void
    {
        $this->addData('name', $this->param->getName());
        $this->addData('status', $this->param->getStatus());
    }

    public function setValidationRules(): void
    {
        $this->addRules('name', [
            new NotEmpty(),
            new MinLength(2)
        ]);

        $this->addRules('status', [
            new NotEmpty()
        ]);
    }
}

class MyEventSubscriber implements SubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            'fooevent' => 'handleFooEvent'
        ];
    }

    public function handleFooEvent(EventInterface $e): void
    {
        echo $e->getName();
    }
}

class MyEventListener implements ListenerInterface
{
    public function handle(EventInterface $event): mixed
    {
        echo $event->getName();

        return true;
    }
}

class MyRequestHandle implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new Response(200))->getBody()->write(__CLASS__);
    }
}

class MyCommand extends Command
{
    public function __construct()
    {
        parent::__construct('mycommand');
    }
}

class MyTask implements TaskInterface
{
    public function expression(): string
    {
        return '*/50 * * * *';
    }

    public function name(): string
    {
        return 'mytask';
    }

    public function run(): void
    {
        echo __METHOD__;
    }
}

class MyTask2 extends MyTask
{
    public function expression(): string
    {
        return '* * * * *';
    }

    public function name(): string
    {
        return 'mytask2';
    }
}

class MyTaskException implements TaskInterface
{
    public function expression(): string
    {
        return '* * * * *';
    }

    public function name(): string
    {
        return 'mytask_exception';
    }

    public function run(): void
    {
        throw new Exception(__METHOD__);
    }
}


class MyServiceProvider extends ServiceProvider
{
    public function addRoutes(Router $router): void
    {
        $router->get('/home', MyRequestHandle::class);
    }

    public function boot(): void
    {
        echo __CLASS__ . '::boot';
    }

    public function register(): void
    {
        $this->addCommand(MyCommand::class);
        $this->addTask(MyTask::class);
        $this->app->bind(MyRequestHandle::class);
    }
}
