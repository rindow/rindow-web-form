<?php
namespace Rindow\Web\Form\Builder;

use Rindow\Web\Form\Exception;
use Rindow\Stdlib\FileUtil\FileLocator;

class YamlBuilder extends ArrayBuilder
{
    const DEFAULT_POSTFIX = '.form.yml';

    protected $yaml;
    protected $fileLocator;

    public function setYaml($yaml)
    {
        $this->yaml = $yaml;
    }

    public function getYaml()
    {
        return $this->yaml;
    }

    public function setFileLocator($fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    protected function getFileLocator()
    {
        if($this->fileLocator)
            return $this->fileLocator;
        $config = $this->getConfig();
        if(!isset($config['paths']))
            return null;
        if(isset($config['postfix']))
            $postfix = $config['postfix'];
        else
            $postfix = self::DEFAULT_POSTFIX;
        $this->fileLocator = new FileLocator($config['paths'],$postfix);
        return $this->fileLocator;
    }

    protected function loadDefinition($className)
    {
        $config = $this->getConfig();
        $fileLocator = $this->getFileLocator();
        if($fileLocator==null)
            return false;
        $yaml = $this->getYaml();
        $fullPath = $fileLocator->findMappingFile($className);
        if(!$fullPath)
            return false;
        try {
            $definition = $yaml->fileToArray($fullPath);
        } catch(\Exception $e) {
            throw new Exception\DomainException('Yaml load error to build a form for "'.$className.'" in form definition "'.$fullPath.'".',0,$e);
        }
        if(!is_array($definition) || !isset($definition[$className]))
            throw new Exception\DomainException('Yaml load error or a class not found to build a form for "'.$className.'" in form definition "'.$fullPath.'".');
        return $definition[$className];
    }
}