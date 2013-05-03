<?php
class Entries_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS entries.entryId AS id, entryTitle AS \'Titulo\', entryUrl AS \'Url\' ', false)
						->like('entryTitle', $filter)
		 				->get('entries', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select($userFilters){
		$lastEntryId = element('lastEntryId', $userFilters);
		unset($userFilters['lastEntryId']);
		$this->load->model('Users_Model');
		$this->Users_Model->updateUserFiltersByUserId($userFilters, (int)$this->session->userdata('userId'));

		if ($userFilters['type'] == 'tag' && $userFilters['id'] == TAG_STAR) {
			$userFilters['onlyUnread'] = false;
		}

		$entryDate = null;
		if ($lastEntryId > 0) {
			$entry = $this->get($lastEntryId);
			$entryDate = $entry['entryDate'];
		}

		$query = $this->db->select('feeds.feedId, feedName, feedUrl, feedLInk, feedIcon, entries.entryId, entryTitle, entryUrl, entryContent, entryDate, entryAuthor, IF(users_entries.tagId = '.TAG_STAR.', true, false) AS starred, entryRead', false)
						->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
						->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
						->join('users_entries', 'users_entries.entryId = entries.entryId AND users_entries.userId = users_feeds.userId', 'left')
						->where('users_feeds.userId', $this->session->userdata('userId'));
						
		if ($userFilters['onlyUnread'] == true) {
			$this->db->where('(users_entries.entryRead IS NULL OR users_entries.entryRead <> true)');
		}

		if ($userFilters['type'] == 'tag') {
			if ($userFilters['id'] != TAG_ALL) {
				if ($userFilters['id'] == TAG_STAR) {
					$this->db->where('users_entries.tagId', $userFilters['id']);
				}
				else {					
					$this->db->join('users_feeds_tags', 'users_feeds_tags.feedId = users_feeds.feedId AND users_feeds_tags.userId = users_feeds.userId', 'inner')
							->where('users_feeds_tags.tagId', $userFilters['id']);
				}
			}
		}
		else {
			$this->db->where('feeds.feedId', $userFilters['id']);
		}

		if ($entryDate != null) {
			$this->db->where('entries.entryDate '.($userFilters['sortDesc'] == true ? '<' : '>'), $entryDate);		
		}

		$query = $this->db
			->order_by('entryDate', ($userFilters['sortDesc'] == 'true' ? 'desc' : 'asc'))
			->get('entries', ENTRIES_PAGE_SIZE)
			->result_array();
		//pr($this->db->last_query());		
		return $query;
	}

	function selectFilters() {
		$aFilters = array();
	
		$result = array(
			array(
				'type'		=> 'tag',
				'id'		=> 'home', // TODO: implementar!
				'name'		=> 'home',
				'icon'		=> site_url().'css/img/default_feed.png', 
			),
			array(
				'type'		=> 'tag',
				'id'		=> TAG_STAR,
				'name'		=> 'starred', 
				'icon'		=> site_url().'css/img/star-on.png', 
			)
		);
		
		$aFilters['tags'] = array(
			'type'		=> 'tag',
			'id'		=> TAG_ALL,		
			'name'		=> 'Subscriptions',
			'count'		=> 380,
			'expanded'	=> true,
			'childs'	=> array()				
		); 		
		
		$result[] = & $aFilters['tags'];

		$query = $this->db->select('feeds.feedId, feedName, feedUrl, tags.tagId, tagName, users_tags.expanded AS eee, IF(users_tags.expanded = 1, true, false) AS expanded, feeds.feedLink, feeds.feedIcon ', false)
						->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'left')
						->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId AND users_feeds_tags.userId = users_feeds.userId', 'left')
						->join('tags', 'users_feeds_tags.tagId = tags.tagId', 'left')
						->join('users_tags', 'users_tags.userId = users_feeds.userId AND users_tags.tagId = tags.tagId', 'left')
						->where('users_feeds.userId', $this->session->userdata('userId'))
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
				'icon'		=> ($row->feedIcon == null ? site_url().'css/img/default_feed.png' : site_url().'img/'.$row->feedIcon), 
				'count'		=> $this->getTotalByFeedId($row->feedId),				
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

	function getTotalByFeedId($feedId) {
		$query = $this->db->select('COUNT(1) AS total')
						->join('users_entries', 'users_entries.entryId = entries.entryId AND users_entries.userId = '.(int)$this->session->userdata('userId'), 'left')
						->where('entries.feedId', $feedId)
						->where('(users_entries.entryRead IS NULL OR users_entries.entryRead <> true)')
		 				->get('entries');
		//pr($this->db->last_query());
		$query = $query->result_array();
		return $query[0]['total'];
	}

	function get($entryId){
		$result = $this->db
				->where('entryId', $entryId)
				->get('entries')->row_array();
		return $result;
	}	
	
	function save($data){
// TODO: usar el metodo saveEntry		
		$entryId = $data['entryId'];

		$values = array(
			'feedId'			=> $data['feedId'],
			'entryTitle'		=> $data['entryTitle'],
			'entryContent'		=> $data['entryContent'],
			'entryAuthor'		=> $data['entryAuthor'],
			'entryDate'			=> $data['entryDate'],
			'entryUrl'			=> $data['entryUrl'],
		);
		

		if ((int)$entryId != -1) {		
			$this->db->where('entryId', $entryId);
			$this->db->update('entries', $values);
		}
		else {
			$this->db
				->ignore()
				->insert('entries', $values);
			$entryId = $this->db->insert_id();
		}
		pr($this->db->last_query());

		return true;
	}
	
	function saveEntry($data) {
		if (trim($data['entryUrl']) == '') {
			return null;
		}
		
		$query = $this->db->where('entryUrl', $data['entryUrl'])->get('entries')->result_array();
		//pr($this->db->last_query());
		if (!empty($query)) {
			$entryId = $query[0]['entryId'];
			
			$this->db->update('entries', $data, array('entryUrl'=> $data['entryUrl']));
			//pr($this->db->last_query());
			
			return $entryId;
		}
		else {	
			$this->db->insert('entries', $data);
			//pr($this->db->last_query());
			return $this->db->insert_id();
		}
	}

	function saveUserEntries($userId, $entries) {
		foreach ($entries as $entry) {
			$data = array(
 				'userId'	=> $userId,
				'entryId'	=> $entry['entryId'],
				'tagId'		=> (element('starred', $entry) == true ? TAG_STAR : null), 
				'entryRead'	=> (element('entryRead', $entry) == true)		
			);
			$this->db->replace('users_entries', $data);
			//pr($this->db->last_query());	
		}
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
			return true;
		}

		$this->db
			->ignore()
			->insert('users_feeds_tags', $values);
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

		$this->db->ignore()->insert('users_feeds', array( 'feedId'	=> $feedId, 'userId' => $userId ));
		//pr($this->db->last_query());
		
		return $feedId;
	}

	function addTag($tagName, $userId, $feedId = null) {
		$tagName = trim($tagName);

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

		$this->db->ignore()->insert('users_tags', array( 'tagId'=> $tagId, 'userId' => $userId ));
		//pr($this->db->last_query());


		if ($feedId != null) {
			$this->db->replace('users_feeds_tags', array( 'tagId'=> $tagId, 'feedId'	=> $feedId, 'userId' => $userId ));
			//pr($this->db->last_query());
		}
		
		return array('tagId' => $tagId);
	}

	function unsubscribeFeed($feedId, $userId) {
		$this->db->delete('users_feeds', array('feedId' => $feedId, 'userId' => $userId));
		//pr($this->db->last_query());
		return true;		
	}
	
	function saveFeedIcon($feedId, $feedLink, $feedIcon) {
		if ($feedIcon == null) {
			$this->load->spark('curl/1.2.1');
			$img = $this->curl->simple_get('https://plus.google.com/_/favicon?domain='.$feedLink);
			$parse = parse_url($feedLink);
			$feedIcon = $parse['host'].'.png'; 
			file_put_contents('./img/'.$feedIcon, $img);
			$values['feedIcon'] = $feedIcon;
			$this->db->update('feeds', $values, array('feedId' => $feedId));	
		}				
	}	

	function getNewsEntries($userId = null) {
		$this->db
			->select('feeds.feedId, feedUrl, feedLink, feedIcon')
			->where('feedLastUpdate < DATE_ADD(NOW(), INTERVAL -'.FEED_TIME_SCAN.' MINUTE)')
			->where('feeds.statusId IN ('.FEED_STATUS_PENDING.', '.FEED_STATUS_APPROVED.')')
			->order_by('feedLastUpdate ASC');

		if (is_null($userId) == false) {
			$this->db
				->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
				->where('users_feeds.userId', $userId);
		} 			 
		 
		$query = $this->db->get('feeds');
		//pr($this->db->last_query()); 
		foreach ($query->result() as $row) {
			$this->saveFeedIcon($row->feedId, $row->feedLink, $row->feedIcon);
			$this->parseRss($row->feedId, $row->feedUrl, $row->feedLink, $row->feedIcon);
		}
	}		

	// TODO: mover estos metodos de aca
	function parseRss($feedId, $feedUrl, $feedLink, $feedIcon) {
		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();

		if ($this->cisimplepie->error() ) {
			return $this->updateFeedStatus($feedId, FEED_STATUS_NOT_FOUND);
		}
		
		$values = array( 'feedLastUpdate' => date("Y-m-d H:i:s") ); 
		if (trim((string)$this->cisimplepie->get_title()) != '') {
			$values['feedName'] = (string)$this->cisimplepie->get_title(); 			
		}
		if (trim((string)$this->cisimplepie->get_link()) != '') {
			$values['feedLink'] = (string)$this->cisimplepie->get_link();
		}
		$this->db->update('feeds', $values, array('feedId' => $feedId));
			
		$rss = $this->cisimplepie->get_items();

		foreach ($rss as $item) {
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
				return $this->updateFeedStatus($feedId, FEED_STATUS_INVALID_FORMAT);
			}
			
			$this->saveEntry($data);
		}
		
		$this->updateFeedStatus($feedId, FEED_STATUS_APPROVED);
	}
}
