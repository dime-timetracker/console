<?php
namespace DimeConsole;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DimeShellController
 * @package DimeConsole
 * @author Thomas Jez
 *
 * controls the Dime client and provides a minimum of business logic
 */
class DimeShellController
{
    protected $client;

    /**
     * @param ContainerBuilder $services
     */
    public function __construct(DimeClient $client) {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function requestActivities() {
        $activities = $this->client->requestActivities();

        $output = [];
        $i = 0;
        foreach ($activities as $activity) {
            $status = 'inactive';
            foreach ($activity->timeslices as $timeslice) {
                if ($timeslice->stoppedAt === null) {
                    $status = 'active';
                    break;
                }
            }
            $outputLine = [];
            $outputLine['id'] = $activity->id;
            $outputLine['description'] = $activity->description;
            $outputLine['status'] = $status;
            $output[$i++] = $outputLine;
        }
        return $output;
    }

    /**
     * @return array
     */
    public function requestActivityIds() {
        $activities = $this->client->requestActivities();

        $ids = [];
        foreach ($activities as $activity) {
            $ids[] = $activity->id;
        }
        return $ids;
    }

    /**
     * @return array
     */
    public function requestActivityNames() {
        $activities = $this->client->requestActivities();

        $names = [];
        foreach ($activities as $activity) {
            $names[str_replace([' ', '(', ')'], ['_', '_', '_'], $activity->description)] = $activity->id;
        }
        return $names;
    }

    /**
     * @param $activityId
     * @return mixed
     * @throws \Exception
     */
    public function resumeActivity($activityId) {
        $this->checkActivityId($activityId);
        return $this->client->resumeActivity($activityId);
    }

    /**
     * @param $activityId
     * @return mixed
     * @throws \Exception
     */
    public function stopActivity($activityId) {
        $this->checkActivityId($activityId);
        return $this->client->stopActivity($activityId);
    }

    /**
     * @param $activityId
     * @throws \Exception
     */
    public function checkActivityId($activityId) {
        $activities = $this->requestActivities();
        $activityIds = [];
        foreach ($activities as $line) {
            $activityIds[] = $line['id'];
        }
        if (!(in_array($activityId, $activityIds))) {
            throw new \Exception('Sorry ' . $activityId . ' isn\'t the id of any activity.');
        }
    }
}
