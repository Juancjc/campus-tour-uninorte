<?php

namespace App\Support;

class UserAgentParser
{
    /** @return array{browser: string, browser_version: string|null, operating_system: string, device: string, device_type: string} */
    public function parse(?string $userAgent): array
    {
        $agent = $userAgent ?? '';
        [$browser, $version] = match (true) {
            preg_match('/Edg\/([\d.]+)/', $agent, $matches) === 1 => ['Edge', $matches[1]],
            preg_match('/OPR\/([\d.]+)/', $agent, $matches) === 1 => ['Opera', $matches[1]],
            preg_match('/Chrome\/([\d.]+)/', $agent, $matches) === 1 => ['Chrome', $matches[1]],
            preg_match('/Firefox\/([\d.]+)/', $agent, $matches) === 1 => ['Firefox', $matches[1]],
            preg_match('/Version\/([\d.]+).*Safari/', $agent, $matches) === 1 => ['Safari', $matches[1]],
            default => ['Outro', null],
        };

        $operatingSystem = match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Outro',
        };

        $deviceType = match (true) {
            preg_match('/iPad|Tablet/i', $agent) === 1 => 'tablet',
            preg_match('/Mobile|Android|iPhone/i', $agent) === 1 => 'mobile',
            default => 'desktop',
        };

        return [
            'browser' => $browser,
            'browser_version' => $version,
            'operating_system' => $operatingSystem,
            'device' => match ($deviceType) {
                'mobile' => 'Smartphone',
                'tablet' => 'Tablet',
                default => 'Computador',
            },
            'device_type' => $deviceType,
        ];
    }
}
