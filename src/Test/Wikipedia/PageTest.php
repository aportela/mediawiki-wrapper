<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends BaseTest
{
    public function testGetJSONFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $obj = $p->getJSON();
        $this->assertIsObject($obj);
        $this->assertIsInt($obj->id);
        $this->assertEquals($obj->id, 38930);
        $this->assertIsString($obj->title);
        $this->assertEquals($obj->title, "Jupiter");
        $this->assertIsString($obj->source);
        $this->assertNotEmpty($obj->source);
    }

    public function testGetJSONMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getJSON();
    }

    public function testGetJSONFromURL(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertIsObject($p->getJSON());
    }

    public function testGetJSONFromWikipediaInvalidURL(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetJSONFromInvalidURL(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetJSONFromTitleNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($title);
        $p->getJSON();
    }

    public function testGetJSONFromURLNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getJSON();
    }

    public function testGetHTMLFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $this->assertIsString($p->getHTML());
    }

    public function testGetHTMLMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getHTML();
    }

    public function testGetHTMLFromURL(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertIsString($p->getHTML());
    }

    public function testGetHTMLFromWikipediaInvalidURL(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getHTML();
    }

    public function testGetHTMLFromInvalidURL(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getHTML();
    }

    public function testGetHTMLFromTitleNotFound(): void
    {
        $title = "Jupiter" . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($title);
        $p->getHTML();
    }

    public function testGetHTMLFromURLNotFound(): void
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
        $this->assertIsString($p->getIntroPlainText());
    }

    public function testGetIntroPlainTextMissingTitle(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidTitleException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromURL(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertIsString($p->getIntroPlainText());
    }

    public function testGetIntroPlainTextFromWikipediaInvalidURL(): void
    {
        $url = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $p->getIntroPlainText();
    }

    public function testGetIntroPlainTextFromInvalidURL(): void
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

    public function testGetIntroPlainTextFromURLNotFound(): void
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
