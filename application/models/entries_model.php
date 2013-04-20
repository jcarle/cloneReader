<?php
class Entries_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS entries.entryId AS id, entryTitle AS \'Titulo\', entryUrl AS \'Url\' ', false)
						->like('entryTitle', $filter)
		 				->get('entries', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select($userFilters, $num, $offset){
		// TODO: mover esto de aca y buscar nuevas entries en forma asyncronica
		$this->getNewsEntries((int)$this->session->userdata('userId'));

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

		$query = $this->db->select('feeds.feedId, feedName, feedUrl, feedLInk, entries.entryId, entryTitle, entryUrl, entryContent, entryDate, entryAuthor, IF(users_entries.tagId = '.TAG_STAR.', true, false) AS starred, entryRead', false)
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
					$this->db->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId', 'inner')
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
			->get('entries', $num, $offset)
			->result_array();
		//pr($this->db->last_query());				

		return $query;
	}

	function selectByTags() {
	// TODO: filtro por usuarios!
		$aTags = array();
	
		$result = array(
			array(
				'type'		=> 'tag',
				'id'		=> 'home', // TODO: implementar!
				'name'		=> 'home',
				'icon'		=> site_url().'css/img/feed.png', 
			),
			array(
				'type'		=> 'tag',
				'id'		=> TAG_STAR,
				'name'		=> 'starred', 
				'icon'		=> site_url().'css/img/star-on.png', 
			)
		);
		
		$aTags['tags'] = array(
			'type'		=> 'tag',
			'id'		=> TAG_ALL,		
			'name'		=> 'Subscriptions',
			'count'		=> 380,
			'expanded'	=> true,
			'childs'	=> array()				
		); 		
		
		$result[] = & $aTags['tags'];

		$query = $this->db->select('feeds.feedId, feedName, feedUrl, tags.tagId, tagName, users_tags.expanded AS eee, IF(users_tags.expanded = 1, true, false) AS expanded, feeds.feedLink ', false)
						->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'left')
						->join('users_feeds_tags', 'users_feeds_tags.feedId = feeds.feedId', 'left')
						->join('tags', 'users_feeds_tags.tagId = tags.tagId', 'left')
						->join('users_tags', 'users_tags.userId = users_feeds.userId AND users_tags.tagId = tags.tagId', 'left')
						->where('users_feeds.userId', $this->session->userdata('userId'))
						->order_by('tagName IS NULL, tagName asc, feedName asc')
		 				->get('feeds');
		//pr($this->db->last_query());				

		foreach ($query->result() as $row) {
			if ($row->tagId != null && !isset($aTags[$row->tagId])) {
				$aTags[$row->tagId] = array(
					'type'		=> 'tag',
					'id'		=> $row->tagId,
					'name'		=> $row->tagName,
					'expanded'	=> ($row->expanded == true),
					'childs'	=> array()				
				); 
				
				$aTags['tags']['childs'][] = & $aTags[$row->tagId];
			}
			
			$feed = array(
				'type'		=> 'feed',
				'id'		=> $row->feedId, 
				'name'		=> $row->feedName, 
				'url'		=> $row->feedUrl,
				'icon'		=> 'https://plus.google.com/_/favicon?domain='.$row->feedLink, // TODO: guardar los iconos en disco!
				'count'		=> $this->getTotalByFeedId($row->feedId),				
			);
			
			if ($row->tagId != null) {
				$aTags[$row->tagId]['childs'][] = $feed;
			}
			else {
				$aTags['tags']['childs'][] = $feed;
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

		//pr($this->db->last_query());

		return true;
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

	function addFeed($feedUrl, $userId) {
		$feedUrl = trim($feedUrl);

		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();
		if ($this->cisimplepie->error() != '' ) {
			return $this->cisimplepie->error();
		}


		$query = $this->db->where('feedUrl', $feedUrl)->get('feeds')->result_array();
		//pr($this->db->last_query());
		if (!empty($query)) {
			$feedId = $query[0]['feedId'];
		}
		else {
			$this->db->ignore()->insert('feeds', array( 'feedUrl'	=> $feedUrl ));
			$feedId = $this->db->insert_id();
			//pr($this->db->last_query());
		}

		$this->db->ignore()->insert('users_feeds', array( 'feedId'	=> $feedId, 'userId' => $userId ));
		//pr($this->db->last_query());

		return true;
	}

	function getNewsEntries($userId = null) {
		$this->db
			->select('feeds.feedId, feedUrl')
			->where('feedLastUpdate < DATE_ADD(NOW(), INTERVAL -'.FEED_SCAN_HOURS.' HOUR)');


		if (is_null($userId) == false) {
			$this->db
				->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
				->where('users_feeds.userId', $userId);
		} 			 
		 
		$query = $this->db->get('feeds');
		//pr($this->db->last_query()); 
		foreach ($query->result() as $row) {
			$this->parseRss($row->feedId, $row->feedUrl);
		}
	}
		

	// TODO: mover estos metodos de aca
	function parseRss($feedId, $feedUrl) {
		$this->load->spark('ci-simplepie/1.0.1/');
		$this->cisimplepie->set_feed_url($feedUrl);
//		$this->cisimplepie->enable_cache(false);
		$this->cisimplepie->init();
		$this->cisimplepie->handle_content_type();
		
		$this->db->update('feeds',  
			array(
				'feedName' 			=> (string)$this->cisimplepie->get_title(), 
				'feedLink' 			=> (string)$this->cisimplepie->get_link(),
				'feedLastUpdate' 	=> date("Y-m-d H:i:s"), 
			), 
			array('feedId' => $feedId));
			
		$rss = $this->cisimplepie->get_items();

		foreach ($rss as $item) {
			$entryAuthor = '';
			if ($author = $item->get_author()) {
				$entryAuthor = $author->get_name();
			}

			$reedEntry = array(
				'feedId' 		=> $feedId,
				'entryId'		=> -1,
				'entryTitle'	=> $item->get_title(),
				'entryContent'	=> $item->get_content(),
				'entryDate'		=> $item->get_date('Y-m-d H:i:s'),
				'entryUrl'		=> $item->get_link(),
				'entryAuthor'	=> $entryAuthor,
			);

			$this->save($reedEntry);
		}
	}
}
