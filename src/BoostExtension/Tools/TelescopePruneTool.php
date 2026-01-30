<?php

namespace LucianoTonet\TelescopeMcp\BoostExtension\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use LucianoTonet\TelescopeMcp\BoostExtension\TelescopeBoostTool;

class TelescopePruneTool extends TelescopeBoostTool
{
    protected string $name = 'telescope_prune';

    public function description(): string
    {
        return 'Prune old entries from Laravel Telescope';
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'hours' => $schema->integer()->default(24)->description('Delete entries older than this many hours'),
        ];
    }
}
