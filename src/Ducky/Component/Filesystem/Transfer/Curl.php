<?php

namespace Ducky\Component\Filesystem\Transfer;

class Curl
{
    public function __construct()
    {
        
    }

    public function prepare()
    {
        echo "准备工作开始.. \n";     
        echo "Prepare finished. \n";
    }

    public function sendHeader()
    {
        echo "send header ... \n"; 
    }

    public function transfer()
    {
        echo "transfer ... \n"; 
    }
}
