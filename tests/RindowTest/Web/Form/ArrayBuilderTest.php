<?php
namespace RindowTest\Web\Form\ArrayBuilderTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Form\Annotation as Form;
use Rindow\Validation\Constraints as Assert;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Web\Form\Builder\ArrayBuilder;

class Entity extends AbstractEntity
{
    public $email;

    public $select;

    public $checkbox;
}

class Entity2
{
    public $email;
}

class Test extends TestCase
{
    public function testArrayBuilder()
    {
        $config = array(
            'mapping' => array(
                __NAMESPACE__.'\Entity' => array(
                    'attributes' => array(
                        'action'=>'/app/form',
                        'method' => 'post',
                    ),
                    'properties' => array(
                        'email' => array(
                            'type'=>'email',
                            'label'=> 'Email',
                        ),
                        'select' => array(
                            'type'=>'checkbox',
                            'label'=> 'Checkbox',
                        ),
                        'checkbox' => array(
                            'type'=>'radio',
                            'label'=> 'Radio',
                            'options' => array(
                                'red'   => 'Red',
                                'green' => 'Green',
                                'blue'  => 'Blue',
                            ),
                        ),
                    ),
                ),
            ),
        );
        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $builder = new ArrayBuilder();
        $builder->setConfig($config);
        $form = $builder->build($entity);
        $this->assertEquals('Rindow\Web\Form\Annotation\Form',get_class($form));
        $this->assertEquals(3,count($form));
        $this->assertEquals('Rindow\Web\Form\Annotation\Input',get_class($form['email']));
        $this->assertEquals('email',$form['email']->type);
        $this->assertEquals('email',$form['email']->name);
        $this->assertEquals(null,$form['email']->value);
        $this->assertEquals('Email',$form['email']->label);
        $this->assertEquals(null,$form['email']->attributes);
        $this->assertEquals('email',$form['email']->fieldName);
        $this->assertEquals('Rindow\Web\Form\Annotation\Select',get_class($form['select']));
        $this->assertEquals('checkbox',$form['select']->type);
        $this->assertEquals(null,$form['select']->options);
        $this->assertEquals('select',$form['select']->name);
        $this->assertEquals(null,$form['select']->value);
        $this->assertEquals('Checkbox',$form['select']->label);
        $this->assertEquals(null,$form['select']->attributes);
        $this->assertEquals('select',$form['select']->fieldName);
        $this->assertEquals(null,$form['select']->multiple);
        $this->assertEquals('Rindow\Web\Form\Annotation\Select',get_class($form['checkbox']));
        $this->assertEquals('radio',$form['checkbox']->type);
        $this->assertEquals(array('red'=>'Red','green'=>'Green','blue'=>'Blue'),$form['checkbox']->options);
        $this->assertEquals('checkbox',$form['checkbox']->name);
        $this->assertEquals(null,$form['checkbox']->value);
        $this->assertEquals('Radio',$form['checkbox']->label);
        $this->assertEquals(null,$form['checkbox']->attributes);
        $this->assertEquals('checkbox',$form['checkbox']->fieldName);
        $this->assertEquals(null,$form['checkbox']->multiple);
        $this->assertEquals(3,count($form['checkbox']));
        $this->assertEquals('Rindow\Web\Form\Element\Element',get_class($form['checkbox']['blue']));
        $this->assertEquals('blue',$form['checkbox']['blue']->value);
        $this->assertEquals('red',$form['checkbox']['red']->value);
        $this->assertEquals('green',$form['checkbox']['green']->value);
    }

    /**
     * @expectedException        Rindow\Web\Form\Exception\DomainException
     * @expectedExceptionMessage Unkown form element type "hoge" in "RindowTest\Web\Form\ArrayBuilderTest\Entity2::email"
     */
    public function testTypeError()
    {
        $config = array(
            'mapping' => array(
                __NAMESPACE__.'\Entity2' => array(
                    'attributes' => array(
                        'action'=>'/app/form',
                        'method' => 'post',
                    ),
                    'properties' => array(
                        'email' => array(
                            'type'=>'hoge',
                            'label'=> 'Email',
                        ),
                    ),
                ),
            ),
        );
        $entity = new Entity2();
        $builder = new ArrayBuilder();
        $builder->setConfig($config);
        $form = $builder->build($entity);
    }

    public function testNotfound()
    {
        $config = array(
            'mapping' => array(
            ),
        );
        $entity = new Entity2();
        $builder = new ArrayBuilder();
        $builder->setConfig($config);
        $form = $builder->build($entity);
        $this->assertEquals(false,$form);
    }
}
