<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class File extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_FILE_GET = "https://commons.wikimedia.org/w/rest.php/v1/file/File:%s";

    protected ?\aportela\MediaWikiWrapper\FileInformation $prefered = null;
    protected ?\aportela\MediaWikiWrapper\FileInformation $original = null;
    protected ?\aportela\MediaWikiWrapper\FileInformation $thumbnail = null;

    public function __construct(\Psr\Log\LoggerInterface $logger, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        parent::__construct($logger, \aportela\MediaWikiWrapper\APIType::REST, $throttleDelayMS, $cache);
    }

    private function parseGetData(string $raw): void
    {
        $json = $this->parseJSONString($raw);
        if (
            isset($json->preferred) &&
            is_object($json->preferred) &&
            isset($json->preferred->mediatype) &&
            isset($json->preferred->url)
        ) {
            $this->prefered = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::PREFERRED,
                $json->preferred->mediatype,
                $json->preferred->size ?? null,
                $json->preferred->width ?? null,
                $json->preferred->height ?? null,
                $json->preferred->duration ?? null,
                $json->preferred->url
            );
        }
        if (
            isset($json->original) &&
            is_object($json->original) &&
            isset($json->original->mediatype) &&
            isset($json->original->url)
        ) {
            $this->original = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL,
                $json->original->mediatype,
                $json->original->size ?? null,
                $json->original->width ?? null,
                $json->original->height ?? null,
                $json->original->duration ?? null,
                $json->original->url
            );
        }
        if (
            isset($json->thumbnail) &&
            is_object($json->thumbnail) &&
            isset($json->thumbnail->mediatype) &&
            isset($json->thumbnail->url)
        ) {
            $this->thumbnail = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL,
                $json->thumbnail->mediatype,
                $json->thumbnail->size ?? null,
                $json->thumbnail->width ?? null,
                $json->thumbnail->height ?? null,
                $json->thumbnail->duration ?? null,
                $json->thumbnail->url
            );
        }
    }

    public function get(string $title): void
    {
        if (! empty($title)) {
            $url = sprintf(self::REST_API_FILE_GET, $title);
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::JSON);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $this->saveCache($cacheHash, $responseBody);
                    $this->parseGetData($responseBody);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\File::get - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                if (!empty($cacheData)) {
                    $this->parseGetData($cacheData);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\File::get - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
                }
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\File::get - Error: empty title");
            throw new \InvalidArgumentException("Empty title");
        }
    }

    public function getURL(\aportela\MediaWikiWrapper\FileInformationType $informationType = \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL): ?string
    {
        $url = null;
        switch ($informationType) {
            case \aportela\MediaWikiWrapper\FileInformationType::PREFERRED:
                if ($this->prefered != null) {
                    $url = $this->prefered->url;
                }
                break;
            case \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL:
                if ($this->original != null) {
                    $url = $this->original->url;
                }
                break;
            case \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL:
                if ($this->thumbnail != null) {
                    $url = $this->thumbnail->url;
                }
                break;
            default:
                throw new \InvalidArgumentException("Invalid informationType");
        }
        return ($url);
    }
}
