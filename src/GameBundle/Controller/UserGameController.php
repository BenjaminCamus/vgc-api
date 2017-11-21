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
use GameBundle\Entity\UserGame;
use GameBundle\Form\ContactType;
use GameBundle\Form\UserGameType;
use GameBundle\Utils\IGDB;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserGameController extends FOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/user/games")
     */
    public function getUserGamesAction()
    {
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $userGames = $userGameRepository->findBy([
            'user' => $this->getUser()
        ]);

        return $userGames;
    }

    /**
     * @Rest\View
     * @Rest\Get("/user/games/{platformSlug}/{gameSlug}", requirements={"platformSlug" = "^[a-z0-9]+(?:-[a-z0-9]+)*$", "gameSlug" = "^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     */
    public function getGameAction($platformSlug, $gameSlug)
    {
        $platformRepository = $this->getDoctrine()->getRepository('GameBundle:Platform');
        $platform = $platformRepository->findOneBySlug($platformSlug);

        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
        $game = $gameRepository->findOneBySlug($gameSlug);

        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $userGameCheck = $userGameRepository->findBy([
            'user' => $this->getUser(),
            'platform' => $platform,
            'game' => $game
        ]);

        $userGame = $userGameCheck[0];

        return $userGame;
    }

    /**
     * @Rest\View(statusCode=Symfony\Component\HttpFoundation\Response::HTTP_CREATED)
     * @Rest\Post("/user/games/add")
     */
    public function postGameAction(Request $request)
    {
        $requestValues = $formValues = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $igdbService = $this->container->get('igdb');

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
        $formValues['platform'] = is_null($platform) ? null : $platform->getId();

        // Contacts / Places
        $contactRepository = $this->getDoctrine()->getRepository('GameBundle:Contact');
        $placeRepository = $this->getDoctrine()->getRepository('GameBundle:Place');

        if (!isset($requestValues['purchaseContact']) || !isset($requestValues['purchaseContact']['id'])) {
            return View::create(['message' => 'Purchase Contact Id is missing.'], Response::HTTP_NOT_FOUND);
        }

        foreach (['purchase', 'sale'] as $type) {

            $formValues[$type . 'Contact'] = ${$type . 'Contact'} = null;
            $formValues[$type . 'Place'] = ${$type . 'Place'} = null;

            if (isset($requestValues[$type . 'Contact'])) {
                ${$type . 'Contact'} = $contactRepository->find($requestValues[$type . 'Contact']['id']);
                $formValues[$type . 'Contact'] = is_null(${$type . 'Contact'}) ? null : ${$type . 'Contact'}->getId();
            }
            if (isset($requestValues[$type . 'Place'])) {
                ${$type . 'Place'} = $placeRepository->find($requestValues[$type . 'Place']['id']);
                $formValues[$type . 'Place'] = is_null(${$type . 'Place'}) ? null : ${$type . 'Place'}->getId();
            }
        }

        // Form UserGame
        $form = $this->createForm(UserGameType::class);
        $form->submit($formValues); // Validation des données

        if ($form->isValid()) {

            if (is_null($game)) {

                // Game not in db : new Game
                $game = new Game();
                $game->setIgdbId($requestValues['game']['igdbId']);

                // Get IGDB game
                $igdb = $igdbService->get('games/' . $game->getIgdbId() . '?fields=*');
                $igdbGame = $igdb[0];

                // Game fields
                $game->setName($igdbGame->name);
                $totalRating = isset($igdbGame->total_rating) ? $igdbGame->total_rating : 0;
                $game->setRating(round($totalRating / 100 * 20, 2));
                $totalRatingCount = isset($igdbGame->total_rating_count) ? $igdbGame->total_rating_count : 0;
                $game->setRatingCount($totalRatingCount);
                $game->setIgdbUrl($igdbGame->url);
                $game->setIgdbCreatedAt(new \DateTime(date('Y-m-d H:i:s', $igdbGame->created_at)));
                $game->setIgdbUpdatedAt(new \DateTime(date('Y-m-d H:i:s', $igdbGame->updated_at)));


                // @TODO: cover + screenshots(ajouter images si absentes, recherche avec url)
                // @TODO: rendre image->url unique
                // Save cover Image
                $cover = new Image();
                $cover->setUrl($igdbGame->cover->url);
                $cover->setCloudinaryId($igdbGame->cover->cloudinary_id);
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
                        $screenshot->setCloudinaryId($igdbScreenshot->cloudinary_id);
                        $screenshot->setWidth($igdbScreenshot->width);
                        $screenshot->setHeight($igdbScreenshot->height);

                        $em->persist($screenshot);
                        $em->flush();

                        $game->addScreenshot($screenshot);
                    }
                }

                // Series
                // TODO: notify admin to set Series

                // Companies
                $companyRepository = $this->getDoctrine()->getRepository('GameBundle:Company');

                foreach (['developer', 'publisher'] as $type) {
                    if (isset($igdbGame->{$type . 's'})) {
                        foreach ($igdbGame->{$type . 's'} as $igdbCompanyId) {
                            $company = $companyRepository->findOneByIgdbId($igdbCompanyId);
                            if (count($company) == 0) {
                                $company = new Company();
                                $company->setIgdbId($igdbCompanyId);

                                $igdb = $igdbService->get('companies/' . $company->getIgdbId() . '?fields=*');
                                $igdbCompany = $igdb[0];

                                $company->setName($igdbCompany->name);
                                $company->setIgdbUrl($igdbCompany->url);

                                $em->persist($company);
                                $em->flush();
                            }

                            $method = 'add' . ucfirst($type);
                            $game->$method($company);
                        }
                    }
                }

                foreach (['mode', 'theme', 'genre'] as $type) {
                    $igdbType = $type == 'mode' ? 'game_' . $type : $type;

                    if (isset($igdbGame->{$igdbType . 's'})) {

                        foreach ($igdbGame->{$igdbType . 's'} as $igdbTagId) {
                            $tagRepository = $this->getDoctrine()->getRepository('GameBundle:' . ucfirst($type));
                            $tag = $tagRepository->findOneByIgdbId($igdbTagId);
                            if (is_null($tag)) {

                                $class = "GameBundle\\Entity\\" . ucfirst($type);
                                $tag = new $class();
                                $tag->setIgdbId($igdbTagId);

                                $igdb = $igdbService->get($igdbType . 's/' . $tag->getIgdbId() . '?fields=*');
                                $igdbTag = $igdb[0];

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

                $em->persist($game);
                $em->flush();

            }

            if (is_null($platform)) {

                // Platform not in db : new Platform
                $platform = new Platform();
                $platform->setIgdbId($requestValues['platform']['igdbId']);

                // Get IGDB game
                $igdb = $igdbService->get('platforms/' . $platform->getIgdbId() . '?fields=*');
                $igdbPlatform = $igdb[0];

                $platform->setName($igdbPlatform->name);
                $platform->setIgdbUrl($igdbPlatform->url);

                $em->persist($platform);
                $em->flush();
            }

            $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
            $userGameCheck = $userGameRepository->findBy([
                'user' => $this->getUser(),
                'game' => $game,
                'platform' => $platform
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

//                    return var_dump($formContact->isValid(), $requestValues[$type . 'Contact']);
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

                $method = 'set' . ucfirst($type) . 'Place';
                $userGame->$method(${$type . 'Place'});

                // Date
                if (isset($requestValues[$type . 'Date'])) {
                    $method = 'set' . ucfirst($type) . 'Date';
                    $userGame->$method(new \DateTime());
                }
            }

            $userGame->setUser($this->getUser());
            $userGame->setGame($game);
            $userGame->setPlatform($platform);

            $userGame->setRating($request->request->get('rating'));
            $userGame->setBox($request->request->get('box'));
            $userGame->setManual($request->request->get('manual'));
            $userGame->setVersion($request->request->get('version'));

            $userGame->setPriceAsked($request->request->get('priceAsked'));
            $userGame->setPricePaid($request->request->get('pricePaid'));
            $userGame->setPriceResale($request->request->get('priceResale'));
            $userGame->setPriceSold($request->request->get('priceSold'));

            $userGame->setPurchaseDate(new \DateTime($request->request->get('purchaseDate')));
            $saleDate = is_null($request->request->get('saleDate')) ? null : new \DateTime($request->request->get('saleDate'));
            $userGame->setSaleDate($saleDate);

            $userGame->setProgress($request->request->get('progress'));

            $userGame->setNote($request->request->get('note'));

            $em->persist($userGame);
            $em->flush();

            return $userGame;
        } else {
            return $form;
        }
    }
}