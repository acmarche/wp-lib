<?php


namespace AcMarche\Pivot;

class PivotType
{
    //http://pivot.tourismewallonie.be/index.php/9-pivot-gest-pc/142-types-de-fiches-pivot
    //https://pivotweb.tourismewallonie.be/PivotWeb-3.1/thesaurus/typeofr;fmt=json

    const TYPE_EVENEMENT = 9;
    const TYPE_MEDIA = 268;

    const TYPES = [
        9   => 'Evenement',
        11  => 'Découverte et Divertissement',
        268 => 'Media',
    ];
}
