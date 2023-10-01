<?php

namespace Alirzaj\ElasticsearchBuilder;

use Alirzaj\ElasticsearchBuilder\Commands\CreateIndices;
use Alirzaj\ElasticsearchBuilder\Commands\DeleteIndices;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
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

    private function bindClient()
    {
        $this->app->singleton(Client::class, function () {
            $hosts = Arr::wrap(config('elasticsearch.hosts'));

            $client = ClientBuilder::create()
                ->setSSLVerification(config('elasticsearch.ssl_verification'))
                ->setHosts(
                    array_map(
                        fn($host) => is_string($host)
                            ? $host
                            : $host['scheme'] . '://' . $host['host'] . ':' . $host['port'],
                        $hosts
                    )
                )
                ->setBasicAuthentication($hosts[0]['user'], $hosts[0]['pass']);

            if ($this->app->environment('local', 'testing')) {
                //TODO test
                $client->setLogger(
                    (new Logger('elasticsearch'))->pushHandler(
                        new StreamHandler(storage_path('logs/elastic.log'), Logger::DEBUG)
                    )
                );
            }

            return $client->build();
        });
    }

    private function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateIndices::class,
                DeleteIndices::class
            ]);
        }

        return $this;
    }

    public function boot()
    {
        $this->registerConfiguration();
    }

    private function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../config/elasticsearch.php' => config_path('elasticsearch.php'),
        ]);
    }
}
