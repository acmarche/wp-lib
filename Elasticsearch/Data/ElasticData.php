<?php

namespace AcMarche\Elasticsearch\Data;

use AcMarche\Bottin\Bottin;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\RouterBottin;
use AcMarche\Common\Mailer;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\WpRepository;
use BottinCategoryMetaBox;
use WP_Post;

class ElasticData
{
    /**
     * @var BottinRepository
     */
    private $bottinRepository;
    /**
     * @var WpRepository
     */
    private $wpRepository;
    /**
     * @var ElasticBottinData
     */
    private $bottinData;

    public function __construct()
    {
        $this->bottinRepository = new BottinRepository();
        $this->wpRepository     = new WpRepository();
        $this->bottinData       = new ElasticBottinData();
    }

    /**
     * @param int $siteId
     *
     * @return DocumentElastic[]
     */
    public function getCategoriesBySite(): array
    {
        $args = array(
            'type'         => 'post',
            'child_of'     => 0,
            'parent'       => '',
            'orderby'      => 'name',
            'order'        => 'ASC',
            'hide_empty'   => 1,
            'hierarchical' => 1,
            'exclude'      => '',
            'include'      => '',
            'number'       => '',
            'taxonomy'     => 'category',
            'pad_counts'   => true,
        );

        $categories = get_categories($args);
        $datas      = [];
        $today      = new \DateTime();

        foreach ($categories as $category) {

            $description = '';
            if ($category->description) {
                $description = Cleaner::cleandata($category->description);
            }

            $date    = $today->format('Y-m-d');
            $content = $description;

            foreach ($this->getPosts($category->cat_ID) as $documentElastic) {
                $content .= $documentElastic->name;
                $content .= $documentElastic->excerpt;
                $content .= $documentElastic->content;
            }

            $content .= $this->getContentFiches($category);

            $children = $this->wpRepository->getChildrenOfCategory($category->cat_ID);
            $tags     = [];
            foreach ($children as $child) {
                $tags[] = $child->name;
            }
            $parent = $this->wpRepository->getParentCategory($category->cat_ID);
            if ($parent) {
                $tags[] = $parent->name;
            }

            $document          = new DocumentElastic();
            $document->id      = $category->cat_ID;
            $document->name    = Cleaner::cleandata($category->name);
            $document->excerpt = $description;
            $document->content = $content;
            $document->tags    = $tags;
            $document->date    = $date;
            $document->url     = get_category_link($category->cat_ID);

            $datas[] = $document;
        }

        return $datas;
    }

    /**
     * @param int|null $categoryId
     *
     * @return DocumentElastic[]
     */
    public function getPosts(int $categoryId = null): array
    {
        $args = array(
            'numberposts' => 5000,
            'orderby'     => 'post_title',
            'order'       => 'ASC',
            'post_status' => 'publish',
        );

        if ($categoryId) {
            $args ['category'] = $categoryId;
        }

        $posts = get_posts($args);
        $datas = [];

        foreach ($posts as $post) {
            if ($document = $this->postToDocumentElastic($post)) {
                $datas[] = $document;
            } else {
                Mailer::sendError(
                    "update elastic error ",
                    "create document ".$post->post_title
                );
                //  var_dump($post);
            }
        }

        return $datas;
    }

    public function postToDocumentElastic(WP_Post $post): ?DocumentElastic
    {
        try {
            return $this->createDocumentElastic($post);
        } catch (\Exception $exception) {
            Mailer::sendError("update elastic", "create document ".$post->post_title.' => '.$exception->getMessage());
        }

        return null;
    }

    /**
     * @return DocumentElastic[]
     */
    public function indexPagesSpecial(): array
    {
        switch_to_blog(Theme::ADMINISTRATION);

        return $this->getPages();
    }

    /**
     * @return DocumentElastic[]
     */
    private function getPages(): array
    {
        $args  = array(
            'sort_order'   => 'asc',
            'sort_column'  => 'post_title',
            'hierarchical' => 1,
            'exclude'      => '',
            'include'      => '',
            'meta_key'     => '',
            'meta_value'   => '',
            'authors'      => '',
            'child_of'     => 0,
            'parent'       => -1,
            'exclude_tree' => '',
            'number'       => '',
            'offset'       => 0,
            'post_type'    => 'page',
            'post_status'  => 'publish',
        );
        $pages = get_pages($args);

        $datas = [];

        foreach ($pages as $post) {
            $datas[] = $this->createDocumentElastic($post);
        }

        return $datas;
    }

    private function createDocumentElastic(WP_Post $post): DocumentElastic
    {
        list($date, $time) = explode(" ", $post->post_date);
        $categories = array();
        foreach (get_the_category($post->ID) as $category) {
            $categories[] = $category->cat_name;
        }

        $content = get_the_content(null, null, $post);
        $content = apply_filters('the_content', $content);

        $document          = new DocumentElastic();
        $document->id      = $post->ID;
        $document->name    = Cleaner::cleandata($post->post_title);
        $document->excerpt = Cleaner::cleandata($post->post_excerpt);
        $document->content = Cleaner::cleandata($content);
        $document->tags    = $categories;
        $document->date    = $date;
        $document->url     = get_permalink($post->ID);

        return $document;
    }

    public function getContentFiches(object $category): string
    {
        $categoryBottinId = get_term_meta($category->cat_ID, BottinCategoryMetaBox::KEY_NAME, true);

        if ($categoryBottinId) {
            $fiches = $this->bottinRepository->getFichesByCategory($categoryBottinId);

            return $this->bottinData->getContentForCategory($fiches);
        }

        return '';
    }

    /**
     * @return DocumentElastic[]
     * @throws \Exception
     */
    public function getAllfiches(): array
    {
        $fiches    = $this->bottinRepository->getFiches();
        $documents = [];
        foreach ($fiches as $fiche) {

            $categories = $this->bottinData->getCategoriesFiche($fiche);

            $document          = new DocumentElastic();
            $document->id      = $fiche->id;
            $document->name    = $fiche->societe;
            $document->excerpt = Bottin::getExcerpt($fiche);
            $document->content = $this->bottinData->getContentFiche($fiche);
            $document->tags    = $categories;
            list($date, $heure) = explode(' ', $fiche->created_at);
            $document->date = $date;
            $document->url  = RouterBottin::getUrlFicheBottin($fiche);
            //  $document->url     = $this->bottinData->generateUrlCapFiche($fiche);
            $documents[] = $document;
        }

        return $documents;
    }

    /**
     * @return DocumentElastic[]
     *
     * @throws \Exception
     */
    public function getAllCategoriesBottin(): array
    {
        $data       = $this->bottinRepository->getAllCategories();
        $categories = [];
        foreach ($data as $category) {
            $document          = new DocumentElastic();
            $document->id      = $category->id;
            $document->name    = $category->name;
            $document->excerpt = $category->description;
            $document->tags    = [];//todo
            $document->date    = $category->created_at;
            $document->url     = RouterBottin::getUrlCategoryBottin($category);
            //$category->url = $this->bottinData->generateUrlCapCategorie($category);
            $fiches            = $this->bottinRepository->getFichesByCategory($category->id);
            $document->content = $this->bottinData->getContentForCategory($fiches);
        }

        return $categories;
    }
}
