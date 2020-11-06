# DIFF REPORT

## Description
Program compares text file with other ones, when found differences sends email (to address defined in .env file),
also when files encodings differ an SMS will be sent (to phone defined in .env file)

## Usage
```
php diffReport.php -f filename -c filename [-c filename]

-f base file
-c file to compare (may be occurred multiple times)
```

## Example of usage
```
cd bin

php diffReport.php -f ../test_files/test.txt -c ../test_files/test1.txt -c ../test_files/test2.txt -c ../test_files/test3.txt
```
