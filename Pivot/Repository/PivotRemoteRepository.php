<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Pivot\ConnectionTrait;
use AcMarche\Pivot\Entity\Event;
use AcMarche\Pivot\Pivot;
use AcMarche\Pivot\PivotType;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * http://w3.ftlb.be/wiki/index.php/Flux
 * Class PivotRemoteRepository
 * @package AcMarche\Pivot\Repository
 */
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
        if (is_readable(ABSPATH.'var/data.json')) {
            return file_get_contents(ABSPATH.'var/data.json');
        }

        $urlDetail = $this->url.'/query/'.$this->code.';content='.$detailLvl;
        $response  = $this->httpClient->request(
            'GET',
            $urlDetail,
        );
        $content   = $response->getContent();

        file_put_contents(ABSPATH.'var/data.json', $content);

        return $content;
    }

    /**
     * @param string $codeCgt
     * info: label
     *
     * @return string
     */
    public function search(string $codeCgt, int $detailLvl = Pivot::OFFER_DETAIL_LVL_DEFAULT)
    {
        $url = $this->url.'/query/';
        try {
            $request = $this->httpClient->request(
                'POST',
                $url,
                [

                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        try {
            //$httpLogs = $request->getInfo('debug');

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

    /**
     * @param string $codeCgt
     * info: label
     *
     * @return string
     */
    public function getDetailOffer(string $codeCgt, int $detailLvl = Pivot::OFFER_DETAIL_LVL_DEFAULT)
    {
        $url = $this->url.'/offer/'.$codeCgt;
        try {
            $request = $this->httpClient->request(
                'GET',
                $url,
                [

                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        try {
            $httpLogs = $request->getInfo('debug');
            var_dump($httpLogs);

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

    /**
     * @return Event[]
     */
    public function getAllEvents(): array
    {
        return $this->cache->get(
            Cache::AGENDA_FULL.time(),//todo remove time
            function () {
                $data   = json_decode($this->getAllOffers(Pivot::QUERY_DETAIL_LVL_LIES));
                $count  = $data->count;
                $offers = $data->offre;
                $events = [];

                foreach ($offers as $offer) {
                    if ($offer instanceof \stdClass) {
                        $type = $offer->typeOffre;
                        if ($type->idTypeOffre === PivotType::TYPE_EVENEMENT) {
                            $event    = new Event();
                            $events[] = $event->createFromStd($offer);
                        }
                    }
                }

                return $events;
            }
        );
    }

    public function getImages(string $offer)
    {
        ///img/ALD-01-00096Z
        $url = $this->url.'/img/'.$offer;
        try {
            $request = $this->httpClient->request(
                'POST',
                $url,
                [

                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        try {
            $httpLogs = $request->getInfo('debug');
            var_dump($httpLogs);

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

    public function getPictogrammes(string $picto, ?string $color)
    {
        ///thesaurus/img;h=48
        ///img/urn:val:class:3star;c=FF0000
        //3 etoiles
    }

    /**
     * La liste des types d’offres existants est disponible dans le thésaurus
     */
    public function getTypes()
    {
        $url = $this->url.'/thesaurus/typeofr;fmt=json';
        try {
            $request = $this->httpClient->request(
                'GET',
                $url,
                [

                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        return $content = $request->getContent();
    }

    /**
     * https://pivotweb.tourismewallonie.be/PivotWeb-3.1/thesaurus/typeofr/9;fmt=json;pretty=true
     * @param int $numberType
     *
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getFields(int $numberType): string
    {
        $url = $this->url.'/thesaurus/typeofr/'.$numberType.';fmt=json';
        try {
            $request = $this->httpClient->request(
                'GET',
                $url,
                [

                ]
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        return $content = $request->getContent();
    }

    public function getOneField(string $field)
    {
        //https://pivotweb.tourismewallonie.be/PivotWeb-3.1/thesaurus/urn/urn:fld:nomofr;fmt=json;pretty=true
    }
}
