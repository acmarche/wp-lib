<?php


namespace AcMarche\Pivot;

use AcMarche\Common\Env;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait ConnectionTrait
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $clef;

    public function connect()
    {
        Env::loadEnv();
        $this->url  = $_ENV['PIVOT_URL'];
        $this->clef = $_ENV['PIVOT_CLEF'];
        $this->code = $_ENV['PIVOT_CODE'];

        $headers = [
            'headers' => [
                'WS_KEY' => $this->clef,
                'Accept' => Pivot::FORMAT_JSON,
            ],
        ];

        $this->httpClient = HttpClient::create($headers);
    }
}
