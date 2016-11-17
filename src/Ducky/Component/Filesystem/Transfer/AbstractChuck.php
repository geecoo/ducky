<?php

/**
 *  
 * 
 */

namespace Ducky\Component\Filesystem\Transfer;

abstract class AbstractChuck
{
    /**
     * 文件路径
     */
    private $filepath;

    /**
     * 附件名称
     */
    private $attachName;

    private $metas = [];

    /**
     * 获取头元信息
     */
    public function getHeaders()
    {
        echo "获取头信息 get_headers... \n";    
    } 


} 
