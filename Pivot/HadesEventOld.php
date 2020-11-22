<?php


namespace AcMarche\Pivot;

class HadesEventOld
{
    const PREFIX = 'event_';

    public function getConcordance()
    {
        $base = array(
            'id' => 'eve_id',
            'titre' => 'eve_titre_fr',
            'description' => 'eve_desc_fr',
            'date_maj' => 'lastmod',
            'code_CGT' => 'eve_codecgt',
        );

        $complements = array(
            'adresse' => 'lieu_adr',
            'localite' => 'loc_nom',
            'cp' => 'loc_cp',
            'nom' => 'eve_contact_nom',
            'latitude' => 'lieu_lat',
            'longitude' => 'lieu_long',
            'info' => 'eve_info_fr',
            'telephone' => 'eve_tel',
            'gsm' => '',
            'website' => 'eve_url',
            'email' => 'eve_mail',
            'fax' => 'eve_fax',
        );

        return array_merge($base, $complements);
    }



    public function importEvents()
    {
        $events = $this->getEventsFromXml();
        $categories = array(5);
        $data_type = 'hades_event';
        $clef_titre = 'eve_titre_fr';
        $clef_id = 'eve_id';
        $prefix = $this->getPrefix();
        $tagname = 'categories_fr'; //to set tag to article
        $villes = array(19, 92, 109, 134, 162, 179, 234, 247, 676, 677, 678, 679, 680, 681, 683, 685, 828);

        $hades = new Pivot();

        foreach ($events as $event) {
            $loc_id = $event->loc_id;
            $loc_cp = $event->loc_cp;
            $titre = $event->eve_titre_fr;

            if ($loc_cp == 6900) {
                if (!preg_match("#euro#", $titre)) {
                    $hades->create_post(
                        $event,
                        $categories,
                        'category',
                        $data_type,
                        $clef_titre,
                        $clef_id,
                        $prefix,
                        $tagname
                    );
                }
            }
        }
    }

    /**
     * Retourne les events de la db wp dont la date de fin est dépassée
     * @return \WP_Query
     */
    public function getOld()
    {
        $max_row = 6;
        $prefix = $this->getPrefix();

        $date = new DateTime();
        $today = $date->format("Y-m-d");

        $meta_query = array(
            array(
                'key' => $prefix.'eve_date_fin',
                'value' => $today,
                'compare' => '<',
            ),
        );

        $args = array(
            'post_type' => array('hades_event'),
            'posts_per_page' => $max_row,
            'meta_query' => $meta_query,
        );

        add_filter('posts_join', 'HadesEvent::alter_posts_join');
        add_filter('posts_where', 'HadesEvent::alter_posts_where');

        $the_query = new WP_Query($args);

        return $the_query;
    }

    /**
     *
     * @global wpdb $wpdb
     */
    public function deleteOld()
    {
        global $wpdb;
        $events = $this->getEventsFromXml();
        if (count($events) == 0) {
            return;
        }

        $idsFlux = array_map(
            function ($event) {
                return $event->eve_id;
            },
            $events
        );

        $query = "SELECT * FROM `wp_4_hadesmeta` WHERE `meta_key` LIKE '%event_eve_id%'";
        $result = $wpdb->get_results($query);
        $postsToDelete = array_map(
            function ($row) use ($idsFlux) {
                $postId = $row->hades_id; //post_ID
                $eveId = $row->meta_value; //eve_id
                $eveId = $row->meta_value; //eve_id
                if (!in_array($eveId, $idsFlux)) {
                    return $postId;
                }

                return null;
            },
            $result
        );

        foreach ($postsToDelete as $postID) {
            $post = get_post(intval($postID));
            if ($post) {
                echo $post->post_title."deleted \n";
                //$dateFin = $this->getAttribute($post->ID, 'event_eve_date_fin');
                if (wp_delete_post($post->ID, true)) {
                    $sql = "DELETE FROM `wp_4_hadesmeta` WHERE `hades_id` = $postID";
                    $wpdb->query($sql);
                }
            }
        }
    }

    public function getAttribute(int $postId, string $key): ?string
    {
        global $wpdb;
        $query = "SELECT * FROM `wp_4_hadesmeta` WHERE `meta_key` = '$key' AND `hades_id` = $postId";
        $attribute = $wpdb->get_row($query);
        if ($attribute instanceof stdClass) {
            return $attribute->meta_value;
        }

        return null;
    }

    /**
     * Reset all event and meta
     * @global wpdb $wpdb
     */
    public function razAllEvent()
    {
        global $wpdb;
        $posts = $this->getEventsFromWp();
        $count = count($posts);
        echo "count $count ";
        if ($count > 0) {
            foreach ($posts as $post) {
                $title = $post->post_title;
                //var_dump(wp_get_post_categories($post->ID));
                $result = wp_delete_post($post->ID, true);

                if (!$result) {
                    echo "Rate pour $title <br />";
                }
            }
        }
        /**
         * delete all meta data
         */
        $sql = "DELETE FROM `wp_4_hadesmeta` WHERE `meta_key` LIKE '%event%'";
        $result = $wpdb->query($sql);
        var_dump($result);
        echo "<p>Metas affacee : $result</p>";
    }

