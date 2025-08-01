<?php

namespace App\Services;

class ServerStatusService
{
    /**
     * Check if an IP address is online using ping
     *
     * @param string $ipAddress The IP address to ping
     * @param int $timeout Timeout in seconds
     * @return bool True if the IP is reachable, false otherwise
     */
    public function pingServer(string $ipAddress, int $timeout = 2): bool
    {
        // Windows-specific ping command with timeout in milliseconds
        $cmd = "ping -n 1 -w " . ($timeout * 1000) . " $ipAddress";

        exec($cmd, $output, $returnCode);

        // Return code 0 means success
        return $returnCode === 0;
    }

    /**
     * Check if a port on a server is open and accepting connections
     *
     * @param string $ipAddress The IP address to connect to
     * @param int $port The port to check
     * @param int $timeout Timeout in seconds
     * @return bool True if the port is open, false otherwise
     */
    public function checkPort(string $ipAddress, int $port, int $timeout = 2): bool
    {
        // Create a socket
        $socket = @fsockopen($ipAddress, $port, $errno, $errstr, $timeout);

        if ($socket) {
            fclose($socket);
            return true;
        }

        return false;
    }

    /**
     * Get comprehensive status of a server
     *
     * @param string $ipAddress The IP address to check
     * @param int $port The port to check
     * @param int $timeout Timeout in seconds
     * @return array Status information
     */
    public function getServerStatus(string $ipAddress, int $port, int $timeout = 2): array
    {
        $isOnline = $this->pingServer($ipAddress, $timeout);
        $portOpen = false;

        // Only check port if the server is online
        if ($isOnline) {
            $portOpen = $this->checkPort($ipAddress, $port, $timeout);
        }

        return [
            'ip_address' => $ipAddress,
            'port' => $port,
            'is_online' => $isOnline,
            'port_open' => $portOpen,
            'checked_at' => now()->toDateTimeString(),
        ];
    }
}
