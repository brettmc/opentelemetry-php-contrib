<?php

declare(strict_types=1);

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Logs\Monolog\Handler
 */
class HandlerTest extends TestCase
{
    /**
     * @var LoggerInterface&PHPUnit\Framework\MockObject\MockObject $logger
     */
    private LoggerInterface $logger;
    private LoggerProviderInterface $provider;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->provider = $this->createMock(LoggerProviderInterface::class);
        $this->provider->method('getLogger')->willReturn($this->logger);
    }

    public function test_handle_record(): void
    {
        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $sharedState = $this->createMock(LoggerSharedState::class);
        $resource = $this->createMock(ResourceInfo::class);
        $limits = $this->createMock(LogRecordLimits::class);
        $attributeFactory = Attributes::factory();
        $limits->method('getAttributeFactory')->willReturn($attributeFactory);
        $sharedState->method('getResource')->willReturn($resource);
        $sharedState->method('getLogRecordLimits')->willReturn($limits);
        $handler = new Handler($this->provider, 'error', true);
        $processor = function ($record) {
            $record['extra'] = ['extra' => 'baz'];

            return $record;
        };
        $monolog = new \Monolog\Logger('test', [$handler], [$processor]);

        $this->logger
            ->expects($this->once())
            ->method('logRecord')
            ->with($this->callback(
                function (\OpenTelemetry\API\Logs\LogRecord $logRecord) use ($scope, $sharedState) {
                    $readable = new ReadableLogRecord($scope, $sharedState, $logRecord, false);
                    $this->assertSame('ERROR', $readable->getSeverityText());
                    $this->assertSame(17, $readable->getSeverityNumber());
                    $this->assertGreaterThan(0, $readable->getTimestamp());
                    $this->assertSame('message', $readable->getBody());
                    $attributes = $readable->getAttributes();
                    $this->assertCount(3, $attributes);
                    $this->assertSame('bar', $attributes->get('foo'));
                    $this->assertSame('baz', $attributes->get('extra'));
                    $this->assertNotNull($attributes->get('exception'));

                    return true;
                }
            ));

        $monolog->error('message', ['foo' => 'bar', 'exception' => new \Exception('kaboom', 500)]);
    }
}
