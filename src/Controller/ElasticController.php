<?php

namespace App\Controller;

use Elasticsearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElasticController extends AbstractController
{

    /**
     * @Route("/show", name="show")
     *
     * @return Response
     */
    public function show(): Response {
        return $this->render('elastic/index.html.twig', [
            'controller_name' => 'ElasticController',
        ]);
    }

    /**
     *
     * @Route("/elastic", name="elastic", methods={"GET"})
     *
     * @param Client $client Client
     *
     * @return JsonResponse
     */
    public function elastic(Client $client): JsonResponse {
        return $this->json([
            'message' => 'testing'
        ]);
    }

    /**
     * @Route("/rest/indices", name="elastic_indices", methods={"GET"})
     *
     * @param Client $client Client
     *
     * @return JsonResponse JsonResponse
     */
    public function indices(Client $client): JsonResponse {
        return $this->json([
            'indices' => $client->cat()->indices()
        ]);
    }


    /**
     * @Route("/rest/get-objects-from-selected-tags/{type}/{tagids}", defaults={"type"="","tagids"=""}, name="elastic_search", methods={"GET"})
     *
     * @param Client $client Client
     * @param Request $request Request
     * @param array $elasticIndex ElasticIndex
     * @param string $type Type
     * @param string $tagids Tagids
     *
     * @return JsonResponse JsonResponse
     */
    public function search(Client $client, Request $request, array $elasticIndex, string $type, string $tagids): JsonResponse {

        $startTime  = microtime(true);
        $searchDefinition   = $elasticIndex;
        if ($type) {
            $searchDefinition   += [
                'type'  => $type
            ];
        }
        if ($tagids) {
            $searchDefinition   += [
                'body' => [
                    'query' => [
                        'match' => [
                            'tags' => [
                                'query'     => $tagids,
                                'operator'  => 'and',
                                'analyzer'  => 'standard'
                            ]
                        ],
                    ],
                ]
            ];
        }

        $result = $client->search($searchDefinition);

        return $this->json($result['hits'] + ['execution_time' => (microtime(true) - $startTime).'']);

    }


    /**
     * @Route("/rest/get-facets-of-tags/{tags}", defaults={"tags"=""}, name="elastic_facets", methods={"GET"})
     *
     * @param Client $client Client
     * @param Request $request Request
     * @param array $elasticIndex ElasticIndex
     * @param string $tags Tags
     *
     * @return JsonResponse JsonResponse
     */
    public function facets(Client $client, Request $request, array $elasticIndex, string $tags): JsonResponse {
        $startTime          = microtime(true);

        if ($tags) {
            $tagids             = explode(",", $tags);
            $aggregated         = [];

            foreach ($tagids as $key => $tagid) {
                $searchDefinition   = $elasticIndex + [
                    'body' => [
                        'query' => [
                            'match' => [
                                'tags' => [
                                    'query'     => $tagid,
                                    'analyzer'  => 'standard'
                                ]
                            ],
                        ],
                    ]
                ];
                $result = $client->search($searchDefinition);
                $aggregated[$tagid] = $result['hits']['hits'];
            }

            return $this->json([
                'hits' => $aggregated,
                'execution_time' => (microtime(true) - $startTime).''
            ]);
        }


        return $this->json([
            'hits' => [],
            'execution_time' => (microtime(true) - $startTime).''
        ]);
    }
}
