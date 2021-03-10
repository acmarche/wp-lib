<?php


namespace AcMarche\Common;

use AcMarche\Theme\Inc\Theme;

class Router
{
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
                $blodId = Theme::CITOYEN;
            }

            return get_blog_details($blodId)->path;
        }

        return '/';
    }

    public function flushRoutes()
    {
        if (is_multisite()) {
            $current = get_current_blog_id();
            foreach (get_sites(['fields' => 'ids']) as $site) {
                switch_to_blog($site);
                flush_rewrite_rules();
            }
            switch_to_blog($current);
        } else {
            flush_rewrite_rules();
        }
    }

    public static function getCurrentUrl(): string
    {
        return get_site_url().esc_url_raw(add_query_arg([]));
    }

}
