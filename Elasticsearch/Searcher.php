<?php


namespace AcMarche\Elasticsearch;

use Elastica\Exception\InvalidException;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\SimpleQueryString;
use Elastica\ResultSet;

class Searcher
{
    use ElasticClientTrait;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * @param string $query
     *
     * @return ResultSet
     * @throws  InvalidException
     */
    public function search(string $query): ResultSet
    {
        $result = $this->index->search(new Match('name', $query));

        return $result;
    }

    /**
     * https://camillehdl.dev/php-compose-elastica-queries/
     */
    public function search2()
    {
        $request       = [
            "title" => ["q" => "Apocalypse"],
            "genre" => ["q" => ["war", "horror"], "operator" => "OR"],
        ];
        $query         = new BoolQuery();
        $fullTextQuery = new SimpleQueryString(
            $request["title"]["q"]."*", [
                "title",
            ]
        );
        $fullTextQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);
        $fullTextQuery->setParam("analyze_wildcard", true);
        $query->addMust($fullTextQuery);
    }
}