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
use GameBundle\Entity\Platform;
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
        $em = $this->getDoctrine()->getManager();
        $igdbService = $this->container->get('igdb');
        $igdb = $igdbService->get('games/?search=' . $search . '&fields=*&limit=' . 10);

        $platforms = [];

        foreach ($igdb as $igdbGame) {

            if (!isset($igdbGame->cover) || !isset($igdbGame->platforms)) {
                unset($igdbGame);
                continue;
            }

            $igdbGame->cover->cloudinaryId = $igdbGame->cover->cloudinary_id;

            if (isset($igdbGame->screenshots) && count($igdbGame->screenshots) > 0) {
                foreach ($igdbGame->screenshots as $screenshot) {
                    $screenshot->cloudinaryId = $screenshot->cloudinary_id;
                }
            }

            $igdbGamePlatforms = [];

            foreach ($igdbGame->platforms as $platformId) {

                if (array_key_exists($platformId, $platforms)) {
                    $igdbGamePlatforms[] = $platforms[$platformId];
                } else {
                    $platformRepository = $this->getDoctrine()->getRepository('GameBundle:Platform');
                    $platform = $platformRepository->findOneByIgdbId($platformId);

                    if (is_null($platform)) {

                        // Platform not in db : new Platform
                        $platform = new Platform();
                        $platform->setIgdbId($platformId);

                        // Get IGDB game
                        $igdbPlatforms = $igdbService->get('platforms/' . $platform->getIgdbId() . '?fields=*');
                        $igdbPlatform = $igdbPlatforms[0];

                        $igdbGamePlatforms[] = $igdbPlatform;

                        $platform->setName($igdbPlatform->name);
                        $platform->setIgdbUrl($igdbPlatform->url);

                        $em->persist($platform);
                        $em->flush();
                    } else {
                        $igdbPlatform = new \stdClass();
                        $igdbPlatform->id = $platform->getIgdbId();
                        $igdbPlatform->name = $platform->getName();

                        $igdbGamePlatforms[] = $igdbPlatform;
                    }
                }
            }

            $igdbGame->platforms = $igdbGamePlatforms;
        }

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