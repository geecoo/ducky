<?php

/*
 * This file is part of the Ducky package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ducky\Component\Filesystem;

class Filesystem extends \SplFileObject
{
    private $chunk = 4096;

    public function __construct(
        string $filename, string $openMode = "rb", bool $useIncludePath = false, resource $context = null
    )
    {
        parent :: __construct($filename, openMode, $useIncludePath, $context); 

    }

    public function tail($num = 10)
    {
        $readString = null;

        $fs = $this->getFilesize();
        
        // more than PHP_INT_MAX value has no meaning
        $max = (intval($fs) == PHP_INT_MAX) ? PHP_INT_MAX : $fs;

        for ($len = 0; $len < $max; $len += $this->chunk) {
            $seekSize = ($max - $len > $this->chunk) ? $this->chunk : $max - $len;
            $this->fseek(($len + $seekSize) * -1, SEEK_END); 
            $readString = $this->fread($seekSize) . $readString;

            if (substr_count($readString, "\n") >= $num + 1) {
                preg_match("!(.*?\n){" . ($num) . "}$!", $readString, $match);
                return $match[0]; 
            }
        }
        return null;
    }
    
    public function getLines()
    {
    
    }

    public function getFilesize()
    {
        $size = $this->getSize();
        if ($size < 0) {
            if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
                $size = trim(`stat -c%s $file`);
            } else {
                $fsobj = new COM("Scripting.FileSystemObject");
                $f = $fsobj->GetFile($file);
                $size = $fsobj->Size;
            }
        }

        return sprintf("%u", $size);
    }
}
