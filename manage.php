<?php
define("BASE_PATH", pathinfo(__FILE__)["dirname"]);
include(BASE_PATH . "/auto_loader.php");

parse_str(implode('&', array_slice($argv, 1)), $arrArgs);

foreach($arrArgs as $key=>$value) {
    $strModKey = null;
    
    if(substr($key, 0, 2) == "--") {
        $strModKey = substr($key, 2);
    } else if(substr($key, 0, 1) == "-") {
        $strModKey = substr($key, 1);
    }

    if(!empty($strModKey)) {
        unset($arrArgs[$key]);
        
        $arrArgs[$strModKey] = $value;
    }
}

$strAction = null;
if(isset($arrArgs["action"])) {
    $arrAction = explode("_", strtolower($arrArgs["action"]));

    $strAction = array_shift($arrAction);

    $strAction .= str_replace(" ", "", ucwords(implode(" ", $arrAction)));
}

$objFileManipulation = new FileManipulation($arrArgs);

if(!empty($strAction) && method_exists($objFileManipulation, $strAction)) {
    echo $objFileManipulation->{$strAction}();

    if(isset($arrArgs["show_memory"])) {
        echo "\n\n" . convert(memory_get_peak_usage()) . "\n";
    }
}

class FileManipulation
{
    public $arrArgs = array();

    public function __construct($arrArgs) {
        $this->arrArgs = $arrArgs;
    }

    public function countLines() {
        $strReturn = "";

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            $strReturn .= $strFile . " - lines:";

            $strReturn .= (string)$this->getFileLineCount(BASE_PATH . "/data/" . $strFile) . "\n";
        }

