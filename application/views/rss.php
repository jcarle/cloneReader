<?php echo'<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo $feedTitle ?></title>
		<link>http://localhost/rssCI/feed</link>
		<atom:link href="<?php echo base_url('feed'); ?>" rel="self" type="application/rss+xml" />
		<description><?php echo $feedDesc ?></description>
		<language>es-es</language>
		<!--define la fecha de la última publicación del contenido en el feed RSS-->
		<pubDate></pubDate>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<!--cada cuántas horas quieres que mire si
		hay nuevos posts tu feed, entre 0 y 24-->
		<sy:updateFrequency>1</sy:updateFrequency>
		<!--define la fecha de la última publicación del contenido en el feed RSS-->
		<lastBuildDate></lastBuildDate>
		<docs>http://www.rssboard.org/rss-specification</docs>
		<managingEditor>el email del creador del feed</managingEditor>
		<webMaster>el webmaster</webMaster>
		<!--obtenemos los posts para nuestro feed-->
		<?php 
pr($news);		
		foreach ($news as $new){ ?>
			<item>
				<title><?php echo $new['newTitle']; ?></title>
				<!--el enlace permanente de nuestro posts por ejemplo
				http://localhost/rssCI/titulodelpost/iddelpost, por cada enlace
				se crearía un enlace permanente-->
				<link><?php echo base_url('news/'.$new['newSef']) ?></link>
				<description><![CDATA[<?php echo $new['newContent']; ?>]]></description>
				<pubDate><?php echo $new['newDate']; ?></pubDate>
				<dc:creator><?php echo $new['userFullName']; ?></dc:creator>
				<category> news </category>
			</item>  
		<?php
		}
		?>
	</channel>
</rss>