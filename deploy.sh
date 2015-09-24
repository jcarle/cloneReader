#!/bin/bash

echo "Site in maintenance";
mv .htaccess .htaccess.copy;
cp .htaccess.503 .htaccess;

echo "Running process ..."
git pull;
php index.php > /dev/null;
echo "Process complete."

rm .htaccess;
mv .htaccess.copy .htaccess;


echo "Site online.";
echo "Update complete !!!"
