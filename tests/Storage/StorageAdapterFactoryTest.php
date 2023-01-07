<?php

namespace AnanasWeb\LaravelMonitoring\Tests\Storage;

use AnanasWeb\LaravelMonitoring\Storage\StorageAdapterFactory;
use PHPUnit\Framework\TestCase;
use Prometheus\Exception\StorageException;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;

class StorageAdapterFactoryTest extends TestCase
{
    private StorageAdapterFactory $storageAdapterFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageAdapterFactory = new StorageAdapterFactory();
    }

    public function testMemoryAdapterInitialization()
    {
        $driver = $this->storageAdapterFactory->make('memory', []);

        $this->assertInstanceOf(InMemory::class, $driver);
    }

    public function testAPCAdapterInitialization()
    {
        if (! extension_loaded('apcu')) {
            $this->expectException(StorageException::class);
            $this->expectExceptionMessage('APCu extension is not loaded');
        } elseif (! apcu_enabled()) {
            $this->expectException(StorageException::class);
            $this->expectExceptionMessage('APCu is not enabled');
        }

        $driver = $this->storageAdapterFactory->make('apc', []);

        $this->assertInstanceOf(APC::class, $driver);
    }

    public function disabledtestRedisAdapterInitialization()
    {
        $driver = $this->storageAdapterFactory->make('redis', []);

        $this->assertInstanceOf(Redis::class, $driver);
    }
}
