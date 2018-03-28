<?php

namespace froala\craftfroalawysiwyg\variables;

use froala\craftfroalawysiwyg\Plugin;

/**
 * Class FroalaVariable
 */
class FroalaVariable
{
    /**
     * @return string
     */
    public function name(): string
    {
        return Plugin::getInstance()->name;
    }
}