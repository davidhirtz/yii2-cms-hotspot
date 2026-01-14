<?php

declare(strict_types=1);

$basePath = (getenv('BASE_PATH') ?: getcwd());
$config = require("$basePath/vendor/davidhirtz/yii2-skeleton/config/test.php");

return [
    ...$config,
//    'bootstrap' => [
//        Bootstrap::class,
//    ],
];
