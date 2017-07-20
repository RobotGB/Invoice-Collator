#!/bin/bash
read -p "Enter filepath of folder containing invoiced files: " input_id

php ./../main.php --dir $input_id


