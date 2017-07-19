# Invoice-Collator
Cycles through a directory of Excel invoice files, collating the client sheets into on summary sheet, then also creating a Summary.xlsx which is a collation of the different summary sheets.

## Installation (windows)
1. Install the latest version (currently 7.1.7) of [XAMPP](https://www.apachefriends.org/download.html)
2. Add php to your system path 
    1. Press the windows key and type environment
    2. Select edit system environment variables
    3. Press environment variables
    4. In the bottom box, scroll down to the PATH row and select it
    5. Click Edit
    6. Click New and add "C:\xampp\php" as a new row and save
    7. Click OK, OK, OK
4. Download and install composer
3. Download this repository (green "clone or download button" near the top of the page)
4. Unzip it to wherever you like
5. Run the file bin\install.bat
7. Check there has been a folder called "vendor" created in the project folder
8. Now ready to Run

## Running
To run, create a folder and put all the invoice excel files you would like the tool to process within it. 
Run the file bin\run.bat, you will be prompted to enter the path of the above folder. 
This can be acquired by opening that folder and clicking in the address bar at the top and copy and pasting
The tool will collate the summary pages for each individual excel and create a summary excel containing all of the individual summary sheets together


## Support
If you run into problems with any of the above please contact phil@brainlabsdigital.com
