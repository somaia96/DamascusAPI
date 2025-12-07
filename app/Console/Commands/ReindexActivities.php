<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use Typesense\Client;

class ReindexActivities extends Command
{
    protected $signature = 'typesense:reindex-activities';
    protected $description = 'Reindex activities in Typesense';

    public function handle()
    {
        $client = new Client([
            'api_key' => config('services.typesense.api_key'),
            'nodes' => [
                [
                    'host' => config('services.typesense.host'),
                    'port' => config('services.typesense.port'),
                    'protocol' => config('services.typesense.protocol'),
                ],
            ],
            'connection_timeout_seconds' => 2,
        ]);

        // Delete the existing 'activities' collection if it exists
        try {
            $client->collections['activities']->delete();
        } catch (\Exception $e) {
            // Collection might not exist, which is fine
        }

        // Create a new 'activities' collection
        $client->collections->create([
            'name' => 'activities',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'title', 'type' => 'string', 'sort' => true],
                ['name' => 'description', 'type' => 'string'],
                ['name' => 'activity_date', 'type' => 'int64', 'sort' => true],
            ]
        ]);

        // Index all activities
        Activity::chunk(100, function ($activities) use ($client) {
            $documents = [];
            foreach ($activities as $activity) {
                $documents[] = [
                    'id' => (string) $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'activity_date' => strtotime($activity->activity_date),
                ];
            }
            $client->collections['activities']->documents->import($documents);
        });

        $this->info('Activities reindexed successfully.');
    }
}
