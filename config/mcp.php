<?php

return [
    /**
     * Allowed IPs for MCP access. Supports:
     * - Exact IPs: 203.0.113.5
     * - Wildcard suffix: 192.168.1.*
     * - CIDR notation: 10.0.0.0/8
     * Provide as environment variable `MCP_ALLOWED_IPS` comma-separated, or edit this file.
     */
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('MCP_ALLOWED_IPS', '')))),
];
