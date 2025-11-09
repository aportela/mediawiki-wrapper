<?php

namespace aportela\MediaWikiWrapper;

abstract class API
{
    protected \aportela\HTTPRequestWrapper\HTTPRequest $http;
    private readonly \aportela\SimpleThrottle\Throttle $throttle;

    // TODO: API TOKENS (more api requests allowed) https://api.wikimedia.org/wiki/Authentication#Personal_API_tokens

    /**
     * https://api.wikimedia.org/wiki/Rate_limits
     * API requests without an access token are limited to 500 requests per hour per IP address.
     */
    // TODO
    private const MIN_THROTTLE_DELAY_MS = 20; // min allowed: 50 requests per second
    public const DEFAULT_THROTTLE_DELAY_MS = 1000; // default: 1 request per second

    public function __construct(protected \Psr\Log\LoggerInterface $logger, protected \aportela\MediaWikiWrapper\APIType $apiType = \aportela\MediaWikiWrapper\APIType::REST, int $throttleDelayMS = self::DEFAULT_THROTTLE_DELAY_MS, private readonly ?\aportela\SimpleFSCache\Cache $cache = null)
    {
        $this->http = new \aportela\HTTPRequestWrapper\HTTPRequest($this->logger);
        if ($throttleDelayMS < self::MIN_THROTTLE_DELAY_MS) {
            $this->logger->critical("\aportela\MediaWikiWrapper\API::__construct - ERROR: invalid throttleDelayMS", [$throttleDelayMS, self::MIN_THROTTLE_DELAY_MS]);
            throw new \aportela\MediaWikiWrapper\Exception\InvalidThrottleMsDelayException("min throttle delay ms required: " . self::MIN_THROTTLE_DELAY_MS);
        }
        $this->throttle = new \aportela\SimpleThrottle\Throttle($this->logger, $throttleDelayMS, 5000, 10);
    }

    public function __destruct() {}

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

    protected function saveCache(string $hash, string $raw): bool
    {
        if ($this->cache !== null) {
            return ($this->cache->set($hash, $raw));
        } else {
            return (false);
        }
    }

    protected function getCache(string $hash): bool|string
    {
        if ($this->cache !== null) {
            $cacheData = $this->cache->get($hash, false);
            if (is_string($cacheData)) {
                return ($cacheData);
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    /**
     * http handler GET method wrapper for manage throttle & response, also catches CurlExecException (connection errors / server busy ?)
     */
    protected function httpGET(string $url): ?string
    {
        $this->logger->debug("\aportela\MediaWikiWrapper\Entity::httpGET - Opening URL", [$url]);
        try {
            $this->checkThrottle();
            $response = $this->http->GET($url);
            if ($response->code == 200) {
                $this->resetThrottle();
                return ($response->body);
            } elseif ($response->code == 404) {
                $this->logger->error("\aportela\MediaWikiWrapper\Entity::httpGET - Error opening URL", [$url, $response->code, $response->body]);
                throw new \aportela\MediaWikiWrapper\Exception\NotFoundException("Error opening URL: {$url}", $response->code);
            } elseif ($response->code == 503) {
                $this->incrementThrottle();
                $this->logger->error("\aportela\MediaWikiWrapper\Entity::httpGET - Error opening URL", [$url, $response->code, $response->body]);
                throw new \aportela\MediaWikiWrapper\Exception\RateLimitExceedException("Error opening URL: {$url}", $response->code);
            } else {
                $this->logger->error("\aportela\MediaWikiWrapper\Entity::httpGET - Error opening URL", [$url, $response->code, $response->body]);
                throw new \aportela\MediaWikiWrapper\Exception\HTTPException("Error opening URL: {$url}", $response->code);
            }
        } catch (\aportela\HTTPRequestWrapper\Exception\CurlExecException $e) {
            $this->logger->error("\aportela\MediaWikiWrapper\Entity::httpGET - Error opening URL", [$url, $e->getCode(), $e->getMessage()]);
            $this->incrementThrottle(); // sometimes api calls return connection error, interpret this as rate limit response
            throw new \aportela\MediaWikiWrapper\Exception\RemoteAPIServerConnectionException("Error opening URL: {$url}", 0, $e);
        }
    }

    protected function parseJSONString(string $raw): object
    {
        if (! empty($raw)) {
            $json = json_decode($raw);
            if (json_last_error() != JSON_ERROR_NONE) {
                $this->logger->error("\aportela\MediaWikiWrapper\API::parseJSONString - Error decoding json string", [json_last_error_msg(), json_last_error()]);
                throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException(json_last_error_msg(), json_last_error());
            } else {
                if (is_object($json)) {
                    return ($json);
                } else {
                    $this->logger->error("\aportela\MediaWikiWrapper\API::parseJSONString - Error decoding json string (invalid/null object)");
                    throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException("Error decoding json string (invalid/null object)");
                }
            }
        } else {
            $this->logger->error("\aportela\MediaWikiWrapper\API::parseJSONString - Error decoding json string (empty string)");
            throw new \aportela\MediaWikiWrapper\Exception\InvalidJSONException("Error decoding json string (empty string)");
        }
    }
}
