<?php

namespace LucianoTonet\TelescopeMcp\BoostExtension;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use LucianoTonet\TelescopeMcp\MCP\TelescopeMcpServer;

/**
 * Base class for Telescope MCP Tools that integrate with Laravel Boost.
 */
abstract class TelescopeBoostTool extends Tool
{
    protected TelescopeMcpServer $server;

    public function __construct()
    {
        $this->server = app(TelescopeMcpServer::class);
    }

    /**
     * Execute the tool with given arguments.
     *
     * @param array<string, mixed>|Request $arguments
     */
    public function handle(array|Request $arguments): Response
    {
        if ($arguments instanceof Request) {
            $arguments = $arguments->all();
        }

        // Extract the tool name from the class name
        $toolName = $this->getToolNameFromClass();

        // Execute through TelescopeMcpServer
        $result = $this->server->executeTool($toolName, $arguments);

        // Return structured JSON response as expected by Laravel Boost
        return Response::json($result);
    }

    /**
     * Extract tool name from class name.
     * Example: TelescopeExceptionsTool -> exceptions
     * Example: TelescopeHttpClientTool -> http-client.
     */
    protected function getToolNameFromClass(): string
    {
        $className = class_basename($this);

        // Remove 'Telescope' prefix and 'Tool' suffix
        $name = str_replace(['Telescope', 'Tool'], '', $className);

        // Convert to kebab-case as used internally by Telescope MCP tools
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
    }
}
