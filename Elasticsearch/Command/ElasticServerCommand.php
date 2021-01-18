<?php


namespace AcMarche\Elasticsearch\Command;

use AcMarche\Elasticsearch\ElasticServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticServerCommand extends Command
{
    protected static $defaultName = 'elastic:server';
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Raz l\'index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('Raz. Êtes vous sur ? (Y,N) ', false);

        if (! $helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $elastic = new ElasticServer();
        $elastic->createIndex();
        $elastic->setProperties();

        $this->io->success('Index vidé');

        return Command::SUCCESS;
    }
}
