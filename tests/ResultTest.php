<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

it('can get results of a query as collection', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => $blog1 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                    ],
                    [
                        '_source' => $blog2 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                    ],
                    [
                        '_source' => $blog3 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                    ],
                ],
            ],
        ]);

    $results = Blog::elasticsearchQuery()->match('title', 'ttt')->get();

    expect($results)
        ->toBeCollection()
        ->toHaveCount(3)
        ->sequence($blog1, $blog2, $blog3);
});

it('can hydrate related model when retrieving results', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => $blog1 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                        '_index' => 'blogs',
                    ],
                    [
                        '_id' => 2,
                        '_source' => $blog2 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                        '_index' => 'blogs',
                    ],
                    [
                        '_id' => 3,
                        '_source' => $blog3 = [
                            'title' => Str::random(),
                            'description' => Str::random(),
                            'text' => Str::random(),
                        ],
                        '_index' => 'blogs',
                    ],
                ],
            ],
        ]);

    $results = Blog::elasticsearchQuery()->match('title', 'ttt')->hydrate();

    expect($results)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3)
        ->sequence(
            fn ($value) => $value
                ->id->toEqual(1)
                ->title->toEqual($blog1['title'])
                ->description->toEqual($blog1['description'])
                ->text->toEqual($blog1['text'])
                ->exists->toBeTrue(),
            fn ($value) => $value
                ->id->toEqual(2)
                ->title->toEqual($blog2['title'])
                ->description->toEqual($blog2['description'])
                ->text->toEqual($blog2['text'])
                ->exists->toBeTrue(),
            fn ($value) => $value
                ->id->toEqual(3)
                ->title->toEqual($blog3['title'])
                ->description->toEqual($blog3['description'])
                ->text->toEqual($blog3['text'])
                ->exists->toBeTrue(),
        );
});
