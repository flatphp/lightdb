<?php namespace Lightdb;


class Raw
{
    /**
     * The value of the expression.
     * @var mixed
     */
    protected $value;

    /**
     * Create a new raw query expression.
     * @param  mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the expression.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value of the expression.
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }
}