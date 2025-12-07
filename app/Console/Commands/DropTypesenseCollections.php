<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Typesense\Client;

class DropTypesenseCollections extends Command
{
    protected $signature = 'typesense:drop-collections';
    protected $description = 'Drop all Typesense collections';

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

        $collections = $client->collections->retrieve();

        foreach ($collections as $collection) {
            try {
                $client->collections[$collection['name']]->delete();
                $this->info("Dropped collection: {$collection['name']}");
            } catch (\Exception $e) {
                $this->error("Error dropping collection {$collection['name']}: " . $e->getMessage());
            }
        }

        $this->info('Finished dropping all collections.');
    }
}
