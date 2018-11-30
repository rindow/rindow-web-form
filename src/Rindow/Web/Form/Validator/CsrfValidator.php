<?php
namespace Rindow\Web\Form\Validator;

use Rindow\Web\Form\Element\Element;

class CsrfValidator
{
    const NAME_CSRFTOKEN = 'csrf_token';

    protected $passPhrase;
    protected $session;
    protected $timeout = 3600;

    public function __construct($passPhrase,$session,$timeout=null)
    {
        $this->passPhrase = $passPhrase;
        $this->session = $session;
        if($timeout)
            $this->timeout = $timeout;
    }

    public function generateToken($time=null)
    {
        $sessionId = $this->session->getId();
        if($time==null)
            $time = time();
        $token = sha1($this->passPhrase.$sessionId.$time).':'.$time;
        return $token;
    }

    public function getTokenTimestamp($token)
    {
        $parts = explode(':', $token);
        if(isset($parts[1]))
            return $parts[1];
        else
            return 0;
    }

    public function addForm($form)
    {
        $element = new Element();
        $element->name = self::NAME_CSRFTOKEN;
        $element->type = 'hidden';
        $element->value = $this->generateToken();
        $element->fieldName = self::NAME_CSRFTOKEN;
        $form[self::NAME_CSRFTOKEN] = $element;
    }

    public function isValid($binding,$data,$hydrator,&$errors)
    {
        $receivedToken = isset($data[self::NAME_CSRFTOKEN]) ? $data[self::NAME_CSRFTOKEN] : null;
        $tokenTimestamp = $this->getTokenTimestamp($receivedToken);
        if($this->generateToken($tokenTimestamp)!==$receivedToken) {
            $errors[self::NAME_CSRFTOKEN][] = 'Illegal form access.';
            return false;
        }
        if(time() - $tokenTimestamp > $this->timeout) {
            $errors[self::NAME_CSRFTOKEN][] = 'Session timeout.';
            return false;
        }
        return true;
    }
}