<?php
namespace RindowTest\Web\Form\FormBuilderTest;

use PHPUnit\Framework\TestCase;
use Rindow\Stdlib\Cache\CacheFactory;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Container\ModuleManager;
use Rindow\Validation\Core\Validator;
use Rindow\Annotation\AnnotationManager;

use Rindow\Validation\Constraints as Assert;
use Rindow\Web\Form\Annotation as Form;
use Rindow\Web\Form\View\FormRenderer;
use Rindow\Web\Form\Element\Element;

// Test Target Classes
use Rindow\Web\Form\FormContextBuilder;


class TestTranslator
{
    public function __construct($serviceManager=null)
    {
        $this->serviceManager = $serviceManager;
    }
    public function translate($message, $domain=null, $locale=null)
    {
        if($domain)
            $domain = ':'.$domain;
        else
            $domain = '';
        return '(translate:'.$message.$domain.')';
    }
}

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

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity3 extends AbstractEntity
{
    /**
     * @Form\Input(type="email",label="Email Address")
     * @Assert\Email
     */
    public $email;
}

class EntityPOPO extends AbstractEntity
{
    public $email;
    public $select;
    public $checkbox;
}

class MappedValueCategory extends AbstractEntity
{
    public $idString;
    public $name;
}

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class MappedValueEntity extends AbstractEntity
{
    /**
     * Category Field
     */
    public $category;

    /**
     * @Form\Select(
     *      type="select",label="Category",
     *      bindTo="category",
     *      mappedValue="idString",mappedLabel="name",
     *      mappedOptions="categoryOptions")
     * @Assert\NotBlank(path="category")
     * @Assert\NotNull(path="category")
     */
    public $categoryForm;
    public $categoryOptions;
}

class TestSession
{
    public function getId()
    {
        return 'some_session_id';
    }
}

