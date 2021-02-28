<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\ConnectionHadesTrait;
use AcMarche\Pivot\Hades;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Cache\CacheInterface;

class HadesRemoteRepository
{
    use ConnectionHadesTrait;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct()
    {
        $this->connect();
        $this->cache = Cache::instance();
    }

    /**
     * http://w3.ftlb.be/wiki/index.php/Flux
     * @param array $args
     * @param string $tbl
     *
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getOffres(array $args, string $tbl = 'xmlcomplet')
    {
        $args['tbl'] = $tbl;
        $args['com_id'] = Hades::COMMUNE;
        //  'reg_id' => Hades::PAYS,
        // 'cat_id' => $categorie,
        //   'from_datetime'=>'2020-06-26%2012:27:00'

        try {
            $request = $this->httpClient->request(
                'GET',
                $this->url,
                [
                    'query' => $args,
                ]
            );

            return $request->getContent();
        } catch (ClientException $exception) {
            Mailer::sendError('Erreur avec le xml hades', $exception->getMessage());
            throw  new \Exception($exception->getMessage());
        }
    }

    /**
     * http://w3.ftlb.be/webservice/h2o.php?com_id=263&tbl=xmlcomplet&cat_id=evt_sport,cine_club,conference,exposition,festival,fete_festiv,anim_jeux,livre_conte,manifestatio,foire_brocan,evt_promenad,spectacle,stage_ateli,evt_vis_guid
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function getEvents(): string
    {
        $t = $this->cache->get(
            'events_hades_remote'.time(),
            function () {
                return $this->getOffres(['cat_id' => join(',', array_keys(Hades::EVENEMENTS))]);
            }
        );

        //   echo($t);
        return $t;
    }

    /**
     * http://w3.ftlb.be/webservice/h2o.php?com_id=263&tbl=xmlcomplet&cat_id=evt_sport,cine_club,conference,exposition,festival,fete_festiv,anim_jeux,livre_conte,manifestatio,foire_brocan,evt_promenad,spectacle,stage_ateli,evt_vis_guid
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function getHebergements(array $types = []): string
    {
        $types = count($types) === 0 ? array_keys(Hades::LOGEMENTS) : $types;

        $t = $this->cache->get(
            'hebergements_hades_remote'.time(),
            function () use ($types) {
                return $this->getOffres(['cat_id' => join(',', $types)]);
            }
        );

        //  echo($t);
        return $t;
    }

    /**
     * http://w3.ftlb.be/webservice/h2o.php?tbl=xmlcomplet&off_id=84670
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function getOffreById(string $id): string
    {
        $t = $this->cache->get(
            'events_hades_remote_'.$id.time(),
            function () use ($id) {
                return $this->getOffres(['off_id' => $id]);
            }
        );

        //  echo($t);
        return $t;
    }
}
