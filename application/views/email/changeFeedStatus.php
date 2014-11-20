<?php
echo '
	<p>
		El feed <b>'.$feed['feedName'].'</b>  cambió de estado automáticamente <br/> <br/>
		feedId: '.$feed['feedId'].' <br/>
		status: '.$newStatus.' <br/>
		feedUrl: '.$feed['feedUrl'].' <br/>
		feedLink: '.$feed['feedLink'].'		<br/>
		Abm: '.base_url('feeds/edit/'.$feed['feedId']).' <br/>
	</p>'; 
