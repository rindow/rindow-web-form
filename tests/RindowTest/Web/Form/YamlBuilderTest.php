<?php
namespace RindowTest\Web\Form\Builder\YamlBuilderTest;

use PHPUnit\Framework\TestCase;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Web\Form\Builder\YamlBuilder;
use Rindow\Module\Yaml\Yaml;
use Rindow\Container\ModuleManager;

class Normal extends AbstractEntity
{
    protected $email;
}

class YamlError extends AbstractEntity
{
    protected $email;
}

class Test extends TestCase
{
    public static $skip = false;
    public static function setUpBeforeClass()
    {
        if (!Yaml::ready()) {
            self::$skip = true;
            return;
        }
    }

    public function setUp()
    {
        if(self::$skip)
            $this->markTestSkipped();
    }

    public function testNormal()
    {
    	$className = __NAMESPACE__.'\Normal';
        $config = array(
            'paths' => array(
            	__NAMESPACE__ => __DIR__.'/resources',
            ),
        );
    	$builder = new YamlBuilder();
        $builder->setConfig($config);
        $builder->setYaml(new Yaml());
    	$result = $builder->build($className);
        $this->assertEquals('Rindow\Web\Form\Annotation\Form',
            get_class($result));
    	$this->assertEquals(1,count($result));

    	$this->assertEquals('Rindow\Web\Form\Annotation\Input',
    		get_class($result['email']));
    	$this->assertEquals('Email',$result['email']->label);
    }

    /**
     * @expectedException        Rindow\Web\Form\Exception\DomainException
     * @expectedExceptionMessage Yaml load error
     */
    public function testYamlError()
    {
    	$className = __NAMESPACE__.'\YamlError';
        $config = array(
            'paths' => array(
            	__NAMESPACE__ => __DIR__.'/resources',
            ),
        );
    	$builder = new YamlBuilder();
        $builder->setConfig($config);
        $builder->setYaml(new Yaml());
    	$result = $builder->build($className);
    }

    public function testOnModule()
    {
        $className = __NAMESPACE__.'\Normal';
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                    'Rindow\Module\Yaml\Module' => true,
                ),
                'enableCache'=>false,
            ),
            'web' => array(
                'form' => array(
                    'builders' => array(
                        'annotation' => false,
                        'yaml' => array(
                            'paths' => array(
				            	__NAMESPACE__ => __DIR__.'/resources',
                            ),
                        ),
                    ),
                    'autoCsrfToken' => false,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $product = new $className();
        $result = $builder->build($product);
        $this->assertEquals('Rindow\Web\Form\FormContext',
            get_class($result));
        $form = $result->getForm();
        $this->assertEquals(1,count($form));

        $this->assertEquals('Rindow\Web\Form\Annotation\Input',
            get_class($form['email']));
        $this->assertEquals('Email',$form['email']->label);
    }
}