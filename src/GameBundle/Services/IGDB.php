<?php
namespace GameBundle\Services;

use Unirest;

class IGDB
{
    private $mashapeKey;

    public function __construct($mashapeKey)
    {
        $this->mashapeKey = $mashapeKey;
    }

    public function get($path)
    {

        $response = Unirest\Request::get('https://igdbcom-internet-game-database-v1.p.mashape.com/' . $path,
            array(
                'X-Mashape-Key' => $this->mashapeKey,
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