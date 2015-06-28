<?php
namespace DimeConsole;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class DimeShellController
{
    protected $client;

    public function __construct(ContainerBuilder $services) {
        $this->client = $services->get('client');
    }

    public function requestActivities() {
        $activities = $this->client->requestRawActivities();

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

    public function requestActivityIds() {
        $activities = $this->client->requestRawActivities();

        $ids = [];
        foreach ($activities as $activity) {
            $ids[] = $activity->id;
        }
        return $ids;
    }

    public function requestActivityNames() {
        $activities = $this->client->requestRawActivities();

        $names = [];
        foreach ($activities as $activity) {
            $names[str_replace([' ', '(', ')'], ['_', '_', '_'], $activity->description)] = $activity->id;
        }
        return $names;
    }

    public function resumeActivity($activityId) {
        $this->checkActivityId($activityId);
        return $this->client->resumeActivity($activityId);
    }

    public function stopActivity($activityId) {
        $this->checkActivityId($activityId);
        return $this->client->stopActivity($activityId);
    }

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
