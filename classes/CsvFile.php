<?php
/**
 * Created by PhpStorm.
 * User: mprow
 * Date: 8/24/2018
 * Time: 11:59 AM
 */

/**
 * Class CsvFile handles operations on CSV file stored on file system
 */
class CsvFile {
    private $filename = null;

    private $file = null;

    private $hasHeaderRow = true;

    /**
     * CsvFile constructor
     * @param string $filename
     * @param bool $hasHeaderRow
     */
    public function __construct($filename, $hasHeaderRow = true)
    {
        $this->filename = $filename;
        $this->hasHeaderRow = $hasHeaderRow;
        $this->open();
    }

    /**
     * CsvFile destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return null|string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param null|string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get full path of underlying file resource
     * @return string
     */
    public function getStreamPath()
    {
        if (is_null($this->file)) {
            throw new RuntimeException("File resource not opened");
        }
        return stream_get_meta_data($this->file)['uri'];
    }

    /**
     * @return bool
     */
    public function isHasHeaderRow()
    {
        return $this->hasHeaderRow;
    }

    /**
     * @param bool $hasHeaderRow
     */
    public function setHasHeaderRow($hasHeaderRow)
    {
        $this->hasHeaderRow = $hasHeaderRow;
    }

    /**
     * Open CSV file resource
     */
    public function open()
    {
        if (!file_exists($this->filename)) {
            throw new RuntimeException("File does not exist: " . $this->filename);
        }

        $file = fopen($this->filename, "r+");
        if ($file === false) {
            throw new RuntimeException("Unable to open file for reading/writing: " . $this->filename);
        }

        $this->file = $file;
    }

    /**
     * Close opened CSV file resource
     */
    public function close()
    {
        if (!is_null($this->file)) {
            fclose($this->file);
            $this->file = null;
        }
    }

    /**
     * Count columns in CSV file
     * @return int
     */
    public function countCols()
    {
        if (is_null($this->file)) {
            throw new RuntimeException("File resource not opened");
        }
        if (!rewind($this->file)) {
            throw new RuntimeException("Unable to reset file resource");
        }

        $row = fgetcsv($this->file);
        $count = count($row);

        return $count;
    }

    public function getCols()
    {
        if (is_null($this->file)) {
            throw new RuntimeException("File resource not opened");
        }
        if (!rewind($this->file)) {
            throw new RuntimeException("Unable to reset file resource");
        }

        $row = $this->getNextRow();

        if ($row === false) {
            throw new RuntimeException("No rows found in CSV file");
        }

        if (count($row) === 1 && $row[0] === null) {
            throw new RuntimeException("Header row is missing or empty");
        }

        return $row;
    }

    /**
     * Get next row from CSV file
     * @return array|bool returns array of columns or false on EOF
     */
    public function getNextRow()
    {
        if (is_null($this->file)) {
            throw new RuntimeException("File resource not opened");
        }

        $row = fgetcsv($this->file);

        if ($row === false) {
            if (feof($this->file)) {
                return false;
            }

            throw new RuntimeException("Error occurred reading next row");
        }

        return $row;
    }

    /**
     * Count rows in CSV file
     * @return int
     */
    public function countRows()
    {
        if (is_null($this->file)) {
            throw new RuntimeException("File resource not opened");
        }
        if (!rewind($this->file)) {
            throw new RuntimeException("Unable to reset file resource");
        }

        $count = 0;

        while (($row = fgetcsv($this->file)) !== false) {
            if (count($row) === 1 && $row[0] === null) {
                // skip empty line
                continue;
            }

            $count++;
        }

        if ($count > 0 && $this->hasHeaderRow) {
            // Reduce row count to account for header row
            $count--;
        }

        return $count;
    }
}