<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;
use Illuminate\Support\Str;

it('will index a model when it is created', function () {
    $client = \Pest\Laravel\spy(Client::class);

    $blog = Blog::query()->create([
        'title' => Str::random(),
        'description' => Str::random(),
        'text' => Str::random(),
    ]);

    $client
        ->shouldHaveReceived('index')
        ->once()
        ->with([
            'index' => 'blogs',
            'id' => $blog->id,
            'body' => [
                'title' => $blog->title,
                'text' => $blog->text,
                'description' => $blog->description,
            ],
            'refresh' => true,
        ]);
});

it('will update the document that belongs to a model after its updated', function () {
    $client = \Pest\Laravel\spy(Client::class);

    $blog = Blog::query()->create([
        'title' => Str::random(),
        'description' => Str::random(),
        'text' => Str::random(),
    ]);

    $blog->update($updateAttributes = [
        'title' => Str::random(),
        'description' => Str::random()
    ]);

    $client
        ->shouldHaveReceived('update')
        ->once()
        ->with([
            'index' => 'blogs',
            'refresh' => true,
            'id' => $blog->id,
            'retry_on_conflict' => config('elasticsearch.retry_on_conflict'),
            'body' => [
                'doc' => $updateAttributes + ['text' => $blog->text]
            ]
        ]);
});
