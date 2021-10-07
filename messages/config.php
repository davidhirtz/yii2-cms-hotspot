<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yii message' command.
 */
return [
    'sourcePath' => dirname(__DIR__, 2),
    'messagePath' => __DIR__,
    'languages' => ['de', 'en-US', 'zh-CN'],
    'ignoreCategories' => ['anakin', 'cms', 'media', 'newsletter', 'shop', 'yii', 'skeleton'],
    'overwrite' => true,
    'removeUnused' => true,
    'only' => ['*.php'],
    'format' => 'php',
    'sort' => true,
    'except' => [
        '/config',
        '/messages',
        '/tests',
    ],
];

