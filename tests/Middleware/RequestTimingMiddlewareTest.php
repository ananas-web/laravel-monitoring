<?php

namespace AnanasWeb\LaravelMonitoring\Tests\Middleware;

use AnanasWeb\LaravelMonitoring\MetricsExporter;
use AnanasWeb\LaravelMonitoring\Middleware\RequestTimingMiddleware;
use AnanasWeb\LaravelMonitoring\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

class RequestTimingMiddlewareTest extends TestCase
{
    public function testMiddleware()
    {
        $request = new Request();

        $request->merge([
            'title' => 'Title is in mixed CASE'
        ]);

        $request->setRouteResolver(function () use ($request) {
            $route = new Route(['GET'], '/', [
                'as' => 'test',
            ]);

            $route->bind($request);

            return $route;
        });

        $middleware = $this->app->make(RequestTimingMiddleware::class);

        $middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        /** @var MetricsExporter $exporter */
        $exporter = $this->app->make(MetricsExporter::class);
        $exportData = $exporter->export();

        $this->assertCount(2, $exportData);
        $this->assertEquals('gauge', $exportData[0]->getType());
        $this->assertEquals('php_info', $exportData[0]->getName());
        $this->assertEquals('histogram', $exportData[1]->getType());
        $this->assertEquals('test_application_request_execution_time', $exportData[1]->getName());
    }

    public function getEnvironmentSetUp($app)
    {
        config(['monitoring.namespace' => 'test']);
    }
}
