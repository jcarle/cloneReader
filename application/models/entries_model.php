<?php
class Entries_Model extends CI_Model {
	function selectToList($num, $offset, $filter, $feedId = null){
		$this->db
			->select('SQL_CALC_FOUND_ROWS entries.entryId, feedName, entryTitle, entryUrl, entryDate', false)
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner');
			
		if  ($filter != '') {
			$this->db->like('entryTitle', $filter);
		}
		if ($feedId != null) {
			$this->db->where('feeds.feedId', $feedId);
			
		}
			
		$query = $this->db->order_by('entries.entryId')
		 	->get('entries FORCE INDEX ( PRIMARY ) ', $num, $offset);

		//pr($this->db->last_query()); die;

		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select($userId, $userFilters){
		$this->updateUserFilters($userFilters, $userId);
		
		if (!isset($userFilters['page'])) {
			$userFilters['page'] = 1;
		}
		if ($userFilters['type'] == 'tag' && $userFilters['id'] == TAG_STAR) {
			$userFilters['onlyUnread'] = false;
		}
		
		// Tag home, lo tienen todos los usuarios
		if ($userFilters['type'] == 'tag' && $userFilters['id'] == TAG_HOME) {
			return $this->selectFeedCR($userId, $userFilters);
		}


		$indexName = 'PRIMARY';
		$query = $this->db
			->select('users_entries.feedId, feedName, feedUrl, feedLInk, feedIcon, users_entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor, IF(users_entries.tagId = '.TAG_STAR.', true, false) AS starred, entryRead', false)
			->join('entries', 'users_entries.entryId = entries.entryId AND users_entries.feedId = entries.feedId', 'inner')
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
			->where('users_entries.userId', $userId);
		
		if ($userFilters['type'] == 'feed') {
			$indexName = 'indexFeed';
			$this->db->where('users_entries.feedId', (int)$userFilters['id']);
			$this->db->where('users_entries.tagId', TAG_ALL);
		}
		if ($userFilters['type'] == 'tag') {
			$indexName = 'indexTag';
			$this->db->where('users_entries.tagId', (int)$userFilters['id']);
		}
		if ($userFilters['onlyUnread'] == true) {
			$this->db->where('users_entries.entryRead <> true');
		}

		$query = $this->db
			->order_by('users_entries.entryDate', ($userFilters['sortDesc'] == 'true' ? 'desc' : 'asc'))
			->get('users_entries FORCE INDEX ('.$indexName.')', ENTRIES_PAGE_SIZE, ((int)$userFilters['page'] * ENTRIES_PAGE_SIZE) - ENTRIES_PAGE_SIZE)
			->result_array();
		//pr($this->db->last_query());
		
		return $query;
	}
	
	
	function selectFeedCR($userId, $userFilters) {
		$query = $this->db
			->select('feeds.feedId, feedName, feedUrl, feedLInk, feedIcon, entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor ', false)
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
			->where('feeds.feedId', FEED_CLONEREADER)
			->order_by('entries.entryDate', ($userFilters['sortDesc'] == 'true' ? 'desc' : 'asc'))
			->get('entries ', ENTRIES_PAGE_SIZE, ((int)$userFilters['page'] * ENTRIES_PAGE_SIZE) - ENTRIES_PAGE_SIZE)			
			->result_array();
		//pr($this->db->last_query()); 
		
		return $query;
	}		

	function selectFilters($userId) {
		$aFilters = array();
	
		$result = array(
			'tags'		=> $this->selectTagsByUserId($userId),
			'filters'	=> array(
				array(
					'type'		=> 'tag',
					'id'		=> TAG_HOME,
					'name'		=> $this->lang->line('@tag-home'),
					'icon'		=> site_url().'assets/images/default_feed.png', 
				),
				array(
					'type'		=> 'tag',
					'id'		=> TAG_STAR,
					'name'		=> $this->lang->line('@tag-star'), 
					'icon'		=> site_url().'assets/images/star-on.png', 
				),
				array(
					'type'			=> 'tag',
					'id'			=> TAG_BROWSE,
					'name'			=> $this->lang->line('@tag-browse'), 
					'classIcon'		=> 'icon-tags', 
				)				
				
				
			)
		);

		$aFilters['tags'] = array(
			'type'		=> 'tag',
			'id'		=> TAG_ALL,		
			'name'		=> $this->lang->line('@tag-all'),
			'count'		=> 380,
			'expanded'	=> true,
			'childs'	=> array()
		); 		
		
		$result['filters'][] = & $aFilters['tags'];

// FIXME: la version de mysql que hay en dreamhost no soporta pasar limit como parametro de una function
// 	esta harckodeado el valor 1050 adentro de countUnread()! 
		$query = $this->db->select('feeds.feedId, feeds.statusId, feedName, feedUrl, tags.tagId, tagName, users_tags.expanded AS eee, IF(users_tags.expanded = 1, true, false) AS expanded, feeds.feedLink, feeds.feedIcon, countUnread('.$userId.', feeds.feedId, '.TAG_ALL.', '.(FEED_MAX_COUNT + 50).') AS unread', false)
						->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'left')
						->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId AND users_feeds_tags.userId = users_feeds.userId', 'left')
						->join('tags', 'users_feeds_tags.tagId = tags.tagId', 'left')
						->join('users_tags', 'users_tags.userId = users_feeds.userId AND users_tags.tagId = tags.tagId', 'left')
						->where('users_feeds.userId', $userId)
//						->where('feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.')')
						->order_by('tagName IS NULL, tagName asc, feedName asc')
		 				->get('feeds');
		//pr($this->db->last_query());				
		foreach ($query->result() as $row) {
			if ($row->tagId != null && !isset($aFilters[$row->tagId])) {
				$aFilters[$row->tagId] = array(
					'type'		=> 'tag',
					'id'		=> $row->tagId,
					'name'		=> $row->tagName,
					'expanded'	=> ($row->expanded == true),
					'childs'	=> array()				
				); 
				
				$aFilters['tags']['childs'][] = & $aFilters[$row->tagId];
			}
			
			$feed = array(
				'type'		=> 'feed',
				'id'		=> $row->feedId, 
				'name'		=> $row->feedName, 
				'url'		=> $row->feedUrl,
				'icon'		=> ($row->feedIcon == null ? site_url().'assets/images/default_feed.png' : site_url().'assets/favicons/'.$row->feedIcon), 
				'count'		=> $row->unread,
			);

			if ($row->tagId != null) {
				$aFilters[$row->tagId]['childs'][] = $feed;
			}
			else {
				$aFilters['tags']['childs'][] = $feed;
			}
		}

		return $result;
	}

