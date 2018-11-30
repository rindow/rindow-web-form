<?php
namespace Rindow\Web\Form;

use Rindow\Stdlib\ArrayObject;
use Rindow\Web\Form\Element as ElementInterface;

abstract class AbstractElementArray extends ArrayObject
{
    public function __clone()
    {
        $newElements = array();
        foreach ($this->elements as $key => $value) {
            $newElements[$key] = clone $value;
        }
        $this->elements = $newElements;
    }

    public function offsetSet($name, $value)
    {
        if(!($value instanceof ElementInterface))
            throw new Exception\DomainException('Must be a Element class to set into the offset "'.$name.'" in '.get_class($this));
        if(!is_scalar($name))
            throw new Exception\DomainException('a offset must be a string or number.');
        $this->elements[$name] = $value;
    }

    public function __set($name,$value)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }

    public function __get($name)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }
}