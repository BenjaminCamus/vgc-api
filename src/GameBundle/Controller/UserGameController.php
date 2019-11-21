<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use GameBundle\Entity\Company;
use GameBundle\Entity\Contact;
use GameBundle\Entity\Game;
use GameBundle\Entity\Image;
use GameBundle\Entity\UserGame;
use GameBundle\Entity\Video;
use GameBundle\Form\ContactType;
use GameBundle\Form\UserGameType;
use GameBundle\Repository\UserGameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserGameController extends AbstractFOSRestController
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
        return $userGameRepository->countByUser(/** @scrutinizer ignore-type */ $this->getUser());
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
        /** @scrutinizer ignore-call */
        $game = $gameRepository->findOneByIgdbId($requestValues['game']['igdbId']);
        $formValues['game'] = is_null($game) ? null : $game->getId();

        // Platform
        if (!isset($requestValues['platform']) || !isset($requestValues['platform']['igdbId'])) {
            return View::create(['message' => 'IGDB Platform Id is missing.'], Response::HTTP_NOT_FOUND);
        }
        $platformRepository = $this->getDoctrine()->getRepository('GameBundle:Platform');
        /** @scrutinizer ignore-call */
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
        $form = $this->createForm(UserGameType::class);
        $form->submit($formValues); // Validation des données

        if ($form->isValid()) {

            $igdbService = $this->container->get('igdb');
            $game = $igdbService->update($requestValues['game']['igdbId']);

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

            $userGame->setUser(/** @scrutinizer ignore-type */ $this->getUser());
            $userGame->setGame($game);
            $userGame->setPlatform($platform);

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

    /**
     * @Rest\View
     * @Rest\Get("/user/places")
     */
    public function getPlacesAction()
    {
        /** @var UserGameRepository $userGameRepository */
        $userGameRepository = $this->getDoctrine()->getRepository('GameBundle:UserGame');
        $places = $userGameRepository->userPlaces(/** @scrutinizer ignore-type */ $this->getUser());

        return $places;
    }
}
