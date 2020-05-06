## Text File Manipulation

Currently there is no frontend for this repo. There required functionality exists in [app/CSV.php](app/CSV.php) and there are two test classes [tests/CSVTest.php](tests/CSVTest.php) and [tests\BigDataTest.php](tests\BigDataTest.php) that demonstrate that the required functionality is working appropriately.

## System Requirements

You need PHP >= 7 and Composer installed on your system. If you prefer, you may use the preconfigured docker environment included in this repo. There is a readme in the docker folder explaining [how to use the docker environment](/docker/readme.md).


#### Install dependancies
```
composer install
```

#### Run quick CSV class unit tests
```
composer test-unit
```

#### Run Big Data test
```
composer test-big-data
```

# Requirements
Design an application to manipulate the CSVs in the data directory.

### Your application should accomplish the following:
* Count the number of rows in each file
* Count the number of columns in each file
* Remove rows by matching phone numbers
* Add additional rows
* Allow searching by phone number
* Change the data in a row
* Append the contents of the smaller file to the larger file and then check for and remove duplicates (phone numbers MUST all be unique)

### The app should follow these rules:
* Command line or web based
* Memory footprint should never exceed 1 MB
* No databases
* May be written in the language of your choice.