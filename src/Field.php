<?php

namespace froala\craftfroalawysiwyg;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\HtmlPurifier;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\Template;

use froala\craftfroalawysiwyg\assets\froala\FroalaAsset;
use yii\db\Schema;

use froala\craftfroalawysiwyg\assets\field\FieldAsset;

/**
 * Class Field
 */
class Field extends \craft\base\Field
{
    /**
     * @var \craft\base\Model
     */
    private $pluginSettings;

    /**
     * @var string|null The HTML Purifier config file to use
     */
    public $purifierConfig;

    /**
     * @var bool Whether the HTML should be cleaned up on save
     */
    public $cleanupHtml = true;

    /**
     * @var bool Whether the HTML should be purified on save
     */
    public $purifyHtml = true;

    /**
     * @var string The type of database column the field should have in the content table
     */
    public $columnType = Schema::TYPE_TEXT;

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
     * @var bool
     */
    public $customCssClassesOverride = false;

    /**
     * @var string|array
     */
    public $enabledPlugins = '*';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->pluginSettings = \froala\craftfroalawysiwyg\Plugin::getInstance()->getSettings();
    }

    /**
     * {@inheritdoc}
     */
    public static function displayName(): string
    {
        return Craft::t('froala-editor', 'Froala WYSIWYG');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        /**
         * @var FroalaAsset $froalaAsset
         */
        $froalaAsset = Craft::$app->getView()->getAssetManager()->getBundle(FroalaAsset::class);

        return Craft::$app->getView()->renderTemplate('froala-editor/field/settings', [
            'field'   => $this,
            'plugins' => $froalaAsset->getPlugins($this->pluginSettings->enabledPlugins),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return $this->columnType;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $id = $view->formatInputId($this->handle);
        $nsId = $view->namespaceInputId($id);
        $encValue = htmlentities((string) $value, ENT_NOQUOTES, 'UTF-8');

        // start input editor settings
        $site = ($element ? $element->getSite() : Craft::$app->getSites()->currentSite);
        $settings = [
            'id'             => $nsId,
            'isAdmin'        => Craft::$app->user->getIsAdmin(),
            'editorConfig'   => [
                'craftElementSiteId'        => $site->id,
                'craftLinkElementType'      => Entry::class,
                'craftLinkElementRefHandle' => Entry::refHandle(),
            ],
            'pluginSettings' => $this->pluginSettings->toArray(),
            'fieldSettings'  => $this->getSettings(),
            'corePlugins'    => array_keys(FroalaAsset::CORE_PLUGINS),
        ];

        $view->registerAssetBundle(FieldAsset::class);
        $view->registerJs('new Craft.FroalaEditorInput(' . Json::encode($settings) . ');');

        return Craft::$app->getView()->renderTemplate('froala-editor/field/input', [
            'id'     => $id,
            'handle' => $this->handle,
            'value'  => $encValue,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value === null || $value instanceof \Twig_Markup) {
            return $value;
        }

        // Prevent everyone from having to use the |raw filter when outputting RTE content
        return Template::raw($value);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        /** @var \Twig_Markup|null $value */
        if (!$value) {
            return null;
        }

        // Get the raw value
        $value = (string) $value;
        if (!$value) {
            return null;
        }

        if ($this->purifyHtml) {
            $value = HtmlPurifier::process($value, $this->getPurifierConfig());
        }

        if (Craft::$app->getDb()->getIsMysql()) {
            // Encode any 4-byte UTF-8 characters.
            $value = StringHelper::encodeMb4($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value): bool
    {
        /** @var \Twig_Markup|null $value */
        return $value === null || parent::isEmpty((string) $value);
    }

    /**
     * Returns the HTML Purifier config used by this field.
     *
     * @return array
     * @throws \yii\base\Exception
     */
    private function getPurifierConfig(): array
    {
        if ($config = $this->getConfig('htmlpurifier', $this->purifierConfig)) {
            return $config;
        }

        // Default config
        return [
            'Attr.AllowedFrameTargets' => ['_blank'],
            'HTML.AllowedComments'     => ['pagebreak'],
        ];
    }

    /**
     * Returns a JSON-decoded config, if it exists.
     *
     * @param string $dir
     * @param string|null $file
     *
     * @return array|bool
     * @throws \yii\base\Exception
     */
    private function getConfig(string $dir, string $file = null)
    {
        if (!$file) {
            return false;
        }

        $path = Craft::$app->getPath()->getConfigPath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            return false;
        }

        return Json::decode(file_get_contents($path));
    }
}