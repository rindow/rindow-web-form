<?php
namespace RindowTest\Web\Form\CsrfTokenTest;

use PHPUnit\Framework\TestCase;
use Rindow\Validation\Core\Validator;
use Rindow\Annotation\AnnotationManager;

use Rindow\Validation\Constraints as Assert;
use Rindow\Stdlib\Entity\AbstractEntity;
use Rindow\Web\Form\Annotation as Form;
use Rindow\Web\Form\FormContextBuilder;
use Rindow\Web\Form\View\FormRenderer;
use Rindow\Web\Form\Validator\UniversalCsrfValidator;
use Interop\Lenient\Security\Web\CsrfToken as CsrfTokenInterface;
use Interop\Lenient\Security\Web\Exception\CsrfException as CsrfExceptionInterface;
use RuntimeException;

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
}

class TestSession
{
    public function getId()
    {
        return 'some_session_id';
    }
}

class TestCsrfException extends RuntimeException implements CsrfExceptionInterface
{
}

class TestCsrfToken implements CsrfTokenInterface
{
    public $isInvalid;
    public function generateToken()
    {
        return 'some_token';
    }

    public function isValid($token)
    {
        if($token!='some_token')
            throw new \Exception('Illegal token');
        if($this->isInvalid)
            throw new TestCsrfException('Test Invalid Token');
        return true;
    }
}

class Test extends TestCase
{
    static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/../../resources';
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
    }

    public function testValid()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        $this->assertNotNull($token);
        //var_dump($form);
        //$render = new FormRenderer($config['themes']);
        //echo $render->raw($form);

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);
        $entity = new Entity();
        $formContext = $builder->build($entity);
        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = $token;
        $formContext->setRequestToData($data);

        $this->assertTrue($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertCount(0,$formContext->getViolation());
    }

    public function testInvalid()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        //var_dump($form);
        //$render = new FormRenderer($config['themes']);
        //echo $render->raw($form);

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);
        $entity = new Entity();
        $formContext = $builder->build($entity);
        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = 'INVALID TOKEN';
        $formContext->setRequestToData($data);

        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertCount(1,$formContext->getViolation());
        $violation = $formContext->getViolation();
        $this->assertEquals('Illegal form access.',$violation['csrf_token'][0]);
        $this->assertNotEquals('INVALID TOKEN',$form['csrf_token']->value);
    }

    public function testNoTokenInvalid()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        //var_dump($form);
        //$render = new FormRenderer($config['themes']);
        //echo $render->raw($form);

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);
        $entity = new Entity();
        $formContext = $builder->build($entity);
        $data['email'] = 'hoge@hoge.com';
        // $data['csrf_token'] = 'INVALID TOKEN';
        $formContext->setRequestToData($data);

        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertCount(1,$formContext->getViolation());
        $violation = $formContext->getViolation();
        $this->assertEquals('Illegal form access.',$violation['csrf_token'][0]);
        //$this->assertNotEquals('INVALID TOKEN',$form['csrf_token']->value);
    }

    public function testTimeout()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $time = time() - 3600*2;
        $token = sha1($config['securePassPhrase'].$session->getId().$time).':'.$time;

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);
        $entity = new Entity();
        $formContext = $builder->build($entity);
        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = $token;
        $formContext->setRequestToData($data);

        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertCount(1,$formContext->getViolation());
        $violation = $formContext->getViolation();
        $this->assertEquals('Session timeout.',$violation['csrf_token'][0]);
    }

    public function testExpandLifetime()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'csrfTokenTimeout' => 3600*3,
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $time = time() - 3600*2;
        $token = sha1($config['securePassPhrase'].$session->getId().$time).':'.$time;

        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);
        $entity = new Entity();
        $formContext = $builder->build($entity);
        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = $token;
        $formContext->setRequestToData($data);

        $this->assertTrue($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertCount(0,$formContext->getViolation());
    }


    public function testRender()
    {
        $config = array(
            'autoCsrfToken' => true,
            'securePassPhrase' => 'aaaaaaaaaaaaaaaaaaaaaaaaa',
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $session = new TestSession();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setSession($session);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;

        $render = new FormRenderer();

        $result = <<<EOT
<form action="/app/form" method="post">
<label>Email</label>
<input type="email" value="hoge@hoge.com" name="email">
<input type="hidden" value="{$token}" name="csrf_token">
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result,$render->raw($form));
    }

    public function testUniversalCsrfValidatorAddForm()
    {
        $csrf = new TestCsrfToken();
        $validator = new UniversalCsrfValidator($csrf);

        $form = new \ArrayObject();
        $validator->addForm($form);
        $this->assertCount(1,$form);
        $this->assertInstanceOf('Rindow\Web\Form\Element\Element',$form['csrf_token']);
        $this->assertEquals('csrf_token',$form['csrf_token']->name);
        $this->assertEquals('csrf_token',$form['csrf_token']->fieldName);
        $this->assertEquals('hidden',$form['csrf_token']->type);
        $this->assertEquals('some_token',$form['csrf_token']->value);
    }

    public function testUniversalCsrfValidatorValidSuccess()
    {
        $csrf = new TestCsrfToken();
        $validator = new UniversalCsrfValidator($csrf);

        $form = new \ArrayObject();
        $validator->addForm($form);
        $this->assertCount(1,$form);
        $binding=$hydrator=$errors = null;
        $data['csrf_token'] = 'some_token';
        $this->assertTrue($validator->isValid($binding,$data,$hydrator,$errors));
    }

    public function testUniversalCsrfValidatorValidFail()
    {
        $csrf = new TestCsrfToken();
        $csrf->isInvalid = true;
        $validator = new UniversalCsrfValidator($csrf);

        $form = new \ArrayObject();
        $validator->addForm($form);
        $this->assertCount(1,$form);
        $binding=$hydrator= null;
        $errors = array();
        $data['csrf_token'] = 'some_token';
        $this->assertFalse($validator->isValid($binding,$data,$hydrator,$errors));
        $this->assertEquals(array('Test Invalid Token'),$errors['csrf_token']);
    }

    public function testUniversalCsrfValidatorSuccessOnFormBuilder()
    {
        $config = array(
            'autoCsrfToken' => true,
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $csrf = new TestCsrfToken();
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setCsrfToken($csrf);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        $this->assertEquals('some_token',$token);

        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = $token;
        $formContext->setRequestToData($data);
        $this->assertTrue($formContext->isValid());
    }

    public function testUniversalCsrfValidatorFailOnFormBuilder()
    {
        $config = array(
            'autoCsrfToken' => true,
            'builders' => array(
                'annotation' => array(),
            ),
            'themes' => array(
                'default'    => 'Rindow\Web\Form\View\Theme\Bootstrap3Horizontal',
            ),
        );
        $csrf = new TestCsrfToken();
        $csrf->isInvalid = true;
        $builder = new FormContextBuilder(new Validator(new AnnotationManager()));
        $builder->setConfig($config);
        $builder->setCsrfToken($csrf);

        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $formContext = $builder->build($entity);
        $form = $formContext->getForm();
        $token = $form['csrf_token']->value;
        $this->assertEquals('some_token',$token);

        $data['email'] = 'hoge@hoge.com';
        $data['csrf_token'] = $token;
        $formContext->setRequestToData($data);
        $this->assertFalse($formContext->isValid());
        $this->assertEquals(array('Test Invalid Token'),$form['csrf_token']->errors);
    }
}
