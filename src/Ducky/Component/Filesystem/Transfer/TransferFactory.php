<?php


namespace Ducky\Component\Filesystem\Transfer;

final class TransferFactory
{
    public function getAdaptee($filepath, $attachName = null)
    {
        if (isset($_SERVER['HTTP_RANGE'])) {
            return new PartialChunk($filepath, $attachName); 
        }
        return new Chunk($filepath, $attachName); 
    }
}
