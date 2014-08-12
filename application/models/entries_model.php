<?php
class Entries_Model extends CI_Model {
	function selectToList($num, $offset, $filter, $feedId = null, $orderBy = '', $orderDir = ''){
		$this->db
			->select('SQL_CALC_FOUND_ROWS entries.entryId, feedName, entryTitle, entryUrl, entryDate', false)
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner');
			
		if  ($filter != '') {
			$this->db->like('entryTitle', $filter);
		}
		if ($feedId != null) {
			$this->db->where('feeds.feedId', $feedId);
		}
			
		if (!in_array($orderBy, array('entryId', 'entryDate' ))) {
			$orderBy = 'entryId';
		}
		$this->db->order_by($orderBy, $orderDir == 'desc' ? 'desc' : 'asc');

		
		$query = $this->db->get('entries ', $num, $offset);

		//pr($this->db->last_query()); die;

		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select($userId, $aFilters){
//$aFilters = array();		
		// Default filters, por si viaja mal el js.
		$userFilters = array_merge(
			array(
				'page'			=> 1,
				'onlyUnread'	=> true,
				'sortDesc'	 	=> true,
				'id' 			=> config_item('tagHome'), 
				'type'	 		=> 'tag',
				'viewType'	 	=> 'detail',
				'isMaximized' 	=> false,
			), $aFilters);

		if ($userFilters['type'] == 'tag' && $userFilters['id'] == config_item('tagStar')) {
			$userFilters['onlyUnread'] = false;
		}
		
		$this->updateUserFilters($userFilters, $userId);
		
		// Tag home, lo tienen todos los usuarios
		if ($userFilters['type'] == 'tag' && $userFilters['id'] == config_item('tagHome')) {
			return $this->selectFeedCR($userId, $userFilters);
		}


		$indexName = 'PRIMARY';
		$query = $this->db
			->select('users_entries.feedId, feedName, feedUrl, feedLInk, feedIcon, users_entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor, IF(users_entries.tagId = '.config_item('tagStar').', true, false) AS starred, entryRead', false)
			->join('entries', 'users_entries.entryId = entries.entryId AND users_entries.feedId = entries.feedId', 'inner')
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
			->where('users_entries.userId', $userId);
		
		if ($userFilters['type'] == 'feed') {
			$indexName = 'indexFeed';
			$this->db->where('users_entries.feedId', (int)$userFilters['id']);
			$this->db->where('users_entries.tagId', config_item('tagAll'));
		}
		if ($userFilters['type'] == 'tag') {
			$indexName = 'indexTag';
			$this->db->where('users_entries.tagId', (int)$userFilters['id']);
		}
		if ($userFilters['onlyUnread'] == true) {
			$this->db->where('users_entries.entryRead <> true');
		}

		$query = $this->db
			->order_by('users_entries.entryDate', ($userFilters['sortDesc'] == true ? 'desc' : 'asc'))
			->get('users_entries FORCE INDEX ('.$indexName.')', config_item('entriesPageSize'), ((int)$userFilters['page'] * config_item('entriesPageSize')) - config_item('entriesPageSize'))
			->result_array();
		//pr($this->db->last_query()); die;
		
		return $query;
	}
	
	
	function selectFeedCR($userId, $userFilters) {
		$query = $this->db
			->select('feeds.feedId, feedName, feedUrl, feedLInk, feedIcon, entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor ', false)
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
			->where('feeds.feedId', config_item('feedCloneReader'))
			->order_by('entries.entryDate', ($userFilters['sortDesc'] == true ? 'desc' : 'asc'))
			->get('entries ', config_item('entriesPageSize'), ((int)$userFilters['page'] * config_item('entriesPageSize')) - config_item('entriesPageSize'))
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
					'id'		=> config_item('tagHome'),
					'name'		=> $this->lang->line('@tag-home'),
					'icon'		=> site_url().'assets/images/default_feed.png', 
				),
				array(
					'type'		=> 'tag',
					'id'		=> config_item('tagStar'),
					'name'		=> $this->lang->line('@tag-star'), 
					'icon'		=> site_url().'assets/images/star-on.png', 
				),
				array(
					'type'			=> 'tag',
					'id'			=> config_item('tagBrowse'),
					'name'			=> $this->lang->line('@tag-browse'), 
					'classIcon'		=> 'fa fa-tags', 
				)
			)
		);

		$aFilters['tags'] = array(
			'type'		=> 'tag',
			'id'		=> config_item('tagAll'),		
			'name'		=> $this->lang->line('@tag-all'),
			'count'		=> 380,
			'expanded'	=> true,
			'childs'	=> array()
		); 		
		
		$result['filters'][] = & $aFilters['tags'];

