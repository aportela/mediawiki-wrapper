<?php

namespace aportela\MediaWikiWrapper;

abstract class API
{
    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MediaWikiWrapper\APIType $apiType;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MediaWikiWrapper\APIType $apiType = \aportela\MediaWikiWrapper\APIType::REST)
    {
        $this->logger = $logger;
        $this->logger->debug("MediaWikiWrapper\\API::__construct");
        $this->apiType = $apiType;
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger);
    }

    public function __destruct()
    {
        $this->logger->debug("MediaWikiWrapper\API::__destruct");
    }
}
