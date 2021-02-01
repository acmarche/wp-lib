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

    public function getOffres(string $categorie = '', string $tbl = 'xmlcomplet')
    {
        try {
            $request = $this->httpClient->request(
                'GET',
                $this->url,
                [
                    'query' => [
                        'tbl'    => $tbl,
                        //  'reg_id' => Hades::PAYS,
                        'com_id' => Hades::COMMUNE,
                        'cat_id' => $categorie,
                        //   'from_datetime'=>'2020-06-26%2012:27:00'
                    ],
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
                return $this->getOffres(join(',', Hades::EVENEMENTS));
            }
        );
     //   echo($t);
        return $t;
    }
}
