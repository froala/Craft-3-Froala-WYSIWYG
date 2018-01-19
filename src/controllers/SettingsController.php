<?php

namespace froala\craftfroalawysiwyg\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use froala\craftfroalawysiwyg\Plugin;
use froala\craftfroalawysiwyg\assets\froala\FroalaAsset;

/**
 * Class SettingsController
 */
class SettingsController extends Controller
{
    /**
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionShow()
    {
        $routeParams = Craft::$app->getUrlManager()->getRouteParams();
        if (!isset($routeParams['settingsType'])) {

            return $this->redirect('froala-editor/settings/general');
        }

        $tabs = [
            'general'   => [
                'label' => Craft::t('app', 'General'),
                'url'   => UrlHelper::cpUrl('froala-editor/settings/general'),
            ],
            'plugins'   => [
                'label' => Craft::t('app', 'Plugins'),
                'url'   => UrlHelper::cpUrl('froala-editor/settings/plugins'),
            ],
            'customcss' => [
                'label' => Craft::t('app', 'Custom CSS'),
                'url'   => UrlHelper::cpUrl('froala-editor/settings/customcss'),
            ],
        ];

        // when tab not exists, redirect to general
        if (!array_key_exists($routeParams['settingsType'], $tabs)) {

            return $this->redirect('froala-editor/settings/general');
        }

        /**
         * @var FroalaAsset $froalaAsset
         */
        $froalaAsset = Craft::$app->getView()->getAssetManager()->getBundle(FroalaAsset::class);

        return $this->renderTemplate('froala-editor/settings/' . $routeParams['settingsType'], [
            'tabs'        => $tabs,
            'selectedTab' => $routeParams['settingsType'],
            'settings'    => Plugin::$plugin->getSettings(),
            'plugins'     => $froalaAsset->getPlugins(),
        ]);
    }
}