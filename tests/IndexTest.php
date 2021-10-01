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
