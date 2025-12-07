<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Typesense\Client;

class CreateTypesenseSchema extends Command
{
    protected $signature = 'typesense:create-schemas';
    protected $description = 'Create Typesense schemas for all models';

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

        $schemas = [
            [
                'name' => 'activity_types',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'name_sort', 'type' => 'int32'],
                ],
                'default_sorting_field' => 'name_sort',
            ],
            [
                'name' => 'activities',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'activity_date', 'type' => 'int64'],
                    ['name' => 'activity_type_id', 'type' => 'string'],
                ],
                'default_sorting_field' => 'activity_date',
            ],
            [
                'name' => 'complaints',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at',
            ],
            [
                'name' => 'decisions',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'content', 'type' => 'string'],
                    ['name' => 'decision_date', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'decision_date',
            ],
            [
                'name' => 'news',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'content', 'type' => 'string'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at',
            ],
            [
                'name' => 'services',
                'fields' => [
                    ['name' => 'id', 'type' => 'int32'],
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'name_sort', 'type' => 'int32'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'service_category_id', 'type' => 'string'],
                ],
                'default_sorting_field' => 'name_sort',
            ],
        ];

        foreach ($schemas as $schema) {
            try {
                $client->collections->create($schema);
                $this->info("Created schema for {$schema['name']}");
            } catch (\Exception $e) {
                $this->error("Error creating schema for {$schema['name']}: " . $e->getMessage());
            }
        }
    }
}
