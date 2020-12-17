<?php


namespace AcMarche\Elasticsearch\Command;

use AcMarche\Elasticsearch\Searcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticSearcherCommand extends Command
{
    protected static $defaultName = 'elastic:search';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Effectuer une recherche')
            ->addArgument('query', InputArgument::REQUIRED, 'mot clef pour la description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $query = $input->getArgument('query');
        if (! $query) {
            $this->io->error('Entrez un mot clef');

            return Command::FAILURE;
        }

        $this->search($query);

        return Command::SUCCESS;
    }

    protected function search(string $query)
    {
        $searcher = new Searcher();
        $result   = $searcher->search2($query);
        $this->io->writeln("Found: ".$result->count());
        foreach ($result->getResults() as $result) {
            $hit    = $result->getHit();
            $source = $hit['_source'];
            $this->io->writeln($source['name']);
        }
    }
}
