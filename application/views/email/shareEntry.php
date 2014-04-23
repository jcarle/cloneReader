<?php
if ($shareByEmailComment != '') {
	echo '<p>'.$shareByEmailComment.'</p>';
}
?>
<div style=" background: #F5F5F5; border:1px solid #E5E5E5; border-radius: 5px; padding: 10px;">
	<?php echo sprintf($this->lang->line('Sent to you by %s via cReader'), $userFullName); ?>
</div>
<div style="margin:10px 0;">
	<h2>
		<a style="font-weight: bold; margin: 10px 0;"  href="<?php echo $entry['entryUrl']; ?>">
			<?php echo $entry['entryTitle']; ?></a>
	</h2>
	<div style="display: block; font-size: small;" >
		<?php echo $entryOrigin.' - '. date( $this->lang->line('PHP_DATE_FORMAT').' H:i:s', strtotime($entry['entryDate'])); ?>
	</div>
</div>
<p>
	<?php echo $entry['entryContent']; ?>
</p>
