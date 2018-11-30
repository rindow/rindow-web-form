<?php
namespace Rindow\Web\Form\Annotation;

use Rindow\Web\Form\Element\Element;

/**
 * @Annotation
 * @Target({ FIELD })
 */
class Input extends Element
{
	/**
	 * @Enum({
	 *   "text","password","file","hidden","submit","reset","button","image",
	 *   "search","tel","url","email","number","range","color",
	 *   "datetime","date","month","week","time","datetime-local"
	 * })
	 */
    public $type = 'text';
}