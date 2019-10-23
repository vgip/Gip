<?php

declare(strict_types = 1);

use Vgip\Gip\Html\PageStorage;

$pageStorage = PageStorage::getInstance();

echo '<!doctype html>

<html'.$pageStorage->getHtmlLang().'>
<head>
  '.$pageStorage->getHeadCharset().'

  '.$pageStorage->getHeadTitle().'

</head>

<body>
    '.$pageStorage->getBodyContent().'
</body>

</html>';

