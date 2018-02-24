<?php
declare(strict_types=1);

namespace Knoxzin1\AuraFilterMiddleware\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class AuraFilterMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuraFilterMiddleware
    {
        return new AuraFilterMiddleware(
            $container->get(RouterInterface::class),
            $container
        );
    }
}
