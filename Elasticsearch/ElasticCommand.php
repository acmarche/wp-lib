<?php


namespace AcMarche\Elasticsearch;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticCommand extends Command
{
    protected static $defaultName = 'elastic:execute';
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Remise à zéro de l\'index')
            ->addArgument('action', InputArgument::REQUIRED, 'reset, index, search')
            ->addArgument('query', InputArgument::OPTIONAL, 'mot clef pour la description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action   = $input->getArgument('action');
        $this->io = new SymfonyStyle($input, $output);

        switch ($action) {
            case 'reset':
                $this->reset();
                break;
            case 'index':
                $this->index();
                break;
            case 'search':
                $query = $input->getArgument('query');
                if ( ! $query) {
                    $this->io->error('Entrez un mot clef');

                    return Command::FAILURE;
                }
                $this->search($query);
                break;
        }

        return Command::SUCCESS;
    }

    protected function reset()
    {
        $elastic = new ElasticServer();

        $elastic->createIndex();
        $elastic->setProperties();
    }

    protected function index()
    {
        $elastic = new ElasticServer();
        $elastic->indexAllPosts();
    }

    protected function search(string $query)
    {
        $searcher = new Searcher();
        $result   = $searcher->search($query);
        $this->io->writeln("Found: ".$result->count());
        foreach ($result->getResults() as $result) {
            $hit    = $result->getHit();
            $source = $hit['_source'];
            $this->io->writeln($source['name']);
        }
    }
}