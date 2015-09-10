#!/bin/bash

echo "Site in maintenance";
mv .htaccess .htaccess.copy;
cp .htaccess.503 .htaccess;

echo "Running process ..."
svn up;
php index.php > /dev/null;
chown www-data:www-data -R assets/cache/;
echo "Process complete."

rm .htaccess;
mv .htaccess.copy .htaccess;


echo "Site online.";
echo "Update complete !!!"
