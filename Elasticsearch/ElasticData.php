<?php

namespace AcMarche\Elasticsearch;

use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\Repository\WpRepository;
use AcMarche\Common\MarcheConst;

class ElasticData
{
    /**
     * @var BottinRepository
     */
    private $bottinRepository;
    private $wpRepository;

    public function __construct()
    {
        $this->bottinRepository = new BottinRepository();
        $this->wpRepository     = new \AcMarche\Common\WpRepository();
    }

    public function getCategoriesOfBlog($blog)
    {
        switch_to_blog($blog);

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

        foreach ($categories as $category) {
            $count = $category->count;

            if ($count > 0) {
                $data = [];

                $name               = $category->name;
                $data['post_title'] = AcElasticUtil::cleandata($name);

                $data['post_autocomplete'] = AcElasticUtil::cleandata($name);

                $description = '';
                if ($category->description) {
                    $description = AcElasticUtil::cleandata($category->description);
                }

                $content = $description;

                $cat_ID          = $category->cat_ID;
                $data['post_ID'] = $cat_ID;

                foreach ($this->getPosts($blog, $cat_ID) as $post) {
                    $content .= $post['post_title'];
                    $content .= $post['post_excerpt'];
                    $content .= $post['post_content'];
                }

                $data['post_content'] = $content;

                $category_nicename = $category->category_nicename;
                $data['post_name'] = $category_nicename;

                $url               = get_category_link($cat_ID);
                $data['permalink'] = $url;

                $date              = '2019-06-05T18:34:36.731Z';
                $data['post_date'] = $date;
                $data['date']      = $date;

                $guid = "cat-".$blog.'-'.$cat_ID;

                $data['blog'] = $blog;

                $data['post_excerpt'] = '';
                $data['guid']         = $guid;

                $data['post_type'] = 'category';
                $data['type']      = "category"; //force

                $datas[] = $data;
            }
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
        $datas = array();

        date_default_timezone_set('Europe/Brussels');

        //  $posts = array();
        foreach ($posts as $post) {
            $ID              = $post->ID;
            $data['post_ID'] = $ID;

            $post_title = Cleaner::cleandata($post->post_title);

            $data['post_title']        = $post_title;
            $data['post_suggest']      = Cleaner::cleandata($post_title);
            $data['post_autocomplete'] = Cleaner::cleandata($post_title);

            $post_excerpt         = Cleaner::cleandata($post->post_excerpt);
            $data['post_excerpt'] = $post_excerpt;

            $post_type = $post->post_type;

            $data['type']      = "post"; //force
            $data['post_type'] = $post_type;

            switch ($post_type) {
                case 'bottin_fiche' :
                    $post_content = $this->getFicheBottin($post);
                    break;
                default:
                    $post_content = $post->post_content;
                    break;
            }

            $post_content         = Cleaner::cleandata($post_content);
            $data['post_content'] = $post_content;

            $post_mime_type         = $post->post_mime_type;
            $data['post_mime_type'] = $post_mime_type;
            $guid                   = $post->guid;
            $data['guid']           = $guid;

            $post_name         = $post->post_name;
            $data['post_name'] = $post_name;

            $post_status         = $post->post_status;
            $data['post_status'] = $post_status;

            $permalink         = get_permalink($ID);
            $data['permalink'] = $permalink;

            $categoriesTmp = get_the_category($ID);
            $categories    = array();
            $i             = 0;
            foreach ($categoriesTmp as $category) {
                $categories[$i]['cat_name']              = $category->cat_name;
                $categories[$i]['cat_name_autocomplete'] = $category->cat_name;
                $categories[$i]['cat_description']       = $category->cat_description;
                $i++;
            }

            $data['categories'] = $categories;
            $data['blog']       = $blogId;
            $data['post_date'] = $post->post_date;

            $datas[] = $data;
        }

        return $datas;
    }

    public function getPages($blog)
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

        $datas = array();

        date_default_timezone_set('Europe/Brussels');

        //  $posts = array();
        foreach ($pages as $post) {
            $ID              = $post->ID;
            $data['post_ID'] = $ID;

            $post_title = AcElasticUtil::cleandata($post->post_title);

            $data['post_title']        = $post_title;
            $data['post_suggest']      = AcElasticUtil::cleandata($post_title);
            $data['post_autocomplete'] = AcElasticUtil::cleandata($post_title);

            $post_excerpt         = AcElasticUtil::cleandata($post->post_excerpt);
            $data['post_excerpt'] = $post_excerpt;

            $post_type = $post->post_type;

            $data['type']      = "post"; //force
            $data['post_type'] = $post_type;

            switch ($post_type) {
                case 'bottin_fiche' :
                    $post_content = $this->getFicheBottin($post);
                    break;
                default:
                    $post_content = $post->post_content;
                    break;
            }

            $post_content         = AcElasticUtil::cleandata($post_content);
            $data['post_content'] = $post_content;

            $post_mime_type         = $post->post_mime_type;
            $data['post_mime_type'] = $post_mime_type;
            $guid                   = $post->guid;
            $data['guid']           = $guid;

            $post_name         = $post->post_name;
            $data['post_name'] = $post_name;

            $post_status         = $post->post_status;
            $data['post_status'] = $post_status;

            $permalink         = get_permalink($ID);
            $data['permalink'] = $permalink;

            $data['type'] = 'page';

            $categoriesTmp = get_the_category($ID);
            $categories    = array();
            $i             = 0;
            foreach ($categoriesTmp as $category) {
                $categories[$i]['cat_name']              = $category->cat_name;
                $categories[$i]['cat_name_autocomplete'] = $category->cat_name;
                $categories[$i]['cat_description']       = $category->cat_description;
                $i++;
            }
            $data['categories'] = $categories;

            $data['blog'] = $blog;

            $datas[] = $data;
        }

        return $datas;
    }

