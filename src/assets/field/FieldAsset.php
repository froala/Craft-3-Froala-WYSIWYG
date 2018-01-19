<?php

namespace froala\craftfroalawysiwyg\assets\field;

use craft\web\AssetBundle;
use froala\craftfroalawysiwyg\assets\froala\FroalaAsset;

/**
 * Class FieldAsset
 */
class FieldAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';
        $this->depends = [
            FroalaAsset::class,
        ];

        $this->css = [
            'css/craftcms-theme.css',
        ];

        $this->js = [
            'js/plugins/link.js',
            'js/FroalaEditorConfig.js',
            'js/FroalaEditorInput.js',
        ];

        parent::init();
    }
}