	function getTotalByFeedIdAndUserId($feedId, $userId) {
		$query = ' SELECT 
				COUNT(1) AS total FROM ( 
					SELECT 1 
					FROM users_entries FORCE INDEX (indexUnread)
					WHERE feedId 		= '.$feedId.'
					AND   userId	 	= '.$userId.'
					AND   tagId			= '.TAG_ALL.' 
					AND   entryRead 	= false 
					LIMIT '.(FEED_MAX_COUNT + 50).' 
			) AS tmp ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());
		return $query[0]['total'];
	}
		
	function selectTagsByUserId($userId) {
		$query = $this->db->select('tags.tagId, tagName ', false)
			->join('users_tags', 'users_tags.tagId = tags.tagId', 'inner')
			->where('users_tags.userId', $userId)
			->where('tags.tagId NOT IN ('.TAG_ALL.', '.TAG_STAR.', '.TAG_HOME.')')
			->order_by('tagName asc')
			->get('tags');
		//pr($this->db->last_query());				
		return $query->result_array();
	}

	function get($entryId){
		$result = $this->db
				->select('entries.*, feedName', true)
				->where('entryId', $entryId)
				->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
				->get('entries')->row_array();
		return $result;
	}

	function getEntryIdByEntryUrl($entryUrl) {
		$entryUrl = substr(trim($entryUrl), 0, 255);
		
		$result = $this->db
				->where('entryUrl', $entryUrl)
				->get('entries')->row_array();
		return $result['entryId'];		
	} 
	
	function getLastEntryDate($feedId) {
		$query = $this->db
				->where('feedId', $feedId)
				->order_by('entryDate', 'desc')
				//->get('entries FORCE INDEX (indexFeedIdEntryDate)', 1)->row_array();
				->get('entries', 1)->row_array();
		//pr($this->db->last_query());	
		if (!empty($query)) {
			return $query['entryDate'];
		}
		return null;
	}
	
	function save($data){
// TODO: usar el metodo saveEntry		
		$entryId = $data['entryId'];

		$values = array(
			'feedId'			=> element('feedId', $data),
			'entryTitle'		=> element('entryTitle', $data),
			'entryContent'		=> element('entryContent', $data),
			'entryAuthor'		=> element('entryAuthor', $data),
			'entryDate'			=> element('entryDate', $data),
			'entryUrl'			=> element('entryUrl', $data),
		);

		if ((int)$entryId != 0) {		
			$this->db->where('entryId', $entryId)->update('entries', $values);
		}
		else {
			$this->db
				->ignore()
				->insert('entries', $values);
			$entryId = $this->db->insert_id();
		}
		//pr($this->db->last_query());

		return true;
	}
	
	function saveEntry($data, $aTags = null) {
		if (trim($data['entryUrl']) == '') {
			return null;
		}
		
		$this->db->ignore()->insert('entries', $data);
		//pr($this->db->last_query());
		$entryId = $this->db->insert_id();
		
		if ((int)$entryId == 0) {
			$entryId = $this->getEntryIdByEntryUrl($data['entryUrl']);
		}
		
		
		if (!empty($aTags) && $entryId != null) {
			foreach ($aTags as $tagName) {
				$tagId = $this->addTag($tagName);
				$this->db->ignore()->insert('entries_tags', array('entryId' => $entryId, 'tagId' => $tagId));
			}
		}
		
		return $entryId;
	}

	function delete($entryId) {
		$this->db->delete('entries', array('entryId' => $entryId));
		return true;
	}	

	function saveTmpUsersEntries($userId, $entries) { // utilizo una tabla temporal para guardar los leidos y no romper la paginación infinita
		$aQueries = array();
		foreach ($entries as $entry) {
			$aQueries[] = ' ('.(INT)$userId.', '.(INT)$entry['entryId'].', '.($entry['entryRead'] == true ? 'true' : 'false').', '.(element('starred', $entry) == true ? 'true' : 'false').') ';

		}
		if (count($aQueries) == 0) {
			return;
		}

		$query = 'REPLACE INTO tmp_users_entries (userId, entryId, entryRead, starred) VALUES '.implode(', ', $aQueries).';';
		$this->db->query($query);
		//pr($this->db->last_query());
	}

	// guarda los cambios en la tabla users_entries
	function pushTmpUserEntries($userId) {
//		$aQueries 	= array();
		$entries 	= $this->db->where('userId', $userId)->get('tmp_users_entries')->result_array();
		//pr($this->db->last_query()); 
		
		foreach ($entries as $entry) {		
			if ($entry['starred'] == true) {
				//$aQueries[] = 
				$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate)  
					SELECT userId, entryId, feedId, '.TAG_STAR.', entryRead, entryDate
					FROM users_entries 
					WHERE 	userId	= '.$userId.'
					AND 	tagId	= '.TAG_ALL.'
					AND 	entryId = '.$entry['entryId'];
				$this->db->query($query);
				//pr($this->db->last_query());	 
			}
			else {
				//$aQueries[] = 'DELETE FROM users_entries WHERE userId = '.$userId.' AND entryId = '.$entry['entryId'].' AND tagId = '.TAG_STAR;
				$this->db->delete('users_entries', array(
					'userId'	=> $userId,
					'entryId'	=> $entry['entryId'],
					'tagId'		=> TAG_STAR
				));
			}
			
/*			$aQueries[] = 'UPDATE users_entries SET
				entryRead 		= '.$entry['entryRead'].'
				WHERE userId	= '.$userId.'
				AND   entryId	= '.$entry['entryId'];*/				
			
			$this->db
				->where(array(
					'userId'	=> $userId,
					'entryId'	=> $entry['entryId'])
				)
				->update('users_entries', array('entryRead' => $entry['entryRead']));
			//pr($this->db->last_query());				
		}
	
