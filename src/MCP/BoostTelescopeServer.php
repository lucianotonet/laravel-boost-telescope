<?php

namespace LucianoTonet\LaravelBoostTelescope\MCP;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Contracts\EntriesRepository;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\BatchesTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\CacheTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\CommandsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\DumpsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\EventsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\ExceptionsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\GatesTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\HttpClientTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\JobsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\LogsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\MailTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\ModelsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\NotificationsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\PruneTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\QueriesTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\RedisTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\RequestsTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\ScheduleTool;
use LucianoTonet\LaravelBoostTelescope\MCP\Tools\ViewsTool;
use LucianoTonet\LaravelBoostTelescope\Support\Logger;

class BoostTelescopeServer
{
    protected $entriesRepository;
    protected $tools;
    protected $manifest;

    public function __construct(EntriesRepository $entriesRepository)
    {
        $this->entriesRepository = $entriesRepository;
        $this->tools = new Collection();

        // Register existing tools
        $this->registerTool(new RequestsTool($entriesRepository));
        $this->registerTool(new LogsTool($entriesRepository));
        $this->registerTool(new ExceptionsTool($entriesRepository));

        // Register new tools
        $this->registerTool(new BatchesTool($entriesRepository));
        $this->registerTool(new CacheTool($entriesRepository));
        $this->registerTool(new CommandsTool($entriesRepository));
        $this->registerTool(new DumpsTool($entriesRepository));
        $this->registerTool(new EventsTool($entriesRepository));
        $this->registerTool(new GatesTool($entriesRepository));
        $this->registerTool(new HttpClientTool($entriesRepository));
        $this->registerTool(new JobsTool($entriesRepository));
        $this->registerTool(new MailTool($entriesRepository));
        $this->registerTool(new ModelsTool($entriesRepository));
        $this->registerTool(new NotificationsTool($entriesRepository));
        $this->registerTool(new QueriesTool($entriesRepository));
        $this->registerTool(new RedisTool($entriesRepository));
        $this->registerTool(new ScheduleTool($entriesRepository));
        $this->registerTool(new ViewsTool($entriesRepository));

        // Register PruneTool (doesn't require $entriesRepository in constructor)
        $this->registerTool(new PruneTool());

        $this->buildManifest();
    }

    /**
     * Registers a tool in the MCP server.
     *
     * @param object $tool
     */
    public function registerTool($tool)
    {
        // Use the name returned by getName() (now the short name)
        $toolName = $tool->getName();

        // Add to collection
        $this->tools->put($toolName, $tool);
    }

    /**
     * Checks if a tool is registered.
     *
     * @param string $toolName
     *
     * @return bool
     */
    public function hasTool($toolName)
    {
        // Search by short name
        return $this->tools->has($toolName);
    }

    /**
     * Returns the MCP server manifest.
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Executes a tool with the provided arguments.
     *
     * @param string $toolName  Tool name
     * @param array  $arguments Arguments for the tool
     *
     * @return array Execution result
     *
     * @throws \Exception If tool is not found
     */
    public function executeTool(string $toolName, array $arguments = []): array
    {
        Logger::info('Executing tool', [
            'tool' => $toolName, // toolName is already the short name
            'arguments' => $arguments,
        ]);

        try {
            // Check if tool exists using short name
            if (!$this->hasTool($toolName)) {
                throw new \Exception("Tool not found: {$toolName}");
            }

            // Execute the tool
            $tool = $this->getTool($toolName);
            $result = $tool->execute($arguments);

            Logger::info('Tool execution successful', [
                'tool' => $toolName,
                'result' => $result,
            ]);

            // Ensure result is in the format expected by MCP
            if (!isset($result['content']) || !is_array($result['content'])) {
                $result = [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => is_string($result) ? $result : json_encode($result, JSON_PRETTY_PRINT),
                        ],
                    ],
                ];
            } else {
                // Validate and normalize content format
                $validatedContent = [];
                foreach ($result['content'] as $item) {
                    if (is_array($item) && isset($item['type'], $item['text'])) {
                        $validatedContent[] = [
                            'type' => (string) $item['type'],
                            'text' => (string) $item['text'],
                        ];
                    }
                }

                if (empty($validatedContent)) {
                    $validatedContent = [
                        [
                            'type' => 'text',
                            'text' => 'No valid content returned',
                        ],
                    ];
                }

                $result['content'] = $validatedContent;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Tool execution error', [
                'tool' => $toolName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Gets a tool by name.
     *
     * @param string $toolName
     *
     * @return null|object
     */
    protected function getTool($toolName)
    {
        // Search by short name
        return $this->tools->get($toolName);
    }

    /**
     * Builds the MCP server manifest.
     */
    protected function buildManifest()
    {
        // Format tools to match MCP client expectations
        $toolsFormatted = (object) [];
        foreach ($this->tools as $name => $tool) {
            $schema = $tool->getSchema();
            // The 'name' in schema will come from $tool->getName(), which is the short name
            $toolsFormatted->{$schema['name']} = [
                'name' => $schema['name'],
                'description' => $schema['description'],
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => $schema['parameters']['properties'] ?? [],
                    'required' => $schema['parameters']['required'] ?? [],
                ],
            ];
        }

        $this->manifest = [
            'name' => 'Laravel Boost Telescope',
            'version' => '1.0.0',
            'description' => 'Laravel Telescope tools for Laravel Boost',
            'tools' => $toolsFormatted,
        ];
    }
}
