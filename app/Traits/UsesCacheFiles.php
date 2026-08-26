<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait UsesCacheFiles
{
    use LoadsJsonFiles;

    /**
     * The cache file base directory.
     *
     * @var string|null
     */
    public ?string $cacheBaseDirectory = null;

    /**
     * The cache file path.
     *
     * @var string|null
     */
    public ?string $cacheFilePath = null;

    /**
     * Get the path to the cache file.
     *
     * @param array $dirs
     * @param array $fileName
     * @param string $extension
     *
     * @return string
     */
    public function getCacheFilePath(array $dirs = [], array $fileName = [], string $extension = 'json')
    {
        $path = implode('/', [
            $this->cacheBaseDirectory,
            implode('/', array_filter($dirs)),
        ]);

        $path = storage_path(stripMultipleSlashes($path));

        $this->makeCacheDir($path);

        $file = implode('-', array_filter($fileName)) . '.' . $extension;

        return $path . '/' . $file;
    }

    /**
     * Gets cache file if exists.
     *
     * @return mixed
     */
    public function getCache()
    {
        // bust all the caches to fix player_id
        return false;

        $hasCache = (
            !empty($this->cacheFilePath) &&
            file_exists($this->cacheFilePath)
        );

        return $hasCache
            ? $this->loadJsonFile($this->cacheFilePath)
            : false;
    }

    /**
     * Sets cache file.
     *
     * @param mixed $data
     * @param bool $json
     *
     * @return void
     */
    public function setCache(mixed $data, bool $json = true)
    {
        file_put_contents(
            $this->cacheFilePath,
            $json ? json_encode($data, JSON_PRETTY_PRINT) : $data
        );
    }

    private function makeCacheDir(string $path)
    {
        if (is_dir($path)) {
            return;
        }

        $ds = DIRECTORY_SEPARATOR;
        $relativePath = stripMultipleSlashes(str_replace(base_path(), '', $path));
        $parts = explode($ds, rtrim(ltrim($relativePath, $ds), $ds));

        $dir = base_path();

        foreach ($parts as $part) {
            $dir .= stripMultipleSlashes($ds . $part);

            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    Log::error('Failed to create directory: ' . $dir);
                }
            }
        }
    }
}
