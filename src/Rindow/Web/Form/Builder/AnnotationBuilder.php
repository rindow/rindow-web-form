<?php
namespace Rindow\Web\Form\Builder;

use Rindow\Web\Form\FormBuilder;
use Rindow\Web\Form\Element;
use Rindow\Web\Form\Exception;
use Rindow\Web\Form\Annotation\Form;

/*use Rindow\Annotation\AnnotationReader;*/
use ReflectionClass;

class AnnotationBuilder implements FormBuilder
{
    protected $annotationReader;

    public function __construct(/*AnnotationReader*/ $annotationReader=null)
    {
        $this->annotationReader = $annotationReader;
    }

    public function setConfig(array $config=null)
    {
    }

    public function getConfig()
    {
    }

    public function hasAnnotationReader()
    {
        return $this->annotationReader ? true : false;
    }

    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
        return $this;
    }

    public function addNameSpace($nameSpace)
    {
        $this->annotationReader->addNameSpace($nameSpace);
    }

    public function build($className)
    {
        $reader = $this->getAnnotationReader();
        if($reader==null)
            throw new Exception\DomainException('AnnotationReader is not specified.');

        $classRef = new ReflectionClass($className);
        $annotations = $reader->getClassAnnotations($classRef);
        $form = null;
        foreach ($annotations as $annotation) {
            if($annotation instanceof Form) {
                $form = clone $annotation;
                break;
            }
        }
        if($form==null)
        	return false;

        foreach($classRef->getProperties() as $ref) {
            $annotations = $reader->getPropertyAnnotations($ref);
            if(count($annotations)==0)
                continue;
            foreach ($annotations as $annotation) {
                if($annotation instanceof Element) {
                    $element = clone $annotation;
                    $name = $ref->getName();
                    $element->fieldName = $name;
                    if($element->name) {
                    	$name = $element->name;
                    }
                    if($element->bindTo) {
                        $name = $element->bindTo;
                    }
                    if($element->name===null)
                        $element->name = $name;
                    $form[$name] = $element;
                    break;
                }
            }
        }

        return $form;
    }
}
