Clone of the old google reader. Reader of feeds, rss news.

Open source.

Responsive.

Search in all feeds

Remote login.

Import subscriptions.xml and starred.json from google reader.

Remote Storage.

Multi language. ['en', 'es', 'pt', 'zh']

Share entries in social networks.

Keyboard navigation {'j': 'next', 'k': 'prev', 'u': 'expand', 's': 'toogle start', 'm': 'toogle unread' };

List view or expanded view for item viewing.

Automatic marking of items as read as they scrolled past (expanded view only).

Source: https://github.com/jcarle/cloneReader

Powered with codeigniter, simplepie, jquery, bootstrap 


/*************************************************************************/

Clon de google reader. Lector de feeds, rss, noticias.

Open source.

Responsive.

Buscar en todos los articulos.

Login remoto.

Importa subscriptions.xml y starred.json de google reader.

Remote Storage.

Multi idioma. ['en', 'es', 'pt', 'zh']

Links para compartir en redes sociales.

Atajos de teclado  {'j': 'next', 'k': 'prev', 'u': 'expand', 's': 'toogle start', 'm': 'toogle unread' };

Vista de lista y vista expandida para visualizar artículos.

Marcado automático de los artículos como leídos al desplazarse hacia abajo (solo en vista expandida).

Source: https://github.com/jcarle/cloneReader

Desarrrollado con codeigniter, simplepie, jquery, bootstrap

/*************************************************************************/

beta / demo: http://www.clonereader.com.ar/



## How to install

**Install php, apache, mysql:**
sudo apt-get install apache2 php5 mysql-server php5-mysql php5-curl php5-gd php5-tidy apache2-mpm-prefork libapache2-mod-php5 
**Enable apache modules:**
sudo a2enmod headers rewrite deflate
sudo service apache2 restart
**Install git and download cloneReader source**
sudo apt-get install git
cd /var/www/html/
sudo git clone https://github.com/jcarle/cloneReader.git
sudo chown -R www-data:www-data /var/www/html/cloneReader/
sudo chmod -R 775 /var/www/html/cloneReader/
**Restore database:**
sudo mysql -u root -proot < [path_to_source]/db/cloneReader_empty.sql
With firefox, go to http://localhost/cloneReader
Login with 
username: admin@creader.com
password:root
**NOTE:**
If rewrite not work, please see:
http://www.dev-metal.com/enable-mod_rewrite-ubuntu-14-04-lts/


