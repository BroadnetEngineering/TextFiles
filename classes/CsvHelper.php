<?php
/**
 * Created by PhpStorm.
 * User: mprow
 * Date: 8/24/2018
 * Time: 1:20 PM
 */

/**
 * Class CsvHelper - CSV helper functions
 */
class CsvHelper
{
    const FIELD_UNIQUE = 'Phone';

    /**
     * Add row to specified CSV file resource
     * @param CsvFile $csvFile
     * @param array $values
     */
    static function addRow(CsvFile $csvFile, array $values) {
        $filename = $csvFile->getStreamPath();
        $csvFile->close();

        if (($file = fopen($filename, 'a')) === false) {
            throw new RuntimeException("Unable to open CSV file for appending");
        }

        if (fputcsv($file, $values) === false) {
            echo "Warning: Error adding row to file" . PHP_EOL;
        }

        fclose($file);
        $csvFile->open();
    }

    /**
     * Get row by specified key
     * @param CsvFile $csvFile
     * @param $key
     * @return array|bool row, false if not found
     */
    static function getRow(CsvFile $csvFile, $key) {
        $cols = $csvFile->getCols();
        $uniqueIndex = array_search(static::FIELD_UNIQUE, $cols);

        while (($row = $csvFile->getNextRow()) !== false) {
            if ($row[$uniqueIndex] === $key) {
                return $row;
            }
        }

        return false;
    }

    /**
     * Remove row with specified key from CSV file resource
     * @param CsvFile $csvFile
     * @param $key
     */
    static function removeRow(CsvFile $csvFile, $key) {
        $cols = $csvFile->getCols();
        $uniqueIndex = array_search(static::FIELD_UNIQUE, $cols);

        $destination = tmpfile();
        if (fputcsv($destination, $cols) === false) {
            throw new RuntimeException("Unable to write header row to destination file");
        }

        while (($row = $csvFile->getNextRow()) !== false) {
            if ($row[$uniqueIndex] === $key) {
                continue;
            }

            if (fputcsv($destination, $row) === false) {
                throw new RuntimeException("Unable to write unchanged row to destination file");
            }
        }

        $csvFile->close();

        $tempnam = stream_get_meta_data($destination)['uri'];
        $csvFilename = $csvFile->getFilename();
        copy($tempnam, $csvFilename);

        $csvFile->open();
        fclose($destination);
    }

    /**
     * Update row in specified CSV file resource
     * @param CsvFile $csvFile
     * @param $key
     * @param array $values
     */
    static function updateRow(CsvFile $csvFile, $key, array $values) {
        $cols = $csvFile->getCols();
        $uniqueIndex = array_search(static::FIELD_UNIQUE, $cols);

        $destination = tmpfile();
        if (fputcsv($destination, $cols) === false) {
            throw new RuntimeException("Unable to write header row to destination file");
        }

        while (($row = $csvFile->getNextRow()) !== false) {
            if ($row[$uniqueIndex] === $key) {
                // Merge row with specified values
                $mergedRow = [];

                foreach ($values as $index => $value) {
                    if ($value !== null) {
                        $mergedRow[] = $value;
                    } else {
                        $mergedRow[] = $row[$index];
                    }
                }
                if (fputcsv($destination, $mergedRow) === false) {
                    throw new RuntimeException("Unable to write updated row to destination file");
                }

            } else {
                if (fputcsv($destination, $row) === false) {
                    throw new RuntimeException("Unable to write unchanged row to destination file");
                }
            }
        }

        $csvFile->close();

        $tempnam = stream_get_meta_data($destination)['uri'];
        $csvFilename = $csvFile->getFilename();
        copy($tempnam, $csvFilename);

        $csvFile->open();
        fclose($destination);
    }

    /**
     * Remove duplicates from specified CSV file resource
     * @param CsvFile $csvFile
     * @param bool $createBackup
     */
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

            if (++$loopCount % 1000 === 0) {
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

    /**
     * Combine sourceCsvFile and targetCsvFile
     * @param CsvFile $sourceCsvFile
     * @param CsvFile $targetCsvFile
     * @param bool $removeDupes
     * @param bool $createBackup
     */
    static function combineFiles(CsvFile $sourceCsvFile, CsvFile $targetCsvFile, $removeDupes = true, $createBackup = true) {
        $targetFilename = $targetCsvFile->getStreamPath();
        $targetCsvFile->close();

        if ($createBackup) {
            echo "Creating backup..." . PHP_EOL;
            copy($targetFilename, $targetFilename . "-backup");
        }

        $targetFile = fopen($targetFilename, "a");
        if ($targetFile === false) {
            throw new RuntimeException("Unable to open target file for appending.");
        }

        echo "Merging files..." . PHP_EOL;

        while (($row = $sourceCsvFile->getNextRow()) !== false) {
            if (fputcsv($targetFile, $row) === false) {
                echo "Warning: Error adding row to target file" . PHP_EOL;
            }
        }

        fclose($targetFile);
        $targetCsvFile->open();

        if ($removeDupes) {
            echo "Removing duplicates from target file.";
            static::removeDupes($targetCsvFile, false);
        }
    }

    /**
     * Search CSV file for specified phone number
     * @param CsvFile $csvFile
     * @param $searchTerm
     * @return array|bool
     */
    static function search(CsvFile $csvFile, $searchTerm) {
        $cols = $csvFile->getCols();
        $uniqueIndex = array_search(static::FIELD_UNIQUE, $cols);

        while (($row = $csvFile->getNextRow()) !== false) {
            if (isset($row[$uniqueIndex]) && $row[$uniqueIndex] === $searchTerm) {
                return $row;
            }
        }

        return false;
    }
}
