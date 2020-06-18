<?php
//start
showmenu();

//Classes
class Csvloader {
    public $filename;

    function __construct($filename){
        //load, validate the csv file
        $this->filename = $filename;
    }

    function rowcount(){
        $rows = array_map('str_getcsv', file($this->filename));
        $header = array_shift($rows);
        $csv = array();
        foreach ($rows as $row) {
          $csv[] = array_combine($header, $row);
        }
        return  "\r\n\r\nThere are ".count($csv)." records in the CSV file you selected. Press enter to continue.\r\n";
    }

    function columncount(){
        $rows = array_map('str_getcsv', file($this->filename));
        $header = array_shift($rows);
        return  "\r\n\r\nThere are ".count($header)." columns in the CSV file you selected. Press enter to continue.\r\n";
    }

    function removerow($searchterm){
        if(strlen($searchterm) != 10){
            echo "Please enter 10 digits. Press enter to return to Main Menu.";
            $fselect2 = fopen("php://stdin","r");
            fgets($fselect2);
            fclose($fselect2);
            showmenu();
        }
        $rows = array_map('str_getcsv', file($this->filename));
        $temphandle = fopen(__DIR__."/data/tempfile.csv", "w");
        $header = array_shift($rows);
        $sendline = "";
        foreach($header as $hvalue){
            $sendline = $sendline.$hvalue.",";
        }
        $sendline = substr($sendline, 0, -1);
        $sendline = $sendline."\r\n"; 
        fwrite($temphandle, $sendline);
        foreach ($rows as $row) {
            if($row[0] != $searchterm){
                $sendline = "";
                foreach($row as $value){
                    $sendline = $sendline.$value.",";
                }
                $sendline = substr($sendline, 0, -1);
                $sendline = $sendline."\r\n"; 
                fwrite($temphandle, $sendline);
            }
        }
        fclose($temphandle);
        rename(__DIR__."/data/tempfile.csv", $this->filename);
        return "Records matching the phone number ".$searchterm." have been removed. Press enter to return to main menu.\r\n";
    }

    function addrow($newrow){
        $sendline = "";
        foreach($newrow as $row){
            $sendline = $sendline.$row.",";
        }
        $sendline = substr($sendline, 0, -1);
        $sendline = $sendline."\r\n";
        $handle = fopen($this->filename, "a");
        fwrite($handle, $sendline);
        fclose($handle);
    }

    function searchphone($phonenumber){
        $rows = array_map('str_getcsv', file($this->filename));
        $header = array_shift($rows);
        foreach ($rows as $row) {
            if($row[0] == $phonenumber){
                $recordarray = array_combine($header, $row);
                foreach($recordarray as $key=>$value){
                    echo $key.": ".$value."\r\n";
                }
            }
        }
        echo "\r\nTo return to the main menu, press enter.";
    }

    function modifyrecord($phonenumber){
        echo "\r\nEnter any new values. To maintain the original value (in brackets), press Enter.\r\n";
        $rows = array_map('str_getcsv', file($this->filename));
        $header = array_shift($rows);
        foreach ($rows as $row) {
            if($row[0] == $phonenumber){
                $recordarray = array_combine($header, $row);
                $newline = array();
                foreach($recordarray as $key=>$value){
                    echo $key." [".$value."]: ";
                    $fselect = fopen ("php://stdin","r");
                    $fvalue = fgets($fselect);
                    if(strlen($fvalue) < 2){
                        $newval = $value;
                    }
                    else{
                        $newval = trim($fvalue);
                    }
                    $newline[] = $newval;
                    echo "\r\n";
                }
                $this->removerow($phonenumber);
                $this->addrow($newline);
            }
        }
        echo "\r\nTo return to the main menu, press enter.";
    }
}

