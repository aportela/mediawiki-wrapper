<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper;

enum FileInformationType
{
    case PREFERRED;
    case ORIGINAL;
    case THUMBNAIL;
}
