<?php
namespace Rindow\Web\Form;

class Module
{
    public function getConfig()
    {
        return array(
            'annotation' => array(
                'aliases' => array(
                    'Interop\\Lenient\\Web\\Form\\Annotation\\Form' => 
                        'Rindow\\Web\\Form\\Annotation\\Form',
                    'Interop\\Lenient\\Web\\Form\\Annotation\\Input' => 
                        'Rindow\\Web\\Form\\Annotation\\Input',
                    'Interop\\Lenient\\Web\\Form\\Annotation\\Select' => 
                        'Rindow\\Web\\Form\\Annotation\\Select',
                ),
            ),
            'container' => array(
                'components' => array(
                    'Rindow\\Web\\Form\\DefaultFormContextBuilder' => array(
                        'class' => 'Rindow\\Web\\Form\\FormContextBuilder',
                        'constructor_args' => array(
                            'validator' => array('ref' => 'Rindow\\Validation\\DefaultValidator'),
                            'hydrator' => null,
                            'serviceLocator' => array('ref' => 'ServiceLocator'),
                        ),
                        'properties' => array(
                            'config' => array('config' => 'web::form'),
                            'sessionComponentName' => array('value' => 'Rindow\\Web\\Session\\DefaultSession'),
                        ),
                    ),
                    'Rindow\\Web\\Form\\View\\DefaultFormRenderer'  => array(
                        'class' => 'Rindow\\Web\\Form\\View\\FormRenderer',
                        'constructor_args' => array(
                            'themes' => array('config' => 'web::form::themes'),
                            'translator' => array('ref' => 'I18nMessageTranslator'),
                            'textDomain' => array('config' => 'web::form::translator_text_domain'),
                            'hidePassword' => array('config' => 'web::form::hidePassword'),
                        ),
                    ),
                    'Rindow\\Web\\Form\\Builder\\DefaultYamlBuilder' => array(
                        'class' => 'Rindow\\Web\\Form\\Builder\\YamlBuilder',
                        'properties' => array(
                            'yaml' => array('ref' => 'Rindow\\Module\\Yaml\\Yaml'),
                        ),
                    ),
                    'Rindow\\Web\\Form\\Builder\\DefaultArrayBuilder' => array(
                        'class' => 'Rindow\\Web\\Form\\Builder\\ArrayBuilder',
                    ),
                    'Rindow\\Web\\Form\\Builder\\DefaultAnnotationBuilder' => array(
                        'class' => 'Rindow\\Web\\Form\\Builder\\AnnotationBuilder',
                        'properties' => array(
                            'annotationReader' => array('ref' => 'AnnotationReader'),
                        ),
                    ),
                ),
            ),
            'web' => array(
                'form' => array(
                    'builder_aliases' => array(
                        'annotation' => 'Rindow\\Web\\Form\\Builder\\DefaultAnnotationBuilder',
                        'array' => 'Rindow\\Web\\Form\\Builder\\DefaultArrayBuilder',
                        'yaml' => 'Rindow\\Web\\Form\\Builder\\DefaultYamlBuilder',
                    ),
                    'builders' => array(
                        'annotation' => array(),
                    ),
                    'themes' => array(
                        //'default'     => 'Rindow\\Web\\Form\\View\\Theme\\Bootstrap4Horizontal',
                        'bootstrap4'  => 'Rindow\\Web\\Form\\View\\Theme\\Bootstrap4Horizontal',
                        'bootstrap3'  => 'Rindow\\Web\\Form\\View\\Theme\\Bootstrap3Horizontal',
                        'foundation6' => 'Rindow\\Web\\Form\\View\\Theme\\Foundation6Horizontal',
                        'foundation5' => 'Rindow\\Web\\Form\\View\\Theme\\Foundation5Horizontal',
                    ),
                    'autoCsrfToken' => !getenv('UNITTEST'),
                ),
            ),
        );
    }
}
