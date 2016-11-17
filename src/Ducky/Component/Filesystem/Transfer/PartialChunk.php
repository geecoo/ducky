<?php


namespace Ducky\Component\Filesystem\Transfer;

use Ducky\Component\Filesystem\Transfer\Chunk as Chunk;

class PartialChunk extends Chunk
{
    private $ranges = [];
    
    public function prepareb()
    {
        $this->parseHeaders();
        $this->createHandler();
        $this->closeBufferSetting();
        $this->sendHeader();
        $this->sendPreHeader();
        $this->cleanBuffer();
   }

    public function sendHeader()
    {
        $filesize = $this->metas['Content-Length'];
        $rangeSize = ($filesize - $this->startSeek) > 0 ?  ($filesize - $this->startSeek) : 0; 
        
        header('HTTP/1.1 206 Partial Content');
        header('Content-Length: ' .  $rangeSize);
        header(sprintf("Content-Range: bytes %s-%s/%s", $this->startSeek, $filesize, $filesize)); 

        //header(sprintf("Content-Range: bytes %s-%s/%s", 0, $filesize-1, $filesize)); 

    }

    public function transfer()
    {
        fseek($this->fp, $this->startSeek);

        $chunk = 8192;

        while (!feof($this->fp) && ($len = ftell($this->fp)) < $this->endSeek && connection_status() === 0
        ) { 

            if ($len >= ($this->endSeek - $chunk)) {
                $chunk = $this->endSeek - $len;
            } 

            echo fread($this->fp, $chunk);

            ob_flush();
            flush();
        }
        fclose($this->fp);
        //$this->endPacket();       
    }

    /**
     * 解析头元信息
     */
    public function parseHeaders()
    {
        try {
            $this->metas = get_headers($this->filepath, 1);
        } catch (\Exception $e) {
            throw $e;
        }
        
         
        $this->endSeek = $this->metas['Content-Length'];

        $this->parseHttpRange();

        if (!$this->attachName) {
            $this->attachName = basename($this->filepath);
        }
    }
    
    public function parseHttpRange()
    {
        if(!empty($_SERVER['HTTP_RANGE'])) {
            list($range) = explode('-',(str_replace('bytes=', '', $_SERVER['HTTP_RANGE']))); 
            $this->startSeek = intval($range);
        }
    }

    /**
     * 解析Range头
     * Bytes=500-
     * Bytes=-500
     * Bytes=100-500
     * Bytes=100-500, 600-800 (指定多段)
     * 本库忽略多头的行为，只处理第一段
     */
    public function parseHttpRange2()
    {
        if (!isset($_SERVER['HTTP_RANGE'])) {
            return;
        }

        if (!preg_match('/^bytes=\d*-\d*(,\s*\d*-\d*)*$/', $_SERVER['HTTP_RANGE'])) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            exit;
        }

        $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6)); 

        foreach ($ranges as $range) { 
            $parts = explode('-', $range);
            $start = $parts[0];
            $end   = $parts[1];

            if (empty($end) || $end > $this->metas['Content-Length'] - 1) {
                $end = $this->metas['Content-Length'] - 1;
            }

            if ($start > $end) { 
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                exit;
            }
            
            //$this->ranges[] = ['start' => $start, 'end' => $end];

            $this->startSeek = $start;
            $this->endSeek   = $end;
            break;
        }

    }
}
