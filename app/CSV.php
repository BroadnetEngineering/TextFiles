<?php declare(strict_types=1);

namespace App;

class CSV
{
    private $path;

    /**
     * Memory footprint should never exceed 1 MB
     */
    public function __construct($path = null)
    {
        $this->path = $path;
    }
    
    # Count the number of columns in csv file
    public function getColumnCount() : int
    {
        $handle = fopen($this->path, "r");
        $row = fgetcsv($handle);
        fclose($handle);
        return count($row);
    }
    
    # Count the number of rows in csv file
    public function getRowCount() : int
    {
        $handle = fopen($this->path, "r");
        $count = 0;

        // Iterate over every line of the file
        while (($raw_string = fgets($handle)) !== false) {
            $count++;
        }
        
        fclose($handle);
        
        return $count - 1;
    }

    /**
     * Remove rows by matching phone number
     */
    public function removeRowByPhone($phone = null){
        if($phone) {

            $currentHandle = fopen(
                $this->path, "r"
            );
            
            $tmpHandle = tmpfile();
            
            while( $row = fgetcsv($currentHandle)){
                if($phone == $row[0]) continue;
                fputcsv($tmpHandle, $row);
            }
            fclose($currentHandle);

            $tmpPath = stream_get_meta_data($tmpHandle)['uri'];
            rename(
                $tmpPath,
                $this->path,
            );
            fclose($tmpHandle);
        }
    }

    /**
     * Add additional rows
     */
    public function addRow($row)
    {
        $handle = fopen(
            $this->path, "a"
        );
        fputcsv($handle, $row);
    }
    
    /** 
     * Allow searching by phone number
     */
    public function searchByPhone($phone)
    {
        $handle = fopen(
            $this->path, "r"
        );
        while( $row = fgetcsv($handle, 1024)){
            if($phone == $row[0]) return $row;
        }
    }

    /**
     * Change the data in a row
     */
    public function alterRow($phone, $newRow)
    {
        $this->removeRowByPhone($phone);
        $this->addRow($newRow);
    }

    /**
     * Append the contents of the smaller file to the larger file
     * remove duplicates (phone numbers MUST all be unique)
     */
    public function appendAndRemoveDuplicates($paths)
    {
        $csv_small = fopen($paths[0], 'r');
        $csv_big   = fopen($paths[1], 'a+');

        $tmpHandle = tmpfile();

        $count = -1;
        while (($row = fgetcsv($csv_big)) !== FALSE)
        {
            if($row[0]){
                fwrite($tmpHandle, $row[0]);
                $count++;
            }
            unset($row);
        }

        $phoneBook = file_get_contents(stream_get_meta_data($tmpHandle)['uri']);

        /**
         * Iterate over small csv. 
         * Add row to csv_big if not in phone book 
         */

        $add = 0;
        while (($row = fgetcsv($csv_small)) !== FALSE)
        {
            if (strpos($phoneBook, $row[0]) === false) {
                $add++;
                fputcsv($csv_big, $row);
            }
        }
        
        fclose($tmpHandle);
        fclose($csv_big);
        fclose($csv_small);
    }
}