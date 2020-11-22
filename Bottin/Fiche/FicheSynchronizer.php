<?php


namespace AcMarche\Bottin\Fiche;


use AcMarche\Bottin\Bottin;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\Repository\WpRepository;
use AcMarche\Common\MarcheConst;

class FicheSynchronizer
{
    /**
     * @var FicheFactory
     */
    private $ficheFactory;
    /**
     * @var WpRepository
     */
    private $wpRepository;
    /**
     * @var BottinRepository
     */
    private $bottinRepository;

    public function __construct()
    {
        $this->bottinRepository = new BottinRepository();
        $this->wpRepository = new WpRepository();
        $this->ficheFactory = new FicheFactory();
    }

    /**
     * @param \stdClass $fiche
     * @param int[] $classementsCategoriesId tout les ids des categories du bottin
     * @param \WP_Term[] $categoriesWp les catégories wp avec une référence du bottin
     * @return array[]
     */
    public function synchronize(\stdClass $fiche, array $classementsCategoriesId, array $categoriesWp): array
    {
        $categoriesWpPresente = [];
        $cats = [];
        foreach ($categoriesWp as $category) {
            if (in_array($category->bottinId, $classementsCategoriesId)) {
                $categoriesWpPresente[] = $category->cat_ID;
                $cats[] = get_cat_name($category->cat_ID);
            }
        }

        if (count($categoriesWpPresente) == 0) {
            return ['classements' => []];
        }

        $result = $this->ficheFactory->create_post($fiche, $categoriesWpPresente);
        $result['classements'] = $cats;

        return $result;
    }

    /**
     * @param \WP_Post $post
     * @throws \Exception
     */
    public function deleteFiche(\WP_Post $post): void
    {
        if (!$result = wp_delete_post($post->ID, true)) {
            throw new \Exception('pas su supprimer sur marche.be');
        } else {
            $this->wpRepository->deleteMetaData($post->ID);
        }
    }

    public function deleteOldFiches()
    {
        $fiches = $this->bottinRepository->getFiches();
        $idsBottin = array_map(
            function ($obj) {
                return (int)$obj['id'];
            },
            $fiches
        );

        if (count($idsBottin) > 0) {
            foreach (MarcheConst::SITES as $site) {
                switch_to_blog($site);
                $postsId = $this->wpRepository->getFichesWpToDelete($idsBottin);
                foreach ($postsId as $postId) {
                    $post = get_post($postId);
                    if ($post) {
                        var_dump($post->post_title);
                    }
                }
            }
        }
    }

    /**
     * Retourne les ids des catégories des classements de la fiche
     * @param \stdClass $fiche
     * @return array
     */
    public function getCategoriesIds(\stdClass $fiche): array
    {
        $classements = $this->bottinRepository->getClassementsFiche($fiche->id);

        return array_map(
            function ($classement) {
                return $classement['category_id'];
            },
            $classements
        );
    }

}
