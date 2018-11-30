<?php
namespace Rindow\Web\Form;

use Rindow\Stdlib\Entity\Hydrator;
use Rindow\Stdlib\Entity\EntityHydrator;
use Rindow\Stdlib\Entity\PropertyHydrator;
use Rindow\Stdlib\Entity\SetterHydrator;
use Rindow\Stdlib\Entity\Entity;
use Rindow\Stdlib\Entity\PropertyAccessPolicy;
use Rindow\Stdlib\ListCollection;
use Rindow\Web\Form\Annotation\Select;
use Rindow\Web\Form\Annotation\Input;
use Rindow\Web\Form\Validator\CsrfValidator;
use Traversable;
use IteratorAggregate;
use Interop\Lenient\Web\Form\FormContext as FormContextInterface;

class FormContext implements FormContextInterface
{
    protected $form;
    protected $bindingEntity;
    protected $validators = array();
    protected $hydrator;
    protected $data;
    protected $violation;
    protected $validated;
    protected $formatted;

    public function __construct(
        ElementCollection $form,
        $bindingEntity=null,
        $hydrator=null)
    {
    	$this->form           = $form;
    	$this->bindingEntity  = $bindingEntity;
        $this->hydrator       = $hydrator;
    }

    public function addValidator($validator)
    {
        $this->validators[] = $validator;
    }

    public function getForm()
    {
        if(!$this->formatted) {
            $this->formatElements();
            $this->formatted = true;
        }
        return $this->form;
    }

    public function getEntity()
    {
        return $this->bindingEntity;
    }

    public function bind($bindingEntity)
    {
        $this->bindingEntity = $bindingEntity;
        return $this;
    }

    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function getHydrator()
    {
        if($this->hydrator)
            return $this->hydrator;
        if(is_object($this->bindingEntity)) {
            if($this->bindingEntity instanceof Entity)
                $this->hydrator = new EntityHydrator();
            else if($this->bindingEntity instanceof PropertyAccessPolicy)
                $this->hydrator = new PropertyHydrator();
            else
                $this->hydrator = new SetterHydrator();
            return $this->hydrator;
        }
        throw new Exception\DomainException('Binding Entity or Hydrator type is not spacified.');
    }

    public function setRequestToData(array $data)
    {
        $this->data = array();
        foreach($this->form as $key => $element) {
            if(array_key_exists($key, $data)) {
                $this->data[$element->fieldName] = $data[$key];
            }
        }
        return $this;
    }

    protected function setDataOptions(
        array $data,
        $object,
        $key,
        Element $element,
        Hydrator $hydrator=null)
    {
        if(is_array($data[$key]) ||
            $data[$key] instanceof Traversable ||
            $data[$key] instanceof IteratorAggregate) {
            foreach ($data[$key] as $value) {
                if($hydrator)
                    $this->data[$key][] = $element->value[] = $hydrator->get($value,$element->mappedValue);
                else
                    $this->data[$key][] = $element->value[] = $value[$element->mappedValue];
            }
        } else {
            if($hydrator)
                $this->data[$key] = $element->value = $hydrator->get($data[$key],$element->mappedValue);
            else
                $this->data[$key] = $element->value = $data[$key][$element->mappedValue];
        }
        if($element->mappedOptions) {
            if($hydrator)
                $mappedOptions = $hydrator->get($object,$element->mappedOptions);
            else
                $mappedOptions = $data[$element->mappedOptions];
            if($mappedOptions) {
                foreach ($mappedOptions as $mappedOption) {
                    $option = new Input();
                    if($hydrator) {
                        $option->value = $hydrator->get($mappedOption,$element->mappedValue);
                        $option->label = $hydrator->get($mappedOption,$element->mappedOptionLabel);
                    } else {
                        $option->value = $mappedOption[$element->mappedValue];
                        $option->value = $mappedOption[$element->mappedOptionLabel];
                    }
                    $element[$option->value] = $option;
                }
            }
        }
    }

    public function formatElements()
    {
        $hydrator = $this->getHydrator();
        foreach($this->form as $name => $element) {
            if($name == CsrfValidator::NAME_CSRFTOKEN)
                continue;

            if($element->bindTo)
                $sourceField = $element->bindTo;
            else
                $sourceField = $element->fieldName;
            if($this->data && array_key_exists($element->fieldName,$this->data)) {
                $element->value = $this->data[$element->fieldName];
            } else {
                $element->value = $hydrator->get($this->bindingEntity,$sourceField);
            }
            if($element instanceof Select && $element->mappedValue) {
                $this->setupElementOptions($element,$hydrator);
            }
        }
        return $this;
    }

    protected function setupElementOptions(
        Element $element,
        Hydrator $hydrator)
    {
        if(empty($this->data) || !array_key_exists($element->fieldName,$this->data)) {
            $valueEntity = $element->value;
            if(is_array($valueEntity) ||
                $valueEntity instanceof Traversable ||
                $valueEntity instanceof IteratorAggregate) {
                $valueArray = $valueEntity;
                $element->value = array();
                foreach($valueArray as $valueEntity) {
                    if(is_object($valueEntity))
                        $element->value[] = $hydrator->get($valueEntity,$element->mappedValue);
                    else
                        $element->value[] = $valueEntity;
                }
            } else if(is_object($valueEntity)) {
                $element->value = $hydrator->get($valueEntity,$element->mappedValue);
            } else {
                $element->value = $valueEntity;
            }
        }

        if($element->mappedOptions) {
            $mappedOptions = $hydrator->get($this->bindingEntity,$element->mappedOptions);
            if($mappedOptions) {
                if($element->mappedLabel)
                    $labelField = $element->mappedLabel;
                else
                    $labelField = 'name';
                foreach ($mappedOptions as $mappedOption) {
                    $option = new Input();
                    $option->value = $hydrator->get($mappedOption,$element->mappedValue);
                    $option->label = $hydrator->get($mappedOption,$element->mappedLabel);
                    $element[$option->value] = $option;
                }
            }
        }
    }

    public function setAttributes($attributes)
    {
        $this->form->attributes = $attributes;
        return $this;
    }

    public function setAttribute($name,$value)
    {
        $this->form->attributes[$name] = $value;
        return $this;
    }

    public function setErrors($violation)
    {
        foreach($this->form as $key => $element) {
            if(isset($violation[$key]))
                $element->errors = $violation[$key];//array($violation[$key][0]->getMessage());
        }
        return $this;
    }

    public function isValid()
    {
        if($this->validated!==null)
            return $this->validated;

        if($this->data===null) {
            throw new Exception\DomainException('there is no data to validate.');
        }
        $errors = array();
        $isValid = true;
        foreach ($this->validators as $validator) {
            if(!$validator->isValid(
                                $this->bindingEntity,
                                $this->data,
                                $this->getHydrator(),
                                $errors)) {
                $isValid = false;
            }
        }
        $this->setErrors($errors);
        $this->violation = $errors;
        $this->validated = $isValid;
        return $this->validated;
    }

    public function hasErrors()
    {
        return !$this->isValid();
    }

    public function getViolation()
    {
        return $this->violation;
    }
}