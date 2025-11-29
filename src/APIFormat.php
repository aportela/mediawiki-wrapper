<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper;

enum APIFormat: string
{
    case JSON = "json";

    case XML = "xml";
}
