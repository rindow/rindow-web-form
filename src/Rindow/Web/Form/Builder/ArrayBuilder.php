<?php
namespace Rindow\Web\Form\Builder;

use Rindow\Annotation\AnnotationManager;
use Rindow\Web\Form\FormBuilder;
use Rindow\Web\Form\Element\Element;
use Rindow\Web\Form\Exception;
use Rindow\Web\Form\Annotation\Form;
use Rindow\Web\Form\Annotation\Input;
use Rindow\Web\Form\Annotation\Select;

use ReflectionClass;

class ArrayBuilder implements FormBuilder
{
    protected static $inputType = array(
        "text"=>true,"password"=>true,"file"=>true,"hidden"=>true,
        "submit"=>true,"reset"=>true,"button"=>true,"image"=>true,
        "search"=>true,"tel"=>true,"url"=>true,"email"=>true,
        "number"=>true,"range"=>true,"color"=>true,"datetime"=>true,
        "date"=>true,"month"=>true,"week"=>true,"time"=>true,
        "datetime-local"=>true,
    );
    protected static $selectType = array(
        "select"=>true,"radio"=>true,"checkbox"=>true,
    );

    protected $config;

    public function setConfig(array $config=null)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function build($class)
    {
        if(is_object($class))
            $className = get_class($class);
        else
            $className = $class;

        try {
            $definition = $this->loadDefinition($className);
            if(!$definition)
                return false;
    
            $form = new Form();
            $this->setDefinition($form,$definition);
        } catch(Exception\DomainException $e) {
            throw new Exception\DomainException($e->getMessage().' in "'.$className.'"',0,$e);
        }

        foreach($definition['properties'] as $name => $fieldDefinition) {
            try {
                $element = $this->newElement($fieldDefinition);
            } catch(Exception\DomainException $e) {
                throw new Exception\DomainException($e->getMessage().' in "'.$className.'::'.$name.'"',0,$e);
            }
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
        }

        return $form;
    }

    protected function setDefinition($element,$definition)
    {
        // Select Element & General Element & Select Element
        if(array_key_exists('type', $definition)) {
            $element->type = $definition['type'];
        }
        if(array_key_exists('attributes', $definition)) {
            $element->attributes = $definition['attributes'];
        }
        if($element instanceof Input || $element instanceof Select) {
            // General Element & Select Element
            if(array_key_exists('name', $definition)) {
                $element->name = $definition['name'];
            }
            if(array_key_exists('value', $definition)) {
                $element->value = $definition['value'];
            }
            if(array_key_exists('label', $definition)) {
                $element->label = $definition['label'];
            }
            if(array_key_exists('errors', $definition)) {
                $element->errors = $definition['errors'];
            }
            if(array_key_exists('bindTo', $definition)) {
                $element->bindTo = $definition['bindTo'];
            }
        }
        if($element instanceof Select) {
            // Select Element
            if(array_key_exists('multiple', $definition)) {
                $element->multiple = $definition['multiple'];
            }
            if(array_key_exists('options', $definition)) {
                $element->options = $definition['options'];
            }
            if(array_key_exists('mappedValue', $definition)) {
                $element->mappedValue = $definition['mappedValue'];
            }
            if(array_key_exists('mappedLabel', $definition)) {
                $element->mappedLabel = $definition['mappedLabel'];
            }
            if(array_key_exists('mappedOptions', $definition)) {
                $element->mappedOptions = $definition['mappedOptions'];
            }
        }
    }

    protected function newElement($definition)
    {
        if(!array_key_exists('type', $definition))
            throw new Exception\DomainException('type is unspecified');
        $type = $definition['type'];
        if(isset(self::$inputType[$type])) {
            $element = new Input();
        } else if(isset(self::$selectType[$type])) {
            $element = new Select();
            if(isset($definition['options'])) {
                if(is_array($definition['options']))
                    $options = $definition['options'];
                else
                    $options = array($definition['options']);
                if(isset($options[0]))
                    $asIs = true;
                else
                    $asIs = false;
                foreach($options as $value => $label) {
                    $optElement = new Element();
                    $optElement->label = $label;
                    if($asIs) {
                        $optElement->value = $label;
                        $element[$label] = $optElement;
                    } else {
                        $optElement->value = $value;
                        $element[$value] = $optElement;
                    }
                }
            }
        } else {
            throw new Exception\DomainException('Unkown form element type "'.$type.'"');
        }
        $this->setDefinition($element,$definition);
        return $element;
    }

    protected function loadDefinition($className)
    {
        $config = $this->getConfig();
        if(!isset($config['mapping'][$className]))
            return false;
        return $config['mapping'][$className];
    }
}