class Test extends TestCase
{
    static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/../../../resources';
        CacheFactory::clearCache();
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/twig');
    }

    public static function tearDownAfterClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
    }

    public function setUp()
    {
        usleep( RINDOW_TEST_CLEAR_CACHE_INTERVAL );
        \Rindow\Stdlib\Cache\CacheFactory::clearCache();
        usleep( RINDOW_TEST_CLEAR_CACHE_INTERVAL );
    }

    public function testCloneNestedFormStructure()
    {
        $form = new Form\Form();
        $form->attributes['id'] = 'form1';

        $element = new Form\Input();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $collection = new Form\Select();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $form[$collection->name] = $collection;

        $element = new Element();
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $collection[$element->value] = $element;

        $element = new Element();
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $collection[$element->value] = $element;

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $form2 = $builder->cloneForm($form);
        $this->assertEquals('form1',$form->attributes['id']);
        $this->assertEquals('form1',$form2->attributes['id']);
        $this->assertEquals('value',$form['boo']->value);
        $this->assertEquals('value',$form2['boo']->value);
        $this->assertEquals('value1',$form['foo']['value1']->value);
        $this->assertEquals('value1',$form2['foo']['value1']->value);

        $form2->attributes['id'] = 'form2';
        $form2['boo']->value = 'value2';
        $form2['foo']['value1']->value = 'value2';
        $this->assertEquals('form1',$form->attributes['id']);
        $this->assertEquals('form2',$form2->attributes['id']);
        $this->assertEquals('value',$form['boo']->value);
        $this->assertEquals('value2',$form2['boo']->value);
        $this->assertEquals('value1',$form['foo']['value1']->value);
        $this->assertEquals('value2',$form2['foo']['value1']->value);
    }

    public function testAnnotationBuilder()
    {
        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $formContext = $builder->build($entity);
        //$formContext->setData($entity);

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>(translate:Email)</label>
<input type="email" value="hoge@hoge.com" name="email">
<label>(translate:Checkbox)</label>
<label>(translate:Radio)</label>
<label>
<input name="checkbox" type="radio" value="red">
(translate:Red)
</label>
<label>
<input name="checkbox" type="radio" value="green">
(translate:Green)
</label>
<label>
<input name="checkbox" type="radio" value="blue">
(translate:Blue)
</label>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));
    }

    public function testMappedValue()
    {
        $entity = new MappedValueEntity();
        $categories = array();
        $categories[] = new MappedValueCategory();
        $categories[] = new MappedValueCategory();
        $categories[0]->idString = 'red';
        $categories[0]->name = 'CATEGORY RED';
        $categories[1]->idString = 'blue';
        $categories[1]->name = 'CATEGORY BLUE';
        $entity->categoryOptions = $categories;
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $formContext = $builder->build($entity);
        //$formContext->setData($entity);

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>(translate:Category)</label>
<select name="category">
<option value="red">
(translate:CATEGORY RED)
</option>
<option value="blue">
(translate:CATEGORY BLUE)
</option>
</select>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "hoge" is not allowed for the field "type" of annotation @Rindow\Web\Form\Annotation\Input in RindowTest\Web\Form\FormBuilderTest\Entity2::$email:
     */
    public function testAnnotationBuilderTypeError()
    {
        $entity = new Entity2();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $form = $builder->build($entity)->getForm();

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        //$this->assertEquals($result, $renderer->raw($form));
        echo $renderer->widget($form);
    }

    public function testValidation()
    {
        $entity = new Entity();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $formContext = $builder->build($entity);
        $formContext->setRequestToData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('not a well-formed email address.',$form['email']->errors[0]);
    }

    public function testOnModule()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Validation\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                ),
                'annotation_manager' => true,
            ),
            'container' => array(
                'components' => array(
                    'Rindow\Web\Form\DefaultFormContextBuilder' => array(
                         'properties' => array(
                            'sessionComponentName' => array('value' => __NAMESPACE__.'\TestSession'),
                        ),
                    ),
                    __NAMESPACE__.'\TestSession'=>array(),
                ),
            ),
            'web' => array(
                'form' => array(
                    'themes' => array(
                        'default' => 'Rindow\Web\Form\View\Theme\Bootstrap3Basic',
                    ),
                    'translator_text_domain' => 'form',
                    'securePassPhrase' => 'ko-dfio34@0czx/.aeddop-ea[a',

                    'autoCsrfToken' => true,
                ),
            ),
           'translator' => array(
                'translation_file_patterns' => array(
                    __NAMESPACE__ => array(
                        'type'        => 'Gettext',
                        'base_dir'    => self::$RINDOW_TEST_RESOURCES.'/form/messages',
                        'pattern'     => '%s/LC_MESSAGES/form.mo',
                        'text_domain' => 'form',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $renderer = $sm->get('Rindow\Web\Form\View\DefaultFormRenderer');
        $translator = $sm->get('Rindow\Stdlib\I18n\DefaultTranslator');
        $translator->setLocale('en_US');

        $entity = new Entity3();
        $formContext = $builder->build($entity);
        $formContext->setRequestToData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('not a well-formed email address.',$form['email']->errors[0]);
        $token = $form['csrf_token']->value;

        $result = <<<EOT
<form class="form-control" action="/app/form" method="post">
<div class="form-group has-error">
<label class="control-label">Translated: Email Address</label>
<input class="form-control" type="email" value="abc" name="email">
<small class="help-block">not a well-formed email address.</small>
</div>
<div class="form-group has-error">
<input class="form-control" type="hidden" value="{$token}" name="csrf_token">
<small class="help-block">Illegal form access.</small>
</div>
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));

        $translator->setLocale('ja_JP');
        $formContext = $builder->build($entity);
        $formContext->setRequestToData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('正しいメールアドレス形式ではありません。',$form['email']->errors[0]);
        $token = $form['csrf_token']->value;

        $result = <<<EOT
<form class="form-control" action="/app/form" method="post">
<div class="form-group has-error">
<label class="control-label">Translated in Japanese: Email Address</label>
<input class="form-control" type="email" value="abc" name="email">
<small class="help-block">正しいメールアドレス形式ではありません。</small>
</div>
<div class="form-group has-error">
<input class="form-control" type="hidden" value="{$token}" name="csrf_token">
<small class="help-block">Illegal form access.</small>
</div>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));

        $formContext = $builder->build($entity);
        $formContext->setRequestToData(array('email'=>'abc@abc.com'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('Illegal form access.',$form['csrf_token']->errors[0]);


        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        $formContext->setRequestToData(array('email'=>'abc@abc.com','csrf_token'=>$token));
        $this->assertTrue($formContext->isValid());
    }

    public function testOnModuleWithoutValidator()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                ),
                'annotation_manager' => true,
            ),
            'web' => array(
                'form' => array(
                    'autoCsrfToken' => false,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $formBuilder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $entity = new Entity();
        $formContext = $formBuilder->build($entity);
        $form = $formContext->getForm();
        $this->markTestIncomplete();
    }

    public function testArrayFormBuilder()
    {
        $className = __NAMESPACE__.'\Entity';
        $config = array(
            'builders' => array(
                'array' => array(
                    'mapping' => array(
                        $className => array(
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
                ),
            ),
        );
        $contextBuilder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $contextBuilder->setConfig($config);
        $entity = new $className();
        $context = $contextBuilder->build($entity);
        $form = $context->getForm();
        $this->assertEquals('Rindow\Web\Form\Annotation\Form',get_class($form));
        $this->assertEquals(3,count($form));
        $this->assertEquals('email',$form['email']->type);
        $this->assertEquals('checkbox',$form['select']->type);
        $this->assertEquals('radio',$form['checkbox']->type);
    }

    public function testArrayFormBuilderOnModuleAndDisableAnnotation()
    {
        $className = __NAMESPACE__.'\Entity';
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Validation\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                ),
            ),
            'web' => array(
                'form' => array(
                    'autoCsrfToken' => false,
                    'builders' => array(
                        'annotation' => false,
                        'array' => array(
                            'mapping' => array(
                                $className => array(
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
                        ),
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $entity = new $className();
        $context = $builder->build($entity);
        $form = $context->getForm();
        $this->assertEquals(3,count($form));
        $this->assertEquals('email',$form['email']->type);
        $this->assertEquals('checkbox',$form['select']->type);
        $this->assertEquals('radio',$form['checkbox']->type);
    }

    public function testArrayFormBuilderOnModulePOPOAndEnableAnnotation()
    {
        $className = __NAMESPACE__.'\EntityPOPO';
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Validation\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                ),
                'annotation_manager' => true,
            ),
            'web' => array(
                'form' => array(
                    'autoCsrfToken' => false,
                    'builders' => array(
                        'array' => array(
                            'mapping' => array(
                                $className => array(
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
                        ),
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $entity = new $className();
        $context = $builder->build($entity);
        $form = $context->getForm();
        $this->assertEquals(3,count($form));
        $this->assertEquals('email',$form['email']->type);
        $this->assertEquals('checkbox',$form['select']->type);
        $this->assertEquals('radio',$form['checkbox']->type);
    }

    /**
     * @expectedException        Rindow\Web\Form\Exception\DomainException
     * @expectedExceptionMessage Form is not found for "RindowTest\Web\Form\FormBuilderTest\EntityPOPO"
     */
    public function testNoneForm()
    {
        $className = __NAMESPACE__.'\EntityPOPO';
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Validation\Module' => true,
                    'Rindow\Stdlib\I18n\Module' => true,
                ),
                'annotation_manager' => true,
            ),
            'web' => array(
                'form' => array(
                    'autoCsrfToken' => false,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Rindow\Web\Form\DefaultFormContextBuilder');
        $entity = new $className();
        $context = $builder->build($entity);
    }

    /**
     * @expectedException        Rindow\Web\Form\Exception\DomainException
     * @expectedExceptionMessage there is no data to validate.
    */
    public function testNoData()
    {
        $entity = new Entity();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $formContext = $builder->build($entity);
        $formContext->isValid();
    }

    public function testEmptyData()
    {
        $entity = new Entity();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $formContext = $builder->build($entity);
        $formContext->setRequestToData(array());
        $this->assertTrue($formContext->isValid());
    }
}
