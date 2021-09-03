<?php

namespace davidhirtz\yii2\annotation;

use davidhirtz\yii2\skeleton\modules\ModuleTrait;

/**
 * Class Module
 * @package davidhirtz\yii2\annotation
 */
class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var bool whether annotations should have assets
     */
    public $enableAnnotationAssets = true;

    /**
     * @var int the default annotation type which is applied to all default admin urls
     */
    public $defaultAnnotationType;
}