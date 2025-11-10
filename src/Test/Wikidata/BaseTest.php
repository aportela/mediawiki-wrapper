<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper\Test\Wikidata;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected static \Psr\Log\NullLogger $logger;

    protected static \aportela\SimpleFSCache\Cache $cache;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        self::$logger = new \Psr\Log\NullLogger();
        self::$cache = new \aportela\SimpleFSCache\Cache(self::$logger, dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . "cache", null, \aportela\SimpleFSCache\CacheFormat::NONE);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    protected function setUp(): void
    {
    }

    /**
     * Clean up the test case, called for every defined test
     */
    protected function tearDown(): void
    {
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
    }
}
