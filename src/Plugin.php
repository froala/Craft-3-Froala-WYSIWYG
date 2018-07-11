<?php

namespace froala\craftfroalawysiwyg;

use Craft;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\FileHelper;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use froala\craftfroalawysiwyg\variables\FroalaVariable;
use yii\base\Event;

use froala\craftfroalawysiwyg\services\FieldService;

/**
 * Class Plugin
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var string
     */
    public $changelogUrl = 'https://raw.githubusercontent.com/froala/Craft-3-Froala-WYSIWYG/master/CHANGELOG.md';

    /**
     * @var string
     */
    public $downloadUrl = 'https://github.com/froala/Craft-3-Froala-WYSIWYG/archive/master.zip';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'fieldService' => FieldService::class,
        ]);

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $e) {
                $e->types[] = Field::class;
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['froala-editor/settings'] = 'froala-editor/settings/show';
                $event->rules['froala-editor/settings/<settingsType:{handle}>'] = 'froala-editor/settings/show';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $event->sender->set('froala', FroalaVariable::class);
            }
        );

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[Craft::t('froala-editor', 'Froala WYSIWYG Editor')] = [
                    'froala-allowCodeView' => ['label' => Craft::t('froala-editor', 'Enable HTML Code view button')],
                ];
            }
        );
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

    /**
     * @param string     $settingsKey
     * @param string     $subDir
     * @param array|null $settings
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getCustomConfig($settingsKey, $subDir, $settings = null)
    {
        if (!empty($settings) && is_array($settings) && isset($settings[$settingsKey])) {
            $file = $settings[$settingsKey];
        } else {
            $file = $this->getSettings()->$settingsKey;
        }

        $path = implode(DIRECTORY_SEPARATOR, [
            \Craft::$app->getPath()->getConfigPath(),
            $subDir,
            $file,
        ]);

        if (!$file || !file_exists($path)) {

            if ($settingsKey === 'purifierConfig') {
                return [
                    'Attr.AllowedFrameTargets' => ['_blank'],
                ];
            }

            return [];
        }

        $json = file_get_contents($path);

        return json_decode($json, true);
    }

    /**
     * @param string $dir
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getCustomConfigOptions($dir)
    {
        $options = ['' => Craft::t('froala-editor', 'Default')];
        $path = implode(DIRECTORY_SEPARATOR, [
            \Craft::$app->getPath()->getConfigPath(),
            $dir,
        ]);

        if (is_dir($path)) {

            $files = FileHelper::findFiles($path, [
                'only'      => ['*.json'],
                'recursive' => false,
            ]);

            foreach ($files as $file) {
                $options[pathinfo($file, PATHINFO_BASENAME)] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $options;
    }
}