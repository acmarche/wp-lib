<?php
/**
 * Appelez depuis l'appli bottin
 */

namespace AcMarche\Bottin;

use AcMarche\Bottin\Fiche\FicheSynchronizer;
use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\Repository\WpRepository;
use AcMarche\Common\MarcheConst;

define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'www.marche.be';

require_once(__DIR__.'/../../wp-load.php');
require_once '../../vendor/autoload.php';

$idFiche = (int)$_POST['ficheid'];

if ($idFiche > 0) {
    $synchronizer = new FicheSynchronizer();
    $bottinRepository = new BottinRepository();
    $wpRepository = new WpRepository();
    $fiche = $bottinRepository->getFicheById($idFiche);

    if (!$fiche) {
        Bottin::sendEmail('Fiche non trouvable dans db bottin', "idfiche ".$idFiche);

        return json_encode(['error' => 'Fiche non trouvable dans db bottin idfiche '.$idFiche]);
    }

    $categoriesId = $synchronizer->getCategoriesIds($fiche);
    foreach (MarcheConst::SITES as $site) {
        switch_to_blog($site);
        $categoriesWp = $wpRepository->getCategoriesWp();
        $result = $synchronizer->synchronize($fiche, $categoriesId, $categoriesWp);
        if (isset($result['error'])) {
            Bottin::sendEmail('Erreur update fiche sur site www', "idfiche ".$idFiche." error ".json_encode($result));
        }
        echo json_encode($result);
    }
} else {
    echo json_encode(['error' => 'id non trouve']);
}
