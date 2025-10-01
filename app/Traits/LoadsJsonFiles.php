<?php

namespace App\Traits;

use Exception;

trait LoadsJsonFiles
{
    public function loadJsonFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to load JSON file: ' . json_encode([
                json_last_error_msg(),
                $filePath,
            ]));
        }

        return $data;
    }
}
