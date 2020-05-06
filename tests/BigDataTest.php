<?php 
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\CSV;

final class BigDataTest extends TestCase
{
    private $path500k = 'data/bigtestlist500k_extra_data.csv';
    private $path500kBackup = 'data/bigtestlist500k_backup.csv';
    private $path1k = 'data/bigtestlist1k_extra_data.csv';

    protected function setUp() : void
    {
        /**
         * Reset the 500k csv if it is not in it's original state 
         */
        $csv = new CSV($this->path500k);
        if($csv->getRowCount() > 500000){
            copy($this->path500kBackup, $this->path500k);
        }
    }

    public function testCanAppendAndRemoveDuplicates() : void
    {        
                
        $csv1kCount = $this->getRowCount($this->path1k);
        $csv500kCountBefore = $this->getRowCount($this->path500k);

        $csv = new CSV();
        $csv->appendAndRemoveDuplicates([
            $this->path1k,
            $this->path500k
        ]);

        $csv500kCountAfter = $this->getRowCount($this->path500k);
        $totalRowsBothFiles = $csv1kCount + $csv500kCountBefore;
        
        /**
         * If one file was appended to the other 
         * the row count should have increased
         */
        $this->assertGreaterThan($csv500kCountBefore, $csv500kCountAfter);

        /**
         * If the duplicates were removed the row count should be fewer than
         * the total rows in both files.
         */
        $this->assertLessThan($totalRowsBothFiles, $csv500kCountAfter);

    }

    private function getRowCount($path){
        $csv = new CSV($path);
        return $csv->getRowCount();
    }

}