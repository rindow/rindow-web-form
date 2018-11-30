<?php
namespace Rindow\Web\Form\Element;

use Rindow\Stdlib\Entity\AbstractPropertyAccess;
use Rindow\Web\Form\Element as ElementInterface;

class Element extends AbstractPropertyAccess implements ElementInterface
{
    public $name;
    public $type;
    public $value;
    public $label;
    public $attributes;
    public $errors;
    public $fieldName;
    public $bindTo;
}