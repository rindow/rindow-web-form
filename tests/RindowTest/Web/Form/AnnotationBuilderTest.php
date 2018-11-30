<?php
namespace RindowTest\Web\Form\AnnotationBuilderTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Form\Builder\AnnotationBuilder;
use Rindow\Web\Form\Annotation as Form;
use Rindow\Validation\Constraints as Assert;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Annotation\AnnotationManager;

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity extends AbstractEntity
{
    /**
     * @Form\Input(type="email",label="Email")
     * @Assert\Email
     */
    public $email;
    /**
     * @Form\Select(type="checkbox",label="Checkbox")
     */
    public $select;
    /**
     * @Form\Select(type="radio",label="Radio",options={red="Red",green="Green",blue="Blue"})
     */
    public $checkbox;
}

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity2
{
    /**
     * @Form\Input(type="hoge",label="Email")
     */
    public $email;
}

class EntityPOPO
{
    public $email;
}

class Test extends TestCase
{
    public function testAnnotationBuilder()
    {
        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $builder = new AnnotationBuilder(new AnnotationManager());
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
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "hoge" is not allowed for the field "type" of annotation @Rindow\Web\Form\Annotation\Input in RindowTest\Web\Form\AnnotationBuilderTest\Entity2::$email:
     */
    public function testAnnotationBuilderTypeError()
    {
        $entity = new Entity2();
        $builder = new AnnotationBuilder(new AnnotationManager());
        $form = $builder->build($entity);
    }

    public function testNotfound()
    {
        $entity = new EntityPOPO();
        $builder = new AnnotationBuilder(new AnnotationManager());
        $form = $builder->build($entity);
        $this->assertEquals(false,$form);
    }
}
