<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Event\Entity\Event;
use AcMarche\Pivot\Event\EventUtils;
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
                        $event = Event::createFromDom($offre);
                        if ($event) {
                            $events[] = $event;
                        }
                    }
                }
                $events = EventUtils::sortEvents($events);

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

    public function getEvent(string $id): ?Event
    {
        return $this->cache->get(
            'event_hades-'.$id.time(),
            function () use ($id) {
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getEvent($id));
                $data   = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        return Event::createFromDom($offre);
                    }
                }

                return null;
            }
        );

    }

    public function getEvent2($codeCgt): ?array
    {
        $events = $this->getEvents();
        $event  = null;
        foreach ($events as $element) {
            if ($codeCgt == $element->reference) {
                $event = $element;
                break;
            }
        }

        return $event;
    }
}
