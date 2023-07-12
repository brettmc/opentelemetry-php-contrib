--TEST--
Test generating OTLP from cake logger
--FILE--

<?php
use Cake\Log\Log;
use OpenTelemetry\API\Globals;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_LOGS_EXPORTER=console');
putenv('OTEL_TRACES_EXPORTER=none');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_PHP_DETECTORS=none');
putenv('OTEL_PHP_PSR3_MODE=export');

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$logger = new \Yiisoft\Log\Logger([new \Yiisoft\Log\StreamTarget(STDOUT)]);

$span = Globals::tracerProvider()->getTracer('demo')->spanBuilder('root')->startSpan();
$scope = $span->activate();

$input = require(__DIR__ . '/input.php');

$logger->info($input['message'], $input['context']);

$scope->detach();
$span->end();
?>

--EXPECTF--
%s [info][%s] hello world

Message context:

foo: 'bar'
exception: RuntimeException: kablam in %s
Stack trace:
#0 %s
#1 {main}

Next Exception: kaboom in %s
Stack trace:
#0 %s
#1 {main}
time: %d.%d
memory: %d
category: '%s'

{
    "resource": {
        "attributes": [],
        "dropped_attributes_count": 0
    },
    "scopes": [
        {
            "name": "psr3",
            "version": null,
            "attributes": [],
            "dropped_attributes_count": 0,
            "schema_url": null,
            "logs": [
                {
                    "timestamp": null,
                    "observed_timestamp": %d,
                    "severity_number": %d,
                    "severity_text": null,
                    "body": "hello world",
                    "trace_id": "%s",
                    "span_id": "%s",
                    "trace_flags": 1,
                    "attributes": {
                        "foo": "bar",
                        "exception": {
                            "message": "kaboom",
                            "code": 500,
                            "file": "%s",
                            "line": %d,
                            "trace": [
                                {
                                    "file": "%s",
                                    "line": %d,
                                    "function": "%s"
                                }
                            ],
                            "previous": {
                                "message": "kablam",
                                "code": 0,
                                "file": "%s",
                                "line": %d,
                                "trace": [
                                    {
                                        "file": "%s",
                                        "line": %d,
                                        "function": "%s"
                                    }
                                ],
                                "previous": []
                            }
                        }
                    },
                    "dropped_attributes_count": 0
                }
            ]
        }
    ]
}