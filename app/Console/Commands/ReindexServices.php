<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use Typesense\Client;

class ReindexServices extends Command
{
    protected $signature = 'typesense:reindex-services';
    protected $description = 'Reindex services in Typesense';

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

        // Delete the existing 'services' collection if it exists
        try {
            $client->collections['services']->delete();
        } catch (\Exception $e) {
            // Collection might not exist, which is fine
        }

        // Create a new 'services' collection
        $client->collections->create([
            'name' => 'services',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'title', 'type' => 'string', 'sort' => true],
                ['name' => 'description', 'type' => 'string'],
                ['name' => 'service_category_id', 'type' => 'string'],
            ]
        ]);

        // Index all services
        Service::chunk(100, function ($services) use ($client) {
            $documents = [];
            foreach ($services as $service) {
                $documents[] = [
                    'id' => (string) $service->id,
                    'title' => $service->title,
                    'description' => $service->description,
                    'service_category_id' => (string) $service->service_category_id,
                ];
            }
            $client->collections['services']->documents->import($documents);
        });

        $this->info('Services reindexed successfully.');
    }
}
