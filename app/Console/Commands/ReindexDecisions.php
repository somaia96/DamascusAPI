<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Decision;
use Typesense\Client;

class ReindexDecisions extends Command
{
    protected $signature = 'typesense:reindex-decisions';
    protected $description = 'Reindex decisions in Typesense';

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

        // Delete the existing 'decisions' collection if it exists
        try {
            $client->collections['decisions']->delete();
        } catch (\Exception $e) {
            // Collection might not exist, which is fine
        }

        // Create a new 'decisions' collection
        $client->collections->create([
            'name' => 'decisions',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'decision_id', 'type' => 'string'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'description', 'type' => 'string'],
                ['name' => 'decision_date', 'type' => 'int64', 'sort' => true],
            ]
        ]);

        // Index all decisions
        Decision::chunk(100, function ($decisions) use ($client) {
            $documents = [];
            foreach ($decisions as $decision) {
                $documents[] = [
                    'id' => (string) $decision->id,
                    'decision_id' => $decision->decision_id,
                    'title' => $decision->title,
                    'description' => $decision->description,
                    'decision_date' => strtotime($decision->decision_date),
                ];
            }
            $client->collections['decisions']->documents->import($documents);
        });

        $this->info('Decisions reindexed successfully.');
    }
}
