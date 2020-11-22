<?php

namespace AcMarche\Common;

use WP_Post;
use WP_Query;

class WpRepository
{
    /**
     * @return WP_Post[]
     */
    static function getAllNews(int $max): array
    {
        $sites   = MarcheConst::SITES;
        $allnews = array();

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
            //echo $querynews->request;
            $count = $querynews->post_count;
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

                $allnews[] = $post;
            endwhile;

        endforeach;
        wp_reset_postdata();

        return $allnews;
    }


}
