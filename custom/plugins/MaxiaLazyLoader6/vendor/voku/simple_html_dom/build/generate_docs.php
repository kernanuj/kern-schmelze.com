<?php

namespace _PhpScoperfd240ab1f7e6;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';
$readmeText = (new \_PhpScoperfd240ab1f7e6\voku\PhpReadmeHelper\GenerateApi())->generate(__DIR__ . '/../src/', __DIR__ . '/docs/api.md', [\_PhpScoperfd240ab1f7e6\voku\helper\DomParserInterface::class, \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomNodeInterface::class, \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomInterface::class]);
\file_put_contents(__DIR__ . '/../README_API.md', $readmeText);