    public static function getSchema()
    {
        $schema = array(
            array(
                'COLUMN_NAME' => 'eve_date_affichage',
                'COLUMN_COMMENT' => 'Date affichée réellement (sans traduction) comme 01/02->05/02/2007 ou 02-03-05/02/2007',
            ),
            array(
                'COLUMN_NAME' => 'eve_date_debut',
                'COLUMN_COMMENT' => 'format de date pour traitement (recherches, tris)',
            ),
            array(
                'COLUMN_NAME' => 'eve_date_fin',
                'COLUMN_COMMENT' => 'format de date pour traitement (recherches, tris)',
            ),
            array('COLUMN_NAME' => 'eve_titre_fr', 'COLUMN_COMMENT' => 'Titre FR'),
            //  array('COLUMN_NAME' => 'eve_titre_nl', 'COLUMN_COMMENT' => 'Titre NL'),
            //  array('COLUMN_NAME' => 'eve_titre_en', 'COLUMN_COMMENT' => 'Titre EN'),
            //  array('COLUMN_NAME' => 'eve_titre_de', 'COLUMN_COMMENT' => 'Titre DE'),
            array('COLUMN_NAME' => 'eve_titre_pt', 'COLUMN_COMMENT' => 'Titre PT'),
            array('COLUMN_NAME' => 'eve_desc_fr', 'COLUMN_COMMENT' => 'Description FR'),
            //   array('COLUMN_NAME' => 'eve_desc_nl', 'COLUMN_COMMENT' => 'Description NL'),
            //   array('COLUMN_NAME' => 'eve_desc_en', 'COLUMN_COMMENT' => 'Description EN'),
            //   array('COLUMN_NAME' => 'eve_desc_de', 'COLUMN_COMMENT' => 'Description DE'),
            array('COLUMN_NAME' => 'eve_desc_pt', 'COLUMN_COMMENT' => 'Description PT'),
            array('COLUMN_NAME' => 'eve_info_fr', 'COLUMN_COMMENT' => 'Informations de date heures et prix (fr)'),
            //   array('COLUMN_NAME' => 'eve_info_nl', 'COLUMN_COMMENT' => 'Informations de date heures et prix (nl)'),
            //   array('COLUMN_NAME' => 'eve_info_en', 'COLUMN_COMMENT' => 'Informations de date heures et prix (en)'),
            //   array('COLUMN_NAME' => 'eve_info_de', 'COLUMN_COMMENT' => 'Informations de date heures et prix (de)'),
            //?exite pas array('COLUMN_NAME' => 'eve_info_pt', 'COLUMN_COMMENT' => 'Informations de date heures et prix (pt)'),
            array(
                'COLUMN_NAME' => 'eve_rec',
                'COLUMN_COMMENT' => 'Expression de la récurrence [ si non-null => événement récurrent ]',
            ),
            array('COLUMN_NAME' => 'eve_contact_nom', 'COLUMN_COMMENT' => 'Nom du contact associé au téléphone'),
            array('COLUMN_NAME' => 'eve_tel', 'COLUMN_COMMENT' => 'Téléphone pour renseignements ou réservations'),
            array('COLUMN_NAME' => 'eve_fax', 'COLUMN_COMMENT' => 'Fax pour renseignements ou réservations'),
            array('COLUMN_NAME' => 'eve_mail', 'COLUMN_COMMENT' => 'Email pour renseignements ou réservations'),
            array('COLUMN_NAME' => 'eve_url', 'COLUMN_COMMENT' => 'Url pour renseignements ou réservations'),
            array('COLUMN_NAME' => 'eve_url_fr', 'COLUMN_COMMENT' => 'url vers page francophone'),
            //   array('COLUMN_NAME' => 'eve_url_nl', 'COLUMN_COMMENT' => 'url vers page néerlandophone'),
            //   array('COLUMN_NAME' => 'eve_url_en', 'COLUMN_COMMENT' => 'url vers page anglophone'),
            //   array('COLUMN_NAME' => 'eve_url_de', 'COLUMN_COMMENT' => 'url vers page germanophone'),
            //   array('COLUMN_NAME' => 'eve_url_pt', 'COLUMN_COMMENT' => 'url vers page portugaise'),
            array('COLUMN_NAME' => 'eve_id', 'COLUMN_COMMENT' => 'Clé primaire'),
            array('COLUMN_NAME' => 'loc_id', 'COLUMN_COMMENT' => 'Clé de liaison vers les localités'),
            array('COLUMN_NAME' => 'lieu_id', 'COLUMN_COMMENT' => 'Clé de liaison vers la table des lieux'),
            array(
                'COLUMN_NAME' => 'eve_intreg',
                'COLUMN_COMMENT' => 'Code indiquant la portée de l\'événement [ Niveaux : 1=> SI ; 2=> MT ; 3=> FED ; 4=> OPT/CGT ]',
            ),
            //   array('COLUMN_NAME' => 'eve_mod_dat', 'COLUMN_COMMENT' => 'Date de la dernière modification'),
            //    array('COLUMN_NAME' => 'eve_mod_user', 'COLUMN_COMMENT' => 'Responsable de la dernière modification'),
            //   array('COLUMN_NAME' => 'eve_cre_dat', 'COLUMN_COMMENT' => 'Date de la Création'),
            //   array('COLUMN_NAME' => 'eve_cre_user', 'COLUMN_COMMENT' => 'Responsable de la Création'),
            array(
                'COLUMN_NAME' => 'contact_id',
                'COLUMN_COMMENT' => 'Clé étrangère vers la table des contacts ( sources de l\'information ou organisateurs)',
            ),
            array(
                'COLUMN_NAME' => 'eve_trad_nl',
                'COLUMN_COMMENT' => 'est à 1 si la traduction NL à été complétée et vérifiée',
            ),
            //   array('COLUMN_NAME' => 'eve_trad_en', 'COLUMN_COMMENT' => 'est à 1 si la traduction EN à été complétée et vérifiée'),
            //   array('COLUMN_NAME' => 'eve_trad_de', 'COLUMN_COMMENT' => 'est à 1 si la traduction DE à été complétée et vérifiée'),
            //   array('COLUMN_NAME' => 'eve_trad_pt', 'COLUMN_COMMENT' => 'est à 1 si la traduction PT à été complétée et vérifiée')
        );

        $bon = array();

        foreach ($schema as $tab) {
            //  var_dump($tab);
            $bon[$tab['COLUMN_NAME']] = $tab['COLUMN_COMMENT'];
        }

        return $bon;
    }

