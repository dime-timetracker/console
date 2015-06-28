<?php
namespace DimeConsole;
use GuzzleHttp\Client;

class DimeClient
{
    protected $clientId = '5F30E334-52A6-42D3-BE39-3719DE137C08';
    protected $username;
    protected $password;
    protected $baseUrl;

    protected $authString;

    protected $client;

    public function __construct() {
        $this->client = new Client();
        $this->readConfig();
        $this->login();
    }

    public function __destruct() {
        $this->logout();
    }

    public function readConfig() {
        $configXml = simplexml_load_file(dirname(__DIR__) . '/config.xml');
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

        $credentials = [
            'user' => $this->username,
            'clientId' => $this->clientId,
            'token' => $dataObject->token,
        ];
        $this->authString = 'DimeTimetracker ' . implode(',', $credentials);
    }

    public function logout() {
        $route = '/public/logout';
        $headers = [
            'Authorization' =>  $this->authString,
        ];
        $this->client->post($this->baseUrl . $route, ['headers' => $headers]);
    }

    public function requestRawActivities() {
        $route = '/public/api/activity';
        $headers = [
            'Authorization' =>  $this->authString,
            'Accept' => 'application/json, text/*',
        ];

        $response = $this->client->get($this->baseUrl . $route, ['headers' => $headers]);
        return json_decode($response->getBody()->getContents());
    }

    public function resumeActivity($activityId) {
        $timeslices = $this->requestTimeslices($activityId);
        foreach ($timeslices as $timeslice) {
            if ($timeslice->stoppedAt === null) {
                throw new \Exception('Sorry, this activity is already active');
            }
        }

        $route = '/public/api/timeslice';
        $startedAt = (new \DateTime())->format('Y-m-d H:i:s');
        $postData = [
            'startedAt' => $startedAt,
            'duration' => 0,
            'activity' => (integer)$activityId,
        ];
        $postString = json_encode($postData);
        $headers = [
            'Accept' => 'application/json, text/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($postString),
            'Authorization' =>  $this->authString,
        ];
        $response = $this->client->post($this->baseUrl . $route, ['headers' => $headers, 'body' => $postString]);
        return $response->getStatusCode();
    }

    public function stopActivity($activityId) {
        $timeslices = $this->requestTimeslices($activityId);
        foreach ($timeslices as $timeslice) {
            if ($timeslice->stoppedAt === null) {
                break;
            }
        }
        if ($timeslice->stoppedAt !== null) {
            throw new \Exception('Sorry, this activitiy can\'t be stopped, because it has been already stopped or has never been started');
        }
        $route = '/public/api/timeslice/' . $timeslice->id;
        $stoppedAt = (new \DateTime())->format('Y-m-d H:i:s');
        $duration = (new \DateTime())->getTimestamp() - (new \DateTime($timeslice->startedAt))->getTimestamp();
        $putData = [
            'createdAt' => $timeslice->createdAt,
            'duration' => $duration,
            'id' => $timeslice->id,
            'startedAt' => $timeslice->startedAt,
            'stoppedAt' => $stoppedAt,
            'updatedAt' => $timeslice->updatedAt,
        ];
        $putString = json_encode($putData);
        $headers = [
            'Accept' => 'application/json, text/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($putString),
            'Authorization' =>  $this->authString,
        ];
        $response = $this->client->put($this->baseUrl . $route, ['headers' => $headers, 'body' => $putString]);
        return $response->getStatusCode();
    }

    private function requestTimeslices($activityId) {
        $route = '/public/api/activity/' . $activityId;
        $headers = [
            'Authorization' =>  $this->authString,
            'Accept' => 'application/json, text/*',
        ];

        $response = $this->client->get($this->baseUrl . $route, ['headers' => $headers]);
        $activity = json_decode($response->getBody()->getContents());
        return $activity->timeslices;
    }
}
