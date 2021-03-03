<?php


namespace AcMarche\Bottin\Command;

use AcMarche\Common\MarcheConst;
use AcMarche\Common\Menu;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddItemCommand extends Command
{
    protected static $defaultName = 'menu:additem';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Ajout menu bottin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $menu     = new Menu();
        foreach (MarcheConst::SITES as $siteId => $slug) {
            switch_to_blog($siteId);
            $items = $menu->getItems($siteId);
            $found = false;
            foreach ($items as $item) {
                if ($item->title == 'Bottin') {
                    $found = true;
                }
            }
            if ($found === false) {
                $menuSite = wp_get_nav_menu_object(Menu::MENU_NAME);
                $this->addItem($siteId, $slug, $menuSite);
            }
        }

        return Command::SUCCESS;
    }

    private function addItem(int $siteId, string $slug, \WP_Term $menu)
    {
        if ($siteId === 1) {
            $slug = '';
        }

        $url = home_url('/').'/bwp/categorie/'.$slug;
        //then add the actuall link/ menu item and you do this for each item you want to add
        wp_update_nav_menu_item(
            $menu->term_id,
            0,
            array(
                'menu-item-title'   => 'Bottin',
                'menu-item-classes' => 'home',
                'menu-item-url'     => $url,
                'menu-item-status'  => 'publish',
            )
        );

        // then update the menu_check option to make sure this code only runs once
        update_option('menu_check', true);

        flush_rewrite_rules();
    }
}
