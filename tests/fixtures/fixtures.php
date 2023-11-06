<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Fixture;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Event\EventInterface;
use Platine\Event\ListenerInterface;
use Platine\Event\SubscriberInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Form\Param\BaseParam;
use Platine\Framework\Form\Validator\AbstractValidator;
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
use Platine\Route\Router;
use Platine\Session\Session;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Traversable;

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

    public function get(string $key, $default = null)
    {
        return isset($this->items[$key])
                ? $this->items[$key]
                : $default;
    }

    public function has(string $key): bool
    {
        return isset($this->has[$key]);
    }

    public function set(string $key, $value): void
    {
    }

    public function getFlash(string $key, $default = null)
    {
        return isset($this->flash[$key])
                ? $this->flash[$key]
                : $default;
    }
}

class MyConfig extends Config
{
    protected array $config = [
      'database.migration.table' => 'table'
    ];

    public function __construct($items = [])
    {
        $this->config = array_merge($this->config, (array) $items);
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->config)
               ? $this->config[$key]
                : null;
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
    public function handle(EventInterface $event)
    {
        echo $event->getName();
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
