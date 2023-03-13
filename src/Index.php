<?php

namespace Alirzaj\ElasticsearchBuilder;

class Index
{
    /**
     * name of the index
     */
    public string $name;

    /**
     * properties (columns) and their types
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/explicit-mapping.html
     * 'title' => 'text' means that title field in the index will have text type
     *
     */
    public array $propertyTypes = [];

    /**
     * determine which field should use which analyzer
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analyzer.html
     *
     */
    public array $propertyAnalyzers = [];

    /**
     * determine which field should use which analyzer for the incoming search query
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-analyzer.html
     *
     */
    public array $searchAnalyzers = [];


    /**
     * other definitions of a field
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     *
     */
    public array $fields = [];

    /**
     * determine format of date fields
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-date-format.html
     *
     */
    public array $dateFormats = [];

    /**
     * define custom normalizers for index
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-normalizers.html#_custom_normalizers
     *
     * e.g: 'full_name_normalizer' => [
     *           'type' => 'custom',
     *           'char_filter' => ['special_character_strip'],
     *           'filter' => ['lowercase', 'asciifolding', 'persian_normalization']
     *      ]
     *
     */
    public array $normalizers = [];

    /**
     * define and config analyzers for index
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-custom-analyzer.html
     *
     */
    public array $analyzers = [];

    /**
     * define & config tokenizers for index
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-custom-analyzer.html
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-tokenizers.html
     *
     */
    public array $tokenizers = [];

    /**
     * determine which field should use which normalizer
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-normalizers.html
     *
     * e.g: title => 'lowercase'
     *
     */
    public array $propertyNormalizers = [];

    /**
     * define custom character filters for the index
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-pattern-replace-charfilter.html
     *
     */
    public array $characterFilters = [];

    /**
     * define custom token filters for the index
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-tokenfilters.html
     *
     */
    public array $tokenFilters = [];

    public function getName(): string
    {
        return $this->name ?? strtolower(class_basename($this));
    }
}
