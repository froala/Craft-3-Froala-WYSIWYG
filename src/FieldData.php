<?php

namespace froala\craftfroalawysiwyg;

use Craft;

/**
 * Class FieldData
 */
class FieldData extends \Twig_Markup
{
    /**
     * @var string|null
     */
    private $rawContent;

    /**
     * Constructor
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        // Save the raw content in case we need it later
        $this->rawContent = $content;

        // Parse the ref tags
        $content = Craft::$app->getElements()->parseRefs($content);
        parent::__construct($content, Craft::$app->charset);
    }

    /**
     * Returns the raw content, with reference tags still in-tact.
     *
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    /**
     * Returns the parsed content, with reference tags returned as HTML links.
     *
     * @return string
     */
    public function getParsedContent(): string
    {
        return (string) $this;
    }
}