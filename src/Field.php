<?php

namespace froala\craftfroalawysiwyg;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\helpers\HtmlPurifier;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use yii\db\Schema;

use froala\craftfroalawysiwyg\assets\froala\FroalaAsset;
use froala\craftfroalawysiwyg\assets\field\FieldAsset;

/**
 * Class Field
 */
class Field extends \craft\base\Field
{
    /**
     * @var string The image source to use with current field
     */
    public $assetsImagesSource = '';

    /**
     * @var string A configurable sub path within the image source selected
     */
    public $assetsImagesSubPath = '';

    /**
     * @var string The file source to use with current field
     */
    public $assetsFilesSource = '';

    /**
     * @var string A configurable sub path within the file source selected
     */
    public $assetsFilesSubPath = '';

    /**
     * @var string The name of the custom editor configuration for this field
     */
    public $editorConfig = '';

    /**
     * @var \craft\base\Model
     */
    private $pluginSettings;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->pluginSettings = Plugin::getInstance()->getSettings();
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
        return Craft::$app->getView()->renderTemplate('froala-editor/field/settings', [
            'field'               => $this,
            'pluginSettings'      => $this->pluginSettings,
            'editorConfigOptions' => Plugin::getInstance()->getCustomConfigOptions('froalaeditor'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $id = $view->formatInputId($this->handle);
        $nsId = $view->namespaceInputId($id);

        Plugin::getInstance()->fieldService->setElement($element);
        $pluginSettings = $this->pluginSettings->toArray();

        // start input editor settings
        $site = ($element ? $element->getSite() : Craft::$app->getSites()->currentSite);
        $settings = [
            'id'             => $nsId,
            'isAdmin'        => Craft::$app->user->getIsAdmin(),
            'editorConfig'   => array_merge(
                [
                    'craftElementSiteId'         => $site->id,
                    'craftLinkElementType'       => Entry::class,
                    'craftLinkElementRefHandle'  => Entry::refHandle(),
                    'craftAssetElementType'      => Asset::class,
                    'craftAssetElementRefHandle' => Asset::refHandle(),
                    'craftImageTransforms'       => Plugin::getInstance()->fieldService->getTransforms(),
                    'craftImageSources'          => [
                        Plugin::getInstance()->fieldService->determineFolderId(
                            $this->assetsImagesSource,
                            $this->assetsImagesSubPath
                        ),
                    ],
                    'craftFileSources'           => [
                        Plugin::getInstance()->fieldService->determineFolderId(
                            $this->assetsFilesSource,
                            $this->assetsFilesSubPath
                        ),
                    ],
                    "language" => FroalaAsset::getLanguage(),
                ],
                Plugin::getInstance()->getCustomConfig('editorConfig', 'froalaeditor', $pluginSettings),
                Plugin::getInstance()->getCustomConfig('editorConfig', 'froalaeditor', $this->getSettings())
            ),
            'pluginSettings' => $pluginSettings,
            'fieldSettings'  => $this->getSettings(),
            'corePlugins'    => array_keys(FroalaAsset::CORE_PLUGINS),
        ];

        $view->registerAssetBundle(FieldAsset::class);
        $view->registerJs('new Craft.FroalaEditorInput(' . Json::encode($settings) . ');');

        if ($value instanceof FieldData) {
            $value = $value->getRawContent();
        }

        if ($value !== null) {
            // Parse reference tags
            $value = $this->_parseRefs($value, $element);
        }

        return Craft::$app->getView()->renderTemplate('froala-editor/field/input', [
            'id'     => $id,
            'handle' => $this->handle,
            'value'  => htmlentities((string)$value, ENT_NOQUOTES, 'UTF-8'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof FieldData) {
            return $value;
        }

        if (!$value) {
            return null;
        }

        // Prevent everyone from having to use the |raw filter when outputting RTE content
        return new FieldData($value);
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
        $value = $value->getRawContent();

        if ($this->pluginSettings->purifyHtml) {
            // Parse reference tags so HTMLPurifier doesn't encode the curly braces
            $value = $this->_parseRefs($value, $element);

            $value = HtmlPurifier::process($value, $this->getPurifierConfig());
        }

        if ($this->pluginSettings->cleanupHtml) {

            // Remove <span> and <font> tags
            $value = preg_replace('/<(?:span|font)\b[^>]*>/', '', $value);
            $value = preg_replace('/<\/(?:span|font)>/', '', $value);

            // Remove inline styles
            $value = preg_replace('/(<(?:h1|h2|h3|h4|h5|h6|p|div|blockquote|pre|strong|em|b|i|u|a)\b[^>]*)\s+style="[^"]*"/', '$1', $value);

            // Remove empty tags
            $value = preg_replace('/<(h1|h2|h3|h4|h5|h6|p|div|blockquote|pre|strong|em|a|b|i|u)\s*><\/\1>/', '', $value);
        }

        // Find any element URLs and swap them with ref tags
        $pattern = '/(href=|src=)([\'"])[^\'"#]+?(#[^\'"#]+)?(?:#|%23)([\w\\\\]+)\:(\d+)(\:(?:transform\:)?' . HandleValidator::$handlePattern . ')?\2/';
        $value = preg_replace_callback($pattern, function ($matches) {
            // Create the ref tag, and make sure :url is in there
            $refTag = '{' . $matches[4] . ':' . $matches[5] . (!empty($matches[6]) ? $matches[6] : ':url') . '}';
            $hash = (!empty($matches[3]) ? $matches[3] : '');
            if ($hash) {
                // Make sure that the hash isn't actually part of the parsed URL
                // (someone's Entry URL Format could be "#{slug}", etc.)
                $url = Craft::$app->getElements()->parseRefs($refTag);
                if (mb_strpos($url, $hash) !== false) {
                    $hash = '';
                }
            }

            return $matches[1] . $matches[2] . $refTag . $hash . $matches[2];
        },
            $value
        );

        if (Craft::$app->getDb()->getIsMysql()) {
            // Encode any 4-byte UTF-8 characters.
            $value = StringHelper::encodeMb4($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isValueEmpty($value, ElementInterface $element): bool
    {
        if ($value === null) {
            return true;
        }

        /** @var FieldData $value */
        return parent::isValueEmpty($value->getRawContent(), $element);
    }

    /**
     * Parse ref tags in URLs, while preserving the original tag values in the URL fragments
     * (e.g. `href="{entry:id:url}"` => `href="[entry-url]#entry:id:url"`)
     *
     * @param string                $value
     * @param ElementInterface|null $element
     *
     * @return string
     */
    private function _parseRefs(string $value, ElementInterface $element = null): string
    {
        if (!StringHelper::contains($value, '{')) {
            return $value;
        }

        $pattern = '/(href=|src=)([\'"])(\{([\w\\\\]+\:\d+\:(?:transform\:)?' . HandleValidator::$handlePattern . ')\})(#[^\'"#]+)?\2/';

        return preg_replace_callback($pattern, function ($matches) use ($element) {
            /** @var \craft\base\Element|null $element */
            list (, $attr, $q, $refTag, $ref) = $matches;
            $fragment = $matches[5] ?? '';

            return $attr . $q . Craft::$app->getElements()->parseRefs($refTag, $element->siteId ?? null) . $fragment . '#' . $ref . $q;
        }, $value);
    }

    /**
     * Returns the HTML Purifier config used by this field.
     *
     * @return array
     * @throws \yii\base\Exception
     */
    private function getPurifierConfig(): array
    {
        if ($config = $this->getConfig('htmlpurifier', $this->pluginSettings->purifierConfig)) {
            return $config;
        }

        // Default config
        return [
            'Attr.AllowedFrameTargets' => ['_blank'],
        ];
    }

    /**
     * Returns a JSON-decoded config, if it exists.
     *
     * @param string      $dir
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