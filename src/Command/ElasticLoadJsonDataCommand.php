<?php

namespace App\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticLoadJsonDataCommand extends Command
{
    protected static $defaultName = 'elastic:load-json-data';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $dataFile;

    /**
     * @var array
     */
    private $elasticIndex;

    /**
     * command constructor.
     * 
     * @param Client $client Client
     * @param string $dataFile $DataFile
     * @param array $elasticIndex ElasticIndex
     */
    public function __construct(Client $client, string $dataFile, array $elasticIndex) {
        $this->client       = $client;
        $this->dataFile     = $dataFile;
        $this->elasticIndex = $elasticIndex;
        parent::__construct(null);
    }

    /**
     * Configure
     */
    protected function configure() {
        $this
            ->setDescription('Load elastic data from specified json file')
            // ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Name of the index to repopulate', 'index')
            // ->addArgument('file', InputArgument::OPTIONAL, 'Pass the json file path respect to application root', './.wiki/elasticsearchdata.json')
        ;
    }

    /**
     * Execute
     *
     * @param InputInterface $input Input
     * @param OutputInterface $output Output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $io->note(sprintf('You passed json-file path: %s', $this->dataFile));
        $io->note(sprintf('You passed indexed name: %s', $this->elasticIndex['index']));

        $io->note('Populating Index ....');
        $this->populateJson();

        $io->success('Populating Elasticsearch from "'. $this->dataFile .'" with index "'. $this->elasticIndex['index'] .'"');

        return 0;
    }

    /**
     * Parse json and populate the data to Elasticsearch.
     *
     * @return void
     */
    private function populateJson(): void {
        if ($this->client->indices()->exists($this->elasticIndex)) {
            $this->client->indices()->delete($this->elasticIndex);
        }

        $jsonContent    = file_get_contents($this->dataFile);
        $jsonDocs       = json_decode($jsonContent, true);

        if (array_key_exists('hits', $jsonDocs) && array_key_exists('hits', $jsonDocs['hits'])) {
            foreach ($jsonDocs['hits']['hits'] as $id => $hit) {
                $doc    = $this->elasticIndex + [
                    'type'  => $hit['_type'],
                    'id'    => $hit['_id'],
                    'body'  => $hit['_source']
                ];
                $this->client->index($doc);

            }
        }
    }
}
