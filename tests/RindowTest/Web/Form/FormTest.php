<?php
namespace RindowTest\Web\Form\FormTest;

use PHPUnit\Framework\TestCase;
use Rindow\Stdlib\Cache\CacheFactory;


// Test Target Classes
use Rindow\Web\Form\Element\Element;
use Rindow\Web\Form\Element\ElementSelection;
use Rindow\Web\Form\Element\ElementCollection;
use Rindow\Web\Form\View\FormRenderer;


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


class FormTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearCache();
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/twig');
    }
    public static function tearDownAfterClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
    }

    public function testText()
    {
        $element = new Element();
        $element->name = 'id';
        $element->type = 'text';
        $element->value = 'value';
        $element->label = 'LABEL';
        $element->attributes = array(
            'id'    => 'userid',
            'class' => 'field',
            'placeholder' => 'enter user-id',
        );
        $element->errors = array(
            'ERROR1',
        );

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $this->assertEquals('<label accesskey="n" for="userid">(translate:LABEL)</label>'."\n",$renderer->label($element,array('accesskey'=>'n')));
        $this->assertEquals('<input class="input" type="text" value="value" id="userid" placeholder="(translate:enter user-id)" name="id">'."\n",$renderer->widget($element,array('class'=>'input')));
        $this->assertEquals('<small class="error">(translate:ERROR1)</small>'."\n",$renderer->errors($element,array('class'=>'error')));

        $result = <<<EOT
<label for="userid">(translate:LABEL)</label>
<input type="text" value="value" id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
<small>(translate:ERROR1)</small>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $element = new Element();
        $element->name = 'id';
        $element->type = 'text';
        $element->value = '123';

        $result = <<<EOT
<input type="text" value="123" name="id">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $result = <<<EOT
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass')));

        $element->label = 'label';
        $element->errors = array(
            'ERROR1',
        );
        $result = <<<EOT
<label class="labelClass" for="id">(translate:label)</label>
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass')));

        $result = <<<EOT
<div class="fieldDivClass">
<label class="labelClass" for="id">(translate:label)</label>
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass','fieldDivClass'=>'fieldDivClass')));

        $result = <<<EOT
<div class="fieldDivClass">
<div class="labelDivClass">
<label class="labelClass" for="id">(translate:label)</label>
</div>
<div class="widgetDivClass">
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
</div>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass','fieldDivClass'=>'fieldDivClass','labelDivClass'=>'labelDivClass','widgetDivClass'=>'widgetDivClass')));
    }

    public function testTextarea()
    {
        $element = new Element();
        $element->name = 'id';
        $element->type = 'textarea';
        $element->value = 'value';
        $element->label = 'User-Id';
        $element->attributes = array(
            'id'    => 'userid',
            'class' => 'field',
            'placeholder' => 'enter user-id',
        );
        //$element->errors = array(
        //    'invalid id',
        //);

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $this->assertEquals('<label accesskey="n" for="userid">(translate:User-Id)</label>'."\n",$renderer->label($element,array('accesskey'=>'n')));

        $result = <<<EOT
<textarea id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($element));

        $result = <<<EOT
<label for="userid">(translate:User-Id)</label>
<textarea id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $element = new Element();
        $element->name = 'id';
        $element->type = 'textarea';
        $element->value = 'value';

        $result = <<<EOT
<textarea name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $result = <<<EOT
<textarea class="input" id="id" placeholder="(translate:id-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass')));

        $element->label = 'label';
        $result = <<<EOT
