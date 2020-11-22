<?php

namespace AcMarche\Bottin;

use AcMarche\Bottin\Fiche\FicheSynchronizer;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\Repository\WpRepository;
use AcMarche\Common\MarcheConst;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'www.marche.be';
$_SERVER['SERVER_NAME'] = 'www.marche.be';//pour envoie mail

require_once(__DIR__.'/../../wp-load.php');
require_once 'vendor/autoload.php';

$bottinRepository = new BottinRepository();
$wpRepository = new WpRepository();
$ficheSynchronizer = new FicheSynchronizer();
$output = new ConsoleOutput();
$table = new Table($output);

foreach (MarcheConst::SITES as $site) {
    switch_to_blog($site);
    /**
     * je parcours toutes les catégories wp avec une référence du bottin
     */
    $categoriesWp = $wpRepository->getCategoriesWp();
    foreach ($categoriesWp as $categoryWp) {
        /**
         * Je vais chercher dans le bottin toutes les fiches suivant la référence mise
         */
        $fiches = $bottinRepository->getFichesByCategory($categoryWp->bottinId);
        if (count($fiches) == 0) {
            Bottin::sendEmail(
                'Aucune fiche pour la category wp '.$categoryWp->name,
                'Aucune fiche dans la db du bottin, site: '.$site.' idcat: '.$categoryWp->cat_ID
            );
            continue;
        }
        $table->setHeaderTitle(count($fiches)." fiches, category: ".$categoryWp->name);
        $table->setHeaders(['Nom', 'Classements', 'Résultat']);
        foreach ($fiches as $fiche) {
            $ligne = [];
            $ligne[] = $fiche->societe;
            /**
             * Je prends toutes les rubriques dans les quelles se trouve la fiche
             */
            $classementsCategoriesId = $ficheSynchronizer->getCategoriesIds($fiche);
            if (count($classementsCategoriesId) == 0) {
                Bottin::sendEmail(
                    'Aucun classement dans le bottin pour la fiche'.$fiche->societe,
                    'depuis sync.php'
                );
                continue;
            }
            $result = $ficheSynchronizer->synchronize($fiche, $classementsCategoriesId, $categoriesWp);
            $ligne[] = implode(",", $result['classements']);
            if (isset($result['error'])) {
                $ligne[] = '<error>'.$result['error'].'</error>';
                Bottin::sendEmail(
                    'Bottin error sync '.$fiche->societe,
                    (string)$result['error']
                );
                continue;
            }
            $message = " : ".$result['status'].' idWp: '.$result['idWp'].' ';
            $ligne[] = $message;

            $table->addRow($ligne);
        }
        $table->render();
    }
}

/**
 * Pour fiche seule
 */
foreach (MarcheConst::SITES as $site) {
    switch_to_blog($site);
    WpRepository::set_table_meta();
    $categoriesWp = $wpRepository->getCategoriesWp();
    foreach ($wpRepository->getFichesWp() as $post) {
        $idFiche = $wpRepository->getFicheIdByPostId($post->ID);
        if (!$idFiche) {
            continue;
        }
        $fiche = $bottinRepository->getFiche($idFiche);
        if (!$fiche) {
            Bottin::sendEmail(
                'Sync id Fiche dans wp mais non trouvable dans le bottin ',
                "id fiche ".$idFiche." site ".$site." id post ".$post->ID
            );
            continue;
        }
        /**
         * Je prends toutes les rubriques dans les quelles se trouve la fiche
         */
        $classementsCategoriesId = $ficheSynchronizer->getCategoriesIds($fiche);
        if (count($classementsCategoriesId) == 0) {
            Bottin::sendEmail(
                'Aucun classement dans le bottin pour la fiche'.$fiche->societe,
                'depuis sync.php'
            );
            continue;
        }
        $ficheSynchronizer->synchronize($fiche, $classementsCategoriesId, $categoriesWp);
    }
}

/**
 * Pour suppression
 */
$ficheSynchronizer->deleteOldFiches();
