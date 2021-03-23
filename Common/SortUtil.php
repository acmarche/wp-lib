<?php


namespace AcMarche\Common;


use WP_Post;

class SortUtil
{
    public static function sortPosts(array $posts): array
    {
        usort(
            $posts,
            function ($postA, $postB) {
                {
                    $titleA = is_array($postA) ? $postA['post_title'] : $postA->post_title;
                    $titleB = is_array($postB) ? $postB['post_title'] : $postB->post_title;
                    if ($titleA == $titleB) {
                        return 0;
                    }

                    return ($titleA < $titleB) ? -1 : 1;
                }
            }
        );

        return $posts;
    }

    /**
     * @param WP_Post[] $posts
     *
     * @return WP_Post[]
     */
    public static function sortByPosition(array $posts):array
    {
        usort(
            $posts,
            function ($postA, $postB) {
                {
                    if ($postA->order == $postB->order) {
                        return 0;
                    }

                    return ($postA->order < $postB->order) ? -1 : 1;
                }
            }
        );

        return $posts;
    }
}
