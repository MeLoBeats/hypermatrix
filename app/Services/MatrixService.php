<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use SimpleXMLElement;

class MatrixService
{
    private \Illuminate\Http\Client\PendingRequest $client;

    public function __construct(
        private string $baseUrl
    ) {
        $this->client = Http::baseUrl($this->baseUrl . "/" . config('matrix.mandatory_number') . "/" . config('matrix.interface_number'))
            ->withHeader('Authorization', "Bearer " . config('matrix.api_key'))
            ->contentType('application/xml')
            ->timeout(10); // optionnel
    }

    function xmlToArrayFromString(string $xmlContent): array
    {
        $xml = simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new InvalidArgumentException("Invalid XML string.");
        }

        $json = json_encode($xml);
        return json_decode($json, true);
    }

    private function get(string $url)
    {
        $response = $this->client
            ->withHeader('Accept', 'application/xml')
            ->get($url);

        if (!str_contains($response->header('Content-Type'), 'xml')) {
            throw new \RuntimeException("Expected XML response, got: " . $response->header('Content-Type'));
        }

        dump($response->body());
        return simplexml_load_string($response->body())->asXML();
    }

    private function put(string $url, string $data)
    {
        $response = $this->client
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($data, 'application/xml')
            ->put($url);
        $ok = $response->status() === 204;
        if (!$ok) {
            return [
                "status" => $response->status(),
                "error" => $response->body()
            ];
        }

        return true; // ou ['status' => 204]
    }

    public function getPersonByMatricule(string $matricule)
    {
        return $this->get("person/{$matricule}");
    }

    public function getPersonAccessPermissionsByMatricule(string $matricule)
    {
        return $this->get("person/{$matricule}/accesspermissions");
    }

    public function createAccessPermissionByMatricule(string $matricule, string|array $readerNumber, Carbon $from, Carbon $until)
    {
        $xmlContent = $this->get("person/{$matricule}/accesspermissions");
        $xml = simplexml_load_string($xmlContent);
        dump($xml->asXML());

        $ns = "http://dormakaba.com/EAD/MATRIX/RESTApi/v1";
        $xml->registerXPathNamespace('m', $ns);

        // Accéder à la racine et chercher ReaderSpecialPermissions
        $rspList = $xml->xpath('//m:ReaderSpecialPermissions');

        if (empty($rspList)) {
            // Créer le noeud ReaderSpecialPermissions si inexistant
            $rsp = $xml->addChild('ReaderSpecialPermissions', null, $ns);
        } else {
            $rsp = $rspList[0];
        }

        // On uniformise le format du readerNumber
        $readerNumbers = is_array($readerNumber) ? $readerNumber : [$readerNumber];

        foreach ($readerNumbers as $reader) {
            $new = $rsp->addChild('ReaderSpecialPermission', null, $ns);
            $new->addChild('ValidFrom', $from->format('Y-m-d'), $ns);
            $new->addChild('ValidUntil', $until->format('Y-m-d'), $ns);
            $new->addChild('ReaderNumber', $reader, $ns);
            $new->addChild('AccessWeeklyProfileNumber', 8, $ns);
        }

        $body = $xml->asXML();
        return $body;
        file_put_contents(storage_path("logs/matrix-check.xml"), $body);
        return $this->put("person/{$matricule}/accesspermissions", $body);
    }
}
