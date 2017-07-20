#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
read -p "Enter filepath of folder containing invoiced files: " input_id

php $DIR/../main.php --dir $input_id



