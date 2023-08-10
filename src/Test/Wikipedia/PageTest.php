<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends BaseTest
{
    public function testGetHTMLFromTitle(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $p->setURL("https://en.wikipedia.org/wiki/Iron_Maiden");
        $this->assertIsString($p->getHTML());
    }

    public function testGetHTMLFromURL(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
        $this->assertIsString($p->getHTML());
    }

    public function testGetHTMLFromInvalidURL(): void
    {
        $url = "https://www.google.es";
        $this->expectException(\aportela\MediaWikiWrapper\Exception\InvalidURLException::class);
        $this->expectExceptionMessage($url);
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setURL($url);
        $this->assertIsString($p->getHTML());
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
}
