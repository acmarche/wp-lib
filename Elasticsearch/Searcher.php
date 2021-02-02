<?php


namespace AcMarche\Elasticsearch;

use Elastica\Exception\InvalidException;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\MultiMatch;
use Elastica\Query\SimpleQueryString;
use Elastica\ResultSet;

/**
 * https://github.com/ruflin/Elastica/tree/master/tests
 * Class Searcher
 * @package AcMarche\Elasticsearch
 */
class Searcher
{
    use ElasticClientTrait;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * @param string $keywords
     *
     * @return ResultSet
     * @throws  InvalidException
     */
    public function search2(string $keywords): ResultSet
    {
        $query               = new BoolQuery();
        $matchName           = new Match('name', $keywords);
        $matchContent        = new Match('content', $keywords);
        $matchExcerpt        = new Match('excerpt', $keywords);
        $matchCatName        = new Match('categories.cat_name', $keywords);
        $matchCatDescription = new Match('categories.cat_description', $keywords);
        $query->addShould($matchName)->setBoost(3);
        $query->addShould($matchExcerpt);
        $query->addShould($matchContent);
        $query->addShould($matchCatName);
        $query->addShould($matchCatDescription);

        $result = $this->index->search($query);

        return $result;
    }

    public function search(string $keywords): ResultSet
    {
        $query = new MultiMatch();
        $query->setFields(
            [
                'name^2',
                'title.autocomplete',
                'content',
                'excerpt',
                'categories.cat_name',
                'categories.cat_description',
            ]
        );
        $query->setQuery($keywords);
        $query->setType(MultiMatch::TYPE_MOST_FIELDS);

        $result = $this->index->search($query);

        return $result;
    }

    /**
     * https://camillehdl.dev/php-compose-elastica-queries/
     */
    public function search3()
    {
        $request       = [
            "title" => ["q" => "Apocalypse"],
            "genre" => ["q" => ["war", "horror"], "operator" => "OR"],
        ];
        $query         = new BoolQuery();
        $fullTextQuery = new SimpleQueryString(
            $request["title"]["q"]."*",
            [
                "title",
            ]
        );
        $fullTextQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);
        $fullTextQuery->setParam("analyze_wildcard", true);
        $query->addMust($fullTextQuery);
    }

    protected function createQuery(string $motclef)
    {
        $query = [
            "multi_match" => [
                "query"  => $motclef,
                "fields" => [
                    'post_title',
                    'name',
                    'content',
                    'description',
                    'post_title.stemmed',//trouve pluriels
                    'post_content',
                    'post_content.stemmed',//trouve pluriels
                    'post_excerpt',
                    'post_excerpt.stemmed',//trouve pluriels
                    'categories.cat_name',
                    'categories.cat_description',
                ],
            ],
        ];

        return $query;
    }
}
