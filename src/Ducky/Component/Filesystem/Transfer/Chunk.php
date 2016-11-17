<?php


namespace Ducky\Component\Filesystem\Transfer;

class Chunk extends AbstractChuck 
{
    /**
     * 文件路径
     */
    protected $filepath;

    /**
     * 附件名称
     */
    protected $attachName;
    
    /**
     * header 元信息
     */
    protected $metas = [];
    
    protected $startSeek = 0;

    protected $endSeek;

    public function __construct($filepath = null, $attachname = null)
    {
        $this->filepath = $filepath; 
        $this->attachName = $attachname;
    } 

    public function prepare()
    {
        $this->parseHeaders();
        $this->createHandler();
        $this->closeBufferSetting();
        $this->sendHeader();
        $this->sendPreHeader();
        $this->cleanBuffer();
    }
    
    public function createHandler()
    {
        try {
            $this->fp = fopen($this->filepath, "rb"); 
        } catch (\Exception $e) {
            throw $e;
        }
    } 

    public function closeBufferSetting()
    {
        ini_set('output_buffering', 'Off');
        ini_set('zlib.output_compression', 'Off');                  
    }

    public function cleanBuffer()
    {
        ob_clean();
        flush(); 
    }
    
    public function sendHeader()
    {
        header('HTTP/1.1 200 OK'); 
        header('Content-Length: ' . $this->metas['Content-Length']);
        echo "HHHH";
    }

    public function sendPreHeader()
    {
        //header("Cache-control: public");
        //header("Pragma: public");

        header('Last-Modified: '  . $this->metas['Last-Modified']);
        header('Content-Type: '   . 'application/octet-stream');
        header('ETag: ' . $this->metas['ETag']);
        header('Content-Transfer-Encoding: binary');
        header("Content-Description: File Transfer");
        header("Accept-Ranges: bytes");

        $disposition = 'attachment';
        if (strpos($this->metas['Content-Type'], 'image/') !== false) {
            $disposition = 'inline';
        }

        header(sprintf('Content-Disposition: %s; filename="%s"', $disposition, rawurlencode($this->attachName)));
    }

    /**
     * 客户端关闭停止传输
     */
    public function transfer()
    {
        $chunk = 8192;

        while (!feof($this->fp) && 
            ($len = ftell($this->fp)) < $this->endSeek && 
            connection_status() === 0
        ) { 

            if ($len >= ($this->endSeek - $chunk)) {
                $chunk = $this->endSeek - $len;
            } 

            echo fread($this->fp, $chunk);

            ob_flush();
            flush();
        }
        $this->endPacket();
    }

    public function endPacket()
    {
        echo "0\r\n\r\n";
        ob_flush();
        flush();
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
        $this->startSeek = 0;

        if (!$this->attachName) {
            $this->attachName = basename($this->filepath);
        }
    }
}
