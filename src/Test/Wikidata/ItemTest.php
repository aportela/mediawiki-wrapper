<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class ItemTest extends BaseTest
{
    public function testGetWikipediaTitle(): void
    {
        $i = new \aportela\MediaWikiWrapper\Wikidata\Item(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $i->setItem("Q319");
        $this->assertEquals($i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH), "Jupiter");
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
