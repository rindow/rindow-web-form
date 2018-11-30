<?php
namespace Rindow\Web\Form\Element;

use Rindow\Web\Form\ElementCollection as ElementCollectionInterface;
use Rindow\Web\Form\AbstractElementArray;

class ElementCollection extends AbstractElementArray implements ElementCollectionInterface
{
	public $type;
	public $attributes;
}