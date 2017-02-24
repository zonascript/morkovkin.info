<?php

/**
* Contains tamplate management functions.
*/
class CTemplates extends CComponent
{
    /**
    * @var strign Catalog path.
    */
    public $templatePath;
    
    /**
    * @var string The name of the template archive.
    */
    public $templateFile;
    
    /**
    * @var string An array containing image size or false not to resize.
    */
    public $templateImageSize;
    
    /**
    * @var string The name of the big preview image.
    */
    public $templateImageFile;
    
    /**
    * @var array An array containing thumbnail size or false not to resize.
    */
    public $templateThumbSize;

    /**
    * @var string The name of the small preview image.
    */
    public $templateThumbFile;
    
    /**
    * @var integer Image save quality.
    */
    // public $imageQuality = 75;
    
    /**
    * @var array Image saving functions.
    */
    private $_functions = array
    (
        'jpg' => 'imagejpeg',
        'jpeg' => 'imagejpeg',
        'gif' => 'imagegif',
        'png' => 'imagepng',
    );
    
    /**
    * Retrieves the name of the template directory by ID.
    * Template directories are named with the following pattern in mind: '002_anything'.
    * First three characters are template ID in the database, while characters to the right
    * might be anything.
    * @param integer Template ID.
    * @return string Template directory path.
    */
    public function getTemplatePath($id, $postfix = null)
    {
        $list = glob($this->templatePath . DIRECTORY_SEPARATOR . sprintf('%03d', $id) . '_*', GLOB_ONLYDIR | GLOB_NOSORT);
        
        if (empty($list) && is_string($postfix))
        {
            if (!mkdir($path = $this->templatePath . DIRECTORY_SEPARATOR . sprintf('%03d', $id) . '_' . $postfix))
                throw new CException('CWebUser could not create template directory: %s', $path);
        }
        else
            $path = isset($list[0]) ? $list[0] . DIRECTORY_SEPARATOR : null;
        
        return $path;
    }
    
    /**
    * Creates/updates product directory/file structure.
    * This function should also be used to update template image / archive.
    */
    public function uploadTemplateFiles($id, $zipPath, $imgPath)
    {
        // Get template directory path or create it
        $path = $this->getTemplatePath($id, 'files');
        
        // Zip upload
        if (is_file($zipPath))
        {
            rename($zipPath, $path . $this->templateFile);
        }
        
        // Image upload
        if (is_file($imgPath))
        {
            // Load uploaded image
            $srcImage = imagecreatefromstring(file_get_contents($imgPath));
            
            // Get image extensions
            $bigImageExt = strtolower(pathinfo($path . $this->templateImageFile, PATHINFO_EXTENSION));
            $smallImageExt = strtolower(pathinfo($path . $this->templateThumbFile, PATHINFO_EXTENSION));
            
            // Get image save functions
            $saveBig = isset($this->_functions[$bigImageExt]) ? $this->_functions[$bigImageExt] : 'imagejpeg';
            $saveSmall = isset($this->_functions[$smallImageExt]) ? $this->_functions[$smallImageExt] : 'imagejpeg';
            
            // Create small preview from a big image
            if (is_array($this->templateThumbSize))
            {
                list($srcWidth, $srcHeight) = getimagesize($imgPath);
                list($dstWidth, $dstHeight) = $this->templateThumbSize;
                $tmpWidth = $dstWidth; $tmpHeight = $dstWidth * $srcHeight / $srcWidth;
                $dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $srcWidth, $srcHeight);
                $saveSmall($dstImage, $path . $this->templateThumbFile/*, $this->imageQuality*/);
            }
            else
                $saveSmall($srcImage, $path . $this->templateThumbFile/*, $this->imageQuality*/);
            
            // Create big preview from a big image
            if (is_array($this->templateImageSize))
            {
                list($srcWidth, $srcHeight) = getimagesize($imgPath);
                list($dstWidth, $dstHeight) = $this->templateImageSize;
                $tmpWidth = $dstWidth; $tmpHeight = $dstWidth * $srcHeight / $srcWidth;
                $dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $srcWidth, $srcHeight);
                $saveBig($dstImage, $path . $this->templateImageFile/*, $this->imageQuality*/);
            }
            else
                $saveBig($srcImage, $path . $this->templateImageFile/*, $this->imageQuality*/);

            // Remove uploaded image
            @unlink($imgPath);
        }

        return true;
    }
    
    /**
    * Removes product directory/file structure.
    * @param integer Template ID.
    */
    public function removeTemplateFiles($id, $zip = true, $img = true)
    {
        if (is_null($path = $this->getTemplatePath($id)))
            throw new CException('Template directory does not exist: %s', $id);
        
        $result = true;
        
        if ($zip) $result = $result && @unlink($path . $this->templateFile);
        if ($img) $result = $result && @unlink($path . $this->templateImageFile);
        if ($img) $result = $result && @unlink($path . $this->templateThumbFile);
        if ($img && $zip) $result = $result && @rmdir($path);
        
        return $result;
    }
    
    /**
    * Downloads the template archive.
    * @param integer Template ID.
    */
    public function downloadTemplate($id)
    {
        if (is_null($path = $this->getTemplatePath($id)))
            throw new CException('Template directory not found: %s', $id);
        
        if (!is_file($file = $path . $this->templateFile))
            throw new CException('Template archive not found: %s', $file);
        
        header('Content-Length: ' . filesize($file));
        header('Content-Type: application/zip');
        header('Content-Transfer-Encoding: Binary'); 
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        
        readfile($file);
    }
    
    /**
    * @return array The list of products currently on sale.
    */
    public function getTemplateFiles($checkFiles = true)
    {
        $list = array();
        $path = $this->templatePath . DIRECTORY_SEPARATOR . '*';
        $dirs = glob($path, GLOB_ONLYDIR | GLOB_NOSORT);
        foreach ($dirs as $dir)
        {
            $dirFullPath = $dir;
            $dirRelative = str_replace($this->templatePath . DIRECTORY_SEPARATOR, '', $dir);
            
            if (preg_match('/\d{3}/', $id = substr($dirRelative, 0, 3)))
            {
                $id = intval($id);
                
                $list[$id]['relativePath'] = $dirRelative;
                $list[$id]['absolutePath'] = $dirFullPath;
                
                $list[$id]['tplPath'] = !$checkFiles || is_file($dirFullPath . '/' . $this->templateFile) ? $dirRelative . '/' . $this->templateFile : null;
                $list[$id]['imagePath'] = !$checkFiles || is_file($dirFullPath . '/' . $this->templateImageFile) ? $dirRelative . '/' . $this->templateImageFile : null;
                $list[$id]['thumbPath'] = !$checkFiles || is_file($dirFullPath . '/' . $this->templateThumbFile) ? $dirRelative . '/' . $this->templateThumbFile : null;
            }
        }
        
        return $list;
    }
}