<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends BaseTest
{
    public function testGetJsonFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $obj = $p->getJSON();
        $this->assertIsInt($obj->id);
        $this->assertEquals($obj->id, 38930);
        $this->assertIsString($obj->title);
        $this->assertEquals($obj->title, "Jupiter");
        $this->assertIsString($obj->source);
        $this->assertNotEmpty($obj->source);
    }

    public function testGetJsonMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getJSON();
    }

    public function testGetJsonFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertEquals($p->getJSON()->title, "Jupiter");
    }

    public function testGetJsonFromWikipediaInvalidUrl(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetJsonFromInvalidUrl(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetJsonFromTitleNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($title);
        $p->getJSON();
    }

    public function testGetJsonFromUrlNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetHtmlFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $this->assertStringContainsString("<title>Jupiter</title>", $p->getHTML());
    }

    public function testGetHtmlMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getHTML();
    }

    public function testGetHtmlFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertStringContainsString("<title>Jupiter</title>", $p->getHTML());
    }

    public function testGetHtmlFromWikipediaInvalidUrl(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getHTML();
    }

    public function testGetHtmlFromInvalidUrl(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getHTML();
    }

    public function testGetHtmlFromTitleNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($title);
        $p->getHTML();
    }

    public function testGetHtmlFromUrlNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getHTML();
    }

    public function testGetIntroPlainText(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $this->assertStringContainsString("Jupiter", $p->getIntroPlainText());
    }

    public function testGetIntroPlainTextMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertStringContainsString("Jupiter", $p->getIntroPlainText());
    }

    public function testGetIntroPlainTextFromWikipediaInvalidUrl(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromInvalidUrl(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromTitleNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($title);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromUrlNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getIntroPlainText();
    }
}
