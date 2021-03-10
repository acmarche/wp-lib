<?php


namespace AcMarche\Elasticsearch\Command;

use AcMarche\Elasticsearch\ElasticIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticIndexerCommand extends Command
{
    protected static $defaultName = 'elastic:indexer';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Mise à jour des données [all, posts, categories, bottin]')
            ->addArgument('action', InputArgument::REQUIRED, 'all, posts, categories, bottin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action   = $input->getArgument('action');
        $this->io = new SymfonyStyle($input, $output);
        $elastic  = new ElasticIndexer($this->io);

        switch ($action) {
            case 'posts':
                $elastic->indexAllPosts();
           //     $elastic->indexPagesSpecial();
                break;
            case 'categories':
                $elastic->indexAllCategories();
                break;
            case 'bottin':
                $elastic->indexAllBottin();
                break;
            case 'all':
                $elastic->indexAllPosts();
              //  $elastic->indexPagesSpecial();
                $elastic->indexAllCategories();
                $elastic->indexAllBottin();
        }

        return Command::SUCCESS;
    }
}
