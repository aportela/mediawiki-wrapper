# mediawiki-wrapper

Custom mediawiki api wrapper

## Requirements

- mininum php version 8.x
- curl extension must be enabled (aportela/httprequest-wrapper)

## Limitations

At this time only Wikipedia & Wikidata english pages are supported, also files

## Install (composer) dependencies:

```
composer require aportela/mediawiki-wrapper
```

## Code example:

```
<?php

    require "vendor/autoload.php";

    $logger = new \Psr\Log\NullLogger("");

    // get wikipedia title page from wikidata item
    $i = new \aportela\MediaWikiWrapper\Wikidata\Item($logger, \aportela\MediaWikiWrapper\APIType::REST);
    $i->setItem("Q319");
    $title = $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);

    // get wikipedia title page from wikidata url
    $i = new \aportela\MediaWikiWrapper\Wikidata\Item($logger, \aportela\MediaWikiWrapper\APIType::REST);
    $i->setURL("https://www.wikidata.org/wiki/Q319");
    $title = $i->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);

    // get wikipedia html page from wikipedia title
    $p = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\APIType::REST);
    $p->setTitle($title);
    $html = $p->getHTML();

    // get wikipedia html page from wikipedia url
    $p = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\APIType::REST);
    $p->setURL("https://en.wikipedia.org/wiki/Jupiter");
    $html = $p->getHTML();

    // get file URL from title
    $f = new \aportela\MediaWikiWrapper\Wikipedia\File($logger, \aportela\MediaWikiWrapper\APIType::REST);
    $f->setTitle("Commons-logo.svg");
    if ($f->get()) {
        $preferredURL = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::PREFERRED);
        $originalURL = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
        $thumbnailURL = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::THUMBNAIL);
    }
```

![PHP Composer](https://github.com/aportela/mediawiki-wrapper/actions/workflows/php.yml/badge.svg)
