<?php


namespace AcMarche\Pivot;


use AcMarche\Pivot\Event\Entity\Categorie;
use AcMarche\Pivot\Event\Entity\Event;

class Router
{
    const PARAM_EVENT = 'codecgt';
    const EVENT_URL = 'manifestation/';

    public function __construct()
    {
        $this->addRouteEvent();
        //   $this->flushRoutes();
    }

    /**
     * Retourne la base du blog (/economie/, /sante/, /culture/...
     *
     * @param int|null $blodId
     *
     * @return string
     */
    public static function getBaseUrlSite(?int $blodId = null): string
    {
        if (is_multisite()) {
            if ( ! $blodId) {
                $blodId = get_current_blog_id();
            }

            return get_blog_details($blodId)->path;
        } else {
            return '/';
        }

    }

    public static function getUrlEvent(Event $event): string
    {
        return self::getBaseUrlSite().\AcMarche\Theme\Inc\Router::EVENT_URL.$event->id;
    }

    public static function getUrlEventCategory(Categorie $categorie): string
    {
        return self::getBaseUrlSite().Router::EVENT_URL.$categorie->id;
    }

    public function addRouteEvent()
    {
        add_action(
            'init',
            function () {
                add_rewrite_rule(
                    self::EVENT_URL.'([a-zA-Z0-9-]+)[/]?$',
                    'index.php?'.self::PARAM_EVENT.'=$matches[1]',
                    'top'
                );
            }
        );
        add_filter(
            'query_vars',
            function ($query_vars) {
                $query_vars[] = self::PARAM_EVENT;

                return $query_vars;
            }
        );
        add_action(
            'template_include',
            function ($template) {
                global $wp_query;
                if (is_admin() || ! $wp_query->is_main_query()) {
                    return $template;
                }

                if (get_query_var(self::PARAM_EVENT) == false ||
                    get_query_var(self::PARAM_EVENT) == '') {
                    return $template;
                }

                return get_template_directory().'/single-event.php';
            }
        );
    }

}
