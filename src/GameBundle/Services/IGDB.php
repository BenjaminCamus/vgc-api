<?php
namespace GameBundle\Services;

use Unirest;

class IGDB
{
    const IGDB_URL = 'https://api-v3.igdb.com/';
    private $igdbKey;

    public function __construct($igdbKey)
    {
        $this->igdbKey = $igdbKey;
    }

    public function get($id)
    {
        $param = 'where id=' . $id . ';';
        return $this->response($param);
    }

    private function response($param)
    {
        $body = 'fields '
            . 'cover.height, '
            . 'cover.image_id, '
            . 'cover.url, '
            . 'cover.width, '
            . 'created_at, '
            . 'genres.name, '
            . 'genres.url, '
            . 'involved_companies.company.name, '
            . 'involved_companies.company.url, '
            . 'involved_companies.developer, '
            . 'involved_companies.publisher, '
            . 'name, '
            . 'game_modes.name, '
            . 'game_modes.url, '
            . 'platforms.name, '
            . 'platforms.url, '
            . 'release_dates.platform, '
            . 'release_dates.date, '
            . 'screenshots.height, '
            . 'screenshots.image_id, '
            . 'screenshots.url, '
            . 'screenshots.width, '
            . 'summary, '
            . 'themes.name, '
            . 'themes.url, '
            . 'total_rating, '
            . 'total_rating_count, '
            . 'updated_at, '
            . 'url, '
            . 'videos.name, '
            . 'videos.video_id;'
            . 'limit 10;'
            . $param;

        $response = Unirest\Request::post(self::IGDB_URL . 'games',
            array(
                'user-key' => $this->igdbKey,
                'Accept' => 'application/json'
            ),
            $body
        );

        if ($response->code == 200) {
            return $response->body;
        } else {
            return false;
        }
    }

    public function search($search)
    {
        $param = 'search "' . addslashes($search) . '";';
        return $this->response($param);
    }
}