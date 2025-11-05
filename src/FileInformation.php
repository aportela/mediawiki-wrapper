<?php

namespace aportela\MediaWikiWrapper;

final class FileInformation
{
    public \aportela\MediaWikiWrapper\FileInformationType $fileInformationType;
    public string $mediaType;
    public ?int $size;
    public ?int $width;
    public ?int $height;
    public ?int $duration;
    public string $url;

    // TODO: change URL to non null
    public function __construct(\aportela\MediaWikiWrapper\FileInformationType $fileInformationType, string $mediaType, ?int $size = null, ?int $width = null, ?int $height = null, ?int $duration = null, string $url = "")
    {
        $this->fileInformationType = $fileInformationType;
        $this->mediaType = $mediaType;
        $this->size = $size;
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
        $this->url = $url;
    }
}
