<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class Page extends \aportela\MediaWikiWrapper\Page
{

    const HTML_BASE_API_URL = "https://api.wikimedia.org/core/v1/wikipedia/en/page/%s/html";

    public function getHTML(): string
    {
        $url = sprintf(self::HTML_BASE_API_URL, urlencode($this->title));
        $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::get", array("title" => $this->title));
        $response = $this->http->GET($url);
        if ($response->code == 200) {
            return ($response->body);
        } else {
            $json = json_decode($response->body);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException("");
            }
            switch ($json->httpCode) {
                case 404:
                    throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                    break;
                default:
                    throw new \aportela\MediaWikiWrapper\Exception\HTTPException("title:" . $this->title, $json->httpCode);
                    break;
            }
        }
    }
}