// FIXME: la version de mysql que hay en dreamhost no soporta pasar limit como parametro de una function
// 	esta harckodeado el valor 1050 adentro de countUnread()! 
		$query = $this->db->select('feeds.feedId, feeds.statusId, feedName, feedUrl, tags.tagId, tagName, users_tags.expanded AS eee, IF(users_tags.expanded = 1, true, false) AS expanded, feeds.feedLink, feeds.feedIcon, countUnread('.$userId.', feeds.feedId, '.config_item('tagAll').', '.(config_item('feedMaxCount') + 50).') AS unread', false)
						->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'left')
						->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId AND users_feeds_tags.userId = users_feeds.userId', 'left')
						->join('tags', 'users_feeds_tags.tagId = tags.tagId', 'left')
						->join('users_tags', 'users_tags.userId = users_feeds.userId AND users_tags.tagId = tags.tagId', 'left')
						->where('users_feeds.userId', $userId)
//						->where('feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').')')
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
					AND   tagId			= '.config_item('tagAll').' 
					AND   entryRead 	= false 
					LIMIT '.(config_item('feedMaxCount') + 50).' 
			) AS tmp ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());
		return $query[0]['total'];
	}
		
	function selectTagsByUserId($userId) {
		$query = $this->db->select('tags.tagId, tagName ', false)
			->join('users_tags', 'users_tags.tagId = tags.tagId', 'inner')
			->where('users_tags.userId', $userId)
			->where('tags.tagId NOT IN ('.config_item('tagAll').', '.config_item('tagStar').', '.config_item('tagHome').')')
			->order_by('tagName asc')
			->get('tags');
		//pr($this->db->last_query());				
		return $query->result_array();
	}

	function get($entryId, $isForm = false){
		$result = $this->db
				->select('entries.*, feedName, feedLink, feedUrl', true)
				->where('entryId', $entryId)
				->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
				->get('entries')->row_array();
				
		if ($isForm == true) {
			$result['feedId'] = array( 'id' => element('feedId', $result), 'text' => element('feedName', $result));
		}

		return $result;
	}

	function getEntryIdByFeedIdAndEntryUrl($feedId, $entryUrl) {
		$entryUrl = substr(trim($entryUrl), 0, 255);
		
		$result = $this->db
				->where(array( 'feedId' => $feedId,'entryUrl' => $entryUrl ))
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
			'entryUrl'			=> substr(trim(element('entryUrl', $data)), 0, 255),
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
			$entryId = $this->getEntryIdByFeedIdAndEntryUrl($data['feedId'], $data['entryUrl']);
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
			$aQueries[] = ' ('.(INT)$userId.', '.(INT)$entry['entryId'].', '.(element('entryRead', $entry) == true ? 'true' : 'false').', '.(element('starred', $entry) == true ? 'true' : 'false').') ';

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
		$this->db->trans_start();
		
//		$aQueries 	= array();
		$entries 	= $this->db->where('userId', $userId)->get('tmp_users_entries')->result_array();
		//pr($this->db->last_query()); 
		
		foreach ($entries as $entry) {		
			if ($entry['starred'] == true) {
				//$aQueries[] = 
				$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate)  
					SELECT userId, entryId, feedId, '.config_item('tagStar').', entryRead, entryDate
					FROM users_entries 
					WHERE 	userId	= '.$userId.'
					AND 	tagId	= '.config_item('tagAll').'
					AND 	entryId = '.$entry['entryId'];
				$this->db->query($query);
				//pr($this->db->last_query());	 
			}
			else {
				//$aQueries[] = 'DELETE FROM users_entries WHERE userId = '.$userId.' AND entryId = '.$entry['entryId'].' AND tagId = '.config_item('tagStar');
				$this->db->delete('users_entries', array(
					'userId'	=> $userId,
					'entryId'	=> $entry['entryId'],
					'tagId'		=> config_item('tagStar')
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
		
		$this->db->trans_complete();
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
						AND   users_entries.tagId	= '.config_item('tagAll').' ';			
		$this->db->query($query);
		//pr($this->db->last_query());
								
		return true;
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
		
		$this->db->delete('users_entries', array('feedId' => $feedId, 'userId' => $userId, 'tagId <>' => config_item('tagStar')));
		//pr($this->db->last_query());
		
		$this->db->delete('users_feeds_tags', array('feedId' => $feedId, 'userId' => $userId));
		//pr($this->db->last_query());
		return true;		
	}
	
	function markAllAsRead($userId, $type, $id) {
		$aFeedId = array();

		if ($type == 'tag') {
			if ($id != config_item('tagAll')) {
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
	
	function saveUserEntries($userId, $feedId, $entryId = null) {
		$aWhere = array('feedId = '.(int)$feedId);
		if ($entryId != null) {
			$aWhere[] = ' entryId = '.(int)$entryId;
		}
		
		$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 		
						SELECT '.$userId.', entries.entryId, entries.feedId, '.config_item('tagAll').', FALSE , entries.entryDate
						FROM entries 
						WHERE '.implode(' AND ', $aWhere).'						
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
		$this->load->model(array('Languages_Model', 'Tags_Model'));
		$languages = $this->Languages_Model->getRelatedLangs($this->session->userdata('langId'));
		$result    = array('tag' => null, 'feeds' => array());
		$tag       = $this->Tags_Model->get($tagId);
		$result['tag'] = array('tagId' => $tagId, 'tagName' => $tag['tagName']);
				
		$query = ' SELECT DISTINCT feeds.feedId, feedName, feedUrl, feedLink, feeds.feedIcon, feedDescription, feedCountUsers
						FROM tags 
						INNER JOIN feeds_tags 	ON feeds_tags.tagId 	= tags.tagId 
						INNER JOIN feeds 		ON feeds.feedId 		= feeds_tags.feedId 
						WHERE tags.tagId = '.(INT)$tagId.'
						AND feeds.feedId NOT IN ( SELECT feedId FROM users_feeds WHERE userId = '.(int)$userId.') 
						AND feeds.langId IN (\''.implode('\' , \'', $languages).'\')
						ORDER BY feedName ASC LIMIT 50 ';	
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());   die;
		foreach ($query as $data) {
			$tags = $this->Tags_Model->selectByFeedId($data['feedId'], 15, array(array('orderBy' =>'countTotal', 'orderDir' =>'desc'))); // TODO: harckodeta!!
			foreach ($tags as $tag) {
				$data['tags'][] = array('tagId' => $tag['tagId'], 'tagName' => $tag['tagName']);
			}

			$result['feeds'][] = $data;
		}
		
		return $result;
	}	
	
	function updateUserFilters($userFilters, $userId){
		unset($userFilters['page']);
		$this->load->model('Users_Model');
		$this->Users_Model->updateUserFiltersByUserId($userFilters, (int)$userId);
	}

	function getNewsEntries($userId = null, $feedId = null) {
		set_time_limit(0);
		
		$this->db
			->select(' DISTINCT feeds.feedId, feedUrl, feedLink, feedIcon, fixLocale', false)
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
			->where('feedLastScan < DATE_ADD(NOW(), INTERVAL -'.config_item('feedTimeScan').' MINUTE)')
			->where('feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').')')
			->where('feedMaxRetries < '.config_item('feedMaxRetries'))
//->where('feeds.feedId IN (340, 512, 555, 989)')
			->order_by('feedLastScan ASC');

		if (is_null($userId) == false) {
			$this->db->where('users_feeds.userId', $userId);
		}
		if (is_null($feedId) == false) {
			$this->db->where('feeds.feedId', $feedId);			
		}
		 
		$query = $this->db->get('feeds');
		//vd($this->db->last_query()); 
		$count = 0;
		foreach ($query->result() as $row) {
			exec('nohup '.PHP_PATH.'  '.BASEPATH.'../index.php feeds/scanFeed/'.(int)$row->feedId.' >> '.BASEPATH.'../application/logs/scanFeed.log &');
			
			$count++;
			if ($count % 40 == 0) {
				sleep(10);
			}
		}
	}	

	function populateMillionsEntries() {
		ini_set('memory_limit', '-1');
				
		$query = $this->db->select('MAX(entryId) + 1 AS entryId', true)->get('entries')->result();
		//pr($this->db->last_query()); 
		$entryId = $query[0]->entryId;

		$query = $this->db
			->select('feeds.feedId')
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
//			->where('feeds.statusId IN ('.config_item('feedStatusPending').', '.config_item('feedStatusApproved').')')
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
		$this->db->trans_start();
		
		// TODO: paginar este proceso para que guarde TODAS las entradas nuevas sin tener que relodear
		// metiendo 20 millones de entradas nuevas hay que relodear bocha de veces hasta ver la mas nueva
		$entryId	 	= null;
		$limit	 		= 2000;

		$aFeedId = array();
		$query = $this->db
			->select('feedId')
			->from('users_feeds') 
			->where('userId', $userId)
			->get()->result_array();
		foreach ($query as $data) {
			$aFeedId[] = $data['feedId'];
		}
		
		if (empty($aFeedId)) {
			return false;
		}
		
		$query = ' SELECT
						MAX(entryId) AS entryId
						FROM  users_entries  
						WHERE userId  	= '.$userId.' 
						AND   tagId 	= '.config_item('tagAll');
		$query = $this->db->query($query)->row_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			$entryId = $query['entryId'];
		}		

		// save tagAll
		$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 
					SELECT '.$userId.', entries.entryId, entries.feedId, '.config_item('tagAll').', FALSE, entries.entryDate 
					FROM entries
					WHERE feedId IN ('.implode(',', $aFeedId).') 
					'.($entryId != null ? ' AND entries.entryId > '.$entryId : '').'
				ORDER BY entries.entryId LIMIT '.$limit;
		$this->db->query($query);
		//vd($this->db->last_query());
		
		$rowsAffected = $this->db->affected_rows();
		
		// save Custom Tags
		$query = ' INSERT IGNORE INTO users_entries (userId, entryId, feedId, tagId, entryRead, entryDate) 
					SELECT '.$userId.', entries.entryId, entries.feedId, users_feeds_tags.tagId, FALSE, entries.entryDate
					FROM entries 
					INNER JOIN users_feeds_tags FORCE INDEX (indexUserIdFeedId) 
						ON users_feeds_tags.feedId = entries.feedId 
						AND users_feeds_tags.userId = '.$userId.' 
					WHERE entries.feedId IN ('.implode(',', $aFeedId).') 
					'.($entryId != null ? ' AND entries.entryId > '.$entryId : '').'
					ORDER BY entries.entryId LIMIT '.$limit;
		$this->db->query($query);
		//vd($this->db->last_query());
		
		$this->db->trans_complete();

		//vd($rowsAffected);
		if ($rowsAffected == $limit) {
			sleep(2);
			$this->saveEntriesTagByUser($userId);
		}
		
		return ($rowsAffected > 0);
	}
	
	function processTagBrowse() {
		set_time_limit(0);
		
		$this->db->trans_start();

		
		// Completo datos en la tabla tags y feeds_tags, basado en los tags de cada entry, y en como tageo cada user un feed.
		// Revisar las queries, quizas convenga ajustar un poco el juego para que tire resultados más relevantes
		
		$aSystenTags 	= array(config_item('tagAll'), config_item('tagStar'), config_item('tagHome'), config_item('tagBrowse'));
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
				countFeeds		= countFeeds + 1,
				countEntries 	= countEntries + '.$row['countEntries'].'
				WHERE tagId 	= '.$row['tagId'];
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
}
