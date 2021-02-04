<?php


namespace AcMarche\Common;

use AcMarche\Bottin\Bottin;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Theme\Inc\Router;
use AcMarche\Theme\Inc\Theme;
use BottinCategoryMetaBox;

class TemplateRender
{
    public static function renderCategory(): void
    {
        /**
         * @var \WP_Query $wp_query
         */
        global $wp_query;

        $bottinRepository = new BottinRepository();
        $wpRepository     = new WpRepository();

        $cat_ID      = get_queried_object_id();
        $description = category_description($cat_ID);
        $title       = single_cat_title('', false);
        $parent      = $wpRepository->getParentCategory($cat_ID);
        $urlBack  = '/';
        $nameBack = 'Accueil';
        if ($parent) {
            $urlBack  = get_category_link($parent->term_id);
            $nameBack = $parent->name;
        }

        $blodId   = get_current_blog_id();
        $color    = Theme::getColorBlog($blodId);
        $blogName = Theme::getTitleBlog($blodId);

        $posts    = $wp_query->get_posts();
        $children = $wpRepository->getChildrenOfCategory($cat_ID);

        $fiches           = [];
        $categoryBottinId = get_term_meta($cat_ID, BottinCategoryMetaBox::KEY_NAME, true);
        if ($categoryBottinId) {
            $fiches = $bottinRepository->getFichesByCategory($categoryBottinId);
        }

        $all = array_merge($posts, $fiches);

        array_map(
            function ($post) {
                if ($post instanceof \WP_Post) {
                    $post->excerpt   = $post->post_excerpt;
                    $post->permalink = get_permalink($post->ID);
                } else {
                    $post->fiche      = true;
                    $post->excerpt    = Bottin::getExcerpt($post);
                    $post->permalink  = Router::getUrlFicheBottin($post);
                    $post->post_title = $post->societe;
                }
            },
            $all
        );

        Twig::rendPage(
            'category/index.html.twig',
            [
                'title'       => $title,
                'description' => $description,
                'children'    => $children,
                'posts'       => $all,
                'category_id' => $cat_ID,
                'blogName'    => $blogName,
                'color'       => $color,
                'urlBack'     => $urlBack,
                'nameBack'    => $nameBack,
            ]
        );
    }
}
