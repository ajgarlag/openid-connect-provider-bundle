<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

DG\BypassFinals::allowPaths([
    '*/oauth2-server-bundle/tests/*',
]);

DG\BypassFinals::enable(bypassReadOnly: false);
