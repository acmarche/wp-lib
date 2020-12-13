<?php


namespace AcMarche\Common;


use AcMarche\Bottin\Bottin;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Theme\Inc\Router;

class TemplateRender
{
    public static function renderCategory(): string
    {
        /**
         * @var \WP_Query $wp_query
         */
        global $wp_query;

        $twig             = Twig::LoadTwig();
        $bottinRepository = new BottinRepository();

        $cat_ID      = get_queried_object_id();
        $description = category_description($cat_ID);
        $title       = single_cat_title('', false);

        $blodId = get_current_blog_id();
        if ($blodId === 1) {
            $siteSlug = 'Citoyen';
        } else {
            $siteSlug = get_blog_details($blodId)->path;
        }
        $color = MarcheConst::COLORS[$blodId];

        $posts = $wp_query->get_posts();

        $args     = ['parent' => $cat_ID, 'hide_empty' => false];
        $children = get_categories($args);
        array_map(
            function ($category) {
                $category->permalink = get_category_link($category->term_id);
                $category->id        = $category->term_id;
            },
            $children
        );

        $fiches           = [];
        $categoryBottinId = get_term_meta($cat_ID, \BottinCategoryMetaBox::KEY_NAME, true);
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

        $content = $twig->render(
            'category/category.html.twig',
            [
                'title'       => $title,
                'description' => $description,
                'children'    => $children,
                'posts'       => $all,
                'category_id' => $cat_ID,
                'site_slug'   => $siteSlug,
                'color'       => $color,
            ]
        );

        return $content;
    }
}