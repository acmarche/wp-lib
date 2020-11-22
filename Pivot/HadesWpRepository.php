<?php


namespace AcMarche\Pivot;

use AcMarche\Common\Cache;

class HadesWpRepository
{
    /**
     * Register table meta data to var $wpdb
     * @global \wpdb $wpdb
     */
    function set_table_meta()
    {
        global $wpdb;
        /*
          $table_name = $type . 'meta';
          if(isset) $wpdb->$table_name;
          return $wpdb->$table_name;
         */
        $wpdb->hadesmeta = $wpdb->prefix.'hades'.'meta';

        return $wpdb->hadesmeta;
    }

    public function getEventsFromWp()
    {
        global $wpdb;

        $acquery = new WP_Query(
            array(
                'post_type' => array('hades_event'),
                'post_status' => 'publish',
                //     'category__in' => array(5)
            )
        );
        $posts = $acquery->get_posts();

        return $posts;
    }


    /**
     * @param int $max
     *
     * @return \WP_Post[]
     */
    public function getEvents(int $max = 100): array
    {
        $currentBlog = get_current_blog_id();
        switch_to_blog(4);
        $key     = 'hades';
        $today   = new \DateTime();
        $prefix  = 'event_';
        $single  = true;

        $args = array(
            'post_type'      => array('hades_event'),
            'posts_per_page' => $max,
            'order'          => 'ASC',
            'type'           => 'date',
        );

        $myquery = new \WP_Query($args);
        $posts   = $myquery->posts;

        self::set_table_meta();

        foreach ($posts as $i => $post) {
            $ID               = $post->ID;
            $post->date_debut = get_metadata($key, $ID, $prefix.'eve_date_debut', $single);
            $post->date_fin   = get_metadata($key, $ID, $prefix.'eve_date_fin', $single);

            if ($today->format('Y-m-d') < $post->date_fin) {
                unset($posts[$i]);
                continue;
            }

            $post->localite = get_metadata($key, $ID, $prefix.'loc_nom', $single);
            $post->url = get_permalink($ID);

            /**
             * Date affichée comme 01/02->05/02/2007 ou 02-03-05/02/2007
             */
            $post->date_affichage = get_metadata($key, $ID, $prefix.'eve_date_affichage', $single);
            list($post->year, $post->month, $post->day) = explode("-", $post->date_debut);
        }

        switch_to_blog($currentBlog);
        return $posts;
    }

    public function getCached() {
        $cache = Cache::instance();
    }
}