    public function getImages($post, $display = true)
    {
        $photos = get_metadata('hades', $post->ID, $this->getPrefix().'photo', true);

        if (!$photos) {
            return false;
        }

        if (!is_array($photos)) {
            $photos = array($photos);
        }

        if (!$display) {
            return $photos;
        }

        //   $twig = Hades::twig();
        //   echo $twig->render('images.html.twig', $photos);

        foreach ($photos as $photo) {
            $header = get_headers($photo, 1);

            if ($header['Content-Type'] == 'image/jpeg') {
                ?>
                <a href="<?php echo $photo ?>" rel="groupevent" class="fancybox">
                    <img id="photo_ftlb" src="<?php echo $photo ?>" alt="photo" class="photoHebergement"
                         rel="lightbox-imagesetname">
                </a>
                <?php
            }
        }
        ?>
        <br clear="all"/>
        <?php
    }

    public static function getPdfAgenda($idEve, $titre)
    {
        $PathImg = "http://www.ftlb.be/dbimages/docs/";
        $PathFilePdf = $PathImg."event".$idEve;

        $url = $PathFilePdf;

        $header = get_headers($url.".pdf", 1);

        if ($header['Content-Type'] == 'application/pdf') {
            ?>
            <br clear="all"/>Vous pouvez également consulter le pdf suivant :
            <a href="<?php echo $url ?>.pdf"><img id="pdf_ftlb"
                                                  src="<?php echo plugin_dir_url(__FILE__) ?>/images/pdf.png"
                                                  alt="pdf"><?php echo $titre ?>.pdf</a>

            <?php
        }
        ?>
        <br clear="all"/>
        <?php
    }

    static function alter_posts_join($join)
    {
        // global $wp_query, $wpdb;
        //  echo "<br />AVANT $join<br /><br />";

        $join = preg_replace("#wp_4_postmeta#", "wp_4_hadesmeta", $join);
        $join = preg_replace("#wp_4_hadesmeta.post_id#", "wp_4_hadesmeta.hades_id", $join);
        $join = preg_replace("#mt1.post_id#", "mt1.hades_id", $join);
        $join = preg_replace("#mt2.post_id#", "mt2.hades_id", $join);
        $join = preg_replace("#mt3.post_id#", "mt3.hades_id", $join);
        $join = preg_replace("#mt4.post_id#", "mt4.hades_id", $join);

        //   echo "APRES $join<br /><br />";

        return $join;
    }

    static function alter_posts_where($where)
    {
        //global $wp_query, $wpdb;
        //  echo "<br />AVANT WHERE $where<br /><br />";

        $where = preg_replace("#wp_4_postmeta#", "wp_4_hadesmeta", $where);

        //    echo " APRES WHERE $where<br /><br />";

        return $where;
    }

    static function alter_posts_orderby($where)
    {
        //       echo "<br />AVANT WHERE $orderby_statement<br /><br />";

        $where = preg_replace("#wp_4_postmeta.meta_value#", "wp_4_hadesmeta.meta_value", $where);

        //BUG recherche manifestation!!
        if (preg_match("#post_title ASC#", $where)) {
            $where = preg_replace("#post_title ASC#", "wp_4_hadesmeta.meta_value ASC", $where);
        }

        //echo " APRES WHERE $orderby_statement<br /><br />";
        //ORDER BY wp_4_hadesmeta.meta_value ASC LIMIT 0, 9
        //ORDER BY post_title ASC

        return $where;
    }

}

?>
