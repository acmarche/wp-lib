<?php


namespace AcMarche\Bottin\Fiche;

use AcMarche\Bottin\Repository\WpRepository;

class FicheFactory
{
    public function create_post(\stdClass $fiche, array $categories): array
    {
        $result = [];

        $societe = $fiche->societe;
        $nom = $fiche->nom;
        $prenom = $fiche->prenom;
        if (empty($societe)) {
            $societe = "$nom $prenom";
        }
        $description = $fiche->comment1.' '.$fiche->comment2.' '.$fiche->comment3;

        $data_post = array(
            'post_type' => WpRepository::DATA_TYPE,
            'post_title' => wp_strip_all_tags($societe),
            'post_content' => $description,
            'post_status' => 'publish',
            'post_category' => $categories,
            'post_author' => 1,
        );

        /**
         * check is already import in wp
         */
        $wpRepository = new WpRepository();
        $ID = $wpRepository->getPostIdByFicheId((int)$fiche->id);

        //societe deja encodee on fait un update
        if ($ID) {
            $post_exist = get_post($ID);

            if (!$post_exist) {
                return array('error' => 'Meta presente mais pas post');
            }

            $data_post['ID'] = $ID;
            $result['status'] = "updated";
        } else {
            $result['status'] = "created";
        }

        //return post id
        $insert = wp_insert_post($data_post, true);

        if (is_wp_error($result)) {
            return array('error' => ' erreur wp : '.$insert->get_error_message().$insert->get_error_data());
        }

        $idWp = (int)$insert;
        $result['idWp'] = $idWp;
        //add meta data
        $this->add_meta_id_fiche($idWp, $fiche);

        return $result;
    }

    /**
     * Add meta data in table meta data
     * @param int $post_ID
     * @param object stdclass $fiche  with all columns of bdmarche
     */
    private function add_meta_id_fiche($post_ID, $fiche)
    {
        $data_type = WpRepository::DATA_TYPE;
        add_metadata($data_type, $post_ID, 'id', $fiche->id, true);
    }
}
