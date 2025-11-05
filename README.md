# mediawiki-wrapper

Custom mediawiki api wrapper

## Requirements

- mininum php version 8.4
- curl extension must be enabled (aportela/httprequest-wrapper)

## Limitations

At this time only Wikipedia & Wikidata english pages are supported, also files

## Install (composer) dependencies:

```Shell
composer require aportela/mediawiki-wrapper
```

## Code example:

```php
<?php

    require "vendor/autoload.php";

    $logger = new \Psr\Log\NullLogger("");

    $cache = null;
    // uncomment the following lines for storing into disk cache the lyrics
    //$cachePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache";
    //$cache = new \aportela\SimpleFSCache\Cache($logger, \aportela\SimpleFSCache\CacheFormat::NONE, $cachePath);

    $wikidataItem = new \aportela\MediaWikiWrapper\Wikidata\Item($logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, $cache);
    // get wikipedia title page from wikidata item
    $title = $wikidataItem->getWikipediaTitleFromIdentifier("Q319");
    // get wikipedia title page from wikidata url
    $title = $wikidataItem->getWikipediaTitleFromURL("https://www.wikidata.org/wiki/Q319");

    $wikipediaPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, $cache);
    // get wikipedia html page from wikipedia title
    $html = $wikipediaPage->getHTMLFromTitle("Jupiter");
    // get wikipedia html page from wikipedia url
    $html = $wikipediaPage->getHTMLFromURL("https://en.wikipedia.org/wiki/Jupiter");

    $wikipediaFile = new \aportela\MediaWikiWrapper\Wikipedia\File($logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, $cache);
    $wikipediaFile->get("Commons-logo.svg");
    // get preferred/original/thumbnail file URL
    $preferredURL = $wikipediaFile->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED);
    $originalURL = $wikipediaFile->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
    $thumbnailURL = $wikipediaFile->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL);

```

![PHP Composer](https://github.com/aportela/mediawiki-wrapper/actions/workflows/php.yml/badge.svg)
