<?php

namespace Alirzaj\ElasticsearchBuilder;

use Alirzaj\ElasticsearchBuilder\Commands\CreateIndices;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ElasticsearchBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this
            ->registerCommands()
            ->bindClient();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/elasticsearch.php',
            'elasticsearch'
        );
    }

    public function boot()
    {
        $this->registerConfiguration();
    }

    private function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateIndices::class,
            ]);
        }

        return $this;
    }

    private function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../config/elasticsearch.php' => config_path('elasticsearch.php'),
        ]);
    }

    private function bindClient()
    {
        $this->app->bind(Client::class, function () {
            $client = ClientBuilder::create()->setHosts(
                Arr::wrap(config('elasticsearch.hosts'))
            );

            if ($this->app->environment('local', 'testing')) {
                $client->setLogger(
                    (new Logger('elasticsearch'))->pushHandler(
                        new StreamHandler(storage_path('logs/elastic.log'), Logger::DEBUG)
                    )
                );
            }

            return $client->build();
        });
    }
}
