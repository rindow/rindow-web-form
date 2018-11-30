<?php
namespace Rindow\Web\Form\View\Theme;

class Bootstrap4Basic
{
	public static $config = array(
        'field'  => array(
            'default' => array(
                'success' => array(
                    'field' => array('class'=>'form-group'),
                ),
                'error'   => array(
                    'field' => array('class'=>'form-group has-error'),
                ),
            ),
            'hidden' => array(
                'error'   => array(
                    'field' => array('class'=>'form-group has-error'),
                ),
            ),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'default'  => array('class'=>'form-control'),
            'form'  => array(),
            'radio'    => array('itemDivClass'=>'radio'),
            'checkbox' => array('itemDivClass'=>'checkbox'),
            'submit'   => array('class'=>'btn btn-default'),
            'button'   => array('class'=>'btn btn-default'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn btn-default'),
        ),
    );
}