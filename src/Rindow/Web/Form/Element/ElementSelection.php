<?php
namespace Rindow\Web\Form\Element;

use Rindow\Web\Form\ElementSelection as ElementSelectionInterface;
use Rindow\Web\Form\AbstractElementArray;

class ElementSelection extends AbstractElementArray implements ElementSelectionInterface
{
	public $name;
	public $type;
	public $value;
	public $label;
	public $attributes;
	public $errors;
	public $fieldName;
	public $bindTo;
	public $multiple;
    public $options;
}