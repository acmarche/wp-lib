<?php

namespace AcMarche\Elasticsearch\Data;

use AcMarche\Bottin\Bottin;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Common\MarcheConst;
use AcMarche\Common\WpRepository;
use AcMarche\Theme\Inc\Router;

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

    public function getCategoriesBySite(int $siteId)
    {
        switch_to_blog($siteId);

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
            $data = [];

            $name                      = Cleaner::cleandata($category->name);
            $data['name']              = $name;
            $data['post_autocomplete'] = $name;

            $description = '';
            if ($category->description) {
                $description = Cleaner::cleandata($category->description);
            }

            $content = $description;

            $cat_ID     = $category->cat_ID;
            $data['id'] = $cat_ID;

            foreach ($this->getPosts($siteId, $cat_ID) as $post) {
                $content .= $post['name'];
                $content .= $post['excerpt'];
                $content .= $post['content'];
            }

            $content .= $this->getFiches($data);

            $data['content'] = $content;

            $category_nicename = $category->category_nicename;
            $data['post_name'] = $category_nicename;

            $url         = get_category_link($cat_ID);
            $data['url'] = $url;

            $date              = $today->format('Y-m-d');
            $data['post_date'] = $date;
            $data['date']      = $date;

            $guid = "cat-".$siteId.'-'.$cat_ID;

            $data['blog'] = $siteId;

            $data['excerpt'] = '';
            $data['guid']    = $guid;

            $data['type'] = "category"; //force

            $datas[] = $data;
        }

        return $datas;
    }

    public function getPosts($blogId, int $categoryId = null)
    {
        switch_to_blog($blogId);

        $args = array(
            'numberposts'      => 5000,
            'offset'           => 0,
            'category'         => 0,
            'orderby'          => 'post_title',
            'order'            => 'ASC',
            'include'          => array(),
            'exclude'          => array(),
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => array('post'),
            'suppress_filters' => true,
            'post_status'      => 'publish',
        );

        if ($categoryId) {
            $args ['category'] = $categoryId;
        }

        $posts = get_posts($args);
        $datas = [];

        foreach ($posts as $post) {
            $datas[] = $this->extractData($post, $blogId);
        }

        if ($blogId == MarcheConst::ADMINISTRATION) {
            $pages = $this->getPages($blogId);
            $datas = array_merge($datas, $pages);
        }

        return $datas;
    }

    private function getPages(int $blogId)
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
            $datas[] = $this->extractData($post, $blogId);
        }

        return $datas;
    }

    private function extractData(\WP_Post $post, int $blogId)
    {
        $data              = [];
        $data['id']        = $post->ID;
        $post_title        = Cleaner::cleandata($post->post_title);
        $data['post_type'] = 'post';

        $data['name']              = $post_title;
        $data['name2']             = $post_title;
        $data['post_suggest']      = Cleaner::cleandata($post_title);
        $data['post_autocomplete'] = Cleaner::cleandata($post_title);

        $data['excerpt'] = Cleaner::cleandata($post->post_excerpt);

        $data['content'] = Cleaner::cleandata($post->post_content);

        $data['post_mime_type'] = $post->post_mime_type;
        $data['guid']           = $post->guid;

        $data['post_name']   = $post->post_name;
        $data['post_status'] = $post->post_status;

        $data['url'] = get_permalink($post->ID);

        $categories = array();
        $i          = 0;
        foreach (get_the_category($post->ID) as $category) {
            $categories[$i]['cat_name']              = $category->cat_name;
            $categories[$i]['cat_name_autocomplete'] = $category->cat_name;
            $categories[$i]['cat_description']       = $category->cat_description;
            $i++;
        }

        $data['categories'] = $categories;
        $data['blog']       = $blogId;
        list($date, $time) = explode(" ", $post->post_date);
        $data['post_date'] = $date;

        return $data;
    }

    public function getFiches(array $category): string
    {
        $categoryBottinId = get_term_meta($category['id'], \BottinCategoryMetaBox::KEY_NAME, true);
        if ($categoryBottinId) {
            $fiches = $this->bottinRepository->getFichesByCategory($categoryBottinId);

            return $this->bottinData->getContentForCategory($fiches);
        }

        return '';
    }

    public function getAllfiches(): array
    {
        $fiches = $this->bottinRepository->getFiches();
        foreach ($fiches as $fiche) {
            $fiche->name    = $fiche->societe;
            $fiche->name2   = $fiche->societe;
            $fiche->excerpt = Bottin::getExcerpt($fiche);
            $fiche->url     = Router::getUrlFicheBottin($fiche);
            $fiche->content = $this->bottinData->getContentFiche($fiche);
            $fiche->url_cap = $this->bottinData->generateUrlCapFiche($fiche);
        }

        return $fiches;
    }

    public function getAllCategoriesBottin()
    {
        $categories = $this->bottinRepository->getAllCategories();
        foreach ($categories as $category) {
            $category->name2   = $category->name;
            $category->url     = Router::getUrlCategoryBottin($category);
            $category->url_cap = $this->bottinData->generateUrlCapCategorie($category);
            $category->excerpt = $category->description;
            $fiches            = $this->bottinRepository->getFichesByCategory($category->id);
            $category->content = $this->bottinData->getContentForCategory($fiches);
        }

        return $categories;
    }
}
