<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Instrumentation\Wordpress\WordpressInstrumentation;
use OpenTelemetry\SemConv\TraceAttributes;

assert(extension_loaded('otel_instrumentation'));

$instrumentation = new CachedInstrumentation('io.opentelemetry.contrib.php.wordpress');

$factory = new Psr17Factory();
$request = (new ServerRequestCreator($factory, $factory, $factory, $factory))->fromGlobals();

//start a root http span, prior to wordpress running
$span = $instrumentation
    ->tracer()
    ->spanBuilder(sprintf('HTTP %s', $request->getMethod()))
    ->setSpanKind(SpanKind::KIND_CLIENT)
    ->setAttribute(TraceAttributes::HTTP_URL, (string) $request->getUri())
    ->setAttribute(TraceAttributes::HTTP_METHOD, $request->getMethod())
    ->setAttribute(TraceAttributes::HTTP_FLAVOR, $request->getProtocolVersion())
    ->setAttribute(TraceAttributes::HTTP_USER_AGENT, $request->getHeaderLine('User-Agent'))
    ->setAttribute(TraceAttributes::HTTP_REQUEST_CONTENT_LENGTH, $request->getHeaderLine('Content-Length'))
    ->setAttribute(TraceAttributes::NET_PEER_NAME, $request->getUri()->getHost())
    ->setAttribute(TraceAttributes::NET_PEER_PORT, $request->getUri()->getPort())
    ->startSpan();
Context::storage()->attach($span->storeInContext(Context::getCurrent()));

WordpressInstrumentation::register($instrumentation);
