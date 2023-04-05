# OpenTelemetry Monolog handler

A monolog v3 handler for OpenTelemetry. See See https://opentelemetry.io/docs/instrumentation/php/logging for further documentation.

## Requirements

### API + SDK

This package depends on the OpenTelemetry API, but a configured [OpenTelemetry SDK](https://opentelemetry.io/docs/instrumentation/php/sdk/) should also be provided.

### Exporter

Usually logs are exported to a `receiver` via the `otlp` protocol in the `protobuf` format, via http or `gRPC`.

This requires:

- the protobuf extension (or the `google/protobuf` package if performance is not an important consideration)
- the `open-telemetry/exporter-otlp` package
- the `open-telemetry/transport-grpc` package, if using gRPC transport
- a PSR-7 and PSR-18 implementation, if using HTTP transport

### Receiver
Logs must be emitted to a receiver/system that understands the OpenTelemetry protocol, such as the [OpenTelemetry collector](https://opentelemetry.io/docs/collector/).

## Installation

```shell
composer require open-telemetry/logger-monolog
```

## Usage

The OpenTelemetry handler, configured with an OpenTelemetry `LoggerProvider`, is used to send Monolog `LogRecord`s to OpenTelemetry.

The `LoggerProvider` can be configured in a number of ways: manually, via an SDK Builder, or automatically (using environment/php.ini variables).

### Manual configuration

Set up an SDK LoggerProvider and pass it to the handler:

```php
$loggerProvider = new \OpenTelemetry\SDK\Logs\LoggerProvider(/* params */);
$handler = new \OpenTelemetry\Contrib\Logs\Monolog\Handler(
    loggerProvider: $loggerProvider,
    level: \Monolog\Level::Debug,
    bubble: true,
);
```

### Automatic configuration

If you do not provide a `LoggerProvider` to the handler, it will use the globally configured one. That may be a no-op
implementation, if a global LoggerProvider has not been configured.

See [./example/autoload-sdk.php](autoload-sdk example) and https://opentelemetry.io/docs/instrumentation/php/sdk/#autoloading for
details on autoloading an OpenTelemetry SDK.

## Create a Logger

Finally, add the handler to a Monolog logger:

```php
$logger = new \Monolog\Logger(
    name: 'name',
    handlers: [$handler],
);
$logger->info('hello world');
```