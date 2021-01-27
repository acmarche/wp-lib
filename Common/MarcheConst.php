<?php


namespace AcMarche\Common;

class MarcheConst
{
    const ADMINISTRATION = 2;
    const TOURISME = 4;

    const SITES = [
        1                    => 'citoyen',
        self::ADMINISTRATION => 'administration',
        3                    => 'economie',
        4                    => 'tourisme',
        5                    => 'sport',
        6                    => 'sante',
        7                    => 'social',
        8                    => 'marchois',
        11                   => 'culture',
        12                   => 'roman',
        13                   => 'noel',
        14                   => 'enfance',
    ];

    const COLORS = [
        1                    => 'color-cat-cit',
        self::ADMINISTRATION => 'color-cat-adm',
        3                    => 'color-cat-eco',
        4                    => 'color-cat-tou',
        5                    => 'color-cat-spo',
        6                    => 'color-cat-san',
        7                    => 'color-cat-soc',
        11                   => 'color-cat-cul',
        14                   => 'color-cat-enf',
    ];
}
