<?php
namespace lib;

class RegenerateCsv
{
    private $objFP = null;
    private $strDir = null;
    private $strDestFile = null;

    public function __construct($strDir, $strDestFile) {
        $this->strDir = rtrim($strDir, "/");
        $this->strDestFile = $this->strDir . "/" . $strDestFile;

        $this->strStagingFile = $this->strDir . "/staging_" . $strDestFile;
    }

    public function init() {
        $this->objFP = fopen($this->strStagingFile, "w");
    }

    public function close() {
        fclose($this->objFP);

        $this->objFP = null;
    }

    public function appendRow($arrRow) {
        if(empty($this->objFP)) {
            throw new \Exception("file must be initiated prior to appending data");
        }

        fputcsv($this->objFP, $arrRow);
    }

    public function save() {
        unlink($this->strDestFile);

        rename($this->strStagingFile, $this->strDestFile);
    }
}

?>
