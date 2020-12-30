<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Entity\Event;
use Symfony\Contracts\Cache\CacheInterface;

class HadesRepository
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var HadesRemoteRepository
     */
    private $hadesRemoteRepository;

    public function __construct()
    {
        $this->hadesRemoteRepository = new HadesRemoteRepository();
        $this->cache                 = Cache::instance();
    }

    /**
     * @return array|Event[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEvents(): array
    {
        return $this->cache->get(
            'events_hades'.time(),
            function () {
                $data = $this->decodeXml($this->hadesRemoteRepository->getEvents());
                foreach ($data->offres as $offres) {
                    $events = [];
                    foreach ($offres as $offre) {
                        $event = Event::createFromStd($offre);
                        if ($event  !== null) {
                            $events[] = $event;
                        }
                    }
                }

                return $events;
            }
        );
    }

    /**
     * @param string $xmlString
     *
     * @return array|\Exception
     */
    private function decodeXml(string $xmlString): object
    {
        try {
            $xml   = simplexml_load_string($xmlString);
            $json  = json_encode($xml);
            $array = json_decode($json);

            return $array;
        } catch (\Exception $exception) {
            Mailer::sendError('Erreur avec le xml hades event', $exception->getMessage());

            return new \Exception('Erreur avec le xml');
        }
    }
}
