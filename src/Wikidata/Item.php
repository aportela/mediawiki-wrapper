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
        if (is_array($urlFields) && $urlFields["host"] == "www.wikidata.org") {
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
            $response = $this->http->GET($url);
            $json = json_decode($response->body);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
            } else {
                if ($response->code == 200) {
                    if (
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
                } else {
                    if ($json->httpCode == 404) {
                        throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->item);
                    } else {
                        throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->item, $json->httpCode);
                    }
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidItemException("");
        }
    }
}
