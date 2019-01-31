<?php

namespace froala\craftfroalawysiwyg\traits;

use Craft;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use froala\craftfroalawysiwyg\Field;
use froala\craftfroalawysiwyg\variables\FroalaVariable;
use yii\base\Event;

/**
 * Trait PluginEventsTrait
 */
trait PluginEventsTrait
{
    public function registerEvents()
    {
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
}
