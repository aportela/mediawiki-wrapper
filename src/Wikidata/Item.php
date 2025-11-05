<?php

namespace aportela\MediaWikiWrapper\Wikidata;

class Item extends \aportela\MediaWikiWrapper\API
{
    public const REST_API_GET_WIKIPEDIA_TITLE = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&props=sitelinks&ids=%s&sitefilter=%swiki";

    protected ?string $item;

    public function setItem(string $item): void
    {
        $this->item = $item;
    }

    public function setURL(string $url): void
    {
        $urlFields = parse_url($url);
        if (is_array($urlFields) && isset($urlFields["host"]) && $urlFields["host"] == "www.wikidata.org" && isset($urlFields["path"]) && ! empty($urlFields["path"])) {
            $fields = explode("/", $urlFields["path"]);
            $totalFields = count($fields);
            if ($totalFields == 3 && $fields[1] == "wiki") {
                $this->item = $fields[2];
            } else {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidURLException($url);
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidURLException($url);
        }
    }

    public function getWikipediaTitle(\aportela\MediaWikiWrapper\Language $language): string
    {
        if (!empty($this->item)) {
            $url = sprintf(self::REST_API_GET_WIKIPEDIA_TITLE, $this->item, $language->value);
            $this->logger->debug("MediaWikiWrapper\Wikidata\Item::getWikipediaTitle", array("item" => $this->item, "language" => $language->value));
            $responseBody = $this->httpGET($url);
            if (! empty($responseBody)) {
                $json = json_decode($responseBody);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                } else {
                    if (
                        is_object($json) &&
                        isset($json->entities) &&
                        isset($json->entities->{$this->item}) &&
                        isset($json->entities->{$this->item}->sitelinks) &&
                        isset($json->entities->{$this->item}->sitelinks->{$language->value . "wiki"}) &&
                        isset($json->entities->{$this->item}->sitelinks->{$language->value . "wiki"}->title)
                    ) {
                        return ($json->entities->{$this->item}->sitelinks->{$language->value . "wiki"}->title);
                    } else {
                        throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->item);
                    }
                }
            } else {
                $this->logger->error("\aportela\MediaWikiWrapper\Item::getWikipediaTitle - Error: empty body on API response", [$url]);
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIResponse("Empty body on API response for URL: {$url}");
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidItemException("empty item");
        }
    }
}
