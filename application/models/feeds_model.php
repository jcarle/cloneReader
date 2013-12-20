<?php
class Feeds_Model extends CI_Model {
	function selectToList($num, $offset, $filter = null, $statusId = null, $countryId = null, $langId = null, $tagId = null, $userId = null, $feedSuggest = null){
		$languages = null;
		if ($langId != null) { // Busca lenguages relacionados: si el filtro esta seteado en 'en', trae resultados con 'en-us', 'en-uk', etc tambien
			// TODO: poner un ckeckbox para definir si queres aplicar el filtro asi o no
			$this->load->model('Languages_Model');
			$languages = $this->Languages_Model->getRelatedLangs($langId);
		}
				
		$this->db
			->select('SQL_CALC_FOUND_ROWS feeds.feedId, feedName, feedDescription, feedUrl, feedLink, statusId, countryName, langName, feedLastScan, feedLastEntryDate', false)
			->join('countries', 'countries.countryId = feeds.countryId', 'left')
			->join('languages', 'languages.langId = feeds.langId', 'left');
			
		if ($filter != null) {
			$this->db->like('feedName', $filter);
		}
		if ($statusId != null) {
			$this->db->where('feeds.statusId', $statusId);
		}
		if ($countryId != null) {
			$this->db->where('feeds.countryId', $countryId);
		}
		if ($languages != null) {
			$this->db->where('feeds.langId IN (\''.implode('\', \'', $languages).'\')' );
		}
		if ($tagId != null) {
			$this->db->join('feeds_tags', 'feeds_tags.feedId = feeds.feedId', 'inner');
			$this->db->where('feeds_tags.tagId', $tagId);
		}
		if ($userId != null) {
			$this->db->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner');
			$this->db->where('users_feeds.userId', $userId);
		}
		if ($feedSuggest == true) {
			$this->db->where('feeds.feedSuggest', true);
		}
		
		$query = $this->db->get('feeds', $num, $offset);
		//pr($this->db->last_query()); die;
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select(){
		return $this->db->get('feeds');
	}

	function get($feedId){
		return $this->db->where('feeds.feedId', $feedId)->get('feeds')->row_array();
	}	
	
	function save($data){
		$feedId = (int)element('feedId', $data);
		
		if (trim($data['feedUrl']) == '') {
			return null;
		}
		
		$values = array(
			'feedUrl' 			=> $data['feedUrl'], 
			'statusId' 			=> FEED_STATUS_PENDING,
			'countryId'			=> element('countryId', $data),
			'langId'			=> element('langId', $data),
			'feedSuggest'		=> (element('feedSuggest', $data) == 'on'),
			'fixLocale'			=> (element('fixLocale', $data) == 'on'),
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
			$this->db
				->where('feedId', $feedId)
				->update('feeds', $values);
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
	
	
// TODO: quitar este metodo, llamar a scanFeed 	
	function scan($feedId) {
		$this->db
			->where('feedId', $feedId)
			->update('feeds', array(
				'feedLastScan' 			=> null,
				'feedLastEntryDate'		=> null, 
				'statusId' 				=> 0,
			));		
			
		$this->load->model('Entries_Model');
		
		$this->Entries_Model->getNewsEntries(null, $feedId);
	}
	
	function search($filter){
		$filter = $this->db->escape_like_str($filter);
		
		return $this->db
			->select('DISTINCT feedId AS id, feedName AS text  ', false)
//			->where('statusId', STATUS_ACTIVE)
			->like('feedName', $filter)
			->order_by('text')
			->get('feeds', AUTOCOMPLETE_SIZE)->result_array();
	}	
	
	
	/**
	 * Busca tags que tengan feeds. 
	 */
	function searchTags($filter){
		$filter = $this->db->escape_like_str($filter);

		return $this->db
			->select('DISTINCT tags.tagId AS id, tagName AS text  ', false)
			->join('feeds_tags', 'feeds_tags.tagId = tags.tagId ', 'inner')
			->like('tagName', $filter)
			->order_by('text')
			->get('tags', AUTOCOMPLETE_SIZE)->result_array();
	}	
	
	function saveFeedIcon($feedId, $feed = null) {
		if ($feed == null) {
			$feed = $this->get($feedId);
			$this->load->model('Entries_Model');
		 	$feedLink = $feed['feedLink'];
		 	$feedIcon = $feed['feedIcon'];
		}
		
		if (trim($feedLink) != '' && $feedIcon == null) {
			$this->load->spark('curl/1.2.1');
			$img 			= $this->curl->simple_get('https://plus.google.com/_/favicon?domain='.$feedLink);
			$parse 			= parse_url($feedLink);
			$feedIcon 		= $parse['host'].'.png'; 
			file_put_contents('./assets/favicons/'.$feedIcon, $img);
			$this->db->update('feeds', array('feedIcon' => $feedIcon), array('feedId' => $feedId));	
		}

		return true;				
	}
	
	function updateFeedStatus($feedId, $statusId) {
		$this->db
			->where('feedId', $feedId)
			->set('statusId', $statusId)
			->update('feeds');
		//pr($this->db->last_query());		
	}
	
	function scanFeed($feedId) {
		$this->load->model('Entries_Model');
//sleep(5);
		

		// vuelvo a preguntar si es momento de volver a scanner el feed, ya que pude haber sido scaneado recién al realizar multiples peticiones asyncronicas
		$query = $this->db
			->select('feedLastEntryDate, feedUrl, fixLocale, feedMaxRetries, feedLink, feedIcon, TIMESTAMPDIFF(MINUTE, feedLastScan, DATE_ADD(NOW(), INTERVAL -'.FEED_TIME_SCAN.' MINUTE)) AS minutes ', false)
			->where('feeds.feedId', $feedId)
			->get('feeds')->result_array();
		//pr($this->db->last_query()); 
		$feed = $query[0];
		if ($feed['minutes'] != null && (int)$feed['minutes'] < FEED_TIME_SCAN ) {  // si paso poco tiempo salgo, porque acaba de escanear el mismo feed otro proceso
//			return;
		}
		
		$feedUrl		= $feed['feedUrl']; 
		$fixLocale		= $feed['fixLocale'];
		$feedMaxRetries = $feed['feedMaxRetries'];

		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();

		if ($this->cisimplepie->error() ) {
			if ($feedMaxRetries < FEED_MAX_RETRIES) { 
				$this->db
					->where('feedId', $feedId)
					->set('feedMaxRetries',	' feedMaxRetries + 1 ', false)
					->set('feedLastScan', date("Y-m-d H:i:s"))
					->update('feeds');			
			}
			else {
				$this->updateFeedStatus($feedId, FEED_STATUS_NOT_FOUND);
			}
			return;
		}

		$lastEntryDate = $feed['feedLastEntryDate'];
		
		$langId		= null;
		$countryId 	= null;
		if ($fixLocale == false) {
			$langId 	= strtolower($this->cisimplepie->get_language());
			$aLocale 	= explode('-', $langId);
			if (count($aLocale) == 2) {
				$countryId 	= strtolower($aLocale[1]);
			}
		}

		$rss = $this->cisimplepie->get_items();

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
				'feedId' 		=> $feedId,
				'entryTitle'	=> $item->get_title(),
				'entryContent'	=> (string)$item->get_content(),
				'entryDate'		=> $item->get_date('Y-m-d H:i:s'),
				'entryUrl'		=> (string)$item->get_link(),
				'entryAuthor'	=> (string)$entryAuthor,
			);

			if ($data['entryDate'] == null) {
				$data['entryDate'] = date("Y-m-d H:i:s");
			}

			if ($data['entryDate'] == $lastEntryDate) { // si no hay nuevas entries salgo del metodo
				// TODO: revisar, si la entry no tiene fecha, estoy seteando la fecha actual del sistema; y en este caso nunca va a entrar a este IF y va a hacer queries al pedo
				$this->db->update('feeds', array(
					'statusId' 			=> FEED_STATUS_APPROVED,
					'feedLastScan' 		=> date("Y-m-d H:i:s"),
					'feedMaxRetries'	=> 0,
				), array('feedId' => $feedId));	
				return;
			}

			$this->Entries_Model->saveEntry($data, $aTags);
		}

		$values = array( 
			'statusId'			=> FEED_STATUS_APPROVED,
			'feedLastScan' 		=> date("Y-m-d H:i:s"),
			'feedLastEntryDate' => $this->Entries_Model->getLastEntryDate($feedId),
			'feedMaxRetries'	=> 0,
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
		
		$this->saveFeedIcon($feedId, $feed);
	}	
}
