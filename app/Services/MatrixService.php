<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    $maxRetries = 3;
    $retryDelay = 1000000; // 1 seconde en microsecondes

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Délai exponentiel entre les tentatives
            if ($attempt > 1) {
                $delay = $retryDelay * $attempt; // Augmente le délai à chaque tentative
                usleep($delay);
            }

            $response = $this->client
                ->withHeader('Accept', 'application/xml')
                ->get($url);

            // Vérifier le code de statut HTTP
            $status = $response->status();
            if ($status === 429) { // Too Many Requests
                $retryAfter = $response->header('Retry-After', 5); // 5 secondes par défaut
                sleep($retryAfter);
                continue;
            }

            // Si on arrive ici, la requête a réussi
            return $this->handleSuccessfulResponse($response, $url);

        } catch (\Exception $e) {
            if ($attempt === $maxRetries) {
                Log::error('Échec après plusieurs tentatives', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
            Log::warning("Tentative $attempt échouée, nouvelle tentative...", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

    throw new \RuntimeException("Échec après $maxRetries tentatives pour l'URL: $url");
}

private function handleSuccessfulResponse($response, $url)
{
    $contentType = $response->header('Content-Type');
    $body = $response->body();

    // Log de la réponse complète en mode debug uniquement
    Log::debug('Réponse de l\'API Matrix', [
        'url' => $url,
        'status' => $response->status(),
        'content_type' => $contentType
    ]);

    // Vérifier le type de contenu
    if (!str_contains($contentType, 'xml')) {
        throw new \RuntimeException("Réponse inattendue de type: {$contentType}");
    }

    // Essayer de parser le XML
    $xml = simplexml_load_string($body);
    if ($xml === false) {
        throw new \RuntimeException("Impossible de parser la réponse XML");
    }

    return $xml->asXML();
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

    function getDoors(?int $id = null)
    {
        return $this->get("dooradministration/area/door/{$id}");
    }

    function getDoorName(int $id)
    {
        return $this->xmlToArrayFromString($this->getDoors($id))["Name"];
    }
}
