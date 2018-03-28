<?php

namespace froala\craftfroalawysiwyg\controllers;

use Craft;
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

        // when tab not exists, redirect to general
        $possiblePages = ['general', 'plugins', 'customcss'];
        if (!in_array($routeParams['settingsType'], $possiblePages)) {

            return $this->redirect('froala-editor/settings/general');
        }

        /**
         * @var FroalaAsset $froalaAsset
         */
        $froalaAsset = Craft::$app->getView()->getAssetManager()->getBundle(FroalaAsset::class);

        return $this->renderTemplate('froala-editor/settings/' . $routeParams['settingsType'], [
            'settings'    => Plugin::getInstance()->getSettings(),
            'plugins'     => $froalaAsset->getPlugins(),
        ]);
    }
}