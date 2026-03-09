<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowMcpProvider
{
    /**
     * Allow access only if request includes the configured provider header.
     * Header checked: `X-MCP-PROVIDER` (case-insensitive).
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow if remote IP is in the configured MCP whitelist
        $allowed = config('mcp.allowed_ips', []);
        $remoteIp = $request->ip();

        if (! empty($allowed)) {
            foreach ($allowed as $rule) {
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }

                // exact match
                if ($rule === $remoteIp) {
                    return $next($request);
                }

                // wildcard suffix e.g. 192.168.1.*
                if (str_ends_with($rule, '*')) {
                    $prefix = rtrim($rule, '*');
                    if (str_starts_with($remoteIp, $prefix)) {
                        return $next($request);
                    }
                    continue;
                }

                // CIDR notation
                if (str_contains($rule, '/')) {
                    if ($this->ipInRange($remoteIp, $rule)) {
                        return $next($request);
                    }
                    continue;
                }
            }
            // If whitelist is present and none matched, deny immediately
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Fallback: require header matching configured provider
        $expected = config('ai.default');
        $header = $request->header('X-MCP-PROVIDER') ?: $request->header('X-Provider');

        if (empty($expected) || $header === null) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! hash_equals((string) $expected, (string) $header)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }

    /**
     * Check if an IPv4 address is within a CIDR range.
     * Returns false for invalid inputs or IPv6 addresses.
     */
    protected function ipInRange(string $ip, string $cidr): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $parts = explode('/', $cidr);
        if (count($parts) !== 2) {
            return false;
        }

        [$subnet, $bits] = $parts;

        if (! filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $bits = (int) $bits;
        if ($bits < 0 || $bits > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = $bits === 0 ? 0 : (~0 << (32 - $bits)) & 0xFFFFFFFF;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
