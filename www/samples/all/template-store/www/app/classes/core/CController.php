<?php

/**
* CController is a lightweight controller.
* TODO: Отдельный класс CAction
* TODO: Вынести ренеринг в CViewRenderer
*/
abstract class CController extends CObject
{
    private $_id;
    private $_action;
    
    /**
    * @var mixed The name of the layout to be applied to this controller's views.
    */
    public $layout = '_index';
    
    /**
    * @var string The name of the default action. Defaults to 'index'.
    */
    public $defaultAction = 'index';
    
    /**
    * @param string $id id of this controller
    * @param CWebModule $module the module that this controller belongs to.
    */
    public function __construct($id)
    {
        $this->_id = $id;
    }
    
    /**
    * Initializes the controller.
    * This method is called by the application before the controller starts to execute.
    * You may override this method to perform the needed initialization for the controller.
    */
    public function init()
    {
    }
    
    /**
    * @return string Controller unique ID.
    */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
    * @return string The action currently being executed, null if no active action.
    */
    public function getAction()
    {
        return $this->_action;
    }
    
    /**
    * Runs the controller action.
    * @param string The action to run.
    */
    public function run($action)
    {
        if ($action === '')
            $action = $this->defaultAction;
        
        $method = 'action' . ucfirst($action);
        
        $priorAction = $this->_action;
        $this->_action = $action;
        
        if ($this->beforeAction($action))
        {
            if (method_exists($this, $method))
                $this->$method();
            else
                $this->missingAction($action);
            
            $this->afterAction($action);
        }
        
        $this->_action = $priorAction;
    }
    
    /**
    * This method is invoked right before an action is to be executed (after all possible filters.)
    * You may override this method to do last-minute preparation for the action.
    * @param CAction $method the action to be executed.
    * @return boolean whether the action should be executed.
    */
    protected function beforeAction($method)
    {
        return true;
    }
    
    /**
    * This method is invoked right after an action is executed.
    * You may override this method to do some postprocessing for the action.
    * @param CAction $method the action just executed.
    */
    protected function afterAction($method)
    {
    }
    
    /**
    * Handles the request whose action is not recognized.
    * This method is invoked when the controller cannot find the requested action.
    * The default implementation simply throws an exception.
    * @param string $methodID the missing action name
    * @throws CHttpException whenever this method is invoked
    */
    public function missingAction($actionId)
    {
        throw new CException('[404] The system is unable to find the requested action "%s".', $actionId == '' ? $this->defaultAction : $actionId);
    }
    
    /**
    * @return string Controller view path.
    */
    public function getViewPath()
    {
        return cf::app()->viewPath;
    }
    
    /**
    * @return string Controller view path.
    */
    public function getLayoutPath()
    {
        return cf::app()->viewPath;
    }
    
    /**
    * Finds a view file based on its name.
    * @return mixed the view file path. False if the view file does not exist.
    */
    public function getViewFile($viewName, $viewPath)
    {
        if (empty($viewName)) return false;
        $viewFile = $viewPath . DIRECTORY_SEPARATOR . $viewName . '.php';
        return is_file($viewFile) ? $viewFile : false;
    }
    
    /**
    * Returns whether this is an AJAX (XMLHttpRequest) request.
    * @return boolean Whether this is an AJAX (XMLHttpRequest) request.
    */
    public function getIsAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
    * Renders a view with a layout.
    */
    public function render($view, $data = null, $return = false)
    {
        // Render the view
        $output = $this->renderPartial($this->getId() . '.' . $view, $data, true);
        
        // Wrap into layout
        if (($layoutFile = $this->getViewFile($this->layout, $this->getLayoutPath())) !== false)
            $output = $this->renderInternal($layoutFile, array('content' => $output), true);
        
        // Return or flush output
        if ($return)
            return $output;
        else
            echo $output;
    }
    
    /**
    * Renders a view.
    */
    public function renderPartial($view, $data = null, $return = false)
    {
        $output = '';
        
        // Renders a view
        if (($viewFile = $this->getViewFile($view, $this->getViewPath())) !== false)
            $output = $this->renderInternal($viewFile, $data, true);
        else
            throw new CException('%s cannot find the requested view "%s".', get_class($this), $view);
        
        // Return or flush output
        if ($return)
            return $output;
        else
            echo $output;
    }

    /**
    * Renders a view file.
    */
    protected function renderInternal($_viewFile_, $_data_ = null)
    {
        if (is_array($_data_))
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data_;
        
        ob_start();
        ob_implicit_flush(false);
        include($_viewFile_);
        return ob_get_clean();
    }
    
    /**
    * Sends a JSON data to client.
    */
    public function renderJSON($data)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        
        echo json_encode($data);
        exit();
    }
    
    /**
    * Generates an HTML element.
    * @param string The tag name.
    * @param array The element attributes.
    * @param mixed The content to be enclosed between open and close element tags.
    * @param boolean Whether to generate the close tag.
    * @return string The generated HTML element tag.
    */
    public function renderTag($tag, $htmlAttributes = array(), $content = false, $closeTag = true)
    {
        $html = '<' . $tag . $this->renderAttributes($htmlAttributes);
        if ($content === false)
            return $closeTag ? $html  .' />' : $html . '>';
        else
            return $closeTag ? $html . '>' . $content . '</' . $tag . '>' : $html . '>' . $content;
    }
    
    /**
    * Generates an open HTML element.
    * @param string $tag the tag name
    * @param array $htmlAttributes the element attributes. The values will be HTML-encoded using {@link encode()}.
    * If an 'encode' attribute is given and its value is false,
    * the rest of the attribute values will NOT be HTML-encoded.
    * Since version 1.1.5, attributes whose value is null will not be rendered.
    * @return string the generated HTML element tag
    */
    public function renderOpenTag($tag, $htmlAttributes = array())
    {
        return '<' . $tag . $this->renderAttributes($htmlAttributes) . '>';
    }
    
    /**
    * Generates a close HTML element.
    * @param string $tag the tag name
    * @return string the generated HTML element tag
    */
    public function renderCloseTag($tag)
    {
        return '</' . $tag . '>';
    }
    
    /**
    * Renders the HTML tag attributes.
    * Attributes whose value is null will not be rendered.
    * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
    * properly based on their corresponding boolean value.
    * @param array Attributes to be rendered.
    * @return string The rendering result.
    */
    public function renderAttributes($htmlAttributes)
    {
        if (empty($htmlAttributes))
            return '';
        
        $html = '';
        foreach ($htmlAttributes as $name => $value)
            if ($value !== null && $value !== false && $value != '')
                $html .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        
        return $html;
    }
}