<?php

namespace JianJye\CFStream;

use GuzzleHttp\Client;
use JianJye\CFStream\Exceptions\InvalidFileException;
use JianJye\CFStream\Exceptions\InvalidOriginsException;
use JianJye\CFStream\Exceptions\OperationFailedException;
use JianJye\CFStream\Exceptions\InvalidCredentialsException;

class CFStream
{
    private $key;
    private $zone;
    private $email;

    /**
     * Initialize CFStream with authentication credentials.
     *
     * @param string $key
     * @param string $zone
     * @param string $email
     */
    public function __construct($key, $zone, $email)
    {
        if (empty($key) || empty($zone) || empty($email)) {
            throw new InvalidCredentialsException();
        }

        $this->key = $key;
        $this->zone = $zone;
        $this->email = $email;

        $this->client = new Client();
    }

    /**
     * Get the status of a video.
     *
     * @param string $resourceUrl
     *
     * @return json Response body contents
     */
    public function status($resourceUrl)
    {
        $response = $this->client->get($resourceUrl, [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Upload a video with a given filepath.
     *
     * @param string $filepath
     *
     * @return string $resourceUrl URL to manage the video resource
     */
    public function upload($filepath)
    {
        $file = fopen($filepath, 'r');
        if (!$file) {
            throw new InvalidFileException();
        }

        $filesize = filesize($filepath);
        $filename = basename($filepath);

        $response = $this->post($filename, $filesize);
        $resourceUrl = $response->getHeader('Location')[0];
        $this->patch($resourceUrl, $file, $filesize);

        return $resourceUrl;
    }

    /**
     * Create a resource on Cloudflare Stream.
     *
     * @param string $filename
     * @param int    $filesize
     *
     * @return object $response Response from Cloudflare
     */
    public function post($filename, $filesize)
    {
        if (empty($filename) || empty($filesize)) {
            throw new InvalidFileException();
        }

        $response = $this->client->post("https://api.cloudflare.com/client/v4/zones/{$this->zone}/media", [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Length' => 0,
                'Tus-Resumable' => '1.0.0',
                'Upload-Length' => $filesize,
                'Upload-Metadata' => "filename {$filename}",
            ],
        ]);

        if (201 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }

        return $response;
    }

    /**
     * Upload the file to Cloudflare Stream.
     *
     * @param string   $resourceUrl
     * @param resource $file        fopen() pointer resource
     * @param int      $filesize
     *
     * @return object $response Response from Cloudflare
     */
    public function patch($resourceUrl, $file, $filesize)
    {
        if (empty($file)) {
            throw new InvalidFileException();
        }

        $response = $this->client->patch($resourceUrl, [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Length' => $filesize,
                'Content-Type' => 'application/offset+octet-stream',
                'Tus-Resumable' => '1.0.0',
                'Upload-Offset' => 0,
            ],
            'body' => $file,
        ]);

        if (204 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }

        return $response;
    }

    /**
     * Delete video from Cloudflare Stream.
     *
     * @param string $resourceUrl
     */
    public function delete($resourceUrl)
    {
        $response = $this->client->delete($resourceUrl, [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Length' => 0,
            ],
        ]);

        if (204 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }
    }

    /**
     * Get embed code for the video.
     *
     * @param string $resourceUrl
     *
     * @return string HTML embed code
     */
    public function code($resourceUrl)
    {
        $response = $this->client->get("{$resourceUrl}/embed", [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }

        return $response->getBody()->getContents();
    }

    /**
     * Set allowedOrigins on the video.
     *
     * @param string $resourceUrl
     * @param string $origins     Comma separated hostnames
     */
    public function allow($resourceUrl, $origins)
    {
        if (false !== strpos($origins, '/')) {
            throw new InvalidOriginsException();
        }

        $videoId = @end(explode('/', $resourceUrl));

        $response = $this->client->post($resourceUrl, [
            'body' => "{\"uid\": \"{$videoId}\", \"allowedOrigins\": [\"{$origins}\"]}",
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }
    }
}
