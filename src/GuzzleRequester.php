<?php
declare(strict_types=1);

namespace LiqPay;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use src\Exceptions\LiqPayApiException;

class GuzzleRequester
{
    private int $lastResponseCode = 0;

    /**
     * Виконує HTTP POST запит за допомогою Guzzle.
     *
     * @param string $url
     * @param array  $postFields
     * @param int    $timeout
     *
     * @return string|null
     */
    public function makeRequest(string $url, array $postFields, int $timeout = 5): ?array
    {
        $client = new GuzzleClient([
            'timeout' => $timeout,
            'verify'  => true,
        ]);

        try {
            $response = $client->request('POST', $url, [
                'form_params' => $postFields,
            ]);
            if($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
                $error = $response->getBody()->getContents();
                if(json_decode($error, true)) {
                    $error = json_decode($error, true)['error']??'Unknown error';
                }
                throw new LiqPayApiException($error);
            }
            $this->lastResponseCode = $response->getStatusCode();
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new LiqPayApiException($e->getMessage());
        }
    }

    /**
     * Повертає останній HTTP-код відповіді.
     *
     * @return int
     */
    public function getLastResponseCode(): int
    {
        return $this->lastResponseCode;
    }
}
