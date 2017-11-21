<?php

/*
 *
 *
 *
 */

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

class PlaceController extends FOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/places")
     */
    public function getPlacesAction()
    {
        $placeRepository = $this->getDoctrine()->getRepository('GameBundle:Place');
        $places = $placeRepository->findAll();

        return $places;
    }
}