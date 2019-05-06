<?php
namespace RindowTest\Web\Form\AnnotationManagerTest;

use PHPUnit\Framework\TestCase;
use Rindow\Annotation\AnnotationManager;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Web\Form\Annotation\Form;
use ReflectionClass;

/**
* @Form(attributes={"method"="POST"})
*/
class Product2 extends AbstractEntity
{
    /**
    * @Max(value=10) @GeneratedValue 
    */
    public $id;
    /**
     * Duplicate Annotation
     * @Column
     * #@Max.List({
     *    @Max(value=20,groups={"a"}) 
     *    @Max(value=30,groups={"c"})
     * #})
     */
    public $id2;
    /**
     * @Column
     * @CList({
     *    @Max(value=20,groups={"a"}),
     *    @Max(value=30,groups={"c"})
     * })
     */
    public $stock;
}

class Test extends TestCase
{
    static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/../../../resources';
    }

    public function setUp()
    {
    }

    public function testFromClass()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace('RindowTest\Web\Form\Mapping');
        $reader->addNameSpace('Rindow\Validation\Constraints');
        $classRef = new ReflectionClass(__NAMESPACE__.'\Product2');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('Rindow\Web\Form\Annotation\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['id'][0]));
        $this->assertEquals('RindowTest\Web\Form\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(2,count($annotations['id2']));
        $this->assertEquals('RindowTest\Web\Form\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['id2'][1]));
        $this->assertEquals(30,$annotations['id2'][1]->value);
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('RindowTest\Web\Form\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('Rindow\Validation\Constraints\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['stock'][1]->value[1]));
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testFromClassWithTrait()
    {
        require_once self::$RINDOW_TEST_RESOURCES.'/form/entity/class_with_trait.php';
        $reader = new AnnotationManager();
        $reader->addNameSpace('RindowTest\Web\Form\Mapping');
        $reader->addNameSpace('Rindow\Validation\Constraints');
        $classRef = new ReflectionClass('RindowTest\Web\Form\Entity\Product2WithTrait');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('Rindow\Web\Form\Annotation\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['id'][0]));
        $this->assertEquals('RindowTest\Web\Form\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(2,count($annotations['id2']));
        $this->assertEquals('RindowTest\Web\Form\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['id2'][1]));
        $this->assertEquals(30,$annotations['id2'][1]->value);
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('RindowTest\Web\Form\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('Rindow\Validation\Constraints\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('Rindow\Validation\Constraints\Max',get_class($annotations['stock'][1]->value[1]));
    }
    
}
