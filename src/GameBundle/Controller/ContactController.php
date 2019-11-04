<?php

/*
 *
 *
 *
 */

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ContactController extends AbstractFOSRestController
{
    /**
     * @Rest\View
     * @Rest\Get("/user/contacts")
     */
    public function getContactsAction()
    {
        $contactRepository = $this->getDoctrine()->getRepository('GameBundle:Contact');
        $contacts = $contactRepository->findByUser(/** @scrutinizer ignore-type */ $this->getUser());

        return $contacts;
    }
}
