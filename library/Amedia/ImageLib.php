<?php

class Amedia_ImageLib
{
    private static $instance;
    private $_sourceFile;
    private $_sourceFileName;
    private $_destinationFile;
    private $_destinationWidth;
    private $_destinationHeight;
    private $_resizedName;

    private function __construct()
    {
        return $this;
    }

    public static function getInstance()
    {
        if ( ! self::$instance instanceof self)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function setSourceFile($sourceFile, $sourceFileName)
    {
        $this->_sourceFile = $sourceFile;
        $this->_sourceFileName = $sourceFileName;
        return $this;
    }

    public function setDestinationFile($destinationFile)
    {
        $this->_destinationFile = $destinationFile;
        return $this;
    }

    public function setDestinationWidth($width)
    {
        $this->_destinationWidth = $width;
        return $this;
    }

    public function setDestinationHeight($height)
    {
        $this->_destinationHeight = $height;
        return $this;
    }

    public function resize($size = '92x69', $quality = 80)
    {
        $sizeArray = explode('x', $size);
        $this->setDestinationWidth($sizeArray[0]);
        $this->setDestinationHeight($sizeArray[1]);

        $originalName = $this->_sourceFileName;
        $fileArray = explode('.', $originalName);

        if (file_exists($this->_sourceFile))
        {
            if ( ! is_dir('uploads/' . $size))
            {
                if ( ! mkdir('uploads/' . $size))
                {
                    return FALSE;
                }
            }
        }
        if(!isset($fileArray[1])){
            $this->_resizedName = $size . '/' . $fileArray[0] . '_' . $size . '.' . $fileArray[0];            
        } else {
            $this->_resizedName = $size . '/' . $fileArray[0] . '_' . $size . '.' . $fileArray[1];
        }

        $this->setDestinationFile(str_replace($originalName, $this->_resizedName, $this->_sourceFile));

        if ( ! file_exists($this->_destinationFile))
            $this->resize_image($this->_sourceFile, $this->_destinationFile, $this->_destinationWidth, $this->_destinationHeight, $quality);

        return $this;
    }

    public function getUrl()
    {
        return '/uploads/' . $this->_resizedName;
    }

    /**
     * Resize and Crop the images from the center making sure to actually fit the destination width/height
     *
     * @name 	resize_image
     * @access 	public
     * @param 	string $source_file
     * @param 	string $dest_file
     * @param 	int    $dest_width
     * @param 	int    $dest_height
     * @param 	int    $jpg_quality
     * @return 	boolean/array
     */
    private function resize_image($source_file, $dest_file, $dest_width, $dest_height, $jpg_quality = 80)
    {

        $result = array();

        if ( ! file_exists($source_file))
            return FALSE;

        if ( ! function_exists('getimagesize'))
            return FALSE;

        else list($src_width, $src_height, $file_type, ) = getimagesize($source_file);

        switch ($file_type)
        {

            case 1 :
                $src_handler = imagecreatefromgif($source_file);
                break;

            case 2 :
                $src_handler = imagecreatefromjpeg($source_file);
                break;

            case 3 :
                $src_handler = imagecreatefrompng($source_file);
                break;

            default :
                return FALSE;

        }

        if ( ! $src_handler) return FALSE;

        // Defining Shape
        if ($src_height < $src_width)
        {

        // Source has a horizontal Shape
            $ratio = (double)($src_height / $dest_height);
            $copy_width = round($dest_width * $ratio);

            if ($copy_width > $src_width)
            {

                $ratio = (double)($src_width / $dest_width);
                $copy_width = $src_width;
                $copy_height = round($dest_height * $ratio);
                $x_offset = 0;
                $y_offset = round(($src_height - $copy_height) / 2);

            }
            else
            {

                $copy_height = $src_height;
                $x_offset = round(($src_width - $copy_width) / 2);
                $y_offset = 0;

            }

        }
        else
        {

        // Source has a Vertical Shape
            $ratio = (double)($src_width / $dest_width);

            $copy_height = round($dest_height * $ratio);

            if ($copy_height > $src_height)
            {

                $ratio = (double)($src_height / $dest_height);
                $copy_height = $src_height;
                $copy_width = round($dest_width * $ratio);
                $x_offset = round(($src_width - $copy_width) / 2);
                $y_offset = 0;

            }
            else
            {

                $copy_width = $src_width;
                $x_offset = 0;
                $y_offset = round(($src_height - $copy_height) / 2);

            }
        }

        // Let's figure it out what to use
        if (function_exists('imagecreatetruecolor'))
        {

            $create	= 'imagecreatetruecolor';
            $copy	= 'imagecopyresampled';

        }
        else
        {

            $create	= 'imagecreate';
            $copy	= 'imagecopyresized';

        }

        $dst_handler = $create($dest_width, $dest_height);

        $copy($dst_handler, $src_handler, 0, 0, $x_offset, $y_offset, $dest_width, $dest_height, $copy_width, $copy_height);

        switch ($file_type)
        {
            case 1 :
                @imagegif($dst_handler, $dest_file);
                $result['status']  = TRUE;
                $result['message'] = 'GIF image created successfully.';
                break;

            case 2 :
            // Code taken from CI Image_lib Library
            // PHP 4.4.1 bug #35060 - workaround
                if (phpversion() == '4.4.1') @touch($dest_file);
                @imagejpeg($dst_handler, $dest_file, $jpg_quality);
                $result['status']  = TRUE;
                $result['message'] = 'JPG image created successfully.';
                break;

            case 3 :
                @imagepng($dst_handler, $dest_file);
                $result['status']  = TRUE;
                $result['message'] = 'PNG image created successfully.';
                break;

            default :
                return FALSE;
        }

        //  Kill the file handlers
        imagedestroy($src_handler);
        imagedestroy($dst_handler);

        // Set the file to 777 -- From CI Image_lib Library
        chmod($dest_file, 0777);

        return $result;

    }
}