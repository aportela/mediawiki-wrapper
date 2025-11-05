<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class File extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_FILE_GET = "https://commons.wikimedia.org/w/rest.php/v1/file/File:%s";

    protected ?string $title;

    protected \aportela\MediaWikiWrapper\FileInformation $prefered;
    protected \aportela\MediaWikiWrapper\FileInformation $original;
    protected \aportela\MediaWikiWrapper\FileInformation $thumbnail;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    private function parseGetData(object $json): void
    {
        if (isset($json->preferred)) {
            $this->prefered = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::PREFERRED,
                $json->preferred->mediatype,
                $json->preferred->size,
                $json->preferred->width,
                $json->preferred->height,
                $json->preferred->duration,
                $json->preferred->url
            );
        }
        if (isset($json->original)) {
            $this->original = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL,
                $json->original->mediatype,
                $json->original->size,
                $json->original->width,
                $json->original->height,
                $json->original->duration,
                $json->original->url
            );
        }
        if (isset($json->thumbnail)) {
            $this->thumbnail = new \aportela\MediaWikiWrapper\FileInformation(
                \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL,
                $json->thumbnail->mediatype,
                $json->thumbnail->size,
                $json->thumbnail->width,
                $json->thumbnail->height,
                $json->thumbnail->duration,
                $json->thumbnail->url
            );
        }
    }

    public function get(): void
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_FILE_GET, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::get", array("title" => $this->title));
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::JSON);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $response = $this->httpGET($url);
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                } else {
                    if ($response->code == 200) {
                        $this->resetThrottle();
                        $this->saveCache($cacheHash, $response->body);
                        $this->parseGetData($json);
                    } elseif ($response->code == 503) {
                        $this->incrementThrottle();
                        throw new \aportela\MediaWikiWrapper\Exception\RateLimitExceedException("title: {$this->title}", $response->code);
                    } elseif ($json->httpCode == 404) {
                        throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                    } else {
                        throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                    }
                }
            } else {
                if (!empty($cacheData)) {
                    $json = json_decode($cacheData);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Artist::get - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
                }
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                } else {
                    if (is_object($json)) {
                        $this->parseGetData($json);
                    } else {
                        throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                    }
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
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
        }
        return ($url);
    }
}