//pr(implode(';', $aQueries));		
//$this->db->conn_id->multi_query(implode(';', $aQueries));
		//$this->db->query(implode(';', $aQueries));
		//pr($this->db->last_query());				
		$this->db->delete('tmp_users_entries', array('userId' => $userId));
		// TODO: enviar todos estos queries juntos al servidor
	}
	
	function saveUserTags($userId, $tags) {
		foreach ($tags as $tag) {
			$this->db->replace('users_tags', array('userId' => $userId, 'tagId' => $tag['tagId'], 'expanded' => element('expanded', $tag) == true));
			//pr($this->db->last_query());	
		}
	}

	function saveUserFeedTag($userId, $feedId, $tagId, $append) {
		$values = array('userId' => $userId, 'feedId' => $feedId, 'tagId' => $tagId);

		if ($append == false) {
			$this->db->delete('users_feeds_tags', $values);
			//pr($this->db->last_query());	
			
			$this->db->delete('users_entries', array(
				'userId' => $userId,
				'feedId' => $feedId,
				'tagId'	 => $tagId
			));
			//pr($this->db->last_query());
			
			return true;
		}

		$this->db
			->ignore()
			->insert('users_feeds_tags', $values);
		//pr($this->db->last_query());
		
		$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate)  
						SELECT userId, entryId, feedId, '.$tagId.', entryRead, entryDate
						FROM users_entries
						WHERE users_entries.userId 	= '.$userId.'
						AND   users_entries.feedId 	= '.$feedId.'
						AND   users_entries.tagId	= '.TAG_ALL.' ';			
		$this->db->query($query);
		//pr($this->db->last_query());
								
		return true;
	}
	
	function updateFeedStatus($feedId, $statusId) {
		$this->db
			->where('feedId', $feedId)
			->update('feeds', array('statusId' => $statusId ));
		//pr($this->db->last_query());		
	}	

	function addFeed($userId, $feed) {
		$this->load->model('Feeds_Model');
		$feedId = $this->Feeds_Model->save($feed);

		$this->subscribeFeed($feedId, $userId);
		//pr($this->db->last_query());
		
		return $feedId;
	}

	function addTag($tagName, $userId = null, $feedId = null) {
		$tagName 	= substr(trim($tagName), 0, 200);
		$tagId		= null;

		$query = $this->db->where('tagName', $tagName)->get('tags')->result_array();
		//pr($this->db->last_query()); 
		if (!empty($query)) {
			$tagId = $query[0]['tagId'];
		}
		else {
			$this->db->insert('tags', array( 'tagName'	=> $tagName ));
			$tagId = $this->db->insert_id();
			//pr($this->db->last_query());
		}

		if ($userId != null) {
// FIXME: no esta guardando los entries que ya existen en el nuevo tag en la tabla 'users_entries'			
			$this->db->ignore()->insert('users_tags', array( 'tagId'=> $tagId, 'userId' => $userId ));
			//pr($this->db->last_query());
		}

		if ($feedId != null) {
			$this->db->replace('users_feeds_tags', array( 'tagId'=> $tagId, 'feedId'	=> $feedId, 'userId' => $userId ));
			//pr($this->db->last_query());
		}
		
		return $tagId;
	}

	function subscribeFeed($feedId, $userId) {
		$this->db->ignore()->insert('users_feeds', array( 'feedId'	=> $feedId, 'userId' => $userId ));
		//pr($this->db->last_query());		
		
		return true;		
	}
	
	function unsubscribeFeed($feedId, $userId) {
		$this->db->delete('users_feeds', array('feedId' => $feedId, 'userId' => $userId));
		//pr($this->db->last_query());
		
		$this->db->delete('users_entries', array('feedId' => $feedId, 'userId' => $userId, 'tagId <>' => TAG_STAR));
		//pr($this->db->last_query());
		return true;		
	}
	
	function markAllAsFeed($userId, $type, $id) {
		$aFeedId = array();

		if ($type == 'tag') {
			if ($id != TAG_ALL) {
				$query = $this->db->select('feedId')
					->from('users_feeds_tags')
					->where('tagId', $id)
					->where('userId', $userId)
					->get()->result_array();
				foreach ($query as $row) {
					$aFeedId[] = $row['feedId'];
				}
			}
		}
		else {
			$aFeedId[] = $id;
		}


		$this->db->where('userId', $userId);
		if (!empty($aFeedId)) {
			$this->db->where_in('feedId', $aFeedId);
		}
		$this->db->update('users_entries', array('entryRead' => true));
		//pr($this->db->last_query());   die;
		return true;		
	}	
	
	function saveUserEntries($userId, $feedId) {
		$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 		
						SELECT '.$userId.', entries.entryId, entries.feedId, '.TAG_ALL.', FALSE , entries.entryDate
						FROM entries 
						WHERE feedId = '.$feedId.'
						ORDER BY entryId DESC
						LIMIT 100 '; // TODO: meter en una constante!
		$this->db->query($query);
	}
	
	function browseTags($userId) {
		$this->load->model('Languages_Model');
		$languages = $this->Languages_Model->getRelatedLangs($this->session->userdata('langId'));
		
		$query = ' SELECT * FROM (
						SELECT DISTINCT tags.tagId, tagName, countTotal
						FROM tags 
						INNER JOIN feeds_tags 	ON feeds_tags.tagId 	= tags.tagId 
						INNER JOIN feeds 		ON feeds.feedId 		= feeds_tags.feedId 
						WHERE feeds.feedId NOT IN ( SELECT feedId FROM users_feeds WHERE userId = '.(int)$userId.') 
						AND feeds.langId IN (\''.implode('\' , \'', $languages).'\')
						ORDER BY countTotal DESC LIMIT 50
					) AS tmp
					ORDER BY tagName ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());   die;			
		return $query;
	}
	
	function browseFeedsByTagId($userId, $tagId) {
		$this->load->model('Languages_Model');
		$languages = $this->Languages_Model->getRelatedLangs($this->session->userdata('langId'));
				
		$query = ' SELECT DISTINCT feeds.feedId, feedName, feedUrl, feedLink, feeds.feedIcon, feedDescription
						FROM tags 
						INNER JOIN feeds_tags 	ON feeds_tags.tagId 	= tags.tagId 
						INNER JOIN feeds 		ON feeds.feedId 		= feeds_tags.feedId 
						WHERE tags.tagId = '.(INT)$tagId.'
						AND feeds.feedId NOT IN ( SELECT feedId FROM users_feeds WHERE userId = '.(int)$userId.') 
						AND feeds.langId IN (\''.implode('\' , \'', $languages).'\')
						ORDER BY feedName ASC LIMIT 50 ';	
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());   die;			
		return $query;
	}	
	
	function updateUserFilters($userFilters, $userId){
		unset($userFilters['page']);
		$this->load->model('Users_Model');
		$this->Users_Model->updateUserFiltersByUserId($userFilters, (int)$userId);
	}	
	
	function saveFeedIcon($feedId, $feedLink, $feedIcon) {
		if (trim($feedLink) != '' && $feedIcon == null) {
			$this->load->spark('curl/1.2.1');
			$img 			= $this->curl->simple_get('https://plus.google.com/_/favicon?domain='.$feedLink);
			$parse 			= parse_url($feedLink);
			$feedIcon 	= $parse['host'].'.png'; 
			file_put_contents('./assets/favicons/'.$feedIcon, $img);
			$this->db->update('feeds', array('feedIcon' => $feedIcon), array('feedId' => $feedId));	
		}				
	}	

	function getNewsEntries($userId = null, $feedId = null) {
		set_time_limit(0);
		
		$this->db
			->select(' DISTINCT feeds.feedId, feedUrl, feedLink, feedIcon, fixLocale', false)
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
			->where('feedLastScan < DATE_ADD(NOW(), INTERVAL -'.FEED_TIME_SCAN.' MINUTE)')
			->where('feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.')')
//->where('feeds.feedId IN (530)')
			->order_by('feedLastScan ASC');

		if (is_null($userId) == false) {
			$this->db->where('users_feeds.userId', $userId);
		}
		if (is_null($feedId) == false) {
			$this->db->where('feeds.feedId', $feedId);			
		}
		 
		$query = $this->db->get('feeds');
		//pr($this->db->last_query()); 
		foreach ($query->result() as $row) {
			$this->parseRss($row->feedId, $row->feedUrl, $row->fixLocale);
			$this->saveFeedIcon($row->feedId, $row->feedLink, $row->feedIcon);
		}
	}		

	// TODO: mover estos metodos de aca
	function parseRss($feedId, $feedUrl, $fixLocale = false) {
		// vuelvo a preguntar si es momento de volver a scanner el feed, ya que pude haber sido scaneado recién al realizar multiples peticiones asyncronicas
		$query = $this->db
			->select('feedLastEntryDate, TIMESTAMPDIFF(MINUTE, feedLastScan, DATE_ADD(NOW(), INTERVAL -'.FEED_TIME_SCAN.' MINUTE)) AS minutes ', false)
			->where('feeds.feedId', $feedId)
			->get('feeds')->result_array();
		//pr($this->db->last_query()); 
		$feed = $query[0];
		if ($feed['minutes'] != null && (int)$feed['minutes'] < FEED_TIME_SCAN ) {  // si paso poco tiempo salgo, porque acaba de escanear el mismo feed otro proceso
			return;
		}

		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();

		if ($this->cisimplepie->error() ) {
			return $this->updateFeedStatus($feedId, FEED_STATUS_NOT_FOUND);
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
				// TODO: revisar, si la entry no tiene fecha, estoy seteando la fecha actual del sistema; y en este caso nunca va a entrar a este IF
				$this->db->update('feeds', array(
					'statusId' 		=> FEED_STATUS_APPROVED,
					'feedLastScan' 	=> date("Y-m-d H:i:s")
				), array('feedId' => $feedId));	
				return;
			}
			
			$this->saveEntry($data, $aTags);
		}

		$values = array( 
			'statusId'			=> FEED_STATUS_APPROVED,
			'feedLastScan' 		=> date("Y-m-d H:i:s"),
			'feedLastEntryDate' => $this->getLastEntryDate($feedId),
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
	}
	
	function populateMillionsEntries() {
		ini_set('memory_limit', '-1');
				
		$query = $this->db->select('MAX(entryId) + 1 AS entryId', true)->get('entries')->result();
		//pr($this->db->last_query()); 
		$entryId = $query[0]->entryId;

		$query = $this->db
			->select('feeds.feedId')
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
//			->where('feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.')')
			->get('feeds');
		//pr($this->db->last_query()); 
		foreach ($query->result() as $row) {		
			
			$this->db->trans_start();

			$data = array();
			for ($i=0; $i<10000; $i++) {
				$data[] = array(
					'feedId' 		=> $row->feedId,
					'entryTitle'	=> 'titulooooo '.$entryId,
					'entryContent'	=> 'contenido del entry <b><test/b>'.$entryId,
					'entryDate'		=> date('Y-m-d H:i:s'),
					'entryUrl'		=> 'http://saranga.com/dadadad/'.$entryId,
					'entryAuthor'	=> 'el autor',
				);
				
				
				if (($i % 100) == 0) { 
					$this->db->insert_batch('entries', $data);
					//$this->db->insert('entries', $data);
					unset($data);
					$data = array();
				}
				
				$entryId++;
			}
			$this->db->insert_batch('entries', $data);
			//pr($this->db->last_query());
			
			$this->db->trans_complete();
		}
	}
	
	function saveEntriesTagByUser($userId) {
		// TODO: paginar este proceso para que guarde TODAS las entradas nuevas sin tener que relodear
		// metiendo 20 millones de entradas nuevas hay que relodear bocha de veces hasta ver la mas nueva
		$entryId 	= null;
		$limit 		= 1000;
		
		$query = ' SELECT
						MAX(entryId) AS entryId
						FROM  users_entries  
						WHERE userId  	= '.$userId.' 
						AND   tagId 	= '.TAG_ALL;
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			$entryId = $query[0]['entryId'];
		}		

		// save TAG_ALL
		$query = ' INSERT INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 
					SELECT users_feeds.userId, entries.entryId, entries.feedId, '.TAG_ALL.', false, entries.entryDate 
					FROM entries 
					INNER JOIN users_feeds 
						ON entries.feedId = users_feeds.feedId
						AND users_feeds.userId = '.$userId.' 
					LEFT JOIN users_entries
						ON 		users_entries.userId 	= users_feeds.userId
						AND 	users_entries.entryId 	= entries.entryId
						AND 	users_entries.feedId 	= entries.feedId
						AND 	users_entries.tagId 	= '.TAG_ALL.'
					WHERE users_entries.userId IS NULL
					'.($entryId != null ? ' AND entries.entryId > '.$entryId : '').'
				LIMIT '.$limit;
		$this->db->query($query);
		//pr($this->db->last_query());
		
		// save Custom Tags
		$query = ' INSERT INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 
					SELECT users_feeds_tags.userId, entries.entryId, entries.feedId, users_feeds_tags.tagId, false, entries.entryDate
					FROM entries 
					INNER JOIN users_feeds_tags FORCE INDEX (indexUserIdFeedId) 
						ON users_feeds_tags.feedId = entries.feedId 
						AND users_feeds_tags.userId = '.$userId.' 
					LEFT JOIN users_entries
						ON users_entries.userId  		= users_feeds_tags.userId
						AND   users_entries.entryId 	= entries.entryId
						AND   users_entries.feedId		= entries.feedId 
						AND   users_entries.tagId		= users_feeds_tags.tagId 
					WHERE users_entries.userId IS NULL 
					'.($entryId != null ? ' AND entries.entryId > '.$entryId : '').'
					LIMIT '.$limit;
		$this->db->query($query);
		//pr($this->db->last_query());

		if ($this->db->affected_rows() == $limit) {
			sleep(2);
			$this->saveEntriesTagByUser($userId);
		}
	}
	
	function processTagBrowse() {
		set_time_limit(0);

		
		// Completo datos en la tabla tags y feeds_tags, basado en los tags de cada entry, y en como tageo cada user un feed.
		// Revisar las queries, quizas convenga ajustar un poco el juego para que tire resultados más relevantes
		
		$aSystenTags 	= array(TAG_ALL, TAG_STAR, TAG_HOME, TAG_BROWSE);
		$dayOfLastEntry = 7;
		
		$this->db->query('DELETE FROM feeds_tags ');
		
		$this->db->update('tags', array('countFeeds' => 0, 'countEntries' => 0, 'countUsers' => 0, 'countTotal' => 0));
		
		$query = ' SELECT feedId, tagId, COUNT(tagId) AS countEntries
			FROM entries_tags
			INNER JOIN tags USING (tagId)
			INNER JOIN entries USING (entryId)
			INNER JOIN feeds USING (feedId)
			WHERE tags.tagId NOT IN ('.implode(', ', $aSystenTags).') 
			AND feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.') 
			AND feedLastEntryDate > DATE_ADD(NOW(), INTERVAL -'.$dayOfLastEntry.' DAY)
			AND feeds.feedSuggest = TRUE 
			GROUP BY feedId, tagId 
			HAVING countEntries > 10 ';
		$query = $this->db->query($query)->result_array();		
		foreach ($query as $row) {		
			$update = 'UPDATE tags SET 
				countFeeds		= countFeeds + 1,
				countEntries 	= countEntries + '.$row['countEntries'].'
				WHERE tagId 	= '.$row['tagId'];
			$this->db->query($update);
			//pr($this->db->last_query()); 
			
			$update = 'REPLACE INTO feeds_tags (feedId, tagId) VALUES ('.$row['feedId'].', '.$row['tagId'].') ';
			$this->db->query($update);
		}
		
		
		$query = ' SELECT feedId, tagId, COUNT(*) AS countUsers
			FROM users_feeds_tags
			INNER JOIN tags USING (tagId)
			INNER JOIN feeds USING (feedId)
			WHERE tags.tagId NOT IN ('.implode(', ', $aSystenTags).') 
			AND feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.') 
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
	}
}
