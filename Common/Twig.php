<?php


namespace AcMarche\Common;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Twig
{
    public static function LoadTwig(?string $path = null): Environment
    {
        if ( ! $path) {
            $path = get_template_directory().'/templates';
        }

        $loader = new FilesystemLoader($path);

        $environnement = new Environment(
            $loader, [
                'cache'            => ABSPATH.'var/cache',
                'debug'            => WP_DEBUG,
                'strict_variables' => WP_DEBUG,
            ]
        );

        $environnement->addGlobal('template_directory', get_template_directory_uri());
        $environnement->addFilter(self::categoryLink());
        $environnement->addFilter(self::permalinkArticle());
        $environnement->addFunction(self::showTemplate());

        return $environnement;
    }

    protected static function categoryLink(): TwigFilter
    {
        return new TwigFilter(
            'category_link', function (int $categoryId): ?string {
            return get_category_link($categoryId);
        }
        );
    }

    protected static function permalinkArticle(): TwigFilter
    {
        return new TwigFilter(
            'permalink', function (int $postId): ?string {
            return the_permalink($postId);
        }
        );
    }

    protected static function showTemplate(): TwigFunction
    {
        return new TwigFunction(
            'showTemplate', function (): string {
            if (true === WP_DEBUG) {
                global $template;

                return 'template: '.$template;
            }

            return '';
        }
        );
    }
}
