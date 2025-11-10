<?php

declare(strict_types=1);

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
            isset($json->preferred->mediatype) && is_string($json->preferred->mediatype) &&
            isset($json->preferred->url) && is_string($json->preferred->url)
        ) {
            $this->prefered = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::PREFERRED,
                $json->preferred->mediatype,
                isset($json->preferred->size) && is_numeric($json->preferred->size) ? intval($json->preferred->size) : null,
                isset($json->preferred->width) && is_numeric($json->preferred->width) ? intval($json->preferred->width) : null,
                isset($json->preferred->height) && is_numeric($json->preferred->height) ? intval($json->preferred->height) : null,
                isset($json->preferred->duration) && is_numeric($json->preferred->duration) ? intval($json->preferred->duration) : null,
                $json->preferred->url
            );
        }

        if (
            isset($json->original) &&
            is_object($json->original) &&
            isset($json->original->mediatype) && is_string($json->original->mediatype) &&
            isset($json->original->url) && is_string($json->original->url)
        ) {
            $this->original = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL,
                $json->original->mediatype,
                isset($json->original->size) && is_numeric($json->original->size) ? intval($json->original->size) : null,
                isset($json->original->width) && is_numeric($json->original->width) ? intval($json->original->width) : null,
                isset($json->original->height) && is_numeric($json->original->height) ? intval($json->original->height) : null,
                isset($json->original->duration) && is_numeric($json->original->duration) ? intval($json->original->duration) : null,
                $json->original->url
            );
        }

        if (
            isset($json->thumbnail) &&
            is_object($json->thumbnail) &&
            isset($json->thumbnail->mediatype) && is_string($json->thumbnail->mediatype) &&
            isset($json->thumbnail->url) && is_string($json->thumbnail->url)
        ) {
            $this->thumbnail = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL,
                $json->thumbnail->mediatype,
                isset($json->thumbnail->size) && is_numeric($json->thumbnail->size) ? intval($json->thumbnail->size) : null,
                isset($json->thumbnail->width) && is_numeric($json->thumbnail->width) ? intval($json->thumbnail->width) : null,
                isset($json->thumbnail->height) && is_numeric($json->thumbnail->height) ? intval($json->thumbnail->height) : null,
                isset($json->thumbnail->duration) && is_numeric($json->thumbnail->duration) ? intval($json->thumbnail->duration) : null,
                $json->thumbnail->url
            );
        }
    }

    public function get(string $title): void
    {
        if ($title !== '' && $title !== '0') {
            $url = sprintf(self::REST_API_FILE_GET, rawurlencode($title));
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::JSON);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (!in_array($responseBody, [null, '', '0'], true)) {
                    $this->saveCache($cacheHash, $responseBody);
                    $this->parseGetData($responseBody);
                } else {
                    $this->logger->error(\aportela\MediaWikiWrapper\Wikipedia\File::class . '::get - Error: empty body on API response', [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
                }
            } elseif ($cacheData !== '' && $cacheData !== '0') {
                $this->parseGetData($cacheData);
            } else {
                $this->logger->error(\aportela\MediaWikiWrapper\Wikipedia\File::class . '::get - Error: cached data for identifier is empty', [$cacheHash]);
                throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException(sprintf('Cached data for identifier (%s) is empty', $cacheHash));
            }
        } else {
            $this->logger->error(\aportela\MediaWikiWrapper\Wikipedia\File::class . '::get - Error: empty title');
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
