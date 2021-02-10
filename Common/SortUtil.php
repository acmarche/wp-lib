<?php


namespace AcMarche\Common;


class SortUtil
{
    public static function sortPosts(array $posts): array
    {
        usort(
            $posts,
            function ($postA, $postB) {
                {
                    if ($postA->post_title == $postB->post_title) {
                        return 0;
                    }

                    return ($postA->post_title < $postB->post_title) ? -1 : 1;
                }
            }
        );

        return $posts;
    }
}
