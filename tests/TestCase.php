<?php

namespace Alirzaj\ElasticsearchBuilder\Tests;

use Alirzaj\ElasticsearchBuilder\ElasticsearchBuilderServiceProvider;
use Alirzaj\ElasticsearchBuilder\Tests\Indices\Blogs;
use Alirzaj\ElasticsearchBuilder\Tests\Indices\Users;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Alirzaj\ElasticsearchBuilder\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

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

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('database.connections.mysql.database', 'elastic-builder');
        $app['config']->set('database.connections.mysql.username', 'root');
        $app['config']->set('database.connections.mysql.password', '');

        $app['config']->set('elasticsearch.indices', [
            Blog::class => Blogs::class,
            User::class => Users::class,
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . DIRECTORY_SEPARATOR . 'Migrations');
    }
}
