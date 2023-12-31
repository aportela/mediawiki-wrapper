<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class ItemTest extends BaseTest
{
    public function testGetWikipediaTitleFromItem(): void
    {
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setItem("Q319");
        $this->assertEquals($i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH), "Jupiter");
    }

    public function testGetWikipediaTitleFromItemMissingItem(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidItemException::class);
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
    }

    public function testGetWikipediaTitleFromURL(): void
    {
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setURL("https://www.wikidata.org/wiki/Q319");
        $this->assertEquals($i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH), "Jupiter");
    }

    public function testGetWikipediaTitleFromInvalidWikidataURL(): void
    {
        $url = "https://www.wikidata.org/wiki_NOT_FOUND/Q319";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setURL($url);
        $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
    }

    public function testGetWikipediaTitleFromInvalidURL(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setURL($url);
        $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
    }

    public function testGetWikipediaTitleNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $item = "Q_000" . time();
        $this->expectExceptionMessage(rawurlencode($item));
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setItem($item);
        $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
    }
}
