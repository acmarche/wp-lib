<?php

namespace AcMarche\Pivot;

class Hades
{
    const COMMUNE = 263;
    const MARCHE = 134;
    const PAYS = 9;
    const HEBERGEMENTS_KEY = 'hebergements';
    const RESTAURATIONS_KEY = 'resaurations';
    const EVENEMENTS_KEY = 'evenements';
    const BOUGER_KEY = 'evenements';

    const EVENEMENTS = [
        'evt_sport' => 'Activités sportives',
        'cine_club' => 'Ciné-club',
        'conference' => 'Conférences & Débats',
        'exposition' => 'Expositions',
        'festival' => 'Festivals',
        'fete_festiv' => 'Fêtes & Festivités',
        'anim_jeux' => 'Jeux',
        'livre_conte' => 'Livres & contes',
        'manifestatio' => 'Manifestations',
        'foire_brocan' => 'Marchés, brocantes & Foires',
        'evt_promenad' => 'Promenades',
        'spectacle' => 'Spectacles & Concerts',
        'stage_ateli' => 'Stage et Atelier',
        'evt_vis_guid' => 'Visites guidées',
    ];

    const RESTAURATIONS = [
        'barbecue' => 'Barbecue',
        'bar_vin' => 'Bars à vins',
        'brass_bistr' => 'Brasseries & bistrots',
        'cafe_bar' => 'Cafés & bars',
        'foodtrucks' => 'Food Truck\'s',
        'pique_nique' => 'Pique-nique',
        'restaurant' => 'Restaurants',
        'resto_rapide' => 'Restauration rapide',
        'salon_degus' => 'Salons de dégustation',
        'traiteur' => 'Traiteur',
    ];

    const HEBERGEMENTS = [
        //Hébergements de vacances
        'aire_motorho' => 'Aires pour motorhomes',
        'camping' => 'Campings',
        'centre_vac' => 'Centres de vacances',
        'village_vac' => 'Villages de vacances',
        //Hébergements insolites
        'heb_insolite' => 'Hébergements insolites',
        //Chambres
        'chbre_chb' => 'Chambres',
        'chbre_hote' => 'Chambres d\'hôtes',
        //Gites
        'git_ferme' => 'Gîtes à la ferme',
        'git_citad' => 'Gîtes citadins',
        'git_big_cap' => 'Gîtes de grande capacité',
        'git_rural' => 'Gîtes ruraux',
        'mbl_trm' => 'Meublés de tourisme',
        'mbl_vac' => 'Meublés de vacances',
        'hotel' => 'Hôtels',
    ];

    public static function allCategories(): array
    {
        return [
            self::HEBERGEMENTS_KEY => self::HEBERGEMENTS,
            self::RESTAURATIONS_KEY => self::RESTAURATIONS,
            self::EVENEMENTS_KEY => self::EVENEMENTS,
        ];
    }


}
