<?php

namespace aportela\MediaWikiWrapper\Exception;

class InvalidCacheException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
