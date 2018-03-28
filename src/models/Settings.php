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
     * @var boolean
     */
    public $cleanupHtml = true;

    /**
     * @var boolean
     */
    public $purifyHtml = true;

    /**
     * @var array
     */
    public $purifierConfig = 'default';

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