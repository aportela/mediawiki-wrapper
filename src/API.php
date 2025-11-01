<?php

namespace aportela\MediaWikiWrapper;

abstract class API
{
    protected \Psr\Log\LoggerInterface $logger;
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    protected \aportela\MediaWikiWrapper\APIType $apiType;

    private ?\aportela\SimpleFSCache\Cache $cache = null;

    // TODO: API TOKENS (more api requests allowed) https://api.wikimedia.org/wiki/Authentication#Personal_API_tokens

    /**
     * https://api.wikimedia.org/wiki/Rate_limits
     * API requests without an access token are limited to 500 requests per hour per IP address.
     */
    // TODO
    private const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    public const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second

    private int $originalThrottleDelayMS = 0;
    private int $currentThrottleDelayMS = 0;
    private int $lastThrottleTimestamp = 0;


    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MediaWikiWrapper\APIType $apiType = \aportela\MediaWikiWrapper\APIType::REST, ?\aportela\SimpleFSCache\Cache $cache = null, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS)
    {
        $this->logger = $logger;
        $this->logger->debug("MediaWikiWrapper\\API::__construct");
        $this->apiType = $apiType;
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger);
        if ($throttleDelayMS < self::MIN_THROTTLE_DELAY_MS) {
            throw new \aportela\MediaWikiWrapper\Exception\InvalidThrottleMsDelayException("min throttle delay ms required: " . self::MIN_THROTTLE_DELAY_MS);
        }
        $this->originalThrottleDelayMS = $throttleDelayMS;
        $this->currentThrottleDelayMS = $throttleDelayMS;
        $this->lastThrottleTimestamp = intval(microtime(true) * 1000);
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
        // allow incrementing current throttle delay to a max of 5 seconds
        if ($this->currentThrottleDelayMS < 5000) {
            // set next throttle delay with current value * 2 (wait more time on next api calls)
            $this->currentThrottleDelayMS *= 2;
        }
    }

    /**
     * reset throttle to original value
     */
    protected function resetThrottle(): void
    {
        $this->currentThrottleDelayMS = $this->originalThrottleDelayMS;
    }

    /**
     * throttle api calls
     */
    protected function checkThrottle(): void
    {
        if ($this->currentThrottleDelayMS > 0) {
            $currentTimestamp = intval(microtime(true) * 1000);
            while (($currentTimestamp - $this->lastThrottleTimestamp) < $this->currentThrottleDelayMS) {
                usleep(10);
                $currentTimestamp = intval(microtime(true) * 1000);
            }
            $this->lastThrottleTimestamp = $currentTimestamp;
        }
    }

    private function saveCache(string $mbId, string $raw): bool
    {
        if ($this->cache !== null) {
            return ($this->cache->save($mbId, $raw));
        } else {
            return (false);
        }
    }

    private function getCache(string $hash): bool
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
