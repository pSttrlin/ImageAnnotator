@echo off
echo Cleaning images/
del "images\*.jpeg"
echo Cleaning annotations/Ads
del "annotations\Ads\*.jpeg"
echo Cleaning annotations/Other
del "annotations\Other\*.jpeg"
echo Cleaning pdf/
del "pdf\*.pdf"
echo Done
pause