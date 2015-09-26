#!/bin/bash

echo "Site in maintenance";
mv .htaccess .htaccess.copy;
cp .htaccess.503 .htaccess;

echo "Running process ..."
git pull;

rm -f assets/cache/*.js;
rm -f assets/cache/*.css;

php index.php langs/change/es > /dev/null;
php index.php langs/change/pt-br > /dev/null;
php index.php langs/change/en > /dev/null;
php index.php langs/change/zh-cn > /dev/null;

#chown jcarle:www-data -R assets/cache/;

echo "Process complete."

rm .htaccess;
mv .htaccess.copy .htaccess;


echo "Site online.";
echo "Update complete !!!"
