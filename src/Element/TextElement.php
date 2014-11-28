<?php

namespace DeForm\Element;

class TextElement extends AbstractElement implements ElementInterface
{

    /**
     * Set the value of a form element.
     *
     * @param string $value
     * @return self
     */
    public function setValue($value)
    {
        $this->node->setAttribute('value', $value);

        return $this;
    }

    /**
     * Get the value of a form element.
     *
     * @return string|int
     */
    public function getValue()
    {
        return $this->node->getAttribute('value');
    }

}
