<?php

namespace aportela\MediaWikiWrapper\Test\Wikipedia;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class PageTest extends \PHPUnit\Framework\TestCase
{
    protected static $logger;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        self::$logger = new \Psr\Log\NullLogger("");
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void
    {
    }

    /**
     * Clean up the test case, called for every defined test
     */
    public function tearDown(): void
    {
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
    }

    public function testExistentHTML(): void
    {
        $f = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, "Iron_Maiden");;
        $this->assertIsString($f->getHTML());
    }

    public function testNonExistentHTML(): void
    {
        $this->expectException(\aportela\MediaWikiWrapper\Exception\NotFoundException::class);
        $this->expectExceptionMessage("Iron_Maiden2");
        $f = new \aportela\MediaWikiWrapper\Wikipedia\Page(self::$logger, "Iron_Maiden2");;
        $f->getHTML();
    }
}
