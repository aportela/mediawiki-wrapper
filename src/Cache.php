<?php

namespace aportela\MediaWikiWrapper;

class Cache
{
    private \Psr\Log\LoggerInterface $logger;
    private ?string $cachePath = null;
    private \aportela\MediaWikiWrapper\APIFormat $apiFormat;
    private bool $enabled = true;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MediaWikiWrapper\APIFormat $apiFormat, ?string $cachePath = null)
    {
        $this->logger = $logger;
        if (! empty($cachePath)) {
            $this->cachePath = ($path = realpath($cachePath)) ? $path : null;
        }
        $this->enabled = ! empty($this->cachePath);
        $this->apiFormat = $apiFormat;
    }

    /**
     * return cache directory path for MediaWikiWrapper hash
     */
    private function getCacheDirectoryPath(string $hash): string
    {
        return ($this->cachePath . DIRECTORY_SEPARATOR . mb_substr($hash, 0, 1) . DIRECTORY_SEPARATOR . mb_substr($hash, 1, 1) . DIRECTORY_SEPARATOR . mb_substr($hash, 2, 1) . DIRECTORY_SEPARATOR . mb_substr($hash, 3, 1));
    }

    /**
     * return cache file path for MediaWiki hash
     */
    private function getCacheFilePath(string $hash): string
    {
        $basePath = $this->getCacheDirectoryPath($hash);
        switch ($this->apiFormat) {
            case \aportela\MediaWikiWrapper\APIFormat::JSON:
                return ($basePath . DIRECTORY_SEPARATOR . $hash . ".json");
            case \aportela\MediaWikiWrapper\APIFormat::XML:
                return ($basePath . DIRECTORY_SEPARATOR . $hash . ".xml");
            default:
                return ($basePath . DIRECTORY_SEPARATOR . $hash);
        }
    }


    /**
     * save current raw data into disk cache
     */
    public function saveCache(string $hash, string $raw): bool
    {
        if ($this->enabled) {
            try {
                if (! empty($this->cachePath) && ! empty($raw)) {
                    $this->logger->debug("Saving MediaWiki disk cache", [$hash, $this->cachePath, $this->getCacheFilePath($hash)]);
                    $directoryPath = $this->getCacheDirectoryPath($hash);
                    if (! file_exists($directoryPath)) {
                        if (!mkdir($directoryPath, 0750, true)) {
                            $this->logger->error("Error creating MediaWiki disk cache directory", [$hash, $directoryPath]);
                            return (false);
                        }
                    }
                    return (file_put_contents($this->getCacheFilePath($hash), $raw) > 0);
                } else {
                    return (false);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Error saving MediaWiki disk cache", [$hash, $e->getMessage()]);
                return (false);
            }
        } else {
            return (false);
        }
    }

    /**
     * remove cache entry
     */
    public function removeCache(string $hash): bool
    {
        if ($this->enabled) {
            try {
                if (! empty($this->cachePath)) {
                    $cacheFilePath = $this->getCacheFilePath($hash);
                    if (file_exists($cacheFilePath)) {
                        return (unlink($cacheFilePath));
                    } else {
                        return (false);
                    }
                } else {
                    return (false);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Error removing MediaWiki disk cache", [$hash, $e->getMessage()]);
                return (false);
            }
        } else {
            return (false);
        }
    }

    /**
     * read disk cache
     */
    public function getCache(string $hash): mixed
    {
        if ($this->enabled) {
            try {
                if (! empty($this->cachePath)) {
                    if (file_exists($this->getCacheFilePath($hash))) {
                        $this->logger->debug("Loading MediaWiki disk cache", [$hash, $this->cachePath, $this->getCacheFilePath($hash)]);
                        return (file_get_contents($this->getCacheFilePath($hash)));
                    } else {
                        $this->logger->debug("MediaWiki disk cache not found", [$hash, $this->cachePath, $this->getCacheFilePath($hash)]);
                        return (false);
                    }
                } else {
                    return (false);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Error loading MediaWiki disk cache", [$hash, $this->cachePath, $e->getMessage()]);
                return (false);
            }
        } else {
            return (false);
        }
    }
}
