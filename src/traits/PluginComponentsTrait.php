<?php

namespace froala\craftfroalawysiwyg\traits;

use froala\craftfroalawysiwyg\services\FieldService;

/**
 * Trait PluginComponentsTrait
 */
trait PluginComponentsTrait
{
    /**
     * Registers plugin components
     */
    public function registerComponents()
    {
        $this->setComponents([
            'fieldService' => FieldService::class,
        ]);
    }

    /**
     * @return FieldService
     */
    public function getFieldService()
    {
        return $this->get('fieldService');
    }
}
