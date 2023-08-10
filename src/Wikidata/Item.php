<?php

namespace aportela\MediaWikiWrapper\Wikidata;

class Item extends \aportela\MediaWikiWrapper\API
{
    const REST_API_GET_WIKIPEDIA_TITLE = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&props=sitelinks&ids=%s&sitefilter=%swiki";

    protected ?string $item;

    public function setItem(string $item)
    {
        $this->item = $item;
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
                    switch ($json->httpCode) {
                        case 404:
                            throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->item);
                            break;
                        default:
                            throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->item, $json->httpCode);
                            break;
                    }
                }
            }
        }
    }
}
