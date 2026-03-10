<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowMcpProvider
{
    /**
     * Allow access only when request IP matches configured whitelist rules.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('mcp.allowed_ips', []);
        $remoteIp = (string) $request->ip();

        if ($remoteIp === '' || empty($allowed)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        foreach ($allowed as $rule) {
            $rule = trim((string) $rule);

            if ($rule === '') {
                continue;
            }

            if ($rule === $remoteIp) {
                return $next($request);
            }

            if (str_ends_with($rule, '*')) {
                $prefix = rtrim($rule, '*');

                if (str_starts_with($remoteIp, $prefix)) {
                    return $next($request);
                }

                continue;
            }

            if (str_contains($rule, '/')) {
                if ($this->ipInRange($remoteIp, $rule)) {
                    return $next($request);
                }

                continue;
            }
        }

        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    /**
     * Check if an IPv4 address is within a CIDR range.
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
