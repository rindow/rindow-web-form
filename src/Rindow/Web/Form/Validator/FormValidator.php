<?php
namespace Rindow\Web\Form\Validator;

class FormValidator
{
    protected $validator;
    public function __construct($validator = null)
    {
        $this->validator = $validator;
    }

    public function isValid($bindingEntity,$data,$hydrator,&$errors)
    {
        if($bindingEntity==null) {
            throw new Exception\DomainException('there is no data type to validate.');
        }
        if($this->validator==null) {
            throw new Exception\DomainException('there is no validator.');
        }
        if($hydrator==null) {
            throw new Exception\DomainException('there is no hydrator.');
        }
        $hydrator->hydrate($data,$bindingEntity);
        $violationList = $this->validator->validate($bindingEntity);
        if(count($violationList)==0)
            return true;
        foreach ($violationList as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        return false;
    }
}