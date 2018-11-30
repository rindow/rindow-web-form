<?php
namespace Rindow\Web\Form\View\Theme;

class Mdl13Horizontal
{
	public static $config = array(
        'field'  => array(
            'default' => array(
                'success' => array(
                    'field' => array('class'=>'mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell mdl-cell--12-col'),
                ),
                'error'   => array(
                    'field' => array('class'=>'mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell mdl-cell--12-col is-invalid'),
                ),
            ),
            'hidden' => array(
                'error'   => array(
                    'field' => array('class'=>'mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell mdl-cell--12-col is-invalid'),
                ),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'mdl-textfield__label'),
            'radio'  => array(),
            'checkbox'  => array(),
            'hidden'  => array(),
        ),
        'errors' => array('class'=>'mdl-textfield__error'),
        'widget' => array(
            'default'  => array('class'=>'mdl-textfield__input'),
            'form'  => array('class'=>'mdl-grid'),
            'radio'    => array('class'=>'mdl-radio__button','itemLabelClass'=>'mdl-radio mdl-js-radio mdl-js-ripple-effect','optionLabelDivClass'=>'mdl-radio__label'),
            'checkbox' => array('class'=>'mdl-checkbox__input',
                'itemLabelClass'=>'mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect','optionLabelDivClass'=>'mdl-checkbox__label'),
            'submit'   => array('class'=>'mdl-button mdl-js-button mdl-button--raised mdl-button--colored'),
            'button'   => array('class'=>'mdl-button mdl-js-button mdl-button--raised'),
            'reset'    => array('class'=>'mdl-button mdl-js-button mdl-button--raised'),
            'hidden'  => array(),
        ),
    );
}
