<?php
namespace Rindow\Web\Form\View\Theme;

class Foundation6Horizontal
{
	public static $config = array(
        'field'  => array(
            'default' => array(
                'success' => array(
                    'field' => array('class'=>'grid-x'),
                    'label'  => array('class'=>'cell large-2 medium-2 small-4'),
                    'widget' => array('class'=>'cell large-10 medium-10 small-8'),
                ),
                'error'   => array(
                    'field' => array('class'=>'grid-x'),
                    'label'  => array('class'=>'cell large-2 medium-2 small-4 error'),
                    'widget' => array('class'=>'cell large-10 medium-10 small-8 error'),
                ),
            ),
            'hidden' => array(
                'error'   => array(
                    'field' => array('class'=>'grid-x'),
                    'label'  => array('class'=>'cell large-2 medium-2 small-4 error'),
                    'widget' => array('class'=>'cell large-10 medium-10 small-8 error'),
                ),
            ),
        ),
        'errors' => array('class'=>'error'),
        'label'  => array(
            'default'  => array('class'=>'text-right middle'),
            'radio'    => array('class'=>'text-right'),
            'checkbox' => array('class'=>'text-right'),
        ),
        'widget' => array(
            'submit'   => array('class'=>'button radius'),
            'reset'    => array('class'=>'button radius'),
        ),
    );
}