//Functions
function csvselect(){
    echo "The following files are available. Please select one:\r\n\r\n";
    $directorycsv = __DIR__."/data/*.csv";
    $folder = glob($directorycsv);
    $ioptions = array();
    $i = 0;
    foreach($folder as $file){
        $i++;
        echo $i.".) ".$file."\r\n";
        $ioptions[$i] = 1;
    }
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    $filechoice = (trim($fchoice)*1 - 1);
    if(array_key_exists(trim($fchoice), $ioptions)){
        $filename = $folder[$filechoice];
        return $filename;
    }
    else{
        fclose($fselect);
        echo "Please select a valid option. Press enter to return to Main Menu.";
        $fselect2 = fopen("php://stdin","r");
        fgets($fselect2);
        fclose($fselect2);
        showmenu();
    }
}


function showmenu(){
    echo "________________________________\r\nWelcome to Text File Manipulation\r\n\r\nPlease select an option below:\r\n\r\n1.) Count Rows in File\r\n2.) Count Columns in File\r\n3.) Remove a Record\r\n4.) Add a Record\r\n5.) Search Records\r\n6.) Modify a Record\r\n7.) Merge Files\r\n8.) Exit\r\n";
    $mselect = fopen ("php://stdin","r");
    $mchoice = fgets($mselect);

    if($mchoice == 1){
        countcsvrows(csvselect());
    }
    else if($mchoice == 2){
        countcsvcols(csvselect());
    }
    else if($mchoice == 3){
        removerow(csvselect());
    }
    else if($mchoice == 4){
        addarow(csvselect());
    }
    else if($mchoice == 5){
        searchbyphone(csvselect());
    }

    else if($mchoice == 6){
        modifyarecord(csvselect());
    }

    else if($mchoice == 7){
        mergefiles();
    }

    else if($mchoice == 8){
        die();
    }

    else{
        echo "\r\n\r\nPlease make a valid selection\r\n";
        showmenu();
    }
}

function countcsvrows($filename){
    $csvmod = new Csvloader($filename);
    echo $csvmod->rowcount();
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}

function countcsvcols($filename){
    $csvmod = new Csvloader($filename);
    echo $csvmod->columncount();
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}

function removerow($filename){
    echo "\r\nTo Remove a record, please enter the exact phone number of the record to be removed: ";
    $fselect = fopen ("php://stdin","r");
    $searchbp = fgets($fselect);
    $csvmod = new Csvloader($filename);
    echo $csvmod->removerow(trim($searchbp));
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();

}

function addarow($filename){
    $csvmod = new Csvloader($filename);
    $newline = array();
    echo "\r\nTo add a row to this file, please input the following values: \r\n";
    echo "Phone Number: ";
    $fselect = fopen ("php://stdin","r");
    $fphone = fgets($fselect);
    $newline[] = trim($fphone);
    echo "\r\n";
    echo "Last Name: ";
    $fselect = fopen ("php://stdin","r");
    $flname = fgets($fselect);
    $newline[] = trim($flname);
    echo "\r\n";
    echo "First Name: ";
    $fselect = fopen ("php://stdin","r");
    $ffname = fgets($fselect);
    $newline[] = trim($ffname);
    echo "\r\n";
    echo "Title: ";
    $fselect = fopen ("php://stdin","r");
    $ftitle = fgets($fselect);
    $newline[] = trim($ftitle);
    echo "\r\n";
    echo "Address: ";
    $fselect = fopen ("php://stdin","r");
    $faddress = fgets($fselect);
    $newline[] = trim($faddress);
    echo "\r\n";
    echo "Address (Line 2): ";
    $fselect = fopen ("php://stdin","r");
    $faddress2 = fgets($fselect);
    $newline[] = trim($faddress2);
    echo "\r\n";
    echo "City: ";
    $fselect = fopen ("php://stdin","r");
    $fcity = fgets($fselect);
    $newline[] = trim($fcity);
    echo "\r\n";
    echo "State: ";
    $fselect = fopen ("php://stdin","r");
    $fstate = fgets($fselect);
    $newline[] = trim($fstate);
    echo "\r\n";
    echo "Zip: ";
    $fselect = fopen ("php://stdin","r");
    $fzip = fgets($fselect);
    $newline[] = trim($fzip);
    echo "\r\n";
    echo "Job Title: ";
    $fselect = fopen ("php://stdin","r");
    $fjobtitle = fgets($fselect);
    $newline[] = trim($fjobtitle);
    echo "\r\n";
    echo "Email: ";
    $fselect = fopen ("php://stdin","r");
    $femail = fgets($fselect);
    $newline[] = trim($femail);
    echo "\r\n";
    echo "Voted: ";
    $fselect = fopen ("php://stdin","r");
    $fvoted = fgets($fselect);
    $newline[] = trim($fvoted);
    echo "\r\n";
    echo "District: ";
    $fselect = fopen ("php://stdin","r");
    $fdistrict = fgets($fselect);
    $newline[] = trim($fdistrict);
    echo "\r\n";
    echo "Special ID: ";
    $fselect = fopen ("php://stdin","r");
    $fspecialid = fgets($fselect);
    $newline[] = trim($fspecialid);
    echo "\r\n";
    echo "Affiliation: ";
    $fselect = fopen ("php://stdin","r");
    $faffiliation = fgets($fselect);
    $newline[] = trim($faffiliation);
    echo "\r\n";

    $csvmod->addrow($newline);
    echo "Record Added. Press Enter to return to main menu.";
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}

