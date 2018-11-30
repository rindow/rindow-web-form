<?php
namespace Rindow\Web\Form\Annotation;

use Rindow\Web\Form\Element\ElementCollection;

/**
 * @Annotation
 * @Target({ TYPE })
 */
class Form extends ElementCollection
{
	/**
	 * @Enum({"form"})
	 */
    public $type = 'form';
    
    public $hasErrors;
}