<?php

/**
* CMessages contains user-related functions.
* TODO: Gotta think how we could implement automatic translation of the wildcard values.
*       For example, 'PLAN_INFO' => 'Current investment plan is %s'
*       cf::messafes()->text('PLAN_INFO', '#PLANS/P1');
* TODO: Improve translation integration into CFormModel and CGridModel.
*       - Automatic label translation
*       - Error messages translation
*       - addError() call translation
*/
class CMessages extends CComponent
{
    /**
    * @var string Language file path.
    */
    public $languageFile;
    
    /**
    * @var string Non-existing marker.
    */
    public $emptyMarker = '%%';
    
    /**
    * @var array Translations array.
    */
    private $_tr;
    
    /**
    * Performs text translation.
    * @param string Translation entity ID.
    * @return string Translated text.
    */
    public function text($id, $default = '')
    {
        // Load translations from file
        if (!is_array($this->_tr))
            $this->_tr = include($this->languageFile);
        
        // Get additional arguments
        $unit = '';
        $args = array_slice(func_get_args(), 1);
        @list($one, $two) = explode('/', $id, 2);
        
        // Find translation unit
        if (!isset($this->_tr[$one]))
            return $default === '' ? $this->emptyMarker . $id . $this->emptyMarker : $default;
        else
            $unit = $this->_tr[$one];
        
        // Apply index
        if (isset($two) && is_array($unit) && isset($unit[$two]))
            $unit = $unit[$two];
        else if (isset($two) && is_array($unit) && !isset($unit[$two]))
            return $default === '' ? $this->emptyMarker . $id . $this->emptyMarker : $default;
        
        // Substitute wildcards
        if (!empty($args))
            $unit = vsprintf($unit, $args);
        
        return $unit;
    }
}