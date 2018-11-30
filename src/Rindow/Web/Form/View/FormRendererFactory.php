<?php
namespace Rindow\Form\View;

class FormRendererFactory
{
    const DEFAULT_TRANSLATOR_SERVICE = 'Rindow\Stdlib\I18n\Gettext';

    public static function newInstance($serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['web']['form'];

        $translator = null;
        $textDomain = null;
        $themes = null;
        $hidePassword = null;

        if(isset($config['translator']))
            $translatorName = $config['translator'];
        else
            $translatorName = self::DEFAULT_TRANSLATOR_SERVICE;

        if($serviceLocator->has($translatorName))
            $translator = $serviceLocator->get($translatorName);

        if(isset($config['translator_text_domain']))
            $textDomain = $config['translator_text_domain'];

        if(isset($config['themes']))
            $themes = $config['themes'];

       if(isset($config['hidePassword']))
            $hidePassword = $config['hidePassword'];

        $form = new FormRenderer($themes,$translator,$textDomain,$hidePassword);
 
        return $form;
    }
}