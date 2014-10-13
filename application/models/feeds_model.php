<?php
class Feeds_Model extends CI_Model {
	
	/*
	 * @param  (array)  $filters es un array con el formato: 
	 * 		array(
	 * 			'search'      => null, 
	 * 			'statusId'    => null, 
	 * 			'countryId'   => null, 
	 * 			'langId'      => null, 
	 * 			'tagId'       => null, 
	 * 			'userId'      => null, 
	 * 			'feedSuggest' => null
	 * 		);
	 * */
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array(), array $orders = array()){
		$languages = null;
		if (element('langId', $filters) != null) {
			// TODO: poner un ckeckbox para definir si queres aplicar el filtro asi o no
			$this->load->model('Languages_Model'); // Busca lenguages relacionados: si el filtro esta seteado en 'en', trae resultados con 'en-us', 'en-uk', etc tambien
			$languages = $this->Languages_Model->getRelatedLangs($filters['langId']);
		}
		
		$this->db
			->select('SQL_CALC_FOUND_ROWS feeds.feedId, feedName, feedDescription, feedUrl, feedLink, statusName, countryName, langName, feedLastScan, feedLastEntryDate, feedCountUsers, feedCountEntries, feedIcon', false)
			->from('feeds')
			->join('status', 'status.statusId = feeds.statusId', 'left')
			->join('countries', 'countries.countryId = feeds.countryId', 'left')
			->join('languages', 'languages.langId = feeds.langId', 'left');
			
		if (element('search', $filters) != null) {
			$this->db->like('feedName', $filters['search']);
		}
		if (element('statusId', $filters) != null) {
			$this->db->where('feeds.statusId', $filters['statusId']);
		}
		if (element('countryId', $filters) != null) {
			$this->db->where('feeds.countryId', $filters['countryId']);
		}
		if ($languages != null) {
			$this->db->where('feeds.langId IN (\''.implode('\', \'', $languages).'\')' );
		}
		if (element('tagId', $filters) != null) {
			$this->db->join('feeds_tags', 'feeds_tags.feedId = feeds.feedId', 'inner');
			$this->db->where('feeds_tags.tagId', $filters['tagId']);
		}
		if (element('userId', $filters) != null) {
			$this->db->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner');
			$this->db->where('users_feeds.userId', $filters['userId']);
		}
		if (element('feedSuggest', $filters) == true) {
			$this->db->where('feeds.feedSuggest', true);
		}
		
		$this->Commond_Model->appendOrderByInQuery($orders, array( 'feedId', 'feedName', 'feedLastEntryDate', 'feedLastScan', 'feedCountUsers', 'feedCountEntries' ));
		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);
		
		$query = $this->db->get();
		//pr($this->db->last_query()); die;

		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
	}
	
	function select(){
		return $this->db->get('feeds');
	}

	function get($feedId, $getTags = false, $getIcon = false){
		$result = $this->db->where('feeds.feedId', $feedId)->get('feeds')->row_array();
		if (empty($result)) {
			return $result;
		}
		
		if ($getTags == true) {
			$result['aTagId'] = array();
			$query = $this->Tags_Model->selectToList(null, null, array('feedId' => $feedId));
			foreach ($query['data'] as $data) {
				$result['aTagId'][] = array('id' => $data['tagId'], 'text' => $data['tagName']);
			}
		}


		if ($getIcon == true) {
			$result['feedIcon'] = (element('feedIcon', $result) == null ? site_url().'assets/images/default_feed.png' : site_url().'assets/favicons/'.element('feedIcon', $result));
		}
		
		return $result;
	}	
	
	function save($data){
		$feedId = (int)element('feedId', $data);
		
		if (trim($data['feedUrl']) == '') {
			return null;
		}
		
		$values = array(
			'feedUrl'      => $data['feedUrl'], 
			'statusId'     => config_item('feedStatusPending'),
			'countryId'    => element('countryId', $data),
			'langId'       => element('langId', $data),
			'feedSuggest'  => element('feedSuggest', $data),
			'fixLocale'    => element('fixLocale', $data),
		);
		
		if (isset($data['feedName'])) {
			$values['feedName'] = $data['feedName'];
		}
		if (isset($data['feedLink'])) {
			$values['feedLink']	= $data['feedLink'];
		}
		if (isset($data['feedDescription'])) {
			$values['feedDescription']	= $data['feedDescription'];
		}

		$query = $this->db->where('feedUrl', $values['feedUrl'])->get('feeds')->result_array();
		//pr($this->db->last_query());
		if (!empty($query)) {
			$feedId = $query[0]['feedId'];
			
			if ((string)$query[0]['feedName'] == '') {
				$values['feedName'] = element('feedLink', $values);
			}
		}
		
		if ((int)$feedId != 0) {
			$this->db->update('feeds', $values, array('feedId' => $feedId));
		}
		else {
			$this->db->insert('feeds', $values);
			$feedId = $this->db->insert_id();
		}
		//pr($this->db->last_query());

		return $feedId;
	}
	
	function delete($feedId) {
		$this->db->delete('feeds', array('feedId' => $feedId));
		return true;
	}
	
	function search($filter){
		$filter = $this->db->escape_like_str($filter);
		
		return $this->db
			->select('DISTINCT feedId AS id, feedName AS text  ', false)
//			->where('statusId', STATUS_ACTIVE)
			->like('feedName', $filter)
			->order_by('text')
			->get('feeds', config_item('autocompleteSize'))->result_array();
	}	
	
	function saveFeedIcon($feedId, $feed = null, $force = false) {
		if ($force == true) {
			$this->db->update('feeds', array( 'feedIcon' => null), array('feedId' => $feedId) );	
		}
		
		if ($feed == null) {
			$feed = $this->get($feedId);
			$this->load->model('Entries_Model');
		}
		$feedLink = $feed['feedLink'];
		$feedIcon = $feed['feedIcon'];
		
		if (trim($feedLink) != '' && $feedIcon == null) {
			$this->load->spark('curl/1.2.1');
			$img      = $this->curl->simple_get('https://plus.google.com/_/favicon?domain='.$feedLink);
			$parse    = parse_url($feedLink);
			$feedIcon = $parse['host'].'.png'; 
			file_put_contents('./assets/favicons/'.$feedIcon, $img);
			$this->db->update('feeds', array('feedIcon' => $feedIcon), array('feedId' => $feedId));	
		}

		return true;
	}
	
	function updateFeedStatus($feedId, $statusId) {
		$this->db->update('feeds', array('statusId' => $statusId), array('feedId' => $feedId));
		//pr($this->db->last_query());		
	}

	function resetFeed($feedId) { // Reseteo las propiedades del feed para reescanear
		$this->db->update('feeds', 
			array(
				'feedLastScan' 			=> null,
				'feedLastEntryDate'		=> null, 
				'statusId' 				=> 0,
				'feedMaxRetries'		=> 0,
			),
			array('feedId' => $feedId)
		);
	}
	
	/**
	 * @param $userId
	 * @param $feedId
	 * @param $rescan   indica si SOLO reescanea los 404
	 * 
	 */
	function scanAllFeeds($userId = null, $feedId = null, $rescan = false) {
		set_time_limit(0);
		
		$this->db
			->select(' DISTINCT feeds.feedId, feedUrl, feedLink, feedIcon, fixLocale', false)
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
			->order_by('feedLastScan ASC');
			
		if ($rescan == false) {
			$this->db
				->where('feedLastScan < DATE_ADD(NOW(), INTERVAL -'.config_item('feedTimeScan').' MINUTE)')
				->where('feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').')')
				->where('feedMaxRetries < '.config_item('feedMaxRetries'));
		}
		else {
			$this->db->where('feeds.statusId', config_item('feedStatusNotFound'));
		}

		if (is_null($userId) == false) {
			$this->db->where('users_feeds.userId', $userId);
		}
		if (is_null($feedId) == false) {
			$this->db->where('feeds.feedId', $feedId);
		}
		 
		$query = $this->db->get('feeds');
		// vd($this->db->last_query()); die; 
		$count = 0;
		foreach ($query->result() as $row) {
			exec('nohup '.PHP_PATH.'  '.BASEPATH.'../index.php process/scanFeed/'.(int)$row->feedId.' >> '.BASEPATH.'../application/logs/scanFeed.log &');
			
			$count++;
			if ($count % 40 == 0) {
				sleep(10);
			}
		}
	}

	function scanFeed($feedId) {
		set_time_limit(0);
		
		$this->load->model('Entries_Model');
	
//sleep(5);

		// vuelvo a preguntar si es momento de volver a scanner el feed, ya que pude haber sido scaneado recién al realizar multiples peticiones asyncronicas
		$query = $this->db
			->select('feedLastEntryDate, feedUrl, fixLocale, feedMaxRetries, feedLink, feedIcon, TIMESTAMPDIFF(MINUTE, feedLastScan, DATE_ADD(NOW(), INTERVAL -'.config_item('feedTimeScan').' MINUTE)) AS minutes ', false)
			->where('feeds.feedId', $feedId)
			->get('feeds')->result_array();
		//pr($this->db->last_query());  die;
		$feed = $query[0];
		if ($feed['minutes'] != null && (int)$feed['minutes'] < config_item('feedTimeScan') ) {  // si paso poco tiempo salgo, porque acaba de escanear el mismo feed otro proceso
			return;
		}
		
		$feedUrl        = $feed['feedUrl']; 
		$fixLocale      = $feed['fixLocale'];
		$feedMaxRetries = $feed['feedMaxRetries'];

		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();

		if ($this->cisimplepie->error() ) {
			$this->db->update('feeds', 
				array(
					'feedMaxRetries'    => $feedMaxRetries + 1,
					'statusId'          => config_item('feedStatusPending'),
					'feedLastScan'      => date("Y-m-d H:i:s"),
					'feedLastEntryDate' => $this->Entries_Model->getLastEntryDate($feedId),
				),
				array('feedId' => $feedId)
			);
			if (($feedMaxRetries + 1) >= config_item('feedMaxRetries')) {
				$this->updateFeedStatus($feedId, config_item('feedStatusNotFound'));
			}
			return;
		}

		$lastEntryDate = $feed['feedLastEntryDate'];
		
		$langId    = null;
		$countryId = null;
		if ($fixLocale == false) {
			$langId   = strtolower($this->cisimplepie->get_language());
			$aLocale  = explode('-', $langId);
			if (count($aLocale) == 2) {
				$countryId = strtolower($aLocale[1]);
			}
		}

		$rss = $this->cisimplepie->get_items(0, 50); // TODO: meter en una constante!

		foreach ($rss as $item) { 
			$aTags = array();
			if ($categories = $item->get_categories()) {
				foreach ((array) $categories as $category) {
					if ($category->get_label() != '') {
						$aTags[] = $category->get_label();
					}
				}
			}
			unset($categories, $category);
			
			$entryAuthor = '';
			if ($author = $item->get_author()) {
				$entryAuthor = $author->get_name();
			}

			$data = array(
				'feedId'       => $feedId,
				'entryTitle'   => $item->get_title(),
				'entryContent' => (string)$item->get_content(),
				'entryDate'    => $item->get_date('Y-m-d H:i:s'),
				'entryUrl'     => (string)$item->get_link(),
				'entryAuthor'  => (string)$entryAuthor,
			);

			if ($data['entryDate'] == null) {
				$data['entryDate'] = date("Y-m-d H:i:s");
			}

			if ($data['entryDate'] == $lastEntryDate) { // si no hay nuevas entries salgo del metodo
				// TODO: revisar, si la entry no tiene fecha, estoy seteando la fecha actual del sistema; y en este caso nunca va a entrar a este IF y va a hacer queries al pedo
				$this->db->update('feeds', 
					array(
						'statusId'        => config_item('feedStatusApproved'),
						'feedLastScan'    => date("Y-m-d H:i:s"),
						'feedMaxRetries'  => 0,
					), 
					array('feedId' => $feedId)
				);
				return;
			}

			$this->Entries_Model->saveEntry($data, $aTags);
		}

		$values = array(
			'statusId'          => config_item('feedStatusApproved'),
			'feedLastScan'      => date("Y-m-d H:i:s"),
			'feedLastEntryDate' => $this->Entries_Model->getLastEntryDate($feedId),
			'feedMaxRetries'    => 0,
		); 
		if (trim((string)$this->cisimplepie->get_title()) != '') {
			$values['feedName'] = (string)$this->cisimplepie->get_title(); 			
		}
		if (trim((string)$this->cisimplepie->get_description()) != '') {
			$values['feedDescription'] = (string)$this->cisimplepie->get_description();
		}
		if (trim((string)$this->cisimplepie->get_link()) != '') {
			$values['feedLink'] = (string)$this->cisimplepie->get_link();
		}
		if ($langId != null) {
			$values['langId'] = $langId;
		}
		if ($countryId != null) {
			$values['countryId'] = $countryId;
		}
		
		$this->db->update('feeds', $values, array('feedId' => $feedId));

		$this->saveFeedIcon($feedId, (element('feedLink', $feed) != '' ? $feed : null));
	}
	
	function updateFeedCounts($feedId) {
		$values = array(
			'feedCountUsers'    => $this->countUsersByFeedId($feedId),
			'feedCountEntries'  => $this->countEntriesByFeedId($feedId)
		);

		$this->db->update('feeds', $values, array('feedId' => $feedId));	
	}

	function countUsersByFeedId($feedId) {
		$query = ' SELECT COUNT(1) AS total 
			FROM users_feeds
			WHERE feedId = '.$feedId.' ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());
		return $query[0]['total'];
	}

	function countEntriesByFeedId($feedId) {
		$query = ' SELECT COUNT(1) AS total 
			FROM entries
			WHERE feedId = '.$feedId.' ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());
		return $query[0]['total'];
	}
	
	function countEntriesStarredByFeedId($feedId) {
		$query = ' SELECT COUNT(1) AS total 
			FROM users_entries
			WHERE feedId = '.(int)$feedId.' 
			AND   tagId  = '.config_item('tagStar');
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());
		return $query[0]['total'];
	}
	
	
	function deleteOldEntries($feedId = null) {
		$this->db
			->select(' DISTINCT feeds.feedId ', false)
			->from('feeds')
			->join('entries', 'entries.feedId = feeds.feedId', 'inner')
			->where('feedCountEntries > ', config_item('entriesKeepMin'))
			->where('entryDate < DATE_ADD(NOW(), INTERVAL -'.config_item('entrieskeepMonthMin').' MONTH)');
			
		if ($feedId != null) {
			$this->db->where('feeds.feedId', $feedId);
		}

		$query = $this->db
			->order_by('feedCountEntries', 'desc')
			->get()->result_array();
		// pr($this->db->last_query()); 
		foreach ($query as $row) {
			$this->deleteOldEntriesByFeedId($row['feedId']);
		}
	}
	
	function deleteOldEntriesByFeedId($feedId) {
		$feedId     = (int)$feedId;
		$limit      = 1000;
		$aEntryId   = array();
		
		$this->db->trans_start();
		

		$query = ' SELECT entryId
			FROM entries 
			WHERE feedId = '.$feedId.'
			AND   entryId NOT IN (
				SELECT entryId 
				FROM 
					(
					SELECT * FROM entries 
					WHERE feedId = '.$feedId.' 
					ORDER BY entries.entryDate DESC 
					LIMIT '.config_item('entriesKeepMin').'
					) AS lastEntries
				UNION ALL
					SELECT entryId
					FROM entries 
					WHERE feedId = '.$feedId.'
					AND  entryDate > DATE_ADD(NOW(), INTERVAL -'.config_item('entrieskeepMonthMin').' MONTH)
				UNION ALL
					SELECT entries.entryId
					FROM entries 
					WHERE feedId = '.$feedId.'
					AND entryId IN (SELECT entryId FROM users_entries WHERE tagId = '.config_item('tagStar').' AND feedId = '.$feedId.') 
				) ';
		$query = $this->db->query($query)->result_array();
		// pr($this->db->last_query()); die;
		foreach ($query as $data) {
			$aEntryId[] = $data['entryId'];
		}
		$aEntryId       = array_unique($aEntryId);
		$aDeleteEntryId = array();
		$total          = count($aEntryId);
		for ($i=0; $i<$total; $i++) {
			$aDeleteEntryId[] = (int)$aEntryId[$i];
			
			if ($i % $limit == 0) {
				$query = ' DELETE FROM entries
					WHERE feedId = '.$feedId.' 
					AND entryId IN ('.implode(', ', $aDeleteEntryId).' ) ';
				$this->db->query($query);
				//pr($this->db->last_query());
				
				$aDeleteEntryId = array();
				sleep(1);
			}
		}
		
		if (!empty($aDeleteEntryId)) {
				$query = ' DELETE FROM entries
					WHERE feedId = '.$feedId.' 
					AND entryId IN ('.implode(', ', $aDeleteEntryId).' ) ';
				$this->db->query($query);
				// pr($this->db->last_query());
		}
		
		$this->updateFeedCounts($feedId);
		$this->db->trans_complete();
		
		file_put_contents("./application/logs/scanFeed.log", date("Y-m-d H:i:s")." - feedId: ".$feedId." - rows deleted: ".$total." \n", FILE_APPEND);
		
//$this->db->trans_rollback();
		
		return $total;
	}

	function processFeedsTags() {
		set_time_limit(0);
		
		$this->db->trans_start();

		
		// Completo datos en la tabla tags y feeds_tags, basado en los tags de cada entry, y en como tageo cada user un feed.
		// Revisar las queries, quizas convenga ajustar un poco el juego para que tire resultados más relevantes
		
		$aSystenTags    = array(config_item('tagAll'), config_item('tagStar'), config_item('tagHome'), config_item('tagBrowse'));
		$dayOfLastEntry = 21;
		
		$this->db->query('DELETE FROM feeds_tags ');
		
		$this->db->update('tags', array('countFeeds' => 0, 'countEntries' => 0, 'countUsers' => 0, 'countTotal' => 0));
		
		$query = ' SELECT feedId, tagId, COUNT(tagId) AS countEntries
			FROM entries_tags
			INNER JOIN tags USING (tagId)
			INNER JOIN entries USING (entryId)
			INNER JOIN feeds USING (feedId)
			WHERE tags.tagId NOT IN ('.implode(', ', $aSystenTags).') 
			AND feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').') 
			AND feedLastEntryDate > DATE_ADD(NOW(), INTERVAL -'.$dayOfLastEntry.' DAY)
			AND feeds.feedSuggest = TRUE 
			GROUP BY feedId, tagId 
			HAVING countEntries > 10 ';
		$query = $this->db->query($query)->result_array();		
		foreach ($query as $row) {		
			$update = 'UPDATE tags SET 
				countFeeds    = countFeeds + 1,
				countEntries  = countEntries + '.$row['countEntries'].'
				WHERE tagId   = '.$row['tagId'];
			$this->db->query($update);
			//pr($this->db->last_query()); 
			
			$update = 'REPLACE INTO feeds_tags (feedId, tagId) VALUES ('.$row['feedId'].', '.$row['tagId'].') ';
			$this->db->query($update);
		}
		
		
		$query = ' SELECT feedId, tagId, COUNT(1) AS countUsers
			FROM users_feeds_tags
			INNER JOIN tags USING (tagId)
			INNER JOIN feeds USING (feedId)
			WHERE tags.tagId NOT IN ('.implode(', ', $aSystenTags).') 
			AND feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').') 
			AND feedLastEntryDate > DATE_ADD(NOW(), INTERVAL -'.$dayOfLastEntry.' DAY)
			AND feeds.feedSuggest = TRUE 
			GROUP BY feedId, userId  ';
		$query = $this->db->query($query)->result_array();		
		foreach ($query as $row) {		
			$update = 'UPDATE tags SET 
				countUsers		= countUsers + 1
				WHERE tagId 	= '.$row['tagId'];
			$this->db->query($update);
			//pr($this->db->last_query()); 
			
			$update = 'REPLACE INTO feeds_tags (feedId, tagId) VALUES ('.$row['feedId'].', '.$row['tagId'].') ';
			$this->db->query($update);
		}		
		
		
		$update = ' UPDATE tags SET countTotal = (countFeeds * 1) + (countUsers + 10)  WHERE tagId NOT IN ('.implode(', ', $aSystenTags).')   ';
		$this->db->query($update);
		
		$this->db->trans_complete();
	}

	function selectFeedsOPML($userId) {
		$query = $this->db->select('feeds.feedId, feedName, feedUrl, tags.tagId, tagName, feeds.feedLink ', false)
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'left')
			->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId AND users_feeds_tags.userId = users_feeds.userId', 'left')
			->join('tags', 'users_feeds_tags.tagId = tags.tagId', 'left')
			->where('users_feeds.userId', $userId)
			->order_by('tagName IS NULL, tagName asc, feedName asc')
			->get('feeds')->result_array();
		//pr($this->db->last_query());
		return $query;
	}
}
