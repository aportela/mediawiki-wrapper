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

    public function __construct(\aportela\MediaWikiWrapper\FileInformationType $fileInformationType, string $mediaType, ?int $size, ?int $width, ?int $height, ?int $duration, string $url)
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
