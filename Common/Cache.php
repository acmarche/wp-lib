<?php


namespace AcMarche\Common;


use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class Cache
{
    const MENU_CACHE_NAME = 'menu_all';
    const AGENDA_FULL = 'agenda_full';

    public static function instance(): CacheInterface
    {
        return new ApcuAdapter(

        // a string prefixed to the keys of the items stored in this cache
            $namespace = 'newmarche',

            // the default lifetime (in seconds) for cache items that do not define their
            // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
            // until the APCu memory is cleared)
            $defaultLifetime = 3600,

            // when set, all keys prefixed by $namespace can be invalidated by changing
            // this $version string
            $version = null
        );
    }
}
