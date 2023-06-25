<?php

namespace froala\craftfroalaeditor;

use Craft;
use craft\helpers\FileHelper;
use froala\craftfroalawysiwyg\traits\PluginComponentsTrait;
use froala\craftfroalawysiwyg\traits\PluginEventsTrait;

/**
 * Class Plugin
 */
class Plugin extends \craft\base\Plugin
{
    use PluginComponentsTrait;
    use PluginEventsTrait;

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

        $this->registerComponents();
        $this->registerEvents();
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
