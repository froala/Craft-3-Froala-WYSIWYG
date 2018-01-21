<?php

namespace froala\craftfroalawysiwyg;

use Craft;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

use froala\craftfroalawysiwyg\services\FieldVolume;

/**
 * Class Plugin
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @var Plugin
     */
    public static $plugin;

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var string
     */
    public $changelogUrl = 'https://raw.githubusercontent.com/bertoost/Craft-3-Froala-WYSIWYG/master/CHANGELOG.md';

    /**
     * @var string
     */
    public $downloadUrl = 'https://github.com/bertoost/Craft-3-Froala-WYSIWYG/archive/master.zip';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'fieldVolume' => FieldVolume::class,
        ]);

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $e) {
            $e->types[] = Field::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['froala-editor/settings'] = 'froala-editor/settings/show';
            $event->rules['froala-editor/settings/<settingsType:{handle}>'] = 'froala-editor/settings/show';
        });
    }

    /**
     * @return \froala\craftfroalawysiwyg\models\Settings
     */
    protected function createSettingsModel()
    {
        return new \froala\craftfroalawysiwyg\models\Settings();
    }

    /**
     * @return mixed|\yii\web\Response
     */
    public function getSettingsResponse()
    {
        $url = \craft\helpers\UrlHelper::cpUrl('froala-editor/settings/general');

        return Craft::$app->controller->redirect($url);
    }
}