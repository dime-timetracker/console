<?php
namespace DimeConsole;
use GuzzleHttp\Client;

class DimeConsole
{
    protected $clientId = '5F30E334-52A6-42D3-BE39-3719DE137C08';
    protected $username;
    protected $password;
    protected $baseUrl;

    protected $token;

    protected $client;

    public function __construct() {
        $this->client = new Client();
    }

    public function readConfig() {
        $configXml = simplexml_load_file('config.xml');
        $this->username = (string)$configXml->username;
        $this->password = (string)$configXml->password;
        $this->baseUrl = (string)$configXml->baseUrl;
    }

    public function login() {
        $route = '/public/login';
        $postData = [
            'client' => $this->clientId,
            'password' => $this->password,
            'username' => $this->username,
        ];
        $postString = json_encode($postData);
        $headers = [
            'Accept' => 'application/json, text/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($postString),
        ];

        $response = $this->client->post($this->baseUrl . $route, ['headers' => $headers, 'body' => $postString]);
        $dataObject = json_decode($response->getBody());

        $this->token = $dataObject->token;
    }

    public function showActivities() {
        $route = '/public/api/activity';
        $credentials = [
            'user' => $this->username,
            'clientId' => $this->clientId,
            'token' => $this->token,
        ];
        $authName = 'DimeTimetracker';
        $headers = [
            'Authorization' =>  $authName . ' ' . implode(',', $credentials),
            'Accept' => 'application/json, text/*',
        ];

        $response = $this->client->get($this->baseUrl . $route, ['headers' => $headers]);
        $activities = json_decode($response->getBody()->getContents());

        foreach ($activities as $activity) {
            echo $activity->description . PHP_EOL;
        }
    }
}
