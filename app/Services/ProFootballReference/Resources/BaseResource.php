<?php

namespace App\Services\ProFootballReference\Resources;

abstract class BaseResource
{
    protected function getCacheFilePath(array $params = []): bool|string
    {
        if (empty($params)) {
            return false;
        }

        return storage_path('data/pro-football-reference/' . implode('-', $params) . '.json');
    }

    protected function getCache(array $params): bool|array
    {
        $filePath = $this->getCacheFilePath($params);

        dump(json_encode(['getCache', $filePath, file_exists($filePath)]));

        if (! file_exists($filePath)) {
            return false;
        }

        return json_decode(file_get_contents($filePath), true);
    }

    protected function setCache(string $filePath, array $data): void
    {
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
