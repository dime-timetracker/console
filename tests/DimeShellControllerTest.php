<?php
/**
 * DimeShellController tests
 */
class DimeShellControllerTest extends \PHPUnit_Framework_TestCase {

    public function testRequestActivities()
    {
        $responseJson = file_get_contents('response.json');
        $activities = json_decode($responseJson);
        $result = [['id' => 3, 'description' => 'test activity', 'status' => 'inactive']];
        $client = $this->getMockBuilder('\DimeConsole\DimeClient')->getMock();
        $client->method('requestActivities')->willReturn($activities);
        $controller = new \DimeConsole\DimeShellController($client);
        $activities = $controller->requestActivities();
        $this->assertEquals($activities, $result);
    }
}
