<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class File extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_FILE_GET = "https://commons.wikimedia.org/w/rest.php/v1/file/File:%s";

    protected ?string $title;

    protected \aportela\MediaWikiWrapper\FileInformation $prefered;
    protected \aportela\MediaWikiWrapper\FileInformation $original;
    protected \aportela\MediaWikiWrapper\FileInformation $thumbnail;

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function get(): bool
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_FILE_GET, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::get", array("title" => $this->title));
            $response = $this->http->GET($url);
            $json = json_decode($response->body);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
            } else {
                if ($response->code == 200) {
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
                } else {
                    switch ($json->httpCode) {
                        case 404:
                            throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                            break;
                        default:
                            throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                            break;
                    }
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }

    public function getURL(\aportela\MediaWikiWrapper\FileInformationType $informationType = \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL): ?string
    {
        switch ($informationType) {
            case \aportela\MediaWikiWrapper\FileInformationType::PREFERRED:
                if ($this->prefered != null) {
                    return ($this->prefered->url);
                } else {
                    return (null);
                }
                break;
            case \aportela\MediaWikiWrapper\FileInformationType::ORIGINAL:
                if ($this->original != null) {
                    return ($this->original->url);
                } else {
                    return (null);
                }
                break;
            case \aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL:
                if ($this->thumbnail != null) {
                    return ($this->thumbnail->url);
                } else {
                    return (null);
                }
                break;
        }
    }
}
