<?php
/**
 * Created by PhpStorm.
 * User: mprow
 * Date: 8/23/2018
 * Time: 3:15 PM
 */

/**
 * Functionality:
 * * Count rows in file
 * * Count columns in file
 * * Remove duplicate rows by phone number
 * * Add additional rows
 * * Search by phone number
 * * Change row data
 * * Append smaller file to bigger file, then remove duplicates by phone number
 */

/**
 * Requirements:
 * * Command line or web based
 * * Memory footprint < 1MB
 * * No Databases
 * * Use PHP
 */

abstract class CsvManip {

    const DIRECTORY_DATA = '../data';
    const FILE_BIG_LIST = 'bigtestlist500k_extra_data.csv';
    const FILE_SMALL_LIST = 'bigtestlist1k_extra_data.csv';

    /**
     * Command line options
     */
    const OPTS_SHORT = 'rcldas:umhx:';
    const OPTS_LONG = [
        'help',
        'count-rows',
        'count-cols',
        'list-cols',
        'dupe',
        'add',
        'remove:',
        'search:',
        'update',
        'merge',
        'use-big-list'
    ];
    const OPTS_LONG_VALUES = [
        // these are used for adding/updating records
        'phone',
        'last-name',
        'first-name',
        'title',
        'address',
        'address-2',
        'city',
        'state',
        'zip-code',
        'job-title',
        'email',
        'voted',
        'district',
        'special-id',
        'party'
    ];

    /**
     * Get command line options array
     * @return array
     */
    static function getOpts() {
        $longOptions = static::OPTS_LONG;

        foreach (static::OPTS_LONG_VALUES as $valueKey) {
            $longOptions[] = $valueKey . ':';
        }

        return getopt(static::OPTS_SHORT, $longOptions);
    }

    /**
     * Run application
     */
    static function run() {
        $opts = static::getOpts();
        $list = static::FILE_SMALL_LIST;

        if (!$opts || empty($opts) || isset($opts['h']) || isset($opts['help'])) {
            static::showHelp();
            exit;
        }

        if (isset($opts['use-big-list'])) {
            $list = static::FILE_BIG_LIST;
        }

        $csvFilename = dirname(__FILE__) . DIRECTORY_SEPARATOR . static::DIRECTORY_DATA . DIRECTORY_SEPARATOR . $list;
        $csv = new CsvFile($csvFilename);

        if (isset($opts['a']) || isset($opts['add'])) {
            static::doAddItem($csv, $opts);
        }

        if (isset($opts['c']) || isset($opts['count-cols'])) {
            static::doCountCols($csv);
        }

        if (isset($opts['d']) || isset($opts['dupe'])) {
            static::doRemoveDupes($csv);
        }

        if (isset($opts['l']) || isset($opts['list-cols'])) {
            static::doListCols($csv);
        }

        if (isset($opts['m']) || isset($opts['merge'])) {
            $sourceCsv = new CsvFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . static::DIRECTORY_DATA . DIRECTORY_SEPARATOR  . static::FILE_SMALL_LIST);
            $targetCsv = new CsvFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . static::DIRECTORY_DATA . DIRECTORY_SEPARATOR . static::FILE_BIG_LIST);
            static::doCombineLists($sourceCsv, $targetCsv);
        }

        if (isset($opts['r']) || isset($opts['count-rows'])) {
            static::doCountRows($csv);
        }

        if (isset($opts['s']) || isset($opts['search'])) {
            $searchValue = isset($opts['s']) ? $opts['s'] : $opts['search'];
            static::doSearchList($csv, $searchValue);
        }

        if (isset($opts['u']) || isset($opts['update'])) {
            static::doUpdateItem($csv, $opts);
        }

        if (isset($opts['x']) || isset($opts['remove'])) {
            $key = isset($opts['x']) ? $opts['x'] : $opts['remove'];
            static::doRemoveItem($csv, $key);
        }

        $csv->close();
    }

    private static function doAddItem(CsvFile $csvFile, array $opts) {
        if (!isset($opts['phone'])) {
            throw new InvalidArgumentException("phone argument is required for adding items");
        }

        // Check phone number for uniqueness before adding to file
        $phone = $opts['phone'];
        if (CsvHelper::getRow($csvFile, $phone) !== false) {
            throw new InvalidArgumentException("Cannot add duplicate row: phone must be unique");
        }

        $values = [];
        foreach (static::OPTS_LONG_VALUES as $field) {
            $values[] = isset($opts[$field]) ? $opts[$field] : '';
        }

        CsvHelper::addRow($csvFile, $values);
        echo "Added row to file" . PHP_EOL;
    }

    private static function doCountCols(CsvFile $csvFile) {
        $columnCount = $csvFile->countCols();
        echo "Column count: " . $columnCount . PHP_EOL;
    }

    private static function doCountRows(CsvFile $csvFile) {
        $rowCount = $csvFile->countRows();
        echo "Row count: " . $rowCount . PHP_EOL;
    }

    private static function doListCols(CsvFile $csvFile) {
        $cols = $csvFile->getCols();
        echo "Column list: " . implode(', ', $cols) . PHP_EOL;
    }

    private static function doCombineLists(CsvFile $sourceCsvFile, CsvFile $targetCsvFile) {
        echo "Combining CSV files" . PHP_EOL;
        CsvHelper::combineFiles($sourceCsvFile, $targetCsvFile);
        echo "Finished combining CSV files             " . PHP_EOL;
    }

    private static function doRemoveDupes(CsvFile $csvFile) {
        echo "Removing duplicate rows from CSV file" . PHP_EOL;

        CsvHelper::removeDupes($csvFile);
        echo "Removed duplicate rows from CSV file" . PHP_EOL;
    }

    private static function doRemoveItem(CsvFile $csvFile, $key) {
        if (is_null($key)) {
            throw new InvalidArgumentException("key argument is required for removing items");
        }

        echo "Removing item '" . $key . "' from list" . PHP_EOL;
        CsvHelper::removeRow($csvFile, $key);
        echo "Removed item" . PHP_EOL;
    }

    private static function doSearchList(CsvFile $csvFile, $searchTerm) {
        echo "Searching list for " . $searchTerm . PHP_EOL;

        $row = CsvHelper::search($csvFile, $searchTerm);
        if ($row) {
            $csvFile->getCols();
            echo "Found match for specified search term" . PHP_EOL;
            echo "Row: " . print_r(array_combine($csvFile->getCols(), $row), true);
        } else {
            echo "No matches found" . PHP_EOL;
        }

        echo "Finished searching"  . PHP_EOL;
    }

    private static function doUpdateItem(CsvFile $csvFile, array $opts) {
        if (!isset($opts['phone'])) {
            throw new InvalidArgumentException("phone argument is required for updating items");
        }

        $phone = $opts['phone'];
        $values = [];

        foreach (static::OPTS_LONG_VALUES as $field) {
            $values[] = isset($opts[$field]) ? $opts[$field] : null;
        }

        CsvHelper::updateRow($csvFile, $phone, $values);

        echo "Finished updating row" . PHP_EOL;
    }

    private static function showHelp() {
        echo CommandHelp::getHelpText() . "\n";
    }

}
