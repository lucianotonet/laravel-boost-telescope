<?php

namespace LucianoTonet\LaravelBoostTelescope\BoostExtension\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use LucianoTonet\LaravelBoostTelescope\BoostExtension\TelescopeBoostTool;

class TelescopeModelsTool extends TelescopeBoostTool
{
    protected string $name = 'telescope_models';

    public function description(): string
    {
        return 'Access Models data from Laravel Telescope';
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('Get details of a specific entry by ID'),
            'limit' => $schema->integer()->default(50)->description('Maximum number of entries to return'),
            'request_id' => $schema->string()->description('Filter by the request ID (uses batch_id grouping)'),
        ];
    }
}
