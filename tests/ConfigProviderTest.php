<?php
declare(strict_types=1);

namespace Knoxzin1\AuraFilterMiddleware\Test;

use Knoxzin1\AuraFilterMiddleware\ConfigProvider;
use Knoxzin1\AuraFilterMiddleware\Middleware\AuraFilterMiddleware;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testProviderProvidesFactoriesForAllMiddleware()
    {
        $provider = new ConfigProvider();
        $config = $provider();

        $this->assertTrue(isset($config['dependencies']['factories']));
        $factories = $config['dependencies']['factories'];
        $this->assertArrayHasKey(AuraFilterMiddleware::class, $factories);
    }
}
