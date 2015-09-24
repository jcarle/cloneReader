#!/bin/bash

echo "Site in maintenance";
mv .htaccess .htaccess.copy;
cp .htaccess.503 .htaccess;

echo "Running process ..."
git pull;

rm -f assets/cache/*.js;
rm -f assets/cache/*.css;

php index.php langs/change/es;
php index.php langs/change/pt-br;
php index.php langs/change/en;
php index.php langs/change/zh-cn;


echo "Process complete."

rm .htaccess;
mv .htaccess.copy .htaccess;


echo "Site online.";
echo "Update complete !!!"
