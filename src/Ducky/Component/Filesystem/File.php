<?php

/*
 * This file is part of the Ducky package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ducky\Component\Filesystem;

class File extends \SplFileObject
{
    const CHUCK = 4096;

    public function __construct($filename, $openMode = "rb", $useIncludePath = false, $context = null)
    {
        parent :: __construct($filename, $openMode, $useIncludePath, $context); 

        clearstatcache();
    }
    
    /**
     * @startline int
     * @endline   int
     * @return array
     */
    public function getlines($startline, $length = -1) 
    {
        $lines = [];
        $this->seek($startline - 1); 

        while ($this->valid()) {
            $lines[] = $this->current();
            $this->next();
            if ($length > 0) {
                $length--; 
            }
            if ($length == 0) break;
        }
        return $lines;
    }

    /**
     * @param int $num
     * @return array
     */
    public function tailline($num = 10)
    {
        $pos = -2;
        $lines = [];
        while (($num > 0) && $this->fseek($pos--, SEEK_END) == 0) {
            $char = $this->fgetc();
            if ($char == "\n") {
                $lines[--$num] = $this->fgets();
            }
        }
        return $lines;
    }

    public function tail($num = 10)
    {
        $len = 0;
        $lines = [];
        $readline = 0; 

        $max = $this->getMaxFilesize();
        $seekSize = $num <= 10 ? 1024 : ($num > 100 ? 8192 : 4096); 

        $this->fseek(0, SEEK_END);

        while ($readline <= $num && $len < $max) {
            $seekSize = ($max - $len > $seekSize) ? $seekSize : $max - $len;
            $len += $seekSize;

            // #1
            if (0 !== $this->fseek($seekSize * -1, SEEK_CUR)) {
                throw new \Exception(sprintf("A source is not seekable: %s", $this->getFilename()));
            }

            $readline += substr_count($this->fread($seekSize), "\n"); 

            // fread 读取数据后，指针已经移动了$seekSize个, 所以如果要读取，必须移回来
            // 是否加下面这行，取决于 #1 的写法
            $this->fseek($seekSize * -1, SEEK_CUR);

            if ($readline >= $num - 1) {
                while ($this->valid()) {
                    $lines[] = $this->current(); 
                    $this->next();
                }
                $this->fseek(0);
                break;
            }
        }
        return array_slice($lines, -1 * ($num + 1), $num);
    }
    
    public function getMaxFilesize()
    {
        $filesize = $this->getFilesize();
        return (intval($filesize) == PHP_INT_MAX) ? PHP_INT_MAX : $filesize;       
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
