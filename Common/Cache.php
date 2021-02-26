<?php


namespace AcMarche\Common;

use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class Cache
{
    const MENU_CACHE_NAME = 'menu_all';
    const AGENDA_FULL = 'agenda_full';

    public static function instance(): CacheInterface
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
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
        } else {
            return new FilesystemAdapter(
            // a string used as the subdirectory of the root cache directory, where cache
            // items will be stored
                $namespace = 'newmarche2',

                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until the files are deleted)
                $defaultLifetime = 3600,

                // the main cache directory (the application needs read-write permissions on it)
                // if none is specified, a directory is created inside the system temporary directory
                $directory = null
            );
        }
    }
}
