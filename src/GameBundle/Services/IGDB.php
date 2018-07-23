<?php
namespace GameBundle\Services;

use Unirest;

class IGDB
{
    private $igdbKey;

    public function __construct($igdbKey)
    {
        $this->igdbKey = $igdbKey;
    }

    public function get($path)
    {
        //Unirest\Request::verifyPeer(false);
        $response = Unirest\Request::get('https://api-endpoint.igdb.com/' . $path,
            array(
                'user-key' => $this->igdbKey,
                'Accept' => 'application/json'
            )
        );

        if ($response->code == 200) {
            return $response->body;
        } else {
            return false;
        }
    }
}