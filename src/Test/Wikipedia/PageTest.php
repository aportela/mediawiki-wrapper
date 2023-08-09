<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends BaseTest
{
    public function testGetHTML(): void
    {
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle("Jupiter");
        $this->assertIsString($p->getHTML());
    }

    public function testGetHTMLNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $page = "Jupiter" . time() . time();
        $this->expectExceptionMessage(rawurlencode($page));
        $p = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $p->setTitle($page);
        $p->getHTML();
    }
}
