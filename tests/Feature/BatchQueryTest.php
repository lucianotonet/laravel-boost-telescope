<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use LucianoTonet\TelescopeMcp\MCP\TelescopeMcpServer;

beforeEach(function () {
    $this->server = app(TelescopeMcpServer::class);
    
    // Create tags table if not exists (required by Repository)
    if (!Schema::connection('testbench')->hasTable('telescope_entries_tags')) {
        Schema::connection('testbench')->create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');
            $table->index(['entry_uuid', 'tag']);
            $table->index('tag');
        });
    }

    // Clean up
    DB::connection('testbench')->table('telescope_entries')->delete();
    DB::connection('testbench')->table('telescope_entries_tags')->delete();
});

test('can filter queries by request_id (via batch_id)', function () {
    $batchId1 = (string) Str::uuid();
    $batchId2 = (string) Str::uuid();
    $requestId = (string) Str::uuid();
    
    // 1. Create the Request entry
    DB::table('telescope_entries')->insert([
        'sequence' => 100,
        'uuid' => $requestId,
        'batch_id' => $batchId1,
        'family_hash' => null,
        'should_display_on_index' => 1,
        'type' => 'request',
        'content' => json_encode(['uri' => '/test-uri', 'method' => 'GET']),
        'created_at' => now(),
    ]);
    
    // 2. Create a Query entry associated with the SAME batch
    $queryId1 = (string) Str::uuid();
    DB::table('telescope_entries')->insert([
        'sequence' => 101,
        'uuid' => $queryId1,
        'batch_id' => $batchId1,
        'family_hash' => null,
        'should_display_on_index' => 1,
        'type' => 'query',
        'content' => json_encode(['sql' => 'SELECT * FROM users', 'time' => 1.2]),
        'created_at' => now(),
    ]);
    
    // 3. Create a Query entry associated with a DIFFERENT batch
    $queryId2 = (string) Str::uuid();
    DB::table('telescope_entries')->insert([
        'sequence' => 102,
        'uuid' => $queryId2,
        'batch_id' => $batchId2,
        'family_hash' => null,
        'should_display_on_index' => 1,
        'type' => 'query',
        'content' => json_encode(['sql' => 'SELECT * FROM posts', 'time' => 2.5]),
        'created_at' => now(),
    ]);

    // Mock EntriesRepository to avoid 'telescope_entries_tags' table issue
    $mockRepo = Mockery::mock(\Laravel\Telescope\Contracts\EntriesRepository::class);
    
    $entryResult = new \Laravel\Telescope\EntryResult(
        $requestId,
        100,
        $batchId1,
        'request',
        null,
        ['uri' => '/test-uri'],
        now(),
        []
    );

    $mockRepo->shouldReceive('find')
        ->with($requestId)
        ->andReturn($entryResult);
        
    // Bind mock and re-instantiate server
    $this->app->instance(\Laravel\Telescope\Contracts\EntriesRepository::class, $mockRepo);
    $this->server = app(TelescopeMcpServer::class);

    // Execute the queries tool with request_id
    $result = $this->server->executeTool('queries', [
        'request_id' => $requestId,
    ]);

    $responseText = $result['content'][0]['text'];
    
    if (str_starts_with($responseText, 'Error:')) {
        echo "Response Text: " . $responseText . "\n";
        $entries = DB::table('telescope_entries')->get();
        echo "Entries count: " . $entries->count() . "\n";
        echo "Request ID searched: " . $requestId . "\n";
        foreach ($entries as $e) {
            echo "Entry: {$e->uuid} (Type: {$e->type}, Batch: {$e->batch_id})\n";
        }
    }

    // Verify format - it returns mixed text and JSON
    if (str_contains($responseText, '--- JSON Data ---')) {
        $parts = explode("--- JSON Data ---", $responseText);
        $jsonStr = trim($parts[1]);
        $data = json_decode($jsonStr, true);
    } else {
        // Fallback (shouldn't happen with current tool impl)
        $data = json_decode($responseText, true);
    }
    
    expect($data)->toBeArray();
    
    // Check filtering
    $ids = array_column($data['queries'] ?? [], 'id'); 
    // The previous implementation returned ['queries' => [...]] in JSON.
    
    // If usage of array_column on $data was assuming list of queries directly.
    // Let's check QueriesTool output format.
    // It returns: json_encode(['request_id' => ..., 'queries' => [...]]);
    
    if (isset($data['queries'])) {
        $ids = array_column($data['queries'], 'id');
    } else {
        // Fallback if structure is different
        $ids = array_column($data, 'id');
    }
    
    expect($ids)->toContain($queryId1);
    expect($ids)->not->toContain($queryId2);
});

test('returns error if request_id not found', function () {
    $result = $this->server->executeTool('queries', [
        'request_id' => 'non-existent-uuid',
    ]);
    
    $responseText = $result['content'][0]['text'];
    
    expect($responseText)->toContain('Request not found');
});
