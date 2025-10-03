<?php

namespace App\Traits;

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
            implode('-', array_filter($fileName)) . '.' . $extension,
        ]);

        $path = stripMultipleSlashes($path);

        return storage_path($path);
    }

    /**
     * Gets cache file if exists.
     *
     * @return mixed
     */
    public function getCache()
    {
        return (! empty($this->cacheFilePath) && file_exists($this->cacheFilePath))
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
}
