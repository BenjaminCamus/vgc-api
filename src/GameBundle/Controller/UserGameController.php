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
use GameBundle\Entity\Company;
use GameBundle\Entity\Contact;
use GameBundle\Entity\Game;
use GameBundle\Entity\Image;
use GameBundle\Entity\Platform;
use GameBundle\Entity\Series;
use GameBundle\Entity\UserGame;
use GameBundle\Entity\Video;
use GameBundle\Form\ContactType;
use GameBundle\Form\UserGameType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserGameController extends FOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/user/games")
     */
    public function getUserGamesAction(Request $request)
    {
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $offset = $request->query->get('offset', 0);
        $offset = $offset < 0 ? 0 : $offset;
        $limit = $request->query->get('limit', 10);
        $limit = $limit < 0 ? 0 : $limit;
        $limit = $limit > 100 ? 100 : $limit;

        $userGames = $userGameRepository->findBy([
            'user' => $this->getUser()
        ], [], $limit, $offset);

        return $userGames;
    }

    /**
     * @Rest\View
     * @Rest\Get("/user/games/count")
     */
    public function getCountUserGamesAction(Request $request)
    {
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        return $userGameRepository->countByUser($this->getUser());
    }

    /**
     * @Rest\View
     * @Rest\Get("/user/games/{userGameId}", requirements={"userGameId" = "^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     */
    public function getGameAction($userGameId)
    {

        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $userGame = $userGameRepository->findOneBy([
            'user' => $this->getUser(),
            'id' => $userGameId
        ]);

        if (is_null($userGame)) {
            throw new HttpException(404, "User Game Not Found");
        }

        return $userGame;
    }

    /**
     * @Rest\View
     * @Rest\Delete("/user/games/{userGameId}", requirements={"userGameId" = "^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     */
    public function deleteGameAction($userGameId)
    {
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $userGame = $userGameRepository->findOneBy([
            'user' => $this->getUser(),
            'id' => $userGameId
        ]);

        $em = $this->getDoctrine()->getManager();
        $em->remove($userGame);
        $em->flush();

        $view = $this->view(null, 204);
        return $this->handleView($view);
    }

    /**
     * @Rest\View(statusCode=Symfony\Component\HttpFoundation\Response::HTTP_CREATED)
     * @Rest\Post("/user/games/add")
     */
    public function postGameAction(Request $request)
    {
        $requestValues = $formValues = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        // Id
        unset($formValues['id']);

        // User
        $formValues['user'] = $this->getUser()->getId();

        // Game
        if (!isset($requestValues['game']) || !isset($requestValues['game']['igdbId'])) {
            return View::create(['message' => 'IGDB Game Id is missing.'], Response::HTTP_NOT_FOUND);
        }
        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
        $game = $gameRepository->findOneByIgdbId($requestValues['game']['igdbId']);
        $formValues['game'] = is_null($game) ? null : $game->getId();

        // Platform
        if (!isset($requestValues['platform']) || !isset($requestValues['platform']['igdbId'])) {
            return View::create(['message' => 'IGDB Platform Id is missing.'], Response::HTTP_NOT_FOUND);
        }
        $platformRepository = $this->getDoctrine()->getRepository('GameBundle:Platform');
        $platform = $platformRepository->findOneByIgdbId($requestValues['platform']['igdbId']);
        if (is_null($platform)) {
            return View::create(['message' => 'IGDB Platform Id not found.'], Response::HTTP_NOT_FOUND);
        }
        $formValues['platform'] = $platform->getId();

        // Contacts
        $contactRepository = $this->getDoctrine()->getRepository('GameBundle:Contact');

        foreach (['purchase', 'sale'] as $type) {

            $formValues[$type . 'Contact'] = ${$type . 'Contact'} = null;

            if (isset($requestValues[$type . 'Contact'])) {
                ${$type . 'Contact'} = $contactRepository->find($requestValues[$type . 'Contact']['id']);
                $formValues[$type . 'Contact'] = is_null(${$type . 'Contact'}) ? null : ${$type . 'Contact'}->getId();
            }
        }

        // Form UserGame
        $formValues['releaseDate'] = null;
        $form = $this->createForm(UserGameType::class);
        $form->submit($formValues); // Validation des données

        if ($form->isValid()) {

            $userGameReleaseDate = false;

            if (is_null($game)) {

                // Game not in db : new Game
                $game = new Game();
                $game->setIgdbId($requestValues['game']['igdbId']);
                $igdb = $this->igdbGame($game, $requestValues['platform']['igdbId']);
                $game = $igdb[0];
                $userGameReleaseDate = $igdb[1];
            }

            $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
            $userGameCheck = $userGameRepository->findBy([
                'user' => $this->getUser(),
                'id' => $requestValues['id']
            ]);

            $userGame = (count($userGameCheck) == 0) ? new UserGame() : $userGameCheck[0];

            // Transaction
            foreach (['purchase', 'sale'] as $type) {

                // Contact
                if (is_null($formValues[$type . 'Contact']) && isset($requestValues[$type . 'Contact'])) {

                    // Form Contact
                    $formContact = $this->createForm(ContactType::class);
                    unset($requestValues[$type . 'Contact']['id']);
                    $formContact->submit($requestValues[$type . 'Contact']); // Validation des données

                    if ($formContact->isValid()) {
                        $contact = new Contact();
                        $contact->setEmail($requestValues[$type . 'Contact']['email']);
                        $contact->setFirstName($requestValues[$type . 'Contact']['firstName']);
                        $contact->setLastName($requestValues[$type . 'Contact']['lastName']);
                        $contact->setNickname($requestValues[$type . 'Contact']['nickname']);
                        $contact->setPhone($requestValues[$type . 'Contact']['phone']);
                        $contact->setAddress($requestValues[$type . 'Contact']['address']);
                        $contact->setZipcode($requestValues[$type . 'Contact']['zipcode']);
                        $contact->setCity($requestValues[$type . 'Contact']['city']);

                        $em->persist($contact);
                        $em->flush();

                        ${$type . 'Contact'} = $contact;
                    } else {
                        return $formContact;
                    }
                }

                $method = 'set' . ucfirst($type) . 'Contact';
                $userGame->$method(${$type . 'Contact'});

                // Place
                if (isset($requestValues[$type . 'Place'])) {
                    $method = 'set' . ucfirst($type) . 'Place';
                    $userGame->$method($requestValues[$type . 'Place']);
                }

                // Date
                if (isset($requestValues[$type . 'Date'])) {
                    $date = is_null($requestValues[$type . 'Date']) ? null : new \DateTime($requestValues[$type . 'Date']);
                    $method = 'set' . ucfirst($type) . 'Date';
                    $userGame->$method($date);
                }
            }

            $userGame->setUser($this->getUser());
            $userGame->setGame($game);
            $userGame->setPlatform($platform);

            if ($userGameReleaseDate) {
                $userGame->setReleaseDate($userGameReleaseDate);
            }

            $userGame->setRating($request->request->get('rating'));
            $userGame->setCompleteness($request->request->get('completeness'));
            $userGame->setVersion($request->request->get('version'));

            $userGame->setPriceAsked($request->request->get('priceAsked'));
            $userGame->setPricePaid($request->request->get('pricePaid'));
            $userGame->setPriceResale($request->request->get('priceResale'));
            $userGame->setPriceSold($request->request->get('priceSold'));

            $userGame->setProgress($request->request->get('progress'));
            $userGame->setCond($request->request->get('cond'));

            $userGame->setNote($request->request->get('note'));

            $em->persist($userGame);
            $em->flush();

            return $userGame;
        } else {
            return $form;
        }
    }

    private function igdbGame(Game $game, $platformIgdbId = false)
    {

        $em = $this->getDoctrine()->getManager();
        $igdbService = $this->container->get('igdb');
        $userGameReleaseDate = false;

        foreach (['screenshot', 'video',
                     'developer', 'publisher',
                     'mode', 'theme', 'genre'
                 ] as $type) {
            foreach ($game->{'get' . ucfirst($type) . 's'}() as $obj) {
                $game->{'remove' . ucfirst($type)}($obj);
            }
        }

        // Get IGDB game
        $igdb = $igdbService->get($game->getIgdbId());

        if (!isset($igdb[0])) {
            throw new HttpException(404, "IGDB Game Not Found");
        }

        $igdbGame = $igdb[0];

        // Game fields
        $game->setName($igdbGame->name);
        $totalRating = isset($igdbGame->total_rating) ? $igdbGame->total_rating : 0;
        $game->setRating(round($totalRating / 100 * 20, 2));
        $totalRatingCount = isset($igdbGame->total_rating_count) ? $igdbGame->total_rating_count : 0;
        $game->setRatingCount($totalRatingCount);
        $game->setIgdbUrl($igdbGame->url);
        $game->setIgdbCreatedAt(new \DateTime(date('Y-m-d H:i:s', ($igdbGame->created_at / 1000))));
        $game->setIgdbUpdatedAt(new \DateTime(date('Y-m-d H:i:s', ($igdbGame->updated_at / 1000))));

        if ($igdbGame->release_dates) {

            $dates = [];
            foreach ($igdbGame->release_dates as $releaseDate) {
                if ($releaseDate->platform == $platformIgdbId) {
                    $dates[] = $releaseDate->date;
                }
            }

            if (count($dates) > 0) {
                $userGameReleaseDate = new \DateTime(date('Y-m-d H:i:s', (min($dates))), new \DateTimeZone('UTC'));
            }
        }

        // TODO: cover + screenshots(ajouter images si absentes, recherche avec url)
        // TODO: rendre image->url unique
        // Save cover Image
        $cover = new Image();
        $cover->setUrl($igdbGame->cover->url);
        $cover->setCloudinaryId($igdbGame->cover->image_id);
        $cover->setWidth($igdbGame->cover->width);
        $cover->setHeight($igdbGame->cover->height);

        $em->persist($cover);
        $em->flush();

        $game->setCover($cover);

        // Save screenshots Images
        if (isset($igdbGame->screenshots)) {
            foreach ($igdbGame->screenshots as $igdbScreenshot) {
                $screenshot = new Image();
                $screenshot->setUrl($igdbScreenshot->url);
                $screenshot->setCloudinaryId($igdbScreenshot->image_id);
                $screenshot->setWidth($igdbScreenshot->width);
                $screenshot->setHeight($igdbScreenshot->height);

                $em->persist($screenshot);
                $em->flush();

                $game->addScreenshot($screenshot);
            }
        }

        // Save Videos
        if (isset($igdbGame->videos)) {
            foreach ($igdbGame->videos as $igdbVideo) {
                $video = new Video();
                $video->setName($igdbVideo->name);
                $video->setYoutubeId($igdbVideo->video_id);
                $em->persist($video);
                $em->flush();
                $game->addVideo($video);
            }
        }


        // Series
        // TODO: notify admin to set Series

        // Companies
        $companyRepository = $this->getDoctrine()->getRepository('GameBundle:Company');

        if (isset($igdbGame->involved_companies)) {
            $addedDevelopers = [];
            $addedPublishers = [];
            foreach ($igdbGame->involved_companies as $igdbCompany) {
                if (($igdbCompany->developer && !in_array($igdbCompany->company->id, $addedDevelopers)
                     || ($igdbCompany->publisher && !in_array($igdbCompany->company->id, $addedPublishers) {
                    $company = $companyRepository->findOneByIgdbId($igdbCompany->company->id);
                    if (is_null($company)) {
                        $company = new Company();
                        $company->setIgdbId($igdbCompany->company->id);

                        $company->setName($igdbCompany->company->name);
                        $company->setIgdbUrl($igdbCompany->company->url);

                        $em->persist($company);
                        $em->flush();
                    }

                    if ($igdbCompany->developer) {
                        $game->addDeveloper($company);
                        $addedDevelopers[] = $igdbCompany->company->id;
                    } elseif ($igdbCompany->publisher) {
                        $game->addPublisher($company);
                        $addedPublishers[] = $igdbCompany->company->id;
                    }
                }
            }
        }

        foreach (['mode', 'theme', 'genre'] as $type) {
            $igdbType = $type == 'mode' ? 'game_' . $type : $type;

            if (isset($igdbGame->{$igdbType . 's'})) {

                foreach ($igdbGame->{$igdbType . 's'} as $igdbTag) {
                    $tagRepository = $this->getDoctrine()->getRepository('GameBundle:' . ucfirst($type));
                    $tag = $tagRepository->findOneByIgdbId($igdbTag->id);
                    if (is_null($tag)) {

                        $class = "GameBundle\\Entity\\" . ucfirst($type);
                        $tag = new $class();
                        $tag->setIgdbId($igdbTag->id);

                        $tag->setName($igdbTag->name);
                        $tag->setIgdbUrl($igdbTag->url);

                        $em->persist($tag);
                        $em->flush();
                    }

                    $method = 'add' . ucfirst($type);
                    $game->$method($tag);
                }
            }
        }

        $game->setIgdbUpdate(true);

        $em->persist($game);
        $em->flush();

        return [$game, $userGameReleaseDate];
    }

    /**
     * @Rest\View
     * @Rest\Get("/batch/games/videos")
     */
    public function updateVideosAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        ini_set('max_execution_time', 0);

        $t0 = microtime(true);

        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
        $limit = 20;

        $games = $gameRepository->findBy([
            'igdbUpdate' => false
        ], [], $limit);

        foreach ($games as $game) {

            $this->igdbGame($game);
        }

        $count = $gameRepository->countByIgdbUpdate(false);
        $total = $gameRepository->countAll();
        $t1 = microtime(true);
        $message = count($games) . ' game(s) updated, ' . $count . '/' . $total . ' remaining (' . round($t1 - $t0, 3) . 's)';
        return View::create(['message' => $message], Response::HTTP_OK);
    }

    /**
     * @Rest\View
     * @Rest\Get("/batch/series/csv")
     */
    public function seriesCsvAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        ini_set('max_execution_time', 0);

        $csvFile = $this->container->get('kernel')->getRootDir() . '/../var/csv/VGC_Series.csv';
        $row = 0;

        if (($handle = fopen($csvFile, 'r')) !== FALSE) {

            // Manager / Repositories
            $em = $this->getDoctrine()->getManager();
            $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
            $seriesRepository = $this->getDoctrine()->getRepository('GameBundle:Series');
            $gamesUpdated = 0;

            // Boucle sur les lignes du CSV
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if ($row > 1) {

                    // Nom du jeu
                    $name = $data[0];

                    // Noms des séries
                    $num = count($data) - 1;
                    $seriesNames = [];
                    for ($c = 1; $c < $num; $c++) {

                        if ($data[$c] != '') {

                            $seriesNames[] = $data[$c];
                        }
                    }

                    if (count($seriesNames) > 0) {

                        // Jeu
                        $game = $gameRepository->findOneBy([
                            'name' => $name
                        ]);

                        // Jeu introuvable : 404
                        if (is_null($game)) {
                            continue;
                        } else {

                            // Boucle sur les noms des séries

                            foreach ($game->getSeries() as $gameSeries) {
                                $game->removeSeries($gameSeries);
                            }

                            foreach ($seriesNames as $seriesName) {

                                // Série
                                $series = $seriesRepository->findOneBy([
                                    'name' => $seriesName
                                ]);

                                // Série introuvable : ajout
                                if (is_null($series)) {

                                    $series = new Series();
                                    $series->setName($seriesName);
                                    $em->persist($series);
                                    $em->flush();
                                }

                                $game->addSeries($series);
                            }

                            $gamesUpdated++;

                            // Sauvegarde du jeu
                            $em->persist($game);
                            $em->flush();
                        }
                    }
                }

                $row++;
            }

            fclose($handle);

            return View::create(['message' => $gamesUpdated . ' game(s) updated'], Response::HTTP_OK);
        } else {
            throw new HttpException(404, "Series CSV File Not Found");
        }
    }

    /**
     * @Rest\View
     * @Rest\Get("/user/places")
     */
    public function getPlacesAction()
    {
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $places = $userGameRepository->userPlaces($this->getUser());

        return $places;
    }
}
