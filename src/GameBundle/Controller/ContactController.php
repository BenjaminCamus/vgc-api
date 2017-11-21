<?php

/*
 *
 *
 *
 */

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

class ContactController extends FOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/user/contacts")
     */
    public function getContactsAction()
    {
        $contactRepository = $this->getDoctrine()->getRepository('GameBundle:Contact');
        $contacts = $contactRepository->findByUser($this->getUser());

        return $contacts;
    }
}