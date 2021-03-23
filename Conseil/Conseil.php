<?php


namespace AcMarche\Conseil;


use DateTime;
use Exception;
use Symfony\Component\Finder\Finder;

class Conseil
{
    function find_all_files(int $annee)
    {
        $conseil = '/uploads/conseil/pv/'.$annee.'/';
        $dir     = WP_CONTENT_DIR.$conseil;
        $files   = array();

        if ( ! is_dir($dir)) {
            return $files;
        }

        $finder = new Finder();
        $finder->files()->in($dir);
        $finder->sort(
            function ($a, $b) {
                return ($a->getrelativePathname() > $b->getrelativePathname()) ? -1 : 1;
            }
        );
        $i = 0;

        foreach ($finder as $file) {
            $fileName          = $file->getRelativePathname();
            $files[$i]['name'] = $fileName;
            $file_info         = pathinfo($dir.$file);
            $fichier           = $file_info['filename'];
            $files[$i]['url']  = '/wp-content'.$conseil.$fileName;

            try {
                $date_time = new DateTime($fichier);
                $date_fr   = $date_time->format("d-m-Y");
            } catch (Exception $e) {
                $date_fr = $fileName;
            }
            $i++;
        }

        return $files;
    }
}
