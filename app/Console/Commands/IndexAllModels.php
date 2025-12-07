<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\Complaint;
use App\Models\Decision;
use App\Models\News;
use App\Models\Service;
use Typesense\Client;

class IndexAllModels extends Command
{
    protected $signature = 'typesense:index-all';
    protected $description = 'Index all models in Typesense';

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

        $this->indexModel($client, Activity::class, 'activities');
        $this->indexModel($client, Complaint::class, 'complaints');
        $this->indexModel($client, Decision::class, 'decisions');
        $this->indexModel($client, News::class, 'news');
        $this->indexModel($client, Service::class, 'services');

        $this->info('All models indexed successfully.');
    }

    private function indexModel($client, $modelClass, $collectionName)
    {
        $this->info("Indexing {$collectionName}...");

        // Delete the existing collection if it exists
        try {
            $client->collections[$collectionName]->delete();
            $this->info("Deleted existing {$collectionName} collection.");
        } catch (\Exception $e) {
            $this->info("Collection {$collectionName} does not exist. Creating new.");
        }

        // Create the collection with the correct schema
        $schema = $this->getSchemaForCollection($collectionName);
        $client->collections->create($schema);

        // Index the documents
        $modelClass::chunk(100, function ($models) use ($client, $collectionName) {
            $documents = [];
            foreach ($models as $model) {
                $documents[] = $this->prepareDocument($model, $collectionName);
            }
            $client->collections[$collectionName]->documents->import($documents);
            $this->info("Indexed " . count($documents) . " {$collectionName}.");
        });
    }

    private function getSchemaForCollection($collectionName)
    {
        $schemas = [
            'activities' => [
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
            'complaints' => [
                'name' => 'complaints',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at',
            ],
            'decisions' => [
                'name' => 'decisions',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'decision_date', 'type' => 'int64'],
                    ['name' => 'decision_type_id', 'type' => 'string'],
                ],
                'default_sorting_field' => 'decision_date',
            ],
            'news' => [
                'name' => 'news',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'published_at', 'type' => 'int64'],
                    ['name' => 'news_category_id', 'type' => 'string'],
                ],
                'default_sorting_field' => 'published_at',
            ],
            'services' => [
                'name' => 'services',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'string'],
                    ['name' => 'service_category_id', 'type' => 'string'],
                    ['name' => 'name_sort', 'type' => 'int32'],
                ],
                'default_sorting_field' => 'name_sort',
            ],
        ];

        return $schemas[$collectionName];
    }

    private function prepareDocument($model, $collectionName)
    {
        $document = [
            'id' => (string) $model->id,
        ];

        switch ($collectionName) {
            case 'activities':
                $document['title'] = $model->title;
                $document['description'] = $model->description;
                $document['activity_date'] = strtotime($model->activity_date);
                $document['activity_type_id'] = (string) $model->activity_type_id;
                break;
            case 'complaints':
                $document['title'] = $model->title;
                $document['description'] = $model->description;
                $document['created_at'] = strtotime($model->created_at);
                break;
            case 'services':
                $document['name'] = $model->name;
                $document['name_sort'] = $model->id;
                $document['description'] = $model->description;
                $document['service_category_id'] = (string) $model->service_category_id;
                break;
            case 'decisions':
                $document['title'] = $model->title;
                $document['content'] = $model->content;
                $document['decision_date'] = strtotime($model->decision_date);
                $document['decision_type_id'] = (string) $model->decision_type_id;
                break;
            case 'news':
                $document['title'] = $model->title;
                $document['content'] = $model->content;
                $document['published_at'] = strtotime($model->publication_date);
                $document['news_category_id'] = (string) $model->news_category_id;
                break;
        }

        return $document;
    }
}