<label class="labelClass" for="id">(translate:label)</label>
<textarea class="input" id="id" placeholder="(translate:id-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass')));
    }

    public function testRadio()
    {
        $collection = new ElementSelection();
        $collection->type = 'radio';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        $collection->attributes = array(
            'id'    => 'select1',
            'class' => 'cssClass',
        );

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'radio';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $element->attributes = array(
            'id'    => 'id1',
            'class' => 'cssClass',
        );
        $element->errors = array(
            'invalid id',
        );
        $collection[$element->value] = $element;

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'radio';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $element->attributes = array(
            'id'    => 'id2',
            'class' => 'cssClass',
        );
        $element->errors = array(
            'invalid id',
        );
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label>(translate:LABEL)</label>
<label for="id1">
<input name="foo" type="radio" checked="checked" value="value1" id="id1" class="cssClass">
(translate:LABEL1)
</label>
<label for="id2">
<input name="foo" type="radio" value="value2" id="id2" class="cssClass">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection));
        $this->assertEquals('<input type="radio" value="value1" id="id1" class="cssClass" name="foo">'."\n",$renderer->widget($collection['value1']));
    }

    public function testRadio2()
    {
        $collection = new ElementSelection();
        $collection->type = 'checkbox';
        $collection->name = 'foo';
        $collection->value = array('value1');
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        //$collection->attributes = array(
        //    'id'    => 'select1',
        //    'class' => 'cssClass',
        //);
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        //$element->name = 'foo[]';
        //$element->type = 'radio';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        //$element->attributes = array(
        //    'id'    => 'id1',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $element = new Element();
        //$element->name = 'foo[]';
        //$element->type = 'radio';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        //$element->attributes = array(
        //    'id'    => 'id2',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','id'=>'id','itemDivClass'=>'itemDivClass','labelClass'=>'labelClass')));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<label class="itemLabelClass">
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
<label class="itemLabelClass">
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass')));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
<label class="itemLabelClass">
(translate:LABEL1)
</label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
<label class="itemLabelClass">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass','outOfLabel'=>true)));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1" id="id1">
<label class="itemLabelClass" for="id1">
(translate:LABEL1)
</label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2" id="id2">
<label class="itemLabelClass" for="id2">
(translate:LABEL2)
</label>
EOT;
        $collection['value1']->attributes['id'] = 'id1';
        $collection['value2']->attributes['id'] = 'id2';

        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass','outOfLabel'=>true)));
    }

    public function testSelect()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = array('value1');
        $collection->label = 'LABEL';
        $collection->multiple = true;
        $collection->attributes = array(
            'id'    => 'select1',
            'class' => 'cssClass',
        );
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'option';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $element->attributes = array(
            'id'    => 'id1',
            'class' => 'cssClass',
        );
        $collection[$element->value] = $element;

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'option';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $element->attributes = array(
            'id'    => 'id2',
            'class' => 'cssClass',
        );
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label for="select1">(translate:LABEL)</label>
<select name="foo[]" multiple id="select1" class="cssClass">
<option selected="selected" value="value1" id="id1" class="cssClass">
(translate:LABEL1)
</option>
<option value="value2" id="id2" class="cssClass">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        //echo $renderer->raw($collection);
        $this->assertEquals($result, $renderer->raw($collection));
        //$this->assertEquals('<input id="radio1" class="radioCss" type="radio" value="radio1">'."\n",$renderer->widget($collection['radio1']));
    }

    public function testSelect2()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        //$collection->attributes = array(
        //    'id'    => 'select1',
        //    'class' => 'cssClass',
        //);
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        //$element->name = 'foo';
        //$element->type = 'option';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        //$element->attributes = array(
        //    'id'    => 'id1',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $element = new Element();
        //$element->name = 'foo';
        //$element->type = 'option';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        //$element->attributes = array(
        //    'id'    => 'id2',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label class="labelClass" for="select1">(translate:LABEL)</label>
