<?php


namespace AcMarche\Bottin\Category;

use function wp_update_category;

include 'wp-admin/includes/taxonomy.php';//?? sinon reconnais pas la fonction wp_update_category

class CategoryCreator
{
    public function updateCategory(\WP_Term $categoryWp, \stdClass $category)
    {
        $data = [
            'cat_ID' => $categoryWp->cat_ID,
            'cat_name' => $category->name,
            'category_description' => $category->description,
            'category_nicename' => $category->slug,
        ];

        return wp_update_category($data);
    }

    public function createCategory(\stdClass $data)
    {
        $parent = null;
        $category = ['cat_name' => $data->name, 'category_description' => $data->description, 'parent' => $parent];
        // wp_insert_category($category);
    }
}
