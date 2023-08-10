<?php

namespace aportela\MediaWikiWrapper\Wikipedia;

class Page extends \aportela\MediaWikiWrapper\API
{
    const REST_API_PAGE_HTML = "https://en.wikipedia.org/w/rest.php/v1/page/%s/html";
    /*
    const REST_API_PAGE_SOURCE = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter";
    const REST_API_PAGE_OFFLINE = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/with_html";
    const REST_API_PAGE_LINKS_LANGUAGES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/language";
    const REST_API_PAGE_LINKS_FILES = "https://en.wikipedia.org/w/rest.php/v1/page/Jupiter/links/media";
    */

    protected ?string $title;

    public function setTitle(string $title)
    {
        $this->title = rawurlencode($title);
    }

    public function setURL(string $url)
    {
        $urlFields = parse_url($url);
        if (is_array($urlFields) && $urlFields["host"] == "en.wikipedia.org") {
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

    public function getHTML(): string
    {
        if (!empty($this->title)) {
            $url = sprintf(self::REST_API_PAGE_HTML, $this->title);
            $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::getHTML", array("title" => $this->title));
            $response = $this->http->GET($url);
            if ($response->code == 200) {
                return ($response->body);
            } else {
                $json = json_decode($response->body);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidAPIFormatException(json_last_error_msg());
                }
                switch ($json->httpCode) {
                    case 404:
                        throw new \aportela\MediaWikiWrapper\Exception\NotFoundException($this->title);
                        break;
                    default:
                        throw new \aportela\MediaWikiWrapper\Exception\HTTPException($this->title, $json->httpCode);
                        break;
                }
            }
        } else {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidTitleException("");
        }
    }
}
