<?php

namespace aportela\MediaWikiWrapper;

abstract class Page
{
    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected string $title;

    public function __construct(\Psr\Log\LoggerInterface $logger, $title)
    {
        $this->logger = $logger;
        $this->title = $title;
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger);
        $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::__construct");
    }

    public function __destruct()
    {
        $this->logger->debug("MediaWikiWrapper\Wikipedia\Page::__destruct");
    }
}
