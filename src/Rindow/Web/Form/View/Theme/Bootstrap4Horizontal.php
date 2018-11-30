<?php
namespace Rindow\Web\Form\View\Theme;

class Bootstrap4Horizontal
{
	public static $config = array(
        'field'  => array(
            'default' => array(
                'success' => array(
                    'field' => array('class'=>'form-group row'),
                    'widget' => array('class'=>'col-sm-10'),
                ),
                'error'   => array(
                    'field' => array('class'=>'form-group has-error row'),
                    'widget' => array('class'=>'col-sm-10'),
                ),
            ),
            'hidden' => array(
                'error'   => array(
                    'field' => array('class'=>'form-group has-error row'),
                    'widget' => array('class'=>'col-sm-10'),
                ),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'col-sm-2 col-form-label'),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'default'  => array('class'=>'form-control'),
            'form'     => array(),
            'radio'    => array('itemDivClass'=>'radio'),
            'checkbox' => array('itemDivClass'=>'checkbox'),
            'submit'   => array('class'=>'btn btn-default'),
            'button'   => array('class'=>'btn btn-default'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn btn-default'),
        ),
    );
}
