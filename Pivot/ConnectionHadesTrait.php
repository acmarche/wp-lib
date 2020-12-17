<?php


namespace AcMarche\Pivot;

use AcMarche\Common\Env;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait ConnectionHadesTrait
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
        $this->url = $_ENV['HADES_URL'];
        $user      = $_ENV['HADES_USER'];
        $password  = $_ENV['HADES_PASSWORD'];

        $options   = [
            'auth_basic' => [$user, $password],
        ];

        $this->httpClient = HttpClient::create($options);
    }
}
