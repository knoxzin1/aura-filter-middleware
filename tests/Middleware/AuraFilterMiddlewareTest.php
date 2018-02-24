<?php
declare(strict_types = 1);

namespace Knoxzin1\AuraFilterMiddleware\Test\Middleware;

use Aura\Filter\FilterFactory;
use Aura\Filter\SubjectFilter;
use Knoxzin1\AuraFilterMiddleware\Exception\InvalidFilterException;
use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

class AuraFilterMiddlewareTest extends TestCase
{
    /** @var RouterInterface|ObjectProphecy */
    private $router;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var RouteMiddleware */
    private $middleware;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var DelegateInterface|ObjectProphecy */
    private $handler;

    /** @var SubjectFilter */
    private $filter;

    /** @var ContainerInterface */
    private $container;

    public function setUp()
    {
        $this->router     = $this->prophesize(RouterInterface::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->middleware = new AuraFilterMiddleware(
            $this->router->reveal(),
            $this->container->reveal()
        );
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->handler = $this->prophesize(DelegateInterface::class);

        $filterFactory = new FilterFactory();
        $this->filter = $filterFactory->newSubjectFilter();
    }

    public function testReturnsJsonResponseOnFailure()
    {
        $this->filter->validate('foo')->is('int')->asHardRule();

        $routeMiddleware = $this->prophesize(MiddlewareInterface::class)->reveal();

        $route = new Route('/foo', $routeMiddleware);
        $route->setOptions([
            AuraFilterMiddleware::OPTIONS_KEY => 'FilterInstance',
        ]);

        $result = RouteResult::fromRoute($route);

        $this->container->get(Argument::any())->willReturn($this->filter);

        $this->router->match($this->request->reveal())->willReturn($result);

        $this->request->getParsedBody()->willReturn(['foo' => 'qwe']);
        $this->request->getUploadedFiles()->willReturn([]);
        $this->request->withAttribute()->shouldNotBeCalled();

        $expected = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler->handle($this->request->reveal())->willReturn($expected);

        $response = $this->middleware->process(
            $this->request->reveal(),
            $this->handler->reveal()
        );

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDelegatesToNextMiddlewareIfRouteNotFound()
    {
        $result = RouteResult::fromRouteFailure(null);
        $this->router->match($this->request->reveal())->willReturn($result);

        $expected = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler->handle($this->request->reveal())->willReturn($expected);

        $response = $this->middleware->process(
            $this->request->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($expected, $response);
    }

    public function testDelegatesToNextMiddlewareIfFilterNotFound()
    {
        $routeMiddleware = $this->prophesize(MiddlewareInterface::class)->reveal();
        $route = new Route('/foo', $routeMiddleware);
        $result = RouteResult::fromRoute($route);

        $this->router->match($this->request->reveal())->willReturn($result);

        $this->request->withAttribute()->shouldNotBeCalled();

        $expected = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler->handle($this->request->reveal())->willReturn($expected);

        $response = $this->middleware->process(
            $this->request->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($expected, $response);
    }

    public function testThrowsIfFilterInstanceIsInvalid()
    {
        $this->expectException(InvalidFilterException::class);

        $routeMiddleware = $this->prophesize(MiddlewareInterface::class)->reveal();
        $route = new Route('/foo', $routeMiddleware);
        $route->setOptions([
            AuraFilterMiddleware::OPTIONS_KEY => 'FilterInstance',
        ]);

        $result = RouteResult::fromRoute($route);

        $this->router->match($this->request->reveal())->willReturn($result);

        $this->container->get(Argument::any())->willReturn('invalid filter');

        $this->middleware->process(
            $this->request->reveal(),
            $this->handler->reveal()
        );
    }

    public function testReturnsArrayOnSuccess()
    {
        $this->filter->validate('foo')->is('int')->asHardRule();

        $routeMiddleware = $this->prophesize(MiddlewareInterface::class)->reveal();

        $route = new Route('/foo', $routeMiddleware);
        $route->setOptions([
            AuraFilterMiddleware::OPTIONS_KEY => 'FilterInstance',
        ]);

        $result = RouteResult::fromRoute($route);

        $this->container->get(Argument::any())->willReturn($this->filter);

        $this->router->match($this->request->reveal())->willReturn($result);

        $this->request->getParsedBody()->willReturn(['foo' => 123]);
        $this->request->getUploadedFiles()->willReturn([]);
        $this->request
            ->withAttribute(AuraFilterMiddleware::MIDDLEWARE_KEY, Argument::any())
            ->will([$this->request, 'reveal']);

        $expected = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler->handle($this->request->reveal())->willReturn($expected);

        $response = $this->middleware->process(
            $this->request->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($expected, $response);
    }
}
