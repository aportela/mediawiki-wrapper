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
        if (
            is_array($urlFields) &&
            isset($urlFields["host"]) && ! empty($urlFields["host"]) && str_ends_with($urlFields["host"], "wikipedia.org") &&
            isset($urlFields["path"]) && ! empty($urlFields["path"])
        ) {
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
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::JSON);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getJSON", array("title" => $this->title));
                $response = $this->httpGET($url);
                if ($response->code == 200) {
                    $this->resetThrottle();
                    $json = json_decode($response->body);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                    } else {
                        $this->saveCache($cacheHash, $response->body);
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
                        return ($json);
                    } else {
                        throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                    }
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
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::HTML);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getHTML", array("title" => $this->title));
                $response = $this->httpGET($url);
                if ($response->code == 200) {
                    $this->resetThrottle();
                    $this->saveCache($cacheHash, $response->body);
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
                return ($cacheData);
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }

    public function getIntroPlainText(): string
    {
        if (!empty($this->title)) {
            $url = sprintf(self::API_TEXTEXTRACTS_EXTENSION_PAGE_INTRO, \aportela\MediaWikiWrapper\APIFormat::JSON->value, $this->title);
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::TXT);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getIntoExtract", array("title" => $this->title));
                $response = $this->httpGET($url);
                if ($response->code == 200) {
                    $this->resetThrottle();
                    $json = json_decode($response->body);
                    $pages = get_object_vars($json->query->pages);
                    $page = array_keys($pages)[0];
                    if ($page != -1) {
                        $this->saveCache($cacheHash, $json->query->pages->{$page}->extract);
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
                return ($cacheData);
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }
}
