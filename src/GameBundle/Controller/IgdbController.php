<?php

/*
 *
 *
 *
 */

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use GameBundle\Utils\IGDB;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class IgdbController extends FOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/igdb/search/{search}")
     */
    public function getSearchAction($search)
    {
        $igdbService = $this->container->get('igdb');
        $igdb = $igdbService->get('games/?search=' . $search . '&fields=*&limit=' . 10);

        return new JsonResponse($igdb);
    }

    /**
     * @Rest\View
     * @Rest\Get("/igdb/platform/{platformId}")
     */
    public function getPlatformAction($platformId)
    {
        $igdbService = $this->container->get('igdb');
        $igdb = $igdbService->get('platforms/' . $platformId . '?fields=id,name,slug');

        return new JsonResponse($igdb);
    }

    //'platforms/' + platformId + '?fields=id,name,slug'
}