function searchbyphone($filename){
    $csvmod = new Csvloader($filename);
    echo "To search for a record, enter the phone number associated with the record you would like to view: ";
    $fselect = fopen ("php://stdin","r");
    $phoneinput = fgets($fselect);
    $csvmod->searchphone(trim($phoneinput));
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}

function modifyarecord($filename){
    $csvmod = new Csvloader($filename);
    echo "Enter the phone number associated with the record you would like to modify: ";
    $fselect = fopen ("php://stdin","r");
    $phoneinput = fgets($fselect);
    $csvmod->modifyrecord(trim($phoneinput));
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}

function mergefiles(){
    echo "\r\nThis will take a while! To abort the process, press \"CTRL+C\". Confirmation will be provided when the process has completed.\r\nPROCESSING...\r\n";
    $directorycsv = __DIR__."/data/*.csv";
    $folder = glob($directorycsv);
    $temphandle = fopen(__DIR__."/data/tempfile.csv", "w");
    $i = 0;
    foreach($folder as $file){
        $rows = array_map('str_getcsv', file($file));
        $header = array_shift($rows);
        if($i == 0){
            $sendline = "";
            foreach($header as $hvalue){
                $sendline = $sendline.$hvalue.",";
            }
            $sendline = substr($sendline, 0, -1);
            $sendline = $sendline."\r\n"; 
            fwrite($temphandle, $sendline);
        }
        $i++;
        $phonearray = array();
        foreach ($rows as $row) {
            if(array_key_exists($row[0], $phonearray)){
                echo"\r\n**Duplicate Record Foud, Ignoring**\r\n"; 
            }
            else{
                $phonearray[$row[0]] = 1;
                $sendline = "";
                foreach($row as $value){
                    $cleanvalue = str_replace(",", ";", $value);
                    $sendline = $sendline.$cleanvalue.",";
                }
                $sendline = substr($sendline, 0, -1);
                $sendline = $sendline."\r\n"; 
                fwrite($temphandle, $sendline);
            }
        }
    }
    fclose($temphandle);
    rename(__DIR__."/data/tempfile.csv", __DIR__."/data/MergedRecords.csv");
    echo "\n\rFiles Merged! The merged file may now be selected for any of the functions within this application. Press Enter to return to Main Menu.";
    $fselect = fopen ("php://stdin","r");
    $fchoice = fgets($fselect);
    showmenu();
}


?>