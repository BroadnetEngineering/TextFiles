<?php
namespace lib;

class ManageDups
{
    private $strCacheDir = null;

    public function __construct($strCacheDir) {
        $this->strCacheDir = rtrim($strCacheDir, "/") . "/";

        if(!file_exists($this->strCacheDir)) {
            mkdir($this->strCacheDir);
        }
    }

    public function __destruct() {
        if(file_exists($this->strCacheDir)) {
            if(strpos($this->strCacheDir, "/phone_cache") === false) {
                throw new \Exception("phone cache dir must include phone_cache in order to recursively delete");
            } else {
                exec("rm -rf " . $this->strCacheDir);
            }
        }
    }

    public function isDup($strNumber) {
        $strAC = substr($strNumber, 0, 3);
        $strEC = substr($strNumber, 3, 3);
        $strLine = substr($strNumber, 6);
        $blnDup = true;

        if(!file_exists($this->strCacheDir . $strAC)) {
            mkdir($this->strCacheDir . $strAC);
            $blnDup = false;
        }

        if(!file_exists($this->strCacheDir . $strAC . "/" . $strEC)) {
            mkdir($this->strCacheDir . $strAC . "/" . $strEC);
            $blnDup = false;
        }

        if(!file_exists($this->strCacheDir . $strAC . "/" . $strEC . "/" . $strLine)) {
            touch($this->strCacheDir . $strAC . "/" . $strEC . "/" . $strLine);
            $blnDup = false;
        }

        return $blnDup;
        
    }
}

?>
