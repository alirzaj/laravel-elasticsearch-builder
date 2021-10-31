<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Tag;
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
        'description' => Str::random(),
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
                'doc' => $updateAttributes + ['text' => $blog->text],
            ],
        ]);
});

it('can update an item of array field', function () {
    $client = \Pest\Laravel\spy(Client::class);

    $blog = Blog::query()->create([
        'title' => Str::random(),
        'description' => Str::random(),
        'text' => Str::random(),
    ]);

    $tag = $blog->tags()->create(['tag_name' => 'name of tag']);

    Tag::query()->find($tag->id)->update(['tag_name' => 'new tag name']);

    $client
        ->shouldHaveReceived('updateByQuery')
        ->once()
        ->with([
            'refresh' => true,
            'index' => 'blogs',
            'body' => [
                'script' => [
                    'source' => "
                        ctx._source.tags.remove(ctx._source.tags.indexOf(params.search));
                        ctx._source.tags.add(params.replace);
                        ",
                    'params' => [
                        'search' => 'name of tag',
                        'replace' => 'new tag name',
                    ],
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => ['tags' => 'name of tag'],
                        ],
                    ],
                ],
            ],
        ]);
});

it('can update remove an item of array field', function () {
    $client = \Pest\Laravel\spy(Client::class);

    $blog = Blog::query()->create([
        'title' => Str::random(),
        'description' => Str::random(),
        'text' => Str::random(),
    ]);

    $tag = $blog->tags()->create(['tag_name' => 'name of tag']);

    Tag::query()->find($tag->id)->delete();

    $client
        ->shouldHaveReceived('updateByQuery')
        ->once()
        ->with([
            'refresh' => true,
            'index' => 'blogs',
            'body' => [
                'script' => [
                    'source' => "ctx._source.tags.remove(ctx._source.tags.indexOf(params.search));",
                    'params' => [
                        'search' => 'name of tag',
                    ],
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => ['tags' => 'name of tag'],
                        ],
                    ],
                ],
            ],
        ]);
});
