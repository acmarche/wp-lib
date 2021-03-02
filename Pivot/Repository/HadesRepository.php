<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Entities\Logement;
use AcMarche\Pivot\Entities\Restauration;
use AcMarche\Pivot\Event\Entity\Event;
use AcMarche\Pivot\Event\EventUtils;
use AcMarche\Pivot\Hades;
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
        $this->cache = Cache::instance();
    }

    /**
     * @return array|Event[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEvents(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::EVENEMENTS) : $types;

        return $this->cache->get(
            'events_hades'.time(),
            function () use ($types) {
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffres($types));
                $data = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                $events = [];
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        $event = Event::createFromDom($offre);
                        if ($event) {
                            $events[] = $event;
                            // dump($event->titre);
                            //  foreach ($event->dates() as $date) {
                            //    dump($date);
                            // }
                        }
                    }
                }
                $events = EventUtils::sortEvents($events);

                return $events;
            }
        );
    }

    public function getHebergements(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::HEBERGEMENTS) : $types;

        return $this->cache->get(
            'hebergement_hades'.time(),
            function () use ($types) {
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffres($types));
                $data = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                $hebergements = [];
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        $hotel = Logement::createFromDom($offre);
                        // dump($hotel);
                        $hebergements[] = $hotel;
                    }
                }

                return $hebergements;
            }
        );
    }

    public function getRestaurations(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::RESTAURATIONS) : $types;

        return $this->cache->get(
            'restau_hades'.time(),
            function () use ($types) {
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffres($types));
                $data = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                $restaurations = [];
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        $resto = Restauration::createFromDom($offre);
                        // dump($hotel);
                        $restaurations[] = $resto;
                    }
                }

                return $restaurations;
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
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffreById($id));
                $data = $domdoc->getElementsByTagName('offres');
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

    public function getEventRelations(Event $event): ?array
    {
        $events = $this->getEvents();
        $recommandations = [];

        foreach ($event->categories as $category) {

            foreach ($events as $element) {
                foreach ($element->categories as $category2) {
                    if ($category->lib == $category2->lib && $event->id != $element->id) {

                        $image = null;
                        $images = $element->medias;
                        if (count($images) > 0) {
                            $image = $images[0]->url;
                        }

                        $recommandations[] = [
                            'title' => $element->titre,
                            'url' => $element->url,
                            'image' => $image,
                        ];
                    }
                }
            }
        }

        return $recommandations;
    }
}
