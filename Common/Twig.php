<?php


namespace AcMarche\Common;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Twig
{
    public static function LoadTwig(?string $path = null): Environment
    {
        //todo get instance
        if ( ! $path) {
            $path = get_template_directory().'/templates';
        }

        $loader = new FilesystemLoader($path);

        $environment = new Environment(
            $loader,
            [
                'cache'            => ABSPATH.'var/cache',
                'debug'            => WP_DEBUG,
                'strict_variables' => WP_DEBUG,
            ]
        );

        // wp_get_environment_type();
        if (WP_DEBUG) {
            $environment->addExtension(new DebugExtension());
        }

        $environment->addGlobal('template_directory', get_template_directory_uri());
        $environment->addFilter(self::categoryLink());
        $environment->addFunction(self::showTemplate());
        $environment->addFunction(self::currentUrl());

        return $environment;
    }

    public static function rendPage(string $templatePath, array $variables=[])
    {
        $twig = self::LoadTwig();
        try {
            echo $twig->render(
                $templatePath,
                $variables,
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            echo $twig->render(
                'errors/500.html.twig',
                [
                    'message' => $e->getMessage(),
                    'title'=>'Error 500',
                    'tags' =>[]
                ]
            );
            Mailer::sendError("Error homepage", $e->getMessage());
        }

    }

    protected static function categoryLink(): TwigFilter
    {
        return new TwigFilter(
            'category_link',
            function (int $categoryId): ?string {
                return get_category_link($categoryId);
            }
        );
    }

    protected static function showTemplate(): TwigFunction
    {
        return new TwigFunction(
            'showTemplate',
            function (): string {
                if (true === WP_DEBUG) {
                    global $template;
                    /***
                     * @var \WP_Admin_Bar $wp_admin_bar
                     */
                    global $wp_admin_bar;

                    return 'template: '.$template;
                }

                return '';
            }
        );
    }

    /**
     * For sharing pages
     * @return TwigFunction
     */
    protected static function currentUrl(): TwigFunction
    {
        return new TwigFunction(
            'currentUrl',
            function (): string {
                return get_site_url().esc_url_raw(add_query_arg([]));
            }
        );
    }
}
