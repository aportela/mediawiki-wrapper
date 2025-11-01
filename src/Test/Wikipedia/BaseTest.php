<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected static \Psr\Log\NullLogger $logger;

    protected static \aportela\SimpleFSCache\Cache $cache;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        self::$logger = new \Psr\Log\NullLogger();
        self::$cache = new \aportela\SimpleFSCache\Cache(self::$logger, \aportela\SimpleFSCache\CacheFormat::NONE, dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "cache", false);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void {}

    /**
     * Clean up the test case, called for every defined test
     */
    public function tearDown(): void {}

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void {}
}
