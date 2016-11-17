<?php

/*
 * This file is part of the Ducky package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ducky\Component\Filesystem;

use Ducky\Component\Filesystem\Transfer\TransferFactory as TransferFactory; 

class Download
{
    /**
     * 实际处理下载的源
     */
    private $adaptee;

    
    public function __construct($filepath, $attachname = null)
    {
        $factory = new TransferFactory();
        $this->adaptee = $factory->getAdaptee($filepath, $attachname);     
    } 
    

    public function transfer()
    {
        $this->adaptee->prepare();
        $this->adaptee->transfer(); 
    }
}
