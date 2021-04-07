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
     * command constructor.
     * 
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
        parent::__construct(null);
    }

    /**
     * Configuration
     */
    protected function configure() {
        $this
            ->setDescription('Load elastic data from specified json file')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Name of the index to repopulate', 'index')
            ->addArgument('file', InputArgument::OPTIONAL, 'Pass the json file path respect to application root', './.wiki/elasticsearchdata.json')
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
        $io->note(sprintf('You passed json-file path: %s', $input->getArgument('file')));
        $io->note(sprintf('You passed indexed name: %s', $input->getOption('index')));

        $this->populateJson($input->getArgument('file'));
        $io->success('Populating Elasticsearch from "'. $input->getArgument('file') .'" with index "'. $input->getOption('index') .'"');

        return 0;
    }

    /**
     * Creates index with mapping and analyzer.
     * 
     * @param string $indexName
     *
     * @return void
     */
    private function createIndex(string $indexName): void {

        $this->client->indices()->create(
            array_merge(
                [
                    'index' => $indexName,
                    'type'  => $indexName .'-type'
                ],
                [
                    'body' => [
                        'settings' => [
                            'number_of_shards' => 1,
                            'number_of_replicas' => 0,
                            "analysis" => [
                                "analyzer" => [
                                    "autocomplete" => [
                                        "tokenizer" => "autocomplete",
                                        "filter" => ["lowercase"]
                                    ]
                                ],
                                "tokenizer" => [
                                    "autocomplete" => [
                                        "type" => "edge_ngram",
                                        "min_gram" => 2,
                                        "max_gram" => 20,
                                        "token_chars" => [
                                            "letter",
                                            "digit"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "mappings" => [
                            "properties" => [
                                "title" => [
                                    "type" => "text",
                                    "analyzer" => "autocomplete",
                                    "search_analyzer" => "standard"
                                ]
                            ]
                        ]
                    ]
                ]
            )
        );
    }


    /**
     * Parse json and populate the data to Elasticsearch.
     * 
     * @param string $dataPath
     *
     * @return void
     */
    private function populateJson(string $dataPath): void {

        $jsonContent = file_get_contents($dataPath);
        $jsonDocs    = json_decode($jsonContent, true);

        if (array_key_exists('hits', $jsonDocs) && array_key_exists('hits', $jsonDocs['hits'])) {
            foreach ($jsonDocs['hits']['hits'] as $id => $hit) {
                $this->client->index([
                    'index' => $hit['_index'],
                    'type'  => $hit['_type'],
                    'id'    => $hit['_id'],
                    'body'  => $hit['_source']
                ]);
            }
        }
    }
}
