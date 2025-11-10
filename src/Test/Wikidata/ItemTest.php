<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class ItemTest extends BaseTest
{
    private const string INVALID_ITEM_IDENTIFIER = "";

    private const string INVALID_ITEM_WIKIDATA_URL = "https://www.wikidata.org/wiki_NOT_FOUND/Q319";

    private const string INVALID_ITEM_URL = "https://www.google.es/";

    private const string EXISTENT_ITEM_IDENTIFIER = "Q319";

    private const string EXISTENT_ITEM_URL = "https://www.wikidata.org/wiki/Q319";

    private const EXISTENT_ITEM_WIKIPEDIA_LANG = \aportela\MediaWikiWrapper\Language::ENGLISH;

    private const string EXISTENT_ITEM_WIKIPEDIA_TITLE = "Jupiter";

    public function testGetWikipediaTitleFromItem(): void
    {
        $item = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertEquals($item->getWikipediaTitleFromIdentifier(self::EXISTENT_ITEM_IDENTIFIER, self::EXISTENT_ITEM_WIKIPEDIA_LANG), self::EXISTENT_ITEM_WIKIPEDIA_TITLE);
    }

    public function testGetWikipediaTitleFromItemMissingItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $item = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $item->getWikipediaTitleFromIdentifier(self::INVALID_ITEM_IDENTIFIER, self::EXISTENT_ITEM_WIKIPEDIA_LANG);
    }

    public function testGetWikipediaTitleFromUrl(): void
    {
        $item = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertEquals($item->getWikipediaTitleFromURL(self::EXISTENT_ITEM_URL, self::EXISTENT_ITEM_WIKIPEDIA_LANG), self::EXISTENT_ITEM_WIKIPEDIA_TITLE);
    }

    public function testGetWikipediaTitleFromInvalidWikidataUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL: " . self::INVALID_ITEM_WIKIDATA_URL);
        $item = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $item->getWikipediaTitleFromURL(self::INVALID_ITEM_WIKIDATA_URL, self::EXISTENT_ITEM_WIKIPEDIA_LANG);
    }

    public function testGetWikipediaTitleFromInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL: " . self::INVALID_ITEM_URL);
        $item = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $item->getWikipediaTitleFromURL(self::INVALID_ITEM_URL, self::EXISTENT_ITEM_WIKIPEDIA_LANG);
    }

    public function testGetWikipediaTitleNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage("Error: missing wikipedia title json property");
        $item = "Q_000" . time();
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $i->getWikipediaTitleFromIdentifier($item, self::EXISTENT_ITEM_WIKIPEDIA_LANG);
    }
}
