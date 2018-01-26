<?php

namespace GameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testGetContactsAction()
    {
        $client = static::createClient();
        $url = 'http://api.vgc.local/user/contacts';

        $client->request('GET', $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $client = $this->createAuthenticatedClient();
        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getContent());


//        $data = <<<EOT
//         {
//            "name": "Star Wars T-Shirt",
//            "sku": "SWTS",
//            "price": 50
//        }
//EOT;
//        $this->client->request('POST', '/products/', [], [], [
//            'CONTENT_TYPE' => 'application/json',
//        ], $data);
//        $response = $this->client->getResponse();
//
//        $this->assertResponse($response, 'products/create_response', Response::HTTP_CREATED);
    }

    protected function createAuthenticatedClient($username = 'Ben', $password = 'ben')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            'http://api.vgc.local/login_check',
            array(
                '_username' => $username,
                '_password' => $password,
            )
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }
}
