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
            ->setDescription('Mise à jour des données')
            ->addArgument('action', InputArgument::OPTIONAL, 'post, bottin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action   = $input->getArgument('action');
        $this->io = new SymfonyStyle($input, $output);
        $elastic  = new ElasticIndexer($this->io);
        $elastic->indexAllPosts();
        $elastic->indexAllCategories();

        return Command::SUCCESS;
    }
}