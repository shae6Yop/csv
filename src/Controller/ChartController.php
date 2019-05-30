<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Person;

class ChartController extends AbstractController
{

    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

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
    public function chartApiFromCsv()
    {
        $rawChartData = file_get_contents('../MOCK_DATA.csv');

        $jsonChartData = $this->makeChartJson($rawChartData);

        if (sizeof($this->manager->getRepository(Person::class)->findAll()) == 0) {
            $this->importCsvPersons($rawChartData, true);
        }

        $jsonPersonsInEachCountryData = $this->makePersonsByCountryChartJson($jsonChartData);

        return new JsonResponse(["message" => $jsonPersonsInEachCountryData]);
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

    /**
     * Return json data with person count by country from a json person array
     */
    private function makePersonsByCountryChartJson($jsonArray)
    {
        /* Isolate unique countries */
        $uniqueCountries = [];
        foreach ($jsonArray as $person) {
            $country = $person->country;
            if (in_array($country, $uniqueCountries) == false) {
                array_push($uniqueCountries, $country);
            }
        }

        $personsInEachCountryArray = [];
        foreach ($uniqueCountries as $country) {
            $countryPersonCount = sizeof($this->manager->getRepository(Person::class)->findBy(["country" => $country]));
            array_push($personsInEachCountryArray, (object) ["country" => $country, "count" => $countryPersonCount]);
        }

        return $personsInEachCountryArray;
    }

    /**
     * csv2person
     * insert = true to insert into database
     * otherwise only return array of Persons
     */
    private function importCsvPersons($rawData, $insert = false)
    {
        $persons = [];

        /* Create an array of Persons */
        foreach (explode("\n", $rawData) as $line) {
            if ($line == "") break;

            $lineData = explode(",", $line);

            if ($lineData[0] == "id") continue;

            $currentObj = new Person();
            $currentObj->setFirstName($lineData[1]);
            $currentObj->setLastName($lineData[2]);
            $currentObj->setEmail($lineData[3]);
            $currentObj->setGender($lineData[4]);
            $currentObj->setCountry($lineData[5]);

            array_push($persons, $currentObj);
        }

        if ($insert === true) {
            foreach ($persons as $p) {
                $this->manager->persist($p);
                $this->manager->flush();
            }
        }

        return $persons;
    }
}
