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
     */
    public function show(): Response {
        return $this->render('elastic/index.html.twig', [
            'controller_name' => 'ElasticController',
        ]);
    }

}
