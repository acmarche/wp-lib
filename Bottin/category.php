<?php
/**
 * Appelez depuis l'appli bottin
 */
namespace AcMarche\Bottin;

use AcMarche\Bottin\Category\CategorySynchronizer;

define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'www.marche.be';

require_once(__DIR__.'/../../wp-load.php');
require_once 'vendor/autoload.php';

$id = (int)$_POST['categoryid'];
if ($id > 0) {
    $synchronizer = new CategorySynchronizer($id);
    $synchronizer->synchronize();
}
