<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper\Wikidata;

class Item extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_GET_WIKIPEDIA_TITLE = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&props=sitelinks&ids=%s&sitefilter=%swiki";

    public function __construct(\Psr\Log\LoggerInterface $logger, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        parent::__construct($logger, \aportela\MediaWikiWrapper\APIType::REST, $throttleDelayMS, $cache);
    }

    public function getWikipediaTitleFromIdentifier(string $identifier, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        if ($identifier !== '' && $identifier !== '0') {
            $url = sprintf(self::REST_API_GET_WIKIPEDIA_TITLE, urlencode($identifier), $language->value);
            $responseBody = $this->httpGET($url);
            if (!in_array($responseBody, [null, '', '0'], true)) {
                $json = $this->parseJSONString($responseBody);
                if (
                    isset($json->entities) &&
                    isset($json->entities->{$identifier}) && is_object($json->entities->{$identifier}) &&
                    isset($json->entities->{$identifier}->sitelinks) && is_object($json->entities->{$identifier}->sitelinks) &&
                    isset($json->entities->{$identifier}->sitelinks->{$language->value . "wiki"}) && is_object($json->entities->{$identifier}->sitelinks->{$language->value . "wiki"}) &&
                    isset($json->entities->{$identifier}->sitelinks->{$language->value . "wiki"}->title) && is_string($json->entities->{$identifier}->sitelinks->{$language->value . "wiki"}->title)
                ) {
                    return ($json->entities->{$identifier}->sitelinks->{$language->value . "wiki"}->title);
                } else {
                    $this->logger->error(\aportela\MediaWikiWrapper\Wikidata\Item::class . '::getWikipediaTitle - Error: missing wikipedia title json property', [$identifier, $language->value, $url]);
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException("Error: missing wikipedia title json property");
                }
            } else {
                $this->logger->error("\aportela\MediaWikiWrapper\Item::getWikipediaTitle - Error: empty body on API response", [$url]);
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse('Empty body on API response for URL: ' . $url);
            }
        } else {
            $this->logger->error(\aportela\MediaWikiWrapper\Wikidata\Item::class . '::setItem - Error: empty identifier');
            throw new \InvalidArgumentException("empty identifier");
        }
    }

    public function getWikipediaTitleFromURL(string $url, \aportela\MediaWikiWrapper\Language $language = \aportela\MediaWikiWrapper\Language::ENGLISH): string
    {
        $urlFields = parse_url($url);
        if (
            is_array($urlFields) && isset($urlFields["host"]) && $urlFields["host"] == "www.wikidata.org" && isset($urlFields["path"]) &&  ($urlFields["path"] !== '' && $urlFields["path"] !== '0')
        ) {
            $fields = explode("/", $urlFields["path"]);
            $totalFields = count($fields);
            if ($totalFields === 3 && $fields[1] == "wiki") {
                return ($this->getWikipediaTitleFromIdentifier($fields[2], $language));
            } else {
                $this->logger->error(\aportela\MediaWikiWrapper\Wikidata\Item::class . '::setItemFromURL - Error: invalid URL: ' . $url);
                throw new \InvalidArgumentException('Invalid URL: ' . $url);
            }
        } else {
            $this->logger->error(\aportela\MediaWikiWrapper\Wikidata\Item::class . '::setItemFromURL - Error: invalid URL: ' . $url);
            throw new \InvalidArgumentException('Invalid URL: ' . $url);
        }
    }
}
