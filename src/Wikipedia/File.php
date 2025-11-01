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

    public function get(): bool
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_FILE_GET, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::get", array("title" => $this->title));
            $response = $this->httpGET($url);
            $json = json_decode($response->body);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
            } else {
                if ($response->code == 200) {
                    $this->resetThrottle();
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
                            $json->preferred->mediatype,
                            $json->preferred->size,
                            $json->preferred->width,
                            $json->preferred->height,
                            $json->preferred->duration,
                            $json->preferred->url
                        );
                    }
                    if (isset($json->thumbnail)) {
                        $this->thumbnail = new \aportela\MediaWikiWrapper\FileInformation(
                            \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL,
                            $json->preferred->mediatype,
                            $json->preferred->size,
                            $json->preferred->width,
                            $json->preferred->height,
                            $json->preferred->duration,
                            $json->preferred->url
                        );
                    }
                    return (true);
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
