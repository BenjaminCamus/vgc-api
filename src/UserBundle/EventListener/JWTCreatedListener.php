<?php
/**
 * Created by IntelliJ IDEA.
 * User: David JAY
 * Date: 09/10/2016
 * Time: 19:09
 */

namespace UserBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    // ...

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        // Symfony 2.4+
        //$request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();
        $payload['roles'] = json_encode($event->getUser()->getRoles());

        var_dump($payload);

        $event->setData($payload);
    }
}
