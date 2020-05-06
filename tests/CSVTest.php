<?php 
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\CSV;

final class CSVTest extends TestCase
{
    # Sample csv files
    private $path  = 'data/test.csv';
    private $path2 = 'data/test2.csv';

    private $csv;

    # Data for sample csv file
    private $testData = [
        ["Phone","Last Name","First Name"],
        ["8991231146","Stroman","Lewis"],
        ["8991050123","Hyatt","Roderick"],
        ["8991718311","Ullrich","London"],
        ["8991726803","Duplicate","Entry"]
    ];

    # Data for second sample csv file
    private $testData2 = [
        ["Phone","Last Name","First Name"],
        ["8991974964","Toy","Jordane"],
        ["8991913774","Rosenbaum","Ludie"],
        ["8991726803","Duplicate","Entry"]
    ];

    protected function setUp(): void
    {
        $this->createTestCSV($this->path, $this->testData);
        $this->csv = new CSV($this->path);
    }

    protected function createTestCSV($path, $data){
        $handle = fopen($path, 'c');
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    public function testCanCreateCSVInstance(): void
    {
        $this->assertInstanceOf(
            CSV::class,
            $this->csv
        );
    }

    public function testCanGetColumnCount(): void
    {
        # Counting columns in header
        $count = count($this->testData[0]);
        $this->assertEquals($count, $this->csv->getColumnCount());
    }

    public function testCanGetRowCount(): void
    {
        # Counting number of rows minus header
        $count = count($this->testData) - 1;
        $this->assertEquals($count, $this->csv->getRowCount());
    }

    public function testCanRemoveRowByPhone() : void
    {
        $row = $this->testData[3];
        $phone = $row[0];
        $this->csv->removeRowByPhone($phone);

        $new = new CSV($this->path);
        $this->assertEquals(3, $new->getRowCount());
    }

    public function testCanSearchByPhone() : void
    {
        $searchedRow = $this->testData[3];
        $searchedPhone = $searchedRow[0];

        $result = $this->csv->searchByPhone($searchedPhone);
        $this->assertEquals($searchedRow, $result);
    }

    public function testCanAddRow() : void
    {
        $newRow = ['0001112222','NEW','ROW'];
        $this->csv->addRow($newRow);
        
        $addedRow = $this->csv->searchByPhone($newRow[0]);

        $this->assertEquals($newRow, $addedRow);
    }

    public function testCanAlterRow() : void
    {
        $searchNumber = 8991718311;
        $row = $this->csv->searchByPhone($searchNumber);

        # Altering the date prior to saving
        $row[1] = 'ALTERED';
        $row[2] = 'ROW';

        $this->csv->alterRow($searchNumber, $row);
        
        $alteredRow = $this->csv->searchByPhone($searchNumber);

        $this->assertEquals($row, $alteredRow);
    }

    public function testCanAppendAndRemoveDuplicates() : void
    {
        $this->createTestCSV($this->path2, $this->testData2);      
        
        $this->csv->appendAndRemoveDuplicates([
            $this->path,
            $this->path2
        ]);

        $new = new CSV($this->path2);
        $this->assertEquals(6, $new->getRowCount());
    }

    protected function tearDown(): void
    {
        # Delete sample csv files
        if(file_exists($this->path)){
            unlink($this->path);
        }
        if(file_exists($this->path2)){
            unlink($this->path2);
        }
    }
}