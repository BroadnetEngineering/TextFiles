<?php
/**
 * Created by PhpStorm.
 * User: mprow
 * Date: 8/24/2018
 * Time: 1:20 PM
 */

class CsvHelper
{
    const FIELD_UNIQUE = 'Phone';

    static function removeDupes(CsvFile $csvFile, $createBackup = true) {
        $cols = $csvFile->getCols();
        $uniqueIndex = array_search(static::FIELD_UNIQUE, $cols);
        $values = [];

        $csvFilename = $csvFile->getFilename();

        if ($createBackup) {
            echo "Creating backup..." . PHP_EOL;
            copy($csvFilename, $csvFilename . "-backup");
        }

        // Create destination and add header row
        $destination = tmpfile();
        if ($destination === false) {
            throw new RuntimeException("Unable to create temporary file");
        }

        if (fputcsv($destination, $cols) === false) {
            throw new RuntimeException("Unable to write header row to destination file");
        }

        $loopCount = 0;

        while (($row = $csvFile->getNextRow()) !== false) {
            if (!isset($row[$uniqueIndex])) {
                //error_log("Warning: Empty row found in CSV");
                echo "Warning: Empty row found in CSV" . PHP_EOL;
                continue;
            }

            if ($loopCount++ % 1000 === 0) {
                echo "Count: " . $loopCount . ", Unique: " . count($values) . "             \r";
            }

            $uniqueValue = crc32($row[$uniqueIndex]);

            if (!isset($values[$uniqueValue])) {
                // Found unique row, add to destination file.
                if (fputcsv($destination, $row) === false) {
                    echo "Warning: Error adding row to destination file" . PHP_EOL;
                } else {
                    $values[$uniqueValue] = true;
                }
            }
        }

        $csvFile->close();

        $tempnam = stream_get_meta_data($destination)['uri'];
        copy($tempnam, $csvFilename);

        $csvFile->open();
        fclose($destination);
    }
}