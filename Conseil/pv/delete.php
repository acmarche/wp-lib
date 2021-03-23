<?php

require '../../../vendor/autoload.php';

use AcMarche\Conseil\ConseilConstantes;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

$filesystem = new Filesystem();
$request = Request::createFromGlobals();

$fileName = $request->request->get('file_name');

if ($fileName) {
    try {
        $filesystem->remove(ConseilConstantes::PV_DIRECTORY.$fileName);
        echo json_encode(['result' => '2ok']);
    } catch (IOException $IOException) {
        echo json_encode(['error' => 'Impossible de supprimer le fichier '.$IOException->getMessage()]);

        return;
    }
} else {
    echo json_encode(['error' => 'Nom de fichier obligatoire']);
}
