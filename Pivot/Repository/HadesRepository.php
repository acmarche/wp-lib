<?php


namespace AcMarche\Pivot\Repository;

use AcMarche\Common\Cache;
use AcMarche\Common\Mailer;
use AcMarche\Pivot\Entities\Offre;
use AcMarche\Pivot\Entities\OffreInterface;
use AcMarche\Pivot\Event\EventUtils;
use AcMarche\Pivot\Hades;
use DOMDocument;
use Exception;
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

    public function getOffres(array $types = []): array
    {
        $xmlString = $this->hadesRemoteRepository->getOffres($types);
        if ($xmlString == null) {
            return [];
        }
        $domdoc = $this->loadXml($xmlString);
        if ($domdoc === null) {
            return [];
        }
        $data      = $domdoc->getElementsByTagName('offres');
        $offresXml = $data->item(0);
        $offres    = [];

        foreach ($offresXml->childNodes as $offre) {
            if ($offre->nodeType == XML_ELEMENT_NODE) {
                $offres[] = Offre::createFromDom($offre, $domdoc);
            }
        }

        return $offres;
    }

    /**
     * @param array $types
     *
     * @return OffreInterface[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEvents(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::EVENEMENTS) : $types;

        return $this->cache->get(
            'events_hades',
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
            'hebergement_hades',
            function () use ($types) {
                return $this->getOffres($types);
            }
        );
    }

    public function getRestaurations(array $types = []): array
    {
        $types = count($types) === 0 ? array_keys(Hades::RESTAURATIONS) : $types;

        return $this->cache->get(
            'resto_hades',
            function () use ($types) {
                return $this->getOffres($types);
            }
        );
    }

    /**
     * @param string $xmlString
     *
     * @return DOMDocument|null
     */
    public function loadXml(string $xmlString): ?DOMDocument
    {
        try {
            libxml_use_internal_errors(true);
            $domdoc = new DOMDocument();
            $domdoc->loadXML($xmlString);
            $errors = libxml_get_errors();

            libxml_clear_errors();
            if (count($errors) > 0) {
                $stingError = '';
                foreach ($errors as $error) {
                    if ($error->level != LIBXML_ERR_WARNING) {
                        $stingError .= $error->message;
                    }
                }
                Mailer::sendError('xml error hades', 'error: '.$stingError.'contenu: '.$xmlString);

                return null;
            }

            return $domdoc;
        } catch (Exception $exception) {
            Mailer::sendError('Erreur avec le xml hades', $exception->getMessage());

            return null;
        }
    }

    public function getOffre(string $id): ?OffreInterface
    {
        return $this->cache->get(
            'offre_hades-'.$id,
            function () use ($id) {
                $xmlString = $this->hadesRemoteRepository->getOffreById($id);
                $domdoc    = $this->loadXml($xmlString);
                if ($domdoc === null) {
                    return null;
                }
                if ($xmlString === null) {
                    return null;
                }
                if ($domdoc === null) {
                    return null;
                }
                $data      = $domdoc->getElementsByTagName('offres');
                $offresXml = $data->item(0);

                foreach ($offresXml->childNodes as $offre) {
                    if ($offre->nodeType == XML_ELEMENT_NODE) {
                        return Offre::createFromDom($offre, $domdoc);
                    }
                }

                return null;
            }
        );
    }

    public function getOffresSameCategories(OffreInterface $offre, int $catId): ?array
    {
        $offres = $this->getOffres();
        /*array_map(
            function ($offre) use ($catId) {
                $offre->url = RouterHades::getUrlOffre($offre, $catId);
            },
            $offres
        );*/
        $recommandations = [];

        foreach ($offre->categories as $category) {
            foreach ($offres as $element) {
                foreach ($element->categories as $category2) {
                    if ($category->lib == $category2->lib && $offre->id != $element->id) {
                        $image  = null;
                        $images = $element->medias;
                        if (count($images) > 0) {
                            $image = $images[0]->url;
                        }
                        $recommandations[] = [
                            'title'      => $element->titre,
                            'url'        => $element->url,
                            'image'      => $image,
                            'categories' => $element->categories,
                        ];
                    }
                }
            }
        }

        return $recommandations;
    }
}
