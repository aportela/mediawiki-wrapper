<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends BaseTest
{
    private const EXISTENT_PAGE_TITLE = "Jupiter";
    private const EXISTENT_PAGE_LANGUAGE = \aportela\MediaWikiWrapper\Language::ENGLISH;
    private const EXISTENT_PAGE_URL = "https://en.wikipedia.org/wiki/Jupiter";
    private const INVALID_PAGE_TITLE = "";
    private const NON_EXISTENT_WIKIPEDIA_PAGE_URL = "https://en.wikipedia.org/wiki_NOT_FOUND/Jupiter";
    private const NON_EXISTENT_PAGE_URL = "https://www.google.es/";

    public function testGetJsonFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        /**
         * @var \stdClass $obj
         */
        $obj = $p->getJSONFromTitle(self::EXISTENT_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE);
        $this->assertIsInt($obj->id);
        $this->assertEquals($obj->id, 38930);
        $this->assertIsString($obj->title);
        $this->assertEquals($obj->title, self::EXISTENT_PAGE_TITLE);
        $this->assertIsString($obj->source);
        $this->assertNotEmpty($obj->source);
    }

    public function testGetJsonMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getJSONFromTitle(self::INVALID_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetJsonFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        /**
         * @var \stdClass $object
         */
        $object = $p->getJSONFromURL(self::EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
        $this->assertEquals(self::EXISTENT_PAGE_TITLE, $object->title);
    }

    public function testGetJsonFromWikipediaInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL: " . self::NON_EXISTENT_WIKIPEDIA_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getJSONFromURL(self::NON_EXISTENT_WIKIPEDIA_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetJsonFromInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL: " . self::NON_EXISTENT_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getJSONFromURL(self::NON_EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetJsonFromTitleNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getJSONFromTitle($title, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetJsonFromUrlNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getJSONFromURL($url, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetHtmlFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertStringContainsString("<title>Jupiter</title>", $p->getHTMLFromTitle(self::EXISTENT_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE));
    }

    public function testGetHtmlMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getHTMLFromTitle(self::INVALID_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetHtmlFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertStringContainsString("<title>Jupiter</title>", $p->getHTMLFromURL(self::EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE));
    }

    public function testGetHtmlFromWikipediaInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::NON_EXISTENT_WIKIPEDIA_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getHTMLFromURL(self::NON_EXISTENT_WIKIPEDIA_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetHtmlFromInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::NON_EXISTENT_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getHTMLFromURL(self::NON_EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetHtmlFromTitleNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getHTMLFromTitle($title, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetHtmlFromUrlNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getHTMLFromURL($url, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetIntroPlainTextFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertStringContainsString(self::EXISTENT_PAGE_TITLE, $p->getIntroPlainTextFromTitle(self::EXISTENT_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE));
    }

    public function testGetIntroPlainTextFromTitleMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getIntroPlainTextFromTitle(self::INVALID_PAGE_TITLE, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetIntroPlainTextFromUrl(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $this->assertStringContainsString(self::EXISTENT_PAGE_TITLE, $p->getIntroPlainTextFromURL(self::EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE));
    }

    public function testGetIntroPlainTextFromWikipediaInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::NON_EXISTENT_WIKIPEDIA_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getIntroPlainTextFromURL(self::NON_EXISTENT_WIKIPEDIA_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetIntroPlainTextFromInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::NON_EXISTENT_PAGE_URL);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getIntroPlainTextFromURL(self::NON_EXISTENT_PAGE_URL, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetIntroPlainTextFromTitleNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $this->expectExceptionMessage(rawurlencode($title));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getIntroPlainTextFromTitle($title, self::EXISTENT_PAGE_LANGUAGE);
    }

    public function testGetIntroPlainTextFromUrlNotFound(): void
    {
        $title = self::EXISTENT_PAGE_TITLE . time() . time();
        $url = "https://en.wikipedia.org/wiki/" . $title;
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage($title);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $p->getIntroPlainTextFromURL($url, self::EXISTENT_PAGE_LANGUAGE);
    }
}
