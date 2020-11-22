<?php


namespace AcMarche\Pivot;

use AcMarche\Common\Cache;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PivotRemoteRepository
{
    use ConnectionTrait;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct()
    {
        $this->connect();
        $this->cache = Cache::instance();
    }

    public function getAllOffers(int $detailLvl = Pivot::QUERY_DETAIL_LVL_DEFAULT)
    {
        if (is_readable(ABSPATH.'/data.json')) {
            return file_get_contents(ABSPATH.'/data.json');
        }

        $urlDetail = 'https://pivotweb.tourismewallonie.be/PivotWeb-3.1/query/'.$this->code.';content='.$detailLvl;
        $response  = $this->httpClient->request(
            'GET',
            $urlDetail,
        );
        $content   = $response->getContent();

        //$httpLogs = $response->getInfo('debug');

        return $content;
    }

    public function getDetailOffer(string $offer)
    {
        var_dump($offer);

        $url = $this->url.'/offer/'.$offer;
        try {
            $request = $this->httpClient->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'WS_KEY' => $this->clef,
                        'Accept' => 'application/json',
                    ],
                    'query'   => [
                        //   'fmt'     => Hades::FORMAT_JSON,
                        //   'content' => Hades::CONTENT_OFFRE_DEFAULT,
                    ],
                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        try {
            var_dump($request->getHeaders());

            return $content = $request->getContent();
        } catch (ClientExceptionInterface $e) {
            return $e->getMessage();
        } catch (RedirectionExceptionInterface $e) {
            return $e->getMessage();
        } catch (ServerExceptionInterface $e) {
            return $e->getMessage();
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    public function getImages(string $offer)
    {
        ///img/ALD-01-00096Z
    }

    public function getPictogrammes(string $picto, ?string $color)
    {
        ///thesaurus/img;h=48
        ///img/urn:val:class:3star;c=FF0000
        //3 etoiles
    }

    public function getThesaurus()
    {
        ///thesaurus/typeofr;fmt=json
    }

    public function getFields(string $typeOffre)
    {
        ///thesaurus/typeofr/01;pretty=true;fmt=json
    }

    public function getOneField(string $field)
    {
        ///thesaurus/urn/urn:fld:nomofr;fmt=json;pretty=true
    }
}
