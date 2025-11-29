<?php

declare(strict_types=1);

namespace aportela\MediaWikiWrapper;

final class FileInformation
{
    // TODO: change URL to non null
    public function __construct(public \aportela\MediaWikiWrapper\FileInformationType $fileInformationType, public string $mediaType, public ?int $size = null, public ?int $width = null, public ?int $height = null, public ?int $duration = null, public string $url = "") {}
}
