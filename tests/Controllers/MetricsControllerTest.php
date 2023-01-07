<?php

namespace AnanasWeb\LaravelMonitoring\Tests\Controllers;

use AnanasWeb\LaravelMonitoring\Controllers\MetricsController;
use AnanasWeb\LaravelMonitoring\MetricsExporter;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Prometheus\RenderTextFormat;

class MetricsControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;

    private MetricsExporter $exporter;

    private MetricsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = Mockery::mock(ResponseFactory::class);
        $this->exporter = Mockery::mock(MetricsExporter::class);
        $this->controller = new MetricsController();
    }

    public function testMetricsResponse()
    {
        $mockResponse = Mockery::mock(Response::class);
        $this->responseFactory->shouldReceive('make')
            ->once()
            ->withArgs([
                "\n",
                200,
                ['Content-Type' => RenderTextFormat::MIME_TYPE],
            ])
            ->andReturn($mockResponse);
        $this->exporter->shouldReceive('export')
            ->once()
            ->andReturn([]);

        $actualResponse = ($this->controller)($this->responseFactory, $this->exporter);
        $this->assertSame($mockResponse, $actualResponse);
    }
}
