<?php

namespace davidhirtz\yii2\annotation\assets;

use yii\web\AssetBundle;

/**
 * Class AnnotoriousAssetBundle
 * @package davidhirtz\yii2\annotation\assets
 */
class AnnotoriousAssetBundle extends AssetBundle
{
    /**
     * @var string[]
     */
    public $css = [
        'https://cdn.jsdelivr.net/npm/@recogito/annotorious@latest/dist/annotorious.min.css',
    ];

    /**
     * @var string[]
     */
    public $js = [
        'https://cdn.jsdelivr.net/npm/@recogito/annotorious@latest/dist/annotorious.min.js',
        'https://cdn.jsdelivr.net/npm/@recogito/annotorious-selector-pack@latest/dist/annotorious-selector-pack.min.js',
        // Version 1.0.1 is not complied at the moment (Sep 2021)
        'https://cdn.jsdelivr.net/npm/@recogito/annotorious-toolbar@0.1.1/dist/annotorious-toolbar.min.js',
    ];
}