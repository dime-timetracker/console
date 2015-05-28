<?php
namespace DimeConsole;

class DimeConsole
{
    protected $clientId = '5F30E334-52A6-42D3-BE39-3719DE137C08';
    protected $username;
    protected $password;
    protected $baseUrl;

    protected $token;

    public function readConfig() {
        $configXml = simplexml_load_file('config.xml');
        $this->username = (string)$configXml->username;
        $this->password = (string)$configXml->password;
        $this->baseUrl = (string)$configXml->baseUrl;
    }

    public function login() {
        $route = '/public/login';
        $postData = array(
            'client' => $this->clientId,
            'password' => $this->password,
            'username' => $this->username,
        );
        $postString = json_encode($postData);
        $s = curl_init();
        $headers = array (
            'Accept: application/json, text/*',
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($postString),
        );
        curl_setopt($s, CURLOPT_URL, $this->baseUrl . $route);
        curl_setopt($s, CURLOPT_POST, 1);
        curl_setopt($s, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($s);
        $dataObject = json_decode($data);
        $this->token = $dataObject->token;
        curl_close($s);
    }

    public function showActivities() {
        $route = '/public/api/activity';
        $credentials = array (
            'user' => $this->username,
            'clientId' => $this->clientId,
            'token' => $this->token,
        );
        $authName = 'DimeTimetracker';
        $headers = array (
            'Authorization: ' .  $authName . ' ' . implode(',', $credentials),
            'Accept: application/json, text/*',
        );
        $s = curl_init();
        curl_setopt($s, CURLOPT_URL,$this->baseUrl . $route);
        curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($s);
        curl_close($s);
        $activities = json_decode($data);
        foreach ($activities as $activity) {
            echo $activity->description . PHP_EOL;
        }
    }
}
