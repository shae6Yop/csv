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

         $jsonChartData = $this->makeChartJson($rawChartData);

        return new JsonResponse(["message" => $jsonChartData]);
    }

    /**
     * Translate csv data into json
     */
    private function makeChartJson($rawData) :?array
    {
        $jsonArray = [];

        /* Create an array of objects */
        foreach (explode("\n", $rawData) as $line) {
            if ($line == "") break;

            $lineData = explode(",", $line);

            if ($lineData[0] == "id") continue;

            $currentObj = (object) [
                "id"        => $lineData[0],
                "firstName" => $lineData[1],
                "lastName"  => $lineData[2],
                "email"     => $lineData[3],
                "gender"    => $lineData[4],
                "country"   => $lineData[5]
            ];

            array_push($jsonArray, $currentObj);
        }

        return $jsonArray;
    }
}
