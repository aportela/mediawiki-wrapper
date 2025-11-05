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
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $json = json_decode($responseBody);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException(json_last_error_msg());
                    } else {
                        $this->saveCache($cacheHash, $responseBody);
                        if (is_object($json)) {
                            return ($json);
                        } else {
                            throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException("invalid object");
                        }
                    }
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Page::get - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
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
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $this->saveCache($cacheHash, $responseBody);
                    return ($responseBody);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getHTML - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                if (!empty($cacheData)) {
                    return ($cacheData);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getHTML - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
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
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::TXT);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $json = json_decode($responseBody);
                    if (is_object($json)) {
                        if (isset($json->query)) {
                            $pages = get_object_vars($json->query->pages);
                            $page = array_keys($pages)[0];
                            if ($page != -1) {
                                $this->saveCache($cacheHash, $json->query->pages->{$page}->extract);
                                return ($json->query->pages->{$page}->extract);
                            } else {
                                throw new \aportela\MediaWikiWrapper\Exception\NotFoundException((string)$this->title);
                            }
                        } else {
                            throw new \aportela\MediaWikiWrapper\Exception\NotFoundException((string)$this->title);
                        }
                    } else {
                        $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainText - Error: invalid json object", [$responseBody]);
                        throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException("invalid object");
                    }
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainText - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                return ($cacheData);
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }
}
