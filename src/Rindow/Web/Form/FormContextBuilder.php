<?php
namespace Rindow\Web\Form;

use Rindow\Stdlib\Cache\ConfigCache\ConfigCacheFactory;
/*use Rindow\Validation\Validator;*/
/*use Rindow\Annotation\AnnotationReader;*/
use Rindow\Web\Form\Builder\AnnotationBuilder;
use Rindow\Web\Form\Validator\FormValidator;
use Rindow\Web\Form\Validator\CsrfValidator;
use Rindow\Web\Form\Validator\UniversalCsrfValidator;
use Interop\Lenient\Web\Form\FormContextBuilder as FormContextBuilderInterface;

class FormContextBuilder implements FormContextBuilderInterface
{
    const ANNOTATION_BUILDER = 'Rindow\\Web\\Form\\Builder\\AnnotationBuilder';
    const ARRAY_BUILDER = 'Rindow\\Web\\Form\\Builder\\ArrayBuilder';
    const YAML_BUILDER = 'Rindow\\Web\\Form\\Builder\\YamlBuilder';

    protected $configCacheFactory;
    protected $formCache;
    protected $validator;
    protected $hydrator;
    protected $builders = array();
    protected $csrfToken;
    protected $session;
    protected $sessionComponentName;
    protected $annotationReader;
    protected $builderAliases = array(
        'annotation' => self::ANNOTATION_BUILDER,
        'array' => self::ARRAY_BUILDER,
        'yaml' => self::YAML_BUILDER
    );

    public function __construct(
        /*Validator*/ $validator=null,
        $hydrator=null,
        $serviceLocator=null,
        /*AnnotationReader*/ $annotationReader=null,
        $configCacheFactory=null)
    {
        if($configCacheFactory)
            $this->configCacheFactory = $configCacheFactory;
        else
            $this->configCacheFactory = new ConfigCacheFactory(array('enableCache'=>false));
        $this->validator = $validator;
        $this->hydrator = $hydrator;
        $this->serviceLocator =$serviceLocator;
        if($this->validator && method_exists($this->validator, 'getAnnotationReader')) {
            $this->annotationReader = $this->validator->getAnnotationReader();
        }
        if($annotationReader)
            $this->annotationReader = $annotationReader;
    }

    public function setSession($session)
    {
        $this->session = $session;
    }

    public function setSessionComponentName($sessionComponentName)
    {
        $this->sessionComponentName = $sessionComponentName;
    }

    public function setCsrfToken($csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    protected function getFormCache()
    {
        if($this->formCache)
            return $this->formCache;
        $this->formCache = $this->configCacheFactory->create(__CLASS__);
        return $this->formCache;
    }

    public function setConfig(array $config=null)
    {
        $this->config = $config;
        if(isset($config['builder_aliases']))
            $this->builderAliases = $config['builder_aliases'];
        if(isset($config['builders'])) {
            foreach ($config['builders'] as $name => $option) {
                if($option===false)
                    continue;
                if(isset($this->builderAliases[$name])) {
                    $builderName = $this->builderAliases[$name];
                } else {
                    $builderName = $name;
                }
                $builder = $this->getBuilderInstance($builderName);
                if($name=='annotation' && !$builder->hasAnnotationReader())
                    $builder->setAnnotationReader($this->annotationReader);
                $this->addBuilder($builder);
                $builder->setConfig($option);
            }
        }
    }

    protected function getBuilderInstance($serviceName)
    {
        if($this->serviceLocator) {
            return $this->serviceLocator->get($serviceName);
        }
        if(!class_exists($serviceName))
            throw new Exception\DomainException('constraint context builder"'.$name.'" is not found.');
        return new $serviceName();
    }

    public function addBuilder(FormBuilder $builder)
    {
        $this->builders[] = $builder;
    }

    public function getBuilders()
    {
        if(count($this->builders))
            return $this->builders;

        $annotationReader = null;
        if($this->annotationReader) {
            $annotationReader = $this->annotationReader;
        } elseif($this->validator && method_exists($this->validator, 'getAnnotationReader')) {
            $annotationReader = $this->validator->getAnnotationReader();
        }
        $this->addBuilder(new AnnotationBuilder($annotationReader));
        return $this->builders;
    }
/*
	public function build($className)
	{
        $binding = null;
        if(is_object($className)) {
            $binding = $className;
            $className = get_class($className);
        } else if(!is_string($className)) {
            throw new Exception\DomainException('className must be string or object');
        }
        $formCache = $this->getFormCache();
        if(isset($formCache[$className]))
            return new FormContext(
                $this->cloneForm($formCache[$className]),
                $binding,
                $this->validator,
                $this->hydrator);

        foreach($this->getBuilders() as $builder) {
            $form = $builder->build($className);
            if($form)
                break;
        }
        if(!$form)
            throw new Exception\DomainException('Form is not found for "'.$className.'"');

        $formCache[$className] = $form;
        return new FormContext(
            $this->cloneForm($form),
            $binding,
            $this->validator,
            $this->hydrator);
	}
*/
    public function build($className)
    {
        $binding = null;
        if(is_object($className)) {
            $binding = $className;
            $className = get_class($className);
        } else if(!is_string($className)) {
            throw new Exception\DomainException('className must be string or object');
        }
        $formCache = $this->getFormCache();
        $manager = $this;
        $form = $formCache->getEx(
            $className,
            function ($key,$args,&$save) {
                list($className,$manager) = $args;
                foreach($manager->getBuilders() as $builder) {
                    $form = $builder->build($className);
                    if($form) {
                        return $form;
                    }
                }
                $save = false;
                return false;
            },
            array($className,$this)
        );
        if(!$form)
            throw new Exception\DomainException('Form is not found for "'.$className.'"');

        $form = $this->cloneForm($form);
        $formContext = new FormContext(
            $form,
            $binding,
            $this->hydrator);
        if($this->validator)
            $formContext->addValidator(new FormValidator($this->validator));
        if(isset($this->config['autoCsrfToken']) && $this->config['autoCsrfToken']) {
            $csrfValidator = $this->getCsrfValidator($form);
            if($csrfValidator)
                $formContext->addValidator($csrfValidator);
        }
        return $formContext;
    }

    public function cloneForm($form)
    {
        return clone $form;
    }

    public function getCsrfValidator($form)
    {
        if(!isset($form->attributes['method']) ||
            strtoupper($form->attributes['method'])!='POST')
            return null;
        if($this->csrfToken) {
            $csrfValidator = new UniversalCsrfValidator($this->csrfToken);
        } else {
            if(!isset($this->config['securePassPhrase']))
                throw new Exception\DomainException('Need the "securePassPhrase" option in form configuration.');
            $passPhrase = $this->config['securePassPhrase'];
            if($this->session==null) {
                if($this->sessionComponentName && $this->serviceLocator) {
                    $this->session = $this->serviceLocator->get($this->sessionComponentName);
                }
            }
            if($this->session==null)
                throw new Exception\DomainException('session container is not specified.');
            if(isset($this->config['csrfTokenTimeout']))
                $timeout = $this->config['csrfTokenTimeout'];
            else
                $timeout = null;
            $csrfValidator = new CsrfValidator($passPhrase,$this->session,$timeout);
        }
        $csrfValidator->addForm($form);
        return $csrfValidator;
    }
}