<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChartController extends AbstractController
{
    /**
     * @Route("/", name="chart")
     */
    public function index()
    {
        return $this->render("chart.html.twig");
    }

    /**
     * @Route("/json/chart", name="json_chart")
     */
    public function chartApi()
    {
        $rawChartData = file_get_contents('../MOCK_DATA.csv');

        $jsonArray = [];

        /* Create an array of objects */
        foreach (explode("\n", $rawChartData) as $line) {
            $lineData = explode(",", $line);

            $currentObj = (object) [
                "id"        => $lineData[0],
                "firstName" => $lineData[1],
                "lastName"  => $lineData[2],
                "email"     => $lineData[3],
                "gender"    => $lineData[4],
                "country"   => $lineData[5]
            ];
            dump($currentObj);

            array_push($jsonArray, $currentObj);
        }

        /*
         *$jsonChartData = $this->makeChartJson($rawChartData);
         */
         $jsonChartData = $jsonArray;

        return new JsonResponse(["message" => $jsonChartData]);
    }

    /**
     * Translate csv data into json
     */
    private function makeChartJson($rawData) :?array
    {
        $jsonArray = [];

        /* Create an array of objects */
        foreach (explode(",", $rawData) as $line) {
            $currentObj = (object) [
                "id"        => $line[0],
                "firstName" => $line[1],
                "lastName"  => $line[2],
                "email"     => $line[3],
                "gender"    => $line[4],
                "country"   => $line[5]
            ];

            array_push($jsonArray, $currentObj);
        }

        return $jsonArray;
    }
}
