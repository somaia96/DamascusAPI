<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;
use Typesense\Client;

class ReindexNews extends Command
{
    protected $signature = 'typesense:reindex-news';
    protected $description = 'Reindex news in Typesense';

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

        // Delete the existing 'news' collection if it exists
        try {
            $client->collections['news']->delete();
        } catch (\Exception $e) {
            // Collection might not exist, which is fine
        }

        // Create a new 'news' collection
        $client->collections->create([
            'name' => 'news',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'title', 'type' => 'string', 'sort' => true],
                ['name' => 'description', 'type' => 'string'],
                ['name' => 'content', 'type' => 'string'],
                ['name' => 'created_at', 'type' => 'int64', 'sort' => true],
            ],
            'default_sorting_field' => 'created_at'
        ]);

        // Index all news
        News::chunk(100, function ($newsItems) use ($client) {
            $documents = [];
            foreach ($newsItems as $news) {
                $documents[] = [
                    'id' => (string) $news->id,
                    'title' => $news->title,
                    'description' => $news->description,
                    'content' => $news->content,
                    'created_at' => strtotime($news->created_at),
                ];
            }
            $client->collections['news']->documents->import($documents);
        });

        $this->info('News reindexed successfully.');
    }
}
