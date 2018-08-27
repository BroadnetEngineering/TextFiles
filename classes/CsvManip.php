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
    const OPTS_SHORT = 'rclda:s:u:mh';
    const OPTS_LONG = [
        'help',
        'count-rows',
        'count-cols',
        'list-cols',
        'dupe',
        'add:',
        'search:',
        'update:',
        'merge',
        'use-big-list'
    ];

    static function getOpts() {
        return getopt(static::OPTS_SHORT, static::OPTS_LONG);
    }

    static function run() {
        $opts = static::getOpts();
        $list = static::FILE_SMALL_LIST;

        if (!$opts || empty($opts) || isset($opts['h']) || isset($opts['help'])) {
            //TODO output command line help text and exit
            static::showHelp();
            exit;
        }

        if (isset($opts['use-big-list'])) {
            $list = static::FILE_BIG_LIST;
        }

        $csvFilename = dirname(__FILE__) . DIRECTORY_SEPARATOR . static::DIRECTORY_DATA . DIRECTORY_SEPARATOR . $list;
        $csv = new CsvFile($csvFilename);

        //TODO determine which action(s) are being called from command line arguments
        if (isset($opts['c']) || isset($opts['count-cols'])) {
            static::doCountCols($csv);
        }

        if (isset($opts['r']) || isset($opts['count-rows'])) {
            static::doCountRows($csv);
        }

        if (isset($opts['l']) || isset($opts['list-cols'])) {
            static::doListCols($csv);
        }

        if (isset($opts['d']) || isset($opts['dupe'])) {
            static::doRemoveDupes($csv);
        }

        if (isset($opts['a']) || isset($opts['add'])) {

        }

        if (isset($opts['s']) || isset($opts['search'])) {

        }

        if (isset($opts['u']) || isset($opts['update'])) {

        }

        if (isset($opts['m']) || isset($opts['merge'])) {

        }

    }

    private static function doCountCols(CsvFile $csvFile) {
        //TODO wrap all calls to csvFile with try..catch statements
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

    private static function doRemoveDupes(CsvFile $csvFile) {
        //TODO wrap this in try..catch block
        $startRowCount = $csvFile->countRows();
        CsvHelper::removeDupes($csvFile);

        $endRowCount = $csvFile->countRows();
        $removedRows = $startRowCount - $endRowCount;
        echo "Removed " . $removedRows . " duplicate rows from CSV file" . PHP_EOL;
    }

    private static function showHelp() {
        //TODO show help text for command line
    }

}
