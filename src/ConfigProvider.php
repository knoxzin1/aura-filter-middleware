<?php
declare(strict_types = 1);

namespace Knoxzin1\AuraFilterMiddleware;

use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddleware;
use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddlewareFactory;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide default container dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                AuraFilterMiddleware::class => AuraFilterMiddlewareFactory::class,
            ],
        ];
    }
}
