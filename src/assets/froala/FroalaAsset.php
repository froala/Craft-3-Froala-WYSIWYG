<?php

namespace froala\craftfroalawysiwyg\assets\froala;

use Craft;
use craft\helpers\FileHelper;
use craft\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Class FroalaAsset
 */
class FroalaAsset extends AssetBundle
{
    const CORE_PLUGINS = [
        'bold'            => 'Bold',
        'italic'          => 'Italic',
        'underline'       => 'Underline',
        'strikeTrough'    => 'Strike Through',
        'subscript'       => 'Subscript',
        'superscript'     => 'Superscript',
        'outdent'         => 'Outdent',
        'indent'          => 'Indent',
        'undo'            => 'Undo',
        'redo'            => 'Redo',
        'insertHR'        => 'Insert HR',
        'clearFormatting' => 'Clear Formatting',
        'selectAll'       => 'Select All',
    ];

    const CORE_LANGUAGES = [
        'ar'    => 'Arabic',
        'bs'    => 'Bosnian',
        'cs'    => 'Czech',
        'da'    => 'Danish',
        'de'    => 'German',
        'en_ca' => 'English Canada',
        'en_gb' => 'English United Kingdom',
        'es'    => 'Spanish',
        'et'    => 'Estionian',
        'fa'    => 'Persian',
        'fi'    => 'Finish',
        'fr'    => 'French',
        'he'    => 'Hebrew',
        'hr'    => 'Croatian',
        'hu'    => 'Hungarian',
        'id'    => 'Idonesian',
        'it'    => 'Italian',
        'ja'    => 'Japanese',
        'ko'    => 'Korean',
        'me'    => 'Montenegrin',
        'nb'    => 'Norwegian',
        'nl'    => 'Dutch',
        'pl'    => 'Polish',
        'pt_br' => 'Portuguese Brazil',
        'pt_pt' => 'Portuguese Portugal',
        'ro'    => 'Romanian',
        'ru'    => 'Russian',
        'sk'    => 'Slovak',
        'sr'    => 'Serbian',
        'sv'    => 'Swedish',
        'th'    => 'Thai',
        'tr'    => 'Turkish',
        'uk'    => 'Ukrainian',
        'vi'    => 'Vietnamese',
        'zh_cn' => 'Chinese China',
        'zh_tw' => 'Chinese Taiwan',
    ];

    /**
     * @var array
     */
    private $loadedPlugins = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $vendorPath = Craft::$app->path->getVendorPath() . DIRECTORY_SEPARATOR;
        $this->sourcePath = $vendorPath . 'froala/wysiwyg-editor';
        $this->depends = [
            JqueryAsset::class,
        ];

        $this->css = [
            '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
            'css/froala_editor.pkgd.min.css',
            'css/froala_style.min.css',
        ];

        $this->js = [
            'js/froala_editor.pkgd.min.js',
        ];

        // add-in language
        $language = self::getLanguage();
        if (array_key_exists(strtolower($language), self::CORE_LANGUAGES)) {
            $this->js = array_merge($this->js, [sprintf('js/languages/%s.js', $language)]);
        }

        parent::init();
    }

    /**
     * Returns the list with available and possible plugins
     * Based on the plugins directory of the vendor
     *
     * @param array|string $filter To filter the result against a list of plugins
     *
     * @return array
     */
    public function getAllEditorPlugins($filter = ['*'])
    {
        if (!is_array($filter)) {
            $filter = [$filter];
        }

        if (empty($this->loadedPlugins)) {

            $path = $this->sourcePath . '/js/plugins/';
            $plugins = [];

            $filter = preg_filter('/$/', '.min.js', $filter);
            $files = FileHelper::findFiles($path, [
                'only'      => $filter,
                'recursive' => false,
                'except'    => ['quick_insert.min.js'],
            ]);

            foreach ($files as $pluginFile) {

                $fileName = basename($pluginFile);
                $pluginName = str_replace('.min.js', '', $fileName);
                $pluginLabel = str_replace('_', ' ', $pluginName);
                $pluginLabel = ucwords($pluginLabel);

                $plugins[$pluginName] = $pluginLabel;
            }

            // add-in core plugins
            $plugins = array_merge($plugins, self::CORE_PLUGINS);

            ksort($plugins);

            $this->loadedPlugins = $plugins;
        }

        return $this->loadedPlugins;
    }

    /**
     * Returns the language string applicable to the Froala Editor.
     *
     * @return string
     */
    public static function getLanguage()
    {
        $craftLanguage = Craft::$app->getTargetLanguage();
        $craftLanguage = $language = strtolower(str_replace('-', '_', $craftLanguage));

        if (!array_key_exists($language, self::CORE_LANGUAGES)) {
            $language = substr($language, 0, strpos($language, '_'));
        }

        if (array_key_exists(strtolower($language), self::CORE_LANGUAGES)) {

            return $language;
        }

        return $craftLanguage;
    }
}