<select id="select1" class="cssClass" name="foo">
<option selected="selected" value="value1">
(translate:LABEL1)
</option>
<option value="value2">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        //echo $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass'));
        $this->assertEquals($result, $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass','labelClass'=>'labelClass')));
        $this->assertEquals('<option id="id1" class="cssClass" value="value1">'."\n",$renderer->openTag('option',$collection['value1'],array('id'=>'id1','class'=>'cssClass', 'value'=>$collection['value1']->value)));
        $this->assertEquals('</option>'."\n",$renderer->closeTag('option',$collection['value1'],array('id'=>'id1','class'=>'cssClass')));
    }

    public function testSwitchRadioAndSelect()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';

        $element = new Element();
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $collection[$element->value] = $element;

        $element = new Element();
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label for="select1">(translate:LABEL)</label>
<select id="select1" class="cssClass" name="foo">
<option selected="selected" value="value1">
(translate:LABEL1)
</option>
<option value="value2">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass')));

        $result = <<<EOT
<label>(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo" type="radio" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo" type="radio" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);

        $collection->type = 'radio';
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemDivClass'=>'itemDivClass')));

        $result = <<<EOT
<label>(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);

        $collection->type = 'checkbox';
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemDivClass'=>'itemDivClass')));
    }

    public function testForm()
    {
        $form = new ElementCollection();
        $form->type = 'form';
        $form->attributes['action'] = '/foo/bar';
        $form->attributes['method'] = 'POST';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<form class="formClass" action="/foo/bar" method="POST">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->open($form,array('class'=>'formClass')));

        $result = <<<EOT
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->close($form,array('class'=>'formClass')));

        $result = <<<EOT
<form class="formClass" action="/foo/bar" method="POST">
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($form,array('class'=>'formClass')));
        $this->assertEquals($result, $renderer->raw($form,array('class'=>'formClass')));
    }

    public function testTheme()
    {
        $form = new ElementCollection();
        $form->type = 'form';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $collection = new ElementSelection();
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

        // theme class name
        $foundation = 'Rindow\Web\Form\View\Theme\Foundation5Basic';
        $bootstrap = 'Rindow\Web\Form\View\Theme\Bootstrap3Basic';
        $themes = array(
            'default'   => $foundation,
            'bootstrap' => $bootstrap,
        );

        $translator = new TestTranslator();
        $renderer = new FormRenderer($themes,$translator);

        $result = <<<EOT
<div>
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
</div>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="form-group">
<label class="control-label">(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
</div>
EOT;
        // theme class
        $renderer->setTheme($bootstrap);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $form['boo']->errors = array(
            'ERROR1',
        );
        $result = <<<EOT
<div class="form-group has-error">
<label class="control-label">(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
<small class="help-block">(translate:ERROR1)</small>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="error">
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
<small class="error">(translate:ERROR1)</small>
</div>
EOT;
        // theme alias
        $renderer->setTheme('default');
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="errorfield">
<label>(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
<small>(translate:ERROR1)</small>
</div>
EOT;
        // theme immediate
        $themeconfig  = array(
            'field'  => array(
                'default' => array(
                    'success' => array('field'=>array('class'=>true)),
                    'error'   => array('field'=>array('class'=>'errorfield')),
                ),
            ),
            'widget' => array(
                'default'  => array('class'=>'form-control'),
            ),
        );
        $renderer->setTheme($themeconfig);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="row">
<div class="col-2 error">
<label class="right inline">(translate:LABEL)</label>
</div>
<div class="col-10 error">
<input type="text" value="value" name="boo">
<small>(translate:ERROR1)</small>
</div>
</div>
EOT;
        // theme immediate horizontal mode
        $themeconfig  = array(
            'field'  => array(
                'default' => array(
                    'success' => array(
                        'field'=>array('class'=>'row'),
                        'label'=>array('class'=>'col-2'),
                        'widget'=>array('class'=>'col-10'),
                    ),
                    'error'   => array(
                        'field'=>array('class'=>'row'),
                        'label'=>array('class'=>'col-2 error'),
                        'widget'=>array('class'=>'col-10 error'),
                    ),
                ),
            ),
            'label' => array(
                'default'  => array('class'=>'right inline'),
            ),
        );
        $renderer->setTheme($themeconfig);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));
    }
}
