<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use GameBundle\Entity\Series;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BatchController extends AbstractFOSRestController
{

    const IGDB_UPDATE_LIMIT = 50;
    const SERIES_FILE = 'VGC_Series.csv';

    /**
     * @Rest\View
     * @Rest\Get("/batch/games/update/reset")
     */
    public function updateGamesResetAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');

        $games = $gameRepository->findBy(['igdbUpdate' => true]);

        foreach ($games as $game) {
            $game->setIgdbUpdate(false);
            $this->getDoctrine()->getManager()->persist($game);
        }
        $this->getDoctrine()->getManager()->flush();

        $message = count($games) . ' game(s) resetted';
        return View::create(['message' => $message], Response::HTTP_OK);
    }

    /**
     * @Rest\View
     * @Rest\Get("/batch/games/update")
     */
    public function updateGamesAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        ini_set('max_execution_time', 0);

        $t0 = microtime(true);

        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');

        $games = $gameRepository->findBy([
            'igdbUpdate' => false
        ], [], self::IGDB_UPDATE_LIMIT);

        foreach ($games as $game) {
            $igdbService = $this->container->get('igdb');
            $igdbService->update($game->getIgdbId());
        }
        $this->getDoctrine()->getManager()->flush();

        $count = $gameRepository->countByIgdbUpdate(false);
        $total = $gameRepository->countAll();
        $t1 = microtime(true);
        $message = count($games) . ' game(s) updated, ' . $count . '/' . $total . ' remaining (' . round($t1 - $t0, 3) . 's)';
        return View::create(['message' => $message], Response::HTTP_OK);
    }

    /**
     * Return series CSV file's path
     * @return string
     */
    private function getSeriesCsvPath() {
        return $this->container->get('kernel')->getRootDir() . '/../var/csv/';
    }

    /**
     * @Rest\View
     * @Rest\Get("/batch/series/export")
     */
    public function seriesExportAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        ini_set('max_execution_time', 0);

        $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
        $csv = [];
        $seriesMax = 0;

        foreach ($gameRepository->findBy([], ['name' => 'asc']) as $game) {
            $gameSeries = [];
            /** @var Series $series */
            foreach ($game->getSeries() as $series) {
                $gameSeries[] = $series->getName();
            }
            sort($gameSeries);
            $seriesMax = max($seriesMax, count($gameSeries));

            $line = [$game->getName()];
            foreach ($gameSeries as $series) {
                $line[] = $series;
            }
            $csv[] = $line;
        }

        $firstLine = ['Game'];
        for ($i = 1; $i <= $seriesMax; $i++) {
            $firstLine[] = 'Series '.$i;
        }
        array_unshift($csv, $firstLine);

        $dir = $this->getSeriesCsvPath();
        if (!is_dir($dir)) {
            mkdir($dir, 0755,true);
        }
        $fp = fopen($dir.self::SERIES_FILE, 'w');
        if ($fp !== false) {
            foreach ($csv as $csvLine) {
                fputcsv($fp, $csvLine);
            }
            fclose($fp);

            return $csv;
        }
        return false;
    }

    /**
     * @Rest\View
     * @Rest\Get("/batch/series/import")
     */
    public function seriesImportAction()
    {
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new HttpException(403, "Super Admin Only");
        }

        ini_set('max_execution_time', 0);
        $row = 0;

        if (($handle = fopen($this->getSeriesCsvPath().self::SERIES_FILE, 'r')) !== FALSE) {

            // Manager / Repositories
            $em = $this->getDoctrine()->getManager();
            $gameRepository = $this->getDoctrine()->getRepository('GameBundle:Game');
            $seriesRepository = $this->getDoctrine()->getRepository('GameBundle:Series');
            $gamesUpdated = 0;

            // Boucle sur les lignes du CSV
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if ($row > 0) {

                    // Nom du jeu
                    $name = $data[0];

                    // Noms des séries
                    $num = count($data);
                    $seriesNames = [];
                    for ($c = 1; $c < $num; $c++) {

                        if ($data[$c] != '') {

                            $seriesNames[] = $data[$c];
                        }
                    }

                    if (count($seriesNames) > 0) {

                        // Jeu
                        $game = $gameRepository->findOneBy([
                            'name' => $name
                        ]);

                        // Jeu introuvable : 404
                        if (is_null($game)) {
                            continue;
                        } else {

                            // Boucle sur les noms des séries

                            foreach ($game->getSeries() as $gameSeries) {
                                $game->removeSeries($gameSeries);
                            }

                            foreach ($seriesNames as $seriesName) {

                                // Série
                                $series = $seriesRepository->findOneBy([
                                    'name' => $seriesName
                                ]);

                                // Série introuvable : ajout
                                if (is_null($series)) {

                                    $series = new Series();
                                    $series->setName($seriesName);
                                    $em->persist($series);
                                }

                                $game->addSeries($series);
                            }

                            $gamesUpdated++;

                            // Sauvegarde du jeu
                            $em->persist($game);
                        }
                    }
                }

                $row++;
            }

            $em->flush();
            fclose($handle);

            return View::create(['message' => $gamesUpdated . ' game(s) updated'], Response::HTTP_OK);
        } else {
            throw new HttpException(404, "Series CSV File Not Found");
        }
    }
}
