<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class HyperplanningRestService
{
    private \Illuminate\Http\Client\PendingRequest $client;
    private $dormaFamilyId = "62";

    public function __construct(
        private string $login,
        private string $password,
        private string $baseUrl
    ) {
        $this->client = Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->login, $this->password)
            ->acceptJson()
            ->timeout(10); // optionnel
    }

    public function get(string $url)
    {
        return $this->client->get($url)->json();
    }

    // Tu peux ajouter post(), put(), etc. ici aussi :
    public function post(string $url, array $data = [])
    {
        return $this->client->post($url, $data)->json();
    }

    public function getSalles(): array
    {
        return $this->get('salles?select=cle,nom');
    }

    public function getDormaIdsForOneRoom(int|string $roomId)
    {
        $response = $this->get("salles/{$roomId}/familles/{$this->dormaFamilyId}/rubriques");
        $rubriques = $response["rubriques"] ?? null;

        if (!$rubriques && isset($response["code"]) && $response["code"] == 1) {
            return null;
        }
        return Arr::pluck($response['rubriques'] ?? [], 'cle');
    }

    public function getCoursesByRoomBetweenDates(int|string $roomId, string $fromDate, string $toDate)
    {
        $res = $this->get("salles/{$roomId}/cours?date_debut={$fromDate}&date_fin={$toDate}");
        if (!isset($res["cours"])) {
            return [];
        }
        return $res["cours"];
    }

    public function getCoursesData(array $courseIds)
    {
        $data = [];
        foreach ($courseIds as $courseId) {
            $res = $this->get("cours/{$courseId}/detail_seances_placees");
            array_push($data, $res);
        }
        return $data;
    }

    public function getEnseignantData(string $hp_id)
    {
        return $this->get("enseignants/{$hp_id}?select=nom,prenom,code");
    }

    public function getEnseignantsData(array $hpIds): array
    {
        if (empty($hpIds)) {
            return [];
        }

        $ids = implode(',', $hpIds);

        return $this->get("enseignants?cle={$ids}&select=nom,prenom,code,cle");
    }
}
