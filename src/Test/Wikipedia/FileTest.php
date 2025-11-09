<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class FileTest extends BaseTest
{
    private const string EXISTENT_FILE_TITLE = "Commons-logo.svg";

    public function testGet(): void
    {
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get(self::EXISTENT_FILE_TITLE);
        $this->assertNotEmpty($file->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED));
        $this->assertNotEmpty($file->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL));
        $this->assertNotEmpty($file->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL));
    }

    public function testGetMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Empty title");
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get("");
    }

    public function testGetNotFound(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $page = self::EXISTENT_FILE_TITLE . time() . time();
        $this->expectExceptionMessage(rawurlencode($page));
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get($page);
    }

    public function testGetUrlPreferred(): void
    {
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get(self::EXISTENT_FILE_TITLE);
        $url = $file->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    public function testGetUrlOriginal(): void
    {
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get(self::EXISTENT_FILE_TITLE);
        $url = $file->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    public function testGetUrlThumbnail(): void
    {
        $file = new \aportela\MediaWikiWrapper\Wikipedia\File(self::$logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, self::$cache);
        $file->get(self::EXISTENT_FILE_TITLE);
        $url = $file->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
    }
}
