<?php

namespace App\Console\Commands;

use App\Models\ActivityType;
use Illuminate\Console\Command;
use Typesense\Client;

class IndexActivityTypes extends Command
{
    protected $signature = 'typesense:index-activity-types';
    protected $description = 'Index existing ActivityTypes in Typesense';

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

        $activityTypes = ActivityType::all();

        foreach ($activityTypes as $activityType) {
            try {
                $client->collections['activity_types']->documents->create([
                    'id' => (string) $activityType->id,
                    'name' => $activityType->name,
                    'name_sort' => $activityType->id, 
                ]);
                $this->info("Indexed ActivityType: {$activityType->name}");
            } catch (\Exception $e) {
                $this->error("Error indexing ActivityType {$activityType->name}: " . $e->getMessage());
            }
        }

        $this->info('Finished indexing ActivityTypes.');
    }
}
