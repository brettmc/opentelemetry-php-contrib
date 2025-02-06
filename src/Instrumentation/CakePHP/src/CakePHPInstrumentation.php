<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instrumentation\CakePHP;

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\Contrib\Instrumentation\CakePHP\Hooks\Cake\Command\Command;
use OpenTelemetry\Contrib\Instrumentation\CakePHP\Hooks\Cake\Controller\Controller;
use OpenTelemetry\Contrib\Instrumentation\CakePHP\Hooks\Cake\Http\Server;
use OpenTelemetry\SemConv\Version;

class CakePHPInstrumentation
{
    public const NAME = 'cakephp';

    public static function register(): void
    {
        $instrumentation = new CachedInstrumentation(
            'io.opentelemetry.contrib.php.cakephp',
            null,
            Version::VERSION_1_30_0->url(),
        );
        Server::hook($instrumentation);
        Controller::hook($instrumentation);
        Command::hook($instrumentation);
    }
}
