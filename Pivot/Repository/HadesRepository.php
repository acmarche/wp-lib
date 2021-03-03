<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Entities\Offre;
use AcMarche\Pivot\Entities\OffreInterface;
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

    public function getOffres(array $types = []): array
    {
        $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffres($types));
        $data = $domdoc->getElementsByTagName('offres');
        $offresXml = $data->item(0);
        $offres = [];
        foreach ($offresXml->childNodes as $offre) {
            if ($offre->nodeType == XML_ELEMENT_NODE) {
                $offres[] = Offre::createFromDom($offre);
            }
        }

        return $offres;
    }

    /**
     * @param array $types
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEvents(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::EVENEMENTS) : $types;

        return $this->cache->get(
            'events_hades'.time(),
            function () use ($types) {
                $events = [];
                $offres = $this->getOffres($types);
                foreach ($offres as $offre) {
                    EventUtils::sortDates($offre);
                    if (EventUtils::isEventObsolete($offre)) {
                        continue;
                    }
                    $events[] = $offre;
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
                return $this->getOffres($types);
            }
        );
    }

    public function getRestaurations(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::RESTAURATIONS) : $types;

        return $this->cache->get(
            'resto_hades'.time(),
            function () use ($types) {
                return $this->getOffres($types);
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

    public function getOffre(string $id): ?OffreInterface
    {
        return $this->cache->get(
            'offre_hades-'.$id.time(),
            function () use ($id) {
                $domdoc = $this->loadXml($this->hadesRemoteRepository->getOffreById($id));
                $data = $domdoc->getElementsByTagName('offres');
                $offres = $data->item(0);
                foreach ($offres->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        return Offre::createFromDom($offre);
                    }
                }

                return null;
            }
        );
    }

    public function getEventRelations(OffreInterface $offre): ?array
    {
        $events = $this->getEvents();
        $recommandations = [];

        foreach ($offre->categories as $category) {

            foreach ($events as $element) {
                foreach ($element->categories as $category2) {
                    if ($category->lib == $category2->lib && $offre->id != $element->id) {

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