        return $strReturn;
    }

    public function countColumns() {
        $strReturn = "";

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            $strReturn .= $strFile . " - columns:";

            $strReturn .= (string)count(str_getcsv(exec(escapeshellcmd("head -n 1 " . BASE_PATH . "/data/" . $strFile)))) . "\n";
        }

        return $strReturn;
    }

    public function removeByPhone() {
        if(!isset($this->arrArgs["phone"])) {
            throw new \Exception("phone number must be provided in order to remove a record by phone number");
        }

        $arrPhoneReplace = array(".", "-", "(", ")", "_", " ");
        $arrHeader = null;
        $csvData = null;

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            $objRegenFile = new \lib\RegenerateCsv(BASE_PATH . "/data/", $strFile);
            $objRegenFile->init();

            if(($fp = fopen(BASE_PATH . "/data/" . $strFile, 'r')) !== false)
            {
                $arrHeader = fgetcsv($fp);

                $objRegenFile->appendRow($arrHeader);

                while(($arrData = fgetcsv($fp)) !== false)
                {
                    $csvData = array_combine($arrHeader, $arrData);

                    if(str_replace($arrPhoneReplace, "", $csvData["Phone"]) != str_replace($arrPhoneReplace, "", $this->arrArgs["phone"])) {
                        $objRegenFile->appendRow($csvData);
                    }
                }
                fclose($fp);
            }

            $objRegenFile->close();

            $objRegenFile->save();
        }
    }

    public function addRowsByJson() {
        if(!isset($this->arrArgs["json"])) {
            throw new \Exception("json data representing a new row must be provided");
        }
        
        $arrJson = json_decode($this->arrArgs["json"], true);

        if(empty($arrJson)) {
            throw new \Exception("json formatting is incorrect");
        }

        if(!isset($arrJson[0])) {
            $arrJson = array($arrJson);
        }

        $arrPhoneReplace = array(".", "-", "(", ")", "_", " ");
        $arrHeader = null;

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            if(($fp = fopen(BASE_PATH . "/data/" . $strFile, 'r+')) !== false)
            {
                $arrHeader = fgetcsv($fp);

                fseek($fp, 0, SEEK_END);

                foreach($arrJson as $arrNewRowJson) {
                    $arrNewRow = array();

                    foreach($arrHeader as $strValue) {
                        if(empty($arrNewRowJson[$strValue])) {
                            $strNewValue = null;
                        } else {
                            $strNewValue = $strValue == "Phone"?str_replace($arrPhoneReplace, "", $arrNewRowJson[$strValue]):$arrNewRowJson[$strValue];
                        }

                        $arrNewRow[] = $strNewValue;
                    }

                    fputcsv($fp, $arrNewRow);    
                }

                fclose($fp);
            }

            break;
        }
    }

    public function search() {
        if(!isset($this->arrArgs["phone"])) {
            throw new \Exception("at least a partial phone number must be provided in order to search data");
        }

        $arrPhoneReplace = array(".", "-", "(", ")", "_", " ");
        $arrHeader = null;
        $arrResults = array();

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            if(($fp = fopen(BASE_PATH . "/data/" . $strFile, 'r')) !== false)
            {
                $arrHeader = fgetcsv($fp);

                $intPhonePos = array_flip($arrHeader)["Phone"];

                while(($arrData = fgetcsv($fp)) !== false)
                {
                    if(strpos(str_replace($arrPhoneReplace, "", $arrData[$intPhonePos]), str_replace($arrPhoneReplace, "", $this->arrArgs["phone"])) !== false) {
                        $arrResults[] = array_combine($arrHeader, $arrData);
                    }
                }

                fclose($fp);
            }
        }

        return json_encode($arrResults);
    }

    public function updateRowWithPhoneAndJson() {
        if(!isset($this->arrArgs["json"])) {
            throw new \Exception("json data with updated data is required");
        }

        if(!isset($this->arrArgs["phone"])) {
            throw new \Exception("phone is required for search");
        }
        
        $arrPhoneReplace = array(".", "-", "(", ")", "_", " ");
        $arrHeader = null;
        $csvData = null;

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            $objRegenFile = new \lib\RegenerateCsv(BASE_PATH . "/data/", $strFile);
            $objRegenFile->init();

            if(($fp = fopen(BASE_PATH . "/data/" . $strFile, 'r')) !== false)
            {
                $arrHeader = fgetcsv($fp);

                $objRegenFile->appendRow($arrHeader);

                while(($arrData = fgetcsv($fp)) !== false)
                {
                    $csvData = array_combine($arrHeader, $arrData);

                    if(str_replace($arrPhoneReplace, "", $csvData["Phone"]) == str_replace($arrPhoneReplace, "", $this->arrArgs["phone"])) {
                        foreach(json_decode($this->arrArgs["json"], true) as $strKey=>$strValue) {
                            $csvData[$strKey] = $strValue;
                        }
                    }

                    $objRegenFile->appendRow($csvData);
                }
                fclose($fp);
            }

            $objRegenFile->close();

            $objRegenFile->save();
        }
    }

    public function mergeFiles() {
        $objManageDups = new \lib\ManageDups(BASE_PATH . "/phone_cache/");

        $arrPhoneReplace = array(".", "-", "(", ")", "_", " ");
        $arrHeader = null;
        $csvData = null;
        $intFileLineCount = 0;
        $strLargestFile = "";
        $arrIncludedFiles = array();

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if($strFile == ".." || $strFile == ".") {
                continue;
            }

            $arrIncludedFiles[$strFile] = 1;

            $intLoopLineCount = $this->getFileLineCount(BASE_PATH . "/data/" . $strFile);

            if($intLoopLineCount > $intFileLineCount) {
                $intFileLineCount = $intLoopLineCount;
                $strLargestFile = $strFile;
            }
        }

        $objRegenFile = new \lib\RegenerateCsv(BASE_PATH . "/data/", $strLargestFile);
        $objRegenFile->init();

        foreach(scandir(BASE_PATH . "/data") as $strFile) {
            if(!isset($arrIncludedFiles[$strFile])) {
                continue;
            }

            if(($fp = fopen(BASE_PATH . "/data/" . $strFile, 'r')) !== false)
            {
                $arrHeader = fgetcsv($fp);

                $objRegenFile->appendRow($arrHeader);

                while(($arrData = fgetcsv($fp)) !== false)
                {
                    $csvData = array_combine($arrHeader, $arrData);

                    if(!$objManageDups->isDup($csvData["Phone"])) {
                        $objRegenFile->appendRow($csvData);
                    }
                }
                fclose($fp);
            }

            if($strFile != $strLargestFile) {
                unlink(BASE_PATH . "/data/" . $strFile);
            }
        }

        $objRegenFile->close();

        $objRegenFile->save();
    }

    private function getFileLineCount($strFileName) {
        $objSFO = new SplFileObject($strFileName);
        $objSFO->seek(PHP_INT_MAX);

        return $objSFO->key();
    }
}

//pulled from php.net manual to convert memory usage to a readable format
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
