<?php

namespace Alirzaj\ElasticsearchBuilder\Tests;

use Alirzaj\ElasticsearchBuilder\ElasticsearchBuilderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ElasticsearchBuilderServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
    }
}
