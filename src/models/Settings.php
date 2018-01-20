<?php

namespace froala\craftfroalawysiwyg\models;

use craft\base\Model;

/**
 * Class Settings
 */
class Settings extends Model
{
    /**
     * @var string The license key
     */
    public $licenseKey;

    /**
     * @var string
     */
    public $customCssType;

    /**
     * @var string
     */
    public $customCssFile;

    /**
     * @var array
     */
    public $customCssClasses = [];

    /**
     * @var string|array
     */
    public $enabledPlugins = '*';
}