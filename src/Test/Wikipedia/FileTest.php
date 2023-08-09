<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class FileTest extends BaseTest
{
    public function testGet(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $f->setTitle("Commons-logo.svg");
        $this->assertTrue($f->get());
    }

    public function testGetNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $page = "Commons-logo.svg" . time() . time();
        $this->expectExceptionMessage(rawurlencode($page));
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $f->setTitle($page);
        $f->get();
    }

    public function testGETURLPreferred(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $f->setTitle("Commons-logo.svg");
        $this->assertTrue($f->get());
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
    }

    public function testGETURLOriginal(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $f->setTitle("Commons-logo.svg");
        $this->assertTrue($f->get());
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
    }

    public function testGETURLThumbnail(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\APIType::REST);
        $f->setTitle("Commons-logo.svg");
        $this->assertTrue($f->get());
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
    }
}
