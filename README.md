# Phone CSV

Project desiged to accomplish the following:
* Count the number of rows in each file
* Count the number of columns in each file
* Remove rows by matching phone numbers
* Add additional rows
* Allow searching by phone number
* Change the data in a row
* Append the contents of the smaller file to the larger file and then check for and remove duplicates (phone numbers MUST all be unique)

Follows these rules:
* Command line or web based
* Memory footprint should never exceed 1 MB
* No databases
* May be written in the language of your choice.

Please note, quality and good design practices have been sacrificed due to the time limit.  Therefore, I absolutely would not advocate for using this project outside of its intended use, which is to satisf, which is to satisf, which is to satisf, which is to satisf, which is to satisf, which is to satisf, which is to satisf, which is to satisf, which is to satisfy a coding assessment. 

### Usage
```
php manage.php --action=count_lines --show_memory
php manage.php --action=count_columns --show_memory
php manage.php --action=remove_by_phone --phone="(899) 104-8608" --show_memory
php manage.php --action=add_rows_by_json --json='[{"Phone":"6294638943", "Last Name":"bar", "First Name":"foo", "Title":"foobar", "Address":"foobar", "Address 2":"foobar", "City":"foobar", "State":"foobar", "Zip Code":"foobar", "Job Title":"foobar", "Email":"foobar", "Voted":"foobar", "District":"foobar", "Special ID":"foobar", "Party":"foobar"}, {"Phone":"6294638942", "Last Name":"bar", "First Name":"foo", "Title":"foobar", "Address":"foobar", "Address 2":"foobar", "City":"foobar", "State":"foobar", "Zip Code":"foobar", "Job Title":"foobar", "Email":"foobar", "Voted":"foobar", "District":"foobar", "Special ID":"foobar", "Party":"foobar"}, {"Phone":"6294638941", "Last Name":"bar", "First Name":"foo", "Title":"foobar", "Address":"foobar", "Address 2":"foobar", "City":"foobar", "State":"foobar", "Zip Code":"foobar", "Job Title":"foobar", "Email":"foobar", "Voted":"foobar", "District":"foobar", "Special ID":"foobar", "Party":"foobar"}]' --show_memory
php manage.php --action=search --phone="30338" --show_memory
php manage.php --action=update_row_with_phone_and_json --phone="6294638946" --json='{"Party":"foobar"}' --show_memory
php manage.php --action=merge_files --show_memory
```

### Prerequisites

Tested using PHP version 7.2.24

## Author

* **Robert Baldessari** - (https://github.com/R0bb0b)
