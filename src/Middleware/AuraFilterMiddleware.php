<?php
declare(strict_types = 1);

namespace Knoxzin1\AuraFilterMiddleware\Middleware;

use Aura\Filter\SubjectFilter;
use Knoxzin1\AuraFilterMiddleware\Exception\InvalidFilterException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouterInterface;

class AuraFilterMiddleware implements MiddlewareInterface
{
    public const OPTIONS_KEY = 'aura-filter';

    public const MIDDLEWARE_KEY = 'validationResult';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        RouterInterface $router,
        ContainerInterface $container
    ) {
        $this->router = $router;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $route = $this->router->match($request)->getMatchedRoute();

        if (!$route) {
            return $delegate->handle($request);
        }

        $options = $route->getOptions();

        $filter = $options[self::OPTIONS_KEY] ?? null;

        if (!$filter) {
            return $delegate->handle($request);
        }

        $filterInstance = $this->container->get($filter);

        if (!$filterInstance instanceof SubjectFilter) {
            throw new InvalidFilterException(
                sprintf(
                    'Input filter "%s" does not exist; cannot validate request',
                    $filterInstance
                )
            );
        }

        $formInput = array_merge(
            $request->getParsedBody(),
            $request->getUploadedFiles()
        );

        $isValid = $filterInstance->apply($formInput);

        if (!$isValid) {
            return new JsonResponse([
                'message' => 'Validation Failed',
                'errorDetail' => $filterInstance->getFailures()->getMessages()
            ], 422);
        }

        $request = $request->withAttribute(
            self::MIDDLEWARE_KEY,
            $formInput
        );
        return $delegate->handle($request);
    }
}
