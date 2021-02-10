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
}
