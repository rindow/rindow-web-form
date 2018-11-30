<?php
namespace Rindow\Web\Form;

interface FormBuilder
{
    public function build($className);
    public function setConfig(array $config=null);
}
