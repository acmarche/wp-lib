<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Event\Entity\Event;
use DOMDocument;
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
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getEvents());
                $data   = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                $events = [];
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        $event = Event::createFromStd($offre);
                        // if (count($event['dates']) > 0) {
                        $events[] = $event;
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
    private function loadXml(string $xmlString): DOMDocument
    {
        try {
            $domdoc = new DOMDocument();
            $domdoc->loadXML($xmlString);

            return $domdoc;
        } catch (\Exception $exception) {
            Mailer::sendError('Erreur avec le xml hades event', $exception->getMessage());

            return new \Exception('Erreur avec le xml');
        }
    }

    public function getEvent($codeCgt): ?array
    {
        $events = $this->getEvents();
        $event = null;
        foreach ($events as $element) {
            if ($codeCgt == $element->reference) {
                $event = $element;
                break;
            }
        }

        return $event;
    }
}
