<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class Page extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_PAGE_HTML = "https://%s.wikipedia.org/w/rest.php/v1/page/%s/html";
    public const REST_API_PAGE_JSON = "https://%s.wikipedia.org/w/rest.php/v1/page/%s";
    public const API_TEXTEXTRACTS_EXTENSION_PAGE_INTRO = "https://%s.wikipedia.org/w/api.php?format=%s&action=query&prop=extracts&exintro&explaintext&redirects=1&titles=%s";
    /*
    const REST_API_PAGE_LINKS_LANGUAGES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/language";
    const REST_API_PAGE_LINKS_FILES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/media";
    */

    public function __construct(\Psr\Log\LoggerInterface $logger, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        parent::__construct($logger, \aportela\MediaWikiWrapper\APIType::REST, $throttleDelayMS, $cache);
    }

    private function getTitleFromURL(string $url): string
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
                return ($fields[2]);
            } else {
                $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::setURL - Invalid URL: {$url}");
                throw new \InvalidArgumentException("Invalid URL: {$url}");
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::setURL - Invalid URL: {$url}");
            throw new \InvalidArgumentException("Invalid URL: {$url}");
        }
    }

    public function getJSONFromTitle(string $title, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): object
    {
        if (!empty($title)) {
            $url = sprintf(self::REST_API_PAGE_JSON, $language->value, rawurlencode($title));
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::JSON);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $json = $this->parseJSONString($responseBody);
                    $this->saveCache($cacheHash, $responseBody);
                    return ($json);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Page::get - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                if (!empty($cacheData)) {
                    $json = $this->parseJSONString($cacheData);
                    return ($json);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getJSONFromTitle - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
                }
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getJSONFromTitle - Error: empty title");
            throw new \InvalidArgumentException("Empty title");
        }
    }

    public function getJSONFromURL(string $url, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): object
    {
        return ($this->getJSONFromTitle($this->getTitleFromURL($url), $language));
    }

    public function getHTMLFromTitle(string $title, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        if (!empty($title)) {
            $url = sprintf(self::REST_API_PAGE_HTML, $language->value, rawurlencode($title));
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::HTML);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $this->saveCache($cacheHash, $responseBody);
                    return ($responseBody);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getHTMLFromTitle - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                if (!empty($cacheData)) {
                    return ($cacheData);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getHTMLFromTitle - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
                }
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getHTMLFromTitle - Error: empty title");
            throw new \InvalidArgumentException("Empty title");
        }
    }

    public function getHTMLFromURL(string $url, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        return ($this->getHTMLFromTitle($this->getTitleFromURL($url), $language));
    }

    public function getIntroPlainTextFromTitle(string $title, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        if (!empty($title)) {
            $url = sprintf(self::API_TEXTEXTRACTS_EXTENSION_PAGE_INTRO, $language->value, \aportela\MediaWikiWrapper\APIFormat::JSON->value, urlencode($title));
            $this->setCacheFormat(\aportela\SimpleFSCache\CacheFormat::TXT);
            $cacheHash = md5($url);
            $cacheData = $this->getCache($cacheHash);
            if (! is_string($cacheData)) {
                $responseBody = $this->httpGET($url);
                if (! empty($responseBody)) {
                    $json = $this->parseJSONString($responseBody);
                    if (isset($json->query) && is_object($json->query) && isset($json->query->pages) && is_object($json->query->pages)) {
                        $pages = get_object_vars($json->query->pages);
                        $page = array_keys($pages)[0];
                        if ($page != -1) {
                            if (isset($json->query->pages->{$page}) && is_object($json->query->pages->{$page}) && isset($json->query->pages->{$page}->extract) && is_string($json->query->pages->{$page}->extract)) {
                                $this->saveCache($cacheHash, $json->query->pages->{$page}->extract);
                                return ($json->query->pages->{$page}->extract);
                            } else {
                                $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainText - Error: missing wikipedia extract json property", [$title, $language->value, $url]);
                                throw new \aportela\MediaWikiWrapper\Exception\NotFoundException((string)$title);
                            }
                        } else {
                            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainText - Error: missing query page json property", [$title, $language->value, $url]);
                            throw new \aportela\MediaWikiWrapper\Exception\NotFoundException((string)$title);
                        }
                    } else {
                        $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainText - Error: missing query pages json property", [$title, $language->value, $url]);
                        throw new \aportela\MediaWikiWrapper\Exception\NotFoundException("invalid object");
                    }
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainTextFromTitle - Error: empty body on API response", [$url]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
                }
            } else {
                if (!empty($cacheData)) {
                    return ($cacheData);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainTextFromTitle - Error: cached data for identifier is empty", [$cacheHash]);
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidCacheException("Cached data for identifier ({$cacheHash}) is empty");
                }
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\Wikipedia\Page::getIntroPlainTextFromTitle - Error: empty title");
            throw new \InvalidArgumentException("Empty title");
        }
    }

    public function getIntroPlainTextFromURL(string $url, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        return ($this->getIntroPlainTextFromTitle($this->getTitleFromURL($url), $language));
    }
}
