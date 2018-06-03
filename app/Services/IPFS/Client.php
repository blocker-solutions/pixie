<?php

namespace Pixie\Services\IPFS;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Client.
 *
 * IPFS simple client implementation for allowing adding a file.
 */
class Client
{
    /**
     * @var string IPFS API URL.
     */
    protected $apiURL;

    /**
     * @var HttpClient Guzzle
     */
    protected $httpClient;

    /**
     * Client constructor.
     *
     * @param string $apiURL
     */
    public function __construct(string $apiURL = 'http://ipfs:5001/api/v0/')
    {
        // assign the IPFS API URL.
        $this->apiURL = $apiURL;

        // assign the http client instance.
        $this->httpClient = new HttpClient([
            'base_uri'        => $apiURL,
            'max'             => 5,
            'strict'          => false,
            'referer'         => false,
            'protocols'       => ['http'],
            'track_redirects' => false,
            'expect'          => true,
        ]);
    }

    /**
     * @param string $contents
     *
     * @return null|string Hash of the added object.
     */
    public function add(string $contents)
    {
        try {
            $response = $this->httpClient->request('POST', 'add', [
                'multipart' => [
                    [
                        'Content-Type' => 'multipart/formdata',
                        'name'         => 'object_to_add',
                        'contents'     => $contents,
                    ],
                ],
                'query' => $this->getQueryParameters(),
            ]);

            // get response body contents.
            $decodedResponse = json_decode(html_entity_decode(htmlentities($response->getBody()->getContents())), true);

            return array_get($decodedResponse, 'Hash', null);
        } catch (GuzzleException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * IPFS add query parameters.
     *
     * @return array
     */
    protected function getQueryParameters(): array
    {
        return [
            // not hidden.
            'H' => false,
            // add, not only generate hash.
            'n' => false,
            // no progress.
            'p' => false,
            // pin file
            'pin' => true,
            // not recursive (single file).
            'r' => false,
            // no tickle
            't' => false,
            // no wrap.
            'w' => false,
        ];
    }
}
