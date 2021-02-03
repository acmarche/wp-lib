<?php

namespace AcMarche\Common;

use AcMarche\Theme\Inc\Theme;
use WP_Post;
use WP_Query;

class WpRepository
{
    public static function getPageAlert(): ?WP_Post
    {
        $query = new WP_Query(array("page_id" => Theme::PAGE_ALERT, "post_status" => 'publish', 'post_type' => 'page'));
        $post  = $query->get_posts();
        if (count($post) > 0) {
            return $post[0];
        }

        return null;
    }

    /**
     * @param int $max
     *
     * @return WP_Post[]
     */
    public static function getAllNews(int $max = 20): array
    {
        $sites = MarcheConst::SITES;
        $news  = array();

        foreach ($sites as $siteId => $name) :
            switch_to_blog($siteId);

            $args = array(
                'category_name' => 'quoi-de-neuf-principal,focus-principal',
                'orderby'       => 'title',
                'order'         => 'ASC',
            );

            if ($siteId == 1) {
                $args = array(
                    'category_name' => 'quoi-de-neuf',
                    'orderby'       => 'title',
                    'order'         => 'ASC',
                );
            }

            $querynews = new WP_Query($args);

            while ($querynews->have_posts()) :

                $post = $querynews->next_post();
                $id   = $post->ID;

                if (has_post_thumbnail($id)) {
                    $attachment_id      = get_post_thumbnail_id($id);
                    $images             = wp_get_attachment_image_src($attachment_id, 'original');
                    $post_thumbnail_url = $images[0];
                } else {
                    $post_thumbnail_url = get_template_directory_uri().'/assets/images/404.jpg';
                }

                $post->post_thumbnail_url = $post_thumbnail_url;

                $permalink       = get_permalink($id);
                $post->permalink = $permalink;

                $post->blog_id = $siteId;
                $post->blog    = $name;
                $post->color   = MarcheConst::COLORS[$siteId];

                $news[] = $post;
            endwhile;

        endforeach;
        wp_reset_postdata();

        if (count($news) > $max) {
            $i   = 0;
            $end = count($news) - $max;
            while ($i < $end) {
                unset($news[$i]);
                $i++;
            }
        }
        $news = array_values($news);

        return $news;
    }

    public static function getRelations(int $postId): array
    {
        $categories = get_the_category($postId);
        $args            = array(
            'category__in' => array_map(
                function ($category) {
                    return $category->cat_ID;
                },
                $categories
            ),
            'post__not_in' => [$postId],
            'orderby'      => 'title',
            'order'        => 'ASC',
        );
        $query           = new \WP_Query($args);
        $recommandations = [];
        foreach ($query->posts as $post) {
            $image = null;
            if (has_post_thumbnail($post)) {
                $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'original');
                if ($images) {
                    $image = $images[0];
                }
            }
            $recommandations[] = [
                'title' => $post->post_title,
                'url'   => get_permalink($post->ID),
                'image' => $image,
            ];

        }

        return $recommandations;
    }

    public static function getCategoryBySlug(string $slug)
    {
        return get_category_by_slug($slug);
    }

    public static function getTags(int $postId): array
    {
        $tags = [];
        foreach (get_the_category($postId) as $category) {
            $tags[] = [
                'name' => $category->name,
                'url'  => get_category_link($category),
            ];
        }
        return $tags;
    }
}
