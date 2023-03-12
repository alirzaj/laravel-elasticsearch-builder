<?php

namespace Alirzaj\ElasticsearchBuilder\Testing;

use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Constraint\LogicalNot as ReverseConstraint;

/** @mixin TestCase */

trait InteractsWithElasticsearch
{
    public function assertElasticsearchHas(string $indexName, int $id, array $data, bool $strict = true): TestCase
    {
        $this->assertThat(
            $indexName,
            new HasInElasticsearch($id, $data, $strict)
        );

        return $this;
    }

    protected function assertElasticsearchMissing(string $indexName, int $id, array $data): TestCase
    {
        $constraint = new ReverseConstraint(new HasInElasticsearch($id, $data, true));

        $this->assertThat($indexName, $constraint);

        return $this;
    }
}
