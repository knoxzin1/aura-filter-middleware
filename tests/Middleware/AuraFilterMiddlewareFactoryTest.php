<?php
declare(strict_types=1);

namespace Knoxzin1\AuraFilterMiddleware\Test\Middleware;

use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddleware;
use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class DispatchMiddlewareFactoryTest extends TestCase
{
    public function testFactoryProducesFilterMiddleware()
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RouterInterface::class)->willReturn($router);
        $factory = new AuraFilterMiddlewareFactory();
        $middleware = $factory($container->reveal());
        $this->assertInstanceOf(AuraFilterMiddleware::class, $middleware);
    }
}
