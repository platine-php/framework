<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Fixture;

use ArrayIterator;
use IteratorAggregate;
use Platine\Console\Command\Command;
use Platine\Event\EventInterface;
use Platine\Event\ListenerInterface;
use Platine\Event\SubscriberInterface;
use Platine\Framework\Form\Param\BaseParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Framework\Service\ServiceProvider;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Orm\Entity;
use Platine\Route\Router;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Traversable;

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
    protected string $name;
    protected string $status;

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
        $this->app->bind(MyRequestHandle::class);
    }
}
