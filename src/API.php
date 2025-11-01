<?php

namespace aportela\MediaWikiWrapper;

abstract class API
{
    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MediaWikiWrapper\APIType $apiType;

    private ?\aportela\SimpleFSCache\Cache $cache = null;
    private \aportela\SimpleThrottle\Throttle $throttle;

    // TODO: API TOKENS (more api requests allowed) https://api.wikimedia.org/wiki/Authentication#Personal_API_tokens

    /**
     * https://api.wikimedia.org/wiki/Rate_limits
     * API requests without an access token are limited to 500 requests per hour per IP address.
     */
    // TODO
    private const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    public const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second


    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MediaWikiWrapper\APIType $apiType = \aportela\MediaWikiWrapper\APIType::REST, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        $this->logger = $logger;
        $this->logger->debug("MediaWikiWrapper\\API::__construct");
        $this->apiType = $apiType;
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger);
        if ($throttleDelayMS < self::MIN_THROTTLE_DELAY_MS) {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidThrottleMsDelayException("min throttle delay ms required: " . self::MIN_THROTTLE_DELAY_MS);
        }
        $this->throttle = new \aportela\SimpleThrottle\Throttle($this->logger, $throttleDelayMS, 5000, 10);
        $this->cache = $cache;
    }

    public function __destruct()
    {
        $this->logger->debug("MediaWikiWrapper\API::__destruct");
    }

    /**
     * increment throttle delay (time between api calls)
     * call this function when api returns rate limit exception
     * (or connection reset errors caused by remote server busy ?)
     */
    protected function incrementThrottle(): void
    {
        $this->throttle->increment(\aportela\SimpleThrottle\ThrottleDelayIncrementType::MULTIPLY_BY_2);
    }

    /**
     * reset throttle to original value
     */
    protected function resetThrottle(): void
    {
        $this->throttle->reset();
    }

    /**
     * throttle api calls
     */
    protected function checkThrottle(): void
    {
        $this->throttle->throttle();
    }

    protected function setCacheFormat(\aportela\SimpleFSCache\CacheFormat $format): void
    {
        if ($this->cache !== null) {
            $this->cache->setFormat($format);
        }
    }

    protected function saveCache(string $mbId, string $raw): bool
    {
        if ($this->cache !== null) {
            return ($this->cache->save($mbId, $raw));
        } else {
            return (false);
        }
    }

    protected function getCache(string $hash): bool
    {
        if ($this->cache !== null) {
            if ($cache = $this->cache->get($hash)) {
                // TODO
                return (true);
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    /**
     * http handler GET method wrapper for catching CurlExecException (connection errors / server busy ?)
     */
    protected function httpGET(string $url): \aportela\HTTPRequestWrapper\HTTPResponse
    {
        $this->logger->debug("Opening url: {$url}");
        try {
            return ($this->http->GET($url));
        } catch (\aportela\HTTPRequestWrapper\Exception\CurlExecException $e) {
            $this->logger->error("Error opening URL " . $url, [$e->getCode(), $e->getMessage()]);
            $this->incrementThrottle(); // sometimes api calls return connection error, interpret this as rate limit response
            throw new \aportela\MediaWikiWrapper\Exception\RemoteAPIServerConnectionException("Error opening URL " . $url, 0, $e);
        }
    }
}
