<?php echo'<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo $feedTitle ?></title>
		<link><?php echo base_url(); ?></link>
		<atom:link href="<?php echo base_url('feed'); ?>" rel="self" type="application/rss+xml" />
		<description><?php echo $feedDesc ?></description>
		<language>es-es</language>
		<pubDate><?php echo $news[0]['newDate']; ?></pubDate>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>24</sy:updateFrequency>
		<lastBuildDate><?php echo $news[0]['newDate']; ?></lastBuildDate>
		<docs>http://www.rssboard.org/rss-specification</docs>
		<managingEditor>jcarle@gmail.com</managingEditor>
		<webMaster>jcarle@gmail.com</webMaster>
		<?php 
		foreach ($news as $new){ ?>
			<item>
				<title><?php echo $new['newTitle']; ?></title>
				<link><?php echo base_url('news/view/'.$new['newSef']) ?></link>
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