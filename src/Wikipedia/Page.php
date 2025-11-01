<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class Page extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_PAGE_HTML = "https://en.wikipedia.org/w/rest.php/v1/page/%s/html";
    public const REST_API_PAGE_JSON = "https://en.wikipedia.org/w/rest.php/v1/page/%s";
    public const API_TEXTEXTRACTS_EXTENSION_PAGE_INTRO = "https://en.wikipedia.org/w/api.php?format=%s&action=query&prop=extracts&exintro&explaintext&redirects=1&titles=%s";
    /*
    const REST_API_PAGE_LINKS_LANGUAGES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/language";
    const REST_API_PAGE_LINKS_FILES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/media";
    */

    protected ?string $title;

    public function setTitle(string $title): void
    {
        $this->title = rawurlencode($title);
    }

    public function setURL(string $url): void
    {
        $urlFields = parse_url($url);
        if (is_array($urlFields) &&  str_ends_with($urlFields["host"], "wikipedia.org")) {
            $fields = explode("/", $urlFields["path"]);
            $totalFields = count($fields);
            if ($totalFields == 3 && $fields[1] == "wiki") {
                $this->title = $fields[2];
            } else {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidURLException($url);
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidURLException($url);
        }
    }

    public function getJSON(): object
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_PAGE_JSON, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getJSON", array("title" => $this->title));
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                } else {
                    return ($json);
                }
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                throw new \aportela\MediaWikiWrapper\Exception\RateLimitExceedException("title: {$this->title}", $response->code);
            } else {
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                }
                if ($json->httpCode == 404) {
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                } else {
                    throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }

    public function getHTML(): string
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_PAGE_HTML, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getHTML", array("title" => $this->title));
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                return ($response->body);
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                throw new \aportela\MediaWikiWrapper\Exception\RateLimitExceedException("title: {$this->title}", $response->code);
            } else {
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                }
                if ($json->httpCode == 404) {
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                } else {
                    throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }

    public function getIntroPlainText(): string
    {
        if (!empty($this->title)) {
            $url = sprintf(self::API_TEXTEXTRACTS_EXTENSION_PAGE_INTRO, \aportela\MediaWikiWrapper\APIFormat::JSON->value, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getIntoExtract", array("title" => $this->title));
            $response = $this->httpGET($url);
            if ($response->code == 200) {
                $json = json_decode($response->body);
                $pages = get_object_vars($json->query->pages);
                $page = array_keys($pages)[0];
                if ($page != -1) {
                    return ($json->query->pages->{$page}->extract);
                } else {
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                }
            } else {
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                }
                if ($json->httpCode == 404) {
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                } else {
                    throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }
}
