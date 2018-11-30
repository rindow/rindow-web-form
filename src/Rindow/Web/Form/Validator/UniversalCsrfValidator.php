<?php
namespace Rindow\Web\Form\Validator;

use Rindow\Web\Form\Element\Element;
use Interop\Lenient\Security\Web\Exception\CsrfException;

class UniversalCsrfValidator
{
    const NAME_CSRFTOKEN = 'csrf_token';

    protected $csrfToken;

    public function __construct($csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    public function addForm($form)
    {
        $element = new Element();
        $element->name = self::NAME_CSRFTOKEN;
        $element->type = 'hidden';
        $element->value = $this->csrfToken->generateToken();
        $element->fieldName = self::NAME_CSRFTOKEN;
        $form[self::NAME_CSRFTOKEN] = $element;
    }

    public function isValid($binding,$data,$hydrator,&$errors)
    {
        $receivedToken = isset($data[self::NAME_CSRFTOKEN]) ? $data[self::NAME_CSRFTOKEN] : null;
        try {
            if($this->csrfToken->isValid($receivedToken))
                return true;
            $message = 'Illegal form access.';
        } catch(CsrfException $e) {
            $message = $e->getMessage();
        }
        $errors[self::NAME_CSRFTOKEN][] = $message;
        return false;
    }
}
