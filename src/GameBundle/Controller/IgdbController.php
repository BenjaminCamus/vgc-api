<?php

/*
 *
 *
 *
 */

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use GameBundle\Entity\Platform;
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
        $igdb = $igdbService->search($search);

        $platforms = [];

        $returnGames = [];

        foreach ($igdb as $igdbGame) {

            if (!isset($igdbGame->cover) || !isset($igdbGame->platforms)) {
                continue;
            }

            $igdbGame->cover->cloudinaryId = $igdbGame->cover->image_id;

            if (isset($igdbGame->screenshots) && count($igdbGame->screenshots) > 0) {
                foreach ($igdbGame->screenshots as $screenshot) {
                    $screenshot->cloudinaryId = $screenshot->image_id;
                }
            }

            if (isset($igdbGame->videos) && count($igdbGame->videos) > 0) {
                foreach ($igdbGame->videos as $video) {
                    $video->youtubeId = $video->video_id;
                }
            }

            $igdbGamePlatforms = [];

            foreach ($igdbGame->platforms as $igdbPlatform) {

                if (array_key_exists($igdbPlatform->id, $platforms)) {
                    $igdbGamePlatforms[] = $platforms[$igdbPlatform->id];
                } else {
                    $platformRepository = $this->getDoctrine()->getRepository('GameBundle:Platform');
                    $platform = $platformRepository->findOneByIgdbId($igdbPlatform->id);

                    if (is_null($platform)) {

                        // Platform not in db : new Platform
                        $platform = new Platform();
                        $platform->setIgdbId($igdbPlatform->id);
                        $platform->setName($igdbPlatform->name);
                        $platform->setIgdbUrl($igdbPlatform->url);

                        $em->persist($platform);
                        $em->flush();
                    }

                    $igdbPlatform = new \stdClass();
                    $igdbPlatform->id = $platform->getIgdbId();
                    $igdbPlatform->slug = $platform->getSlug();
                    $igdbPlatform->name = $platform->getName();

                    $igdbGamePlatforms[] = $igdbPlatform;
                    $platforms[$igdbPlatform->id] = $igdbPlatform;
                }
            }

            $igdbGame->platforms = $igdbGamePlatforms;
            $returnGames[] = $igdbGame;
        }

        return new JsonResponse($returnGames);
    }
}