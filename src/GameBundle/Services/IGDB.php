<?php

namespace GameBundle\Services;

use Doctrine\ORM\EntityManager;
use GameBundle\Entity\Company;
use GameBundle\Entity\Game;
use GameBundle\Entity\Image;
use GameBundle\Entity\Platform;
use GameBundle\Entity\ReleaseDate;
use GameBundle\Entity\Video;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unirest;

class IGDB
{
    const IGDB_URL = 'https://api-v3.igdb.com/';
    private $em;
    private $igdbKey;

    public function __construct(EntityManager $em, $igdbKey)
    {
        $this->em = $em;
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
            . 'limit 30;'
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

    public function update($id)
    {
        $gameRepository = $this->em->getRepository('GameBundle:Game');
        $platformRepository = $this->em->getRepository('GameBundle:Platform');

        /** @scrutinizer ignore-call */
        $game = $gameRepository->findOneByIgdbId($id);

        if (null === $game) {
	    $game = new Game();
	    $game->setIgdbId($id);
        }

        foreach (['screenshot', 'video', 'developer', 'publisher', 'mode', 'theme', 'genre'] as $type) {
            foreach ($game->{'get' . ucfirst($type) . 's'}() as $obj) {
                $game->{'remove' . ucfirst($type)}($obj);
            }
        }

        // Get IGDB game
        $igdb = $this->get($game->getIgdbId());

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

        // TODO: cover + screenshots(ajouter images si absentes, recherche avec url)
        // TODO: rendre image->url unique
        // Save cover Image
        $cover = $game->getCover();
        if (null === $cover) {
            $cover = new Image();
        }
        $cover->setUrl($igdbGame->cover->url);
        $cover->setCloudinaryId($igdbGame->cover->image_id);
        $cover->setWidth($igdbGame->cover->width);
        $cover->setHeight($igdbGame->cover->height);
        $this->em->persist($cover);
        $this->em->flush();

        $game->setCover($cover);

        // Save screenshots Images
        if (isset($igdbGame->screenshots)) {
            foreach ($igdbGame->screenshots as $igdbScreenshot) {
                if (is_object($igdbScreenshot)
                    && property_exists($igdbScreenshot, 'url')
                    && property_exists($igdbScreenshot, 'image_id')
                    && property_exists($igdbScreenshot, 'width')
                    && property_exists($igdbScreenshot, 'height')) {
                    $screenshot = new Image();
                    $screenshot->setUrl($igdbScreenshot->url);
                    $screenshot->setCloudinaryId($igdbScreenshot->image_id);
                    $screenshot->setWidth($igdbScreenshot->width);
                    $screenshot->setHeight($igdbScreenshot->height);
                    $this->em->persist($screenshot);
                    $game->addScreenshot($screenshot);
                }
            }
        }

        // Save Videos
        if (isset($igdbGame->videos)) {
            foreach ($igdbGame->videos as $igdbVideo) {
                if (is_object($igdbVideo)
                    && property_exists($igdbVideo, 'name')
                    && property_exists($igdbVideo, 'video_id')) {
                    $video = new Video();
                    $video->setName($igdbVideo->name);
                    $video->setYoutubeId($igdbVideo->video_id);
                    $this->em->persist($video);
                    $game->addVideo($video);
                }
            }
        }

        foreach ($game->getReleaseDates() as $releaseDate) {
            $this->em->remove($releaseDate);
        }

        // Series
        // TODO: notify admin to set Series

        // Companies
        $companyRepository = $this->em->getRepository('GameBundle:Company');

        if (isset($igdbGame->involved_companies)) {
            foreach ($igdbGame->involved_companies as $igdbCompany) {
                /** @scrutinizer ignore-call */
                $company = $companyRepository->findOneByIgdbId($igdbCompany->company->id);
                if (is_null($company)) {
                    $company = new Company();
                    $company->setIgdbId($igdbCompany->company->id);
                    $company->setName($igdbCompany->company->name);
                    $company->setIgdbUrl($igdbCompany->company->url);
                    $this->em->persist($company);
                    $this->em->flush();
                }

                if ($igdbCompany->developer) {
                    $game->addDeveloper($company);
                } elseif ($igdbCompany->publisher) {
                    $game->addPublisher($company);
                }
            }
        }

        foreach (['mode', 'theme', 'genre'] as $type) {
            $igdbType = $type == 'mode' ? 'game_' . $type : $type;

            if (isset($igdbGame->{$igdbType . 's'})) {

                foreach ($igdbGame->{$igdbType . 's'} as $igdbTag) {
                    $tagRepository = $this->em->getRepository('GameBundle:' . ucfirst($type));
                    /** @scrutinizer ignore-call */
                    $tag = $tagRepository->findOneByIgdbId($igdbTag->id);
                    if (is_null($tag)) {
                        $class = "GameBundle\\Entity\\" . ucfirst($type);
                        $tag = new $class();
                        $tag->setIgdbId($igdbTag->id);
                        $tag->setName($igdbTag->name);
                        $tag->setIgdbUrl($igdbTag->url);
                        $this->em->persist($tag);
                        $this->em->flush();
                    }
                    $method = 'add' . ucfirst($type);
                    $game->$method($tag);
                }
            }
        }

        $game->setIgdbUpdate(true);
	$this->em->persist($game);

        // Save Release Dates
        if (isset($igdbGame->release_dates)) {
            foreach ($igdbGame->release_dates as $igdbReleaseDate) {
                if (is_object($igdbReleaseDate) && property_exists($igdbReleaseDate, 'date')) {
                    /** @scrutinizer ignore-call */
                    $platform = $platformRepository->findOneByIgdbId($igdbReleaseDate->platform);
                    if (null !== $platform) {
                        $releaseDate = new ReleaseDate();
                        $releaseDate->setGame($game);
                        $releaseDate->setPlatform($platform);
                        $releaseDate->setDate(new \DateTime(date('Y-m-d H:i:s', $igdbReleaseDate->date)));
                        $this->em->persist($releaseDate);
			$game->addReleaseDate($releaseDate);
                    }
                }
            }
        }

        return $game;
    }
}
