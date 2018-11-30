<?php
namespace RindowTest\Web\Form\AnnotationAliasesTest;

use PHPUnit\Framework\TestCase;
use Rindow\Container\ModuleManager;
use Rindow\Stdlib\Entity\AbstractEntity;
use Interop\Lenient\Web\Form\Annotation\Form;
use Interop\Lenient\Web\Form\Annotation\Input;
use Interop\Lenient\Web\Form\Annotation\Select;

/**
 * @Form(attributes={"action"="/app/form","method"="post"})
 */
class TestForm extends AbstractEntity
{
    /**
     * @Input(type="text",label="Name")
     */
	public $text;

    /**
     * @Select(type="checkbox",label="Checkbox")
     */
	public $option;
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
    public function setUp()
    {
        usleep( RINDOW_TEST_CLEAR_CACHE_INTERVAL );
        \Rindow\Stdlib\Cache\CacheFactory::clearCache();
        usleep( RINDOW_TEST_CLEAR_CACHE_INTERVAL );
    }

	public function getConfig()
	{
		return array(
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
		            'securePassPhrase' => 'sdfkiaw;:sdja:40gel@rt404q4hfkmuru',
		        ),
		    ),
		);
	}

	public function test()
	{
		$mm = new ModuleManager($this->getConfig());
		$builder = $mm->getServiceLocator()->get('Rindow\Web\Form\DefaultFormContextBuilder');
		$context = $builder->build(new TestForm());
		$form = $context->getForm();
		$this->assertInstanceof('Rindow\Web\Form\Annotation\Form',$form);
		$this->assertInstanceof('Rindow\Web\Form\Annotation\Input',$form['text']);
		$this->assertInstanceof('Rindow\Web\Form\Annotation\Select',$form['option']);
	}
}
