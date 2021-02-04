<?php


namespace AcMarche\Common;

use AcMarche\Theme\Inc\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommonCommand extends Command
{
    protected static $defaultName = 'marche:xxx';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Sert a rien')
            ->addArgument('query', InputArgument::REQUIRED, 'mot clef pour la description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $query = $input->getArgument('query');
        if ( ! $query) {
            $this->io->error('Entrez un mot clef');

            return Command::FAILURE;
        }



        return Command::SUCCESS;
    }

}