    public function getNowel($blog)
    {
        $datas = array();

        date_default_timezone_set('Europe/Brussels');

        $ID              = 9999999999;
        $data['post_ID'] = $ID;

        $post_title = AcElasticUtil::cleandata("Marché de Noël - SITE");

        $data['post_title']        = $post_title;
        $data['post_suggest']      = AcElasticUtil::cleandata($post_title);
        $data['post_autocomplete'] = AcElasticUtil::cleandata($post_title);

        $post_excerpt = AcElasticUtil::cleandata(
            "Outre le traditionnel Village de Noël et les illuminations, elle accueillera pour la deuxième fois une patinoire à glace, d’une surface de 230 mètres carrés et pour la première fois, un village des artisans."
        );

        $data['post_excerpt'] = $post_excerpt;

        $data['type'] = "post";

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
            'post_type'        => array('post', 'hades_logement', 'bottin_fiche', 'hades_event'),
            'suppress_filters' => true,
            'post_status'      => 'publish',
        );

        $posts   = get_posts($args);
        $content = "";

        foreach ($posts as $post) {
            $content .= $post->post_title." ".$post->post_content;
        }

        $post_content         = AcElasticUtil::cleandata($content);
        $data['post_content'] = $post_content;

        $data['post_mime_type'] = null;

        $data['guid'] = null;

        $post_name         = "marche_de_noel";
        $data['post_name'] = $post_name;

        $data['post_status'] = "publish";

        $data['permalink'] = "https://www.marche.be/noelamarche/";

        $data['type'] = 'post';

        $categories = array();

        $data['categories'] = $categories;

        $data['blog'] = $blog;

        $datas[] = $data;

        return $datas;
    }

    public function getFicheBottin($post): string
    {
        $content = '';
        $key     = WpRepository::DATA_TYPE;
        WpRepository::set_table_meta();

        $idfiche = get_metadata($key, $post->ID, 'id', true);
        $fiche   = $this->bottinRepository->getFiche($idfiche);

        $content .= ' '.$fiche->localite;
        $content .= ' '.$fiche->nom;
        $content .= ' '.$fiche->prenom;
        $content .= ' '.$fiche->email;
        $content .= ' '.$fiche->website;

        $content .= ' '.$fiche->contact_localite;
        $content .= ' '.$fiche->contact_email;

        $content .= ' '.$fiche->comment1;
        $content .= ' '.$fiche->comment2;
        $content .= ' '.$fiche->comment3;

        $content .= ' '.$fiche->facebook;
        $content .= ' '.$fiche->twitter;

        return $content;
    }

    public function getAllPosts(): array
    {
        $elasticData = new self();

        $sites = MarcheConst::SITES;

        $datas = [];
        foreach ($sites as $blogId => $name) {
            $datas[$blogId] = $elasticData->getPosts($blogId);

            if ($blogId == 2) {
                $datas[$blogId] = array_merge($datas[$blogId], $elasticData->getPages($blogId));
            }

            if ($blogId == 13) {
                $datas[$blogId] = array_merge($datas[$blogId], $elasticData->getNowel($blogId));
            }
        }

        return $datas;
    }

    public function getAllCategories(): array
    {
        $blogs = [
            "citoyen"        => 1,
            "administration" => 2,
            "economie"       => 3,
            "tourisme"       => 4,
            "sport"          => 5,
            "sante"          => 6,
            "social"         => 7,
            "marchois"       => 8,
            "culture"        => 11,
            "eroman"         => 12,
            "noel"           => 13,
            "enfance"        => 14,
        ];

        $datas = [];
        foreach ($blogs as $blog) {
            $datas[$blog] = $this->getCategoriesOfBlog($blog);
        }

        return $datas;
    }

}
