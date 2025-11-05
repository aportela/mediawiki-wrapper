<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class FileTest extends BaseTest
{

    private const EXISTENT_FILE_TITLE = "Commons-logo.svg";

    public function testGet(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, self::EXISTENT_FILE_TITLE, \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
        $this->assertNotEmpty($f->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED));
        $this->assertNotEmpty($f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL));
        $this->assertNotEmpty($f->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL));
    }

    public function testGetMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, "", \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
    }

    public function testGetNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $page = self::EXISTENT_FILE_TITLE . time() . time();
        $this->expectExceptionMessage(rawurlencode($page));
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, $page, \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
    }

    public function testGetUrlPreferred(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, self::EXISTENT_FILE_TITLE, \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    public function testGetUrlOriginal(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, self::EXISTENT_FILE_TITLE, \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    public function testGetUrlThumbnail(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, self::EXISTENT_FILE_TITLE, \aportela\MediaWikiWrapper\APIType::REST, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $f->get();
        $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }
}
