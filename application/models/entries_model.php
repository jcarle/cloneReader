<?php
class Entries_Model extends CI_Model {

	/*
	 * @param  (array)  $filters es un array con el formato:
	 * 		array(
	 * 			'search'      => null,
	 * 			'feedId'      => null
	 * 		);
	 *
	 * */
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array(), array $orders = array()){
		$this->db
			->from('entries')
			->select('SQL_CALC_FOUND_ROWS entries.entryId, feedName, entryTitle, entryUrl, entryDate', false)
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner');

		if (element('search', $filters) != null) {
			$this->db->like('entryTitle', $filters['search']);
		}
		if (element('feedId', $filters) != null) {
			$this->db->where('feeds.feedId', $filters['feedId']);
		}

		$this->Commond_Model->appendOrderByInQuery($orders, array('entryId', 'entryDate' ));
		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);

		$query = $this->db->get();
		//pr($this->db->last_query()); die;

		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
	}

	function select($userId, $aFilters){
		// Default filters, por si viaja mal el js.
		$userFilters = array_merge(
			array(
				'page'          => 1,
				'onlyUnread'    => true,
				'sortDesc'      => true,
				'id'            => config_item('tagHome'),
				'type'          => 'tag',
				'viewType'      => 'detail',
				'isMaximized'   => false,
				'search'        => ''
			), $aFilters);

		$onlyUnread = $userFilters['onlyUnread']; // No guardo el filter onlyUnread si esta viendo los favoritos
		if ($userFilters['type'] == 'tag' && $userFilters['id'] == config_item('tagStar')) {
			$onlyUnread = false;
		}

		$this->updateUserFilters($userFilters, $userId);

		// Buscador
		if (trim($userFilters['search']) != '') {
			return $this->searchEntries($userFilters);
		}

		// Tag home, lo tienen todos los usuarios
		if ($userFilters['type'] == 'tag' && $userFilters['id'] == config_item('tagHome')) {
			return $this->selectFeedCR($userId, $userFilters);
		}


		$indexName = 'PRIMARY';
		$query = $this->db
			->select('users_entries.feedId, feedName, feedUrl, feedLInk, feedIcon, users_entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor, entryRead, entryStarred ', false)
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
		if ($onlyUnread == true) {
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


	function searchEntries($userFilters) {
		$this->load->model('Feeds_Model');

		$userId        = $this->session->userdata('userId');
		$aEntityId     = array();
		$aSearchKey    = array('searchEntries');
		$search        = $userFilters['search'];
		$aSearch       = cleanSearchString($search, $aSearchKey);
		$feedId        = null;
		$tagId         = null;
		$aFeedsSearch  = array();
		$aEntrySearch  = array();
		if (empty($aSearch)) {
			return array();
		}

		if ($userFilters['type'] == 'feed') {
			$feedId = $userFilters['id'];
		}
		if ($userFilters['type'] == 'tag' && $userFilters['id'] != config_item('tagAll')) {
			$tagId = $userFilters['id'];
		}

		if ($userFilters['type'] == 'tag' && $userFilters['id'] == config_item('tagStar')) {
			$tagId     = config_item('tagStar');
			$indexName = 'indexTag';
			$query     = $this->db
				->select(' users_entries.entryId ', false)
				->from('users_entries FORCE INDEX ('.$indexName.')' )
				->where('users_entries.userId', $userId)
				->where('users_entries.tagId', config_item('tagStar'))
				->get()->result_array();
			//pr($this->db->last_query()); die;
			foreach ($query as $data) {
				$aEntrySearch[] = $data['entryId'];
			}
		}
		else {
			$query = $this->Feeds_Model->selectFeedIdByUserId($userId, $feedId, $tagId);
			foreach ($query as $data) {
				$aFeedsSearch[] = " MATCH (entityNameSearch) AGAINST ('+searchEntries  +searchInFeedId".$data['feedId']."' IN BOOLEAN MODE) ";
			}
			$tagId = config_item('tagAll');
		}

		if (empty($aFeedsSearch) && empty($aEntrySearch)) {
			return array();
		}


		$match = 'MATCH (entityFullSearch) AGAINST (\''.implode(' ', $aSearch).'\' IN BOOLEAN MODE)';
		$this->db
			->select(' entityId, entityName, '.$match.' AS score, MATCH (entityNameSearch) AGAINST (\''.implode(' ', cleanSearchString($search, $aSearchKey, false, false)).'\' IN BOOLEAN MODE) AS scoreName ', false)
			->from('entities_search')
			->where($match, NULL, FALSE)
			->order_by('scoreName DESC, score DESC');
		if (!empty($aFeedsSearch) ) {
			$this->db->where('('. implode(' OR ', $aFeedsSearch).')', NULL, FALSE);
		}
		if (!empty($aEntrySearch)) {
			$this->db->where('entityTypeId', config_item('entityTypeEntry'));
			$this->db->where_in('entityId', $aEntrySearch);
		}
		$this->Commond_Model->appendLimitInQuery($userFilters['page'], config_item('entriesPageSize'));
		$query = $this->db->get()->result_array();
		//pr($this->db->last_query()); die;
		foreach ($query as $data) {
			$aEntryId[] = $data['entityId'];
		}

		if (empty($aEntryId)) {
			return array();
		}


		$this->db
			->select('feeds.feedId, feedName, feedUrl, feedLInk, feedIcon, entries.entryId, entryTitle, entryUrl, entryContent, entries.entryDate, entryAuthor, entryRead, entryStarred ', false)
			->from('entries')
			->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
			->join('users_entries', 'users_entries.entryId = entries.entryId AND users_entries.feedId = entries.feedId', 'inner')
			->where_in('entries.entryId', $aEntryId)
			->where('users_entries.userId', $userId)
			->where('users_entries.tagId', $tagId);

		$this->db->_protect_identifiers = FALSE;
		$this->db->order_by('FIELD(entries.entryId, '.implode(',', $aEntryId).' ) ');
		$this->db->_protect_identifiers = TRUE;

		$query = $this->db->get()->result_array();
		//pr($this->db->last_query()); die;
		return $query;
	}

	function selectFilters($userId) {
		$aFilters = array();

		$result = array(
			'tags'    => $this->selectTagsByUserId($userId),
			'filters' => array(
				array(
					'type'  => 'tag',
					'id'    => config_item('tagHome'),
					'name'  => lang('@tag-home'),
					'icon'  => site_url().'assets/images/default_feed.png',
				),
				array(
					'type'  => 'tag',
					'id'    => config_item('tagStar'),
					'name'  => lang('@tag-star'),
					'icon'  => site_url().'assets/images/star-on.png',
				),
				array(
					'type'      => 'tag',
					'id'        => config_item('tagBrowse'),
					'name'      => lang('@tag-browse'),
					'classIcon' => 'fa fa-tags',
				)
			)
		);

		$aFilters['tags'] = array(
			'type'      => 'tag',
			'id'        => config_item('tagAll'),
			'name'      => lang('@tag-all'),
			'count'     => 380,
			'expanded'  => true,
			'childs'    => array()
		);

		$result['filters'][] = & $aFilters['tags'];

// FIXME: la version de mysql que hay en dreamhost no soporta pasar limit como parametro de una function
// 	esta harckodeado el valor 1050 adentro de countUnread()!
		$query = $this->db->select('feeds.feedId, feeds.statusId, feedName, feedUrl, tags.tagId, tagName, users_tags.expanded AS expanded, feeds.feedLink, feeds.feedIcon, countUnread('.$userId.', feeds.feedId, '.config_item('tagAll').', '.(config_item('feedMaxCount') + 50).') AS unread', false)
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
		$query = $this->db
				->select('entries.*, feedName, feedLink, feedUrl', true)
				->where('entryId', $entryId)
				->join('feeds', 'entries.feedId = feeds.feedId', 'inner')
				->get('entries')->row_array();

		if (!empty($query) && $isForm == true) {
			$query['feedId'] = array( 'id' => element('feedId', $query), 'text' => element('feedName', $query));
		}

		return $query;
	}

	function getEntryIdByFeedIdAndEntryUrl($feedId, $entryUrl) {
		$entryUrl = substr(trim($entryUrl), 0, 255);

		$query = $this->db
				->where(array( 'feedId' => $feedId,'entryUrl' => $entryUrl ))
				->get('entries')->row_array();
		if (empty($query)) {
			return null;
		}
		return $query['entryId'];
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
			'feedId'       => element('feedId', $data),
			'entryTitle'   => element('entryTitle', $data),
			'entryContent' => element('entryContent', $data),
			'entryAuthor'  => element('entryAuthor', $data),
			'entryDate'    => element('entryDate', $data),
			'entryUrl'     => substr(trim(element('entryUrl', $data)), 0, 255),
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
		if ($this->session->userdata('userId') == USER_ANONYMOUS) {
			$this->session->set_userdata('addDefaultFeeds', true); // Si crea un usuario en la app le guardo los feeds del user anonimo
		}

		$aQueries = array();
		foreach ($entries as $entry) {
			$aQueries[] = ' ('.(INT)$userId.', '.(INT)$entry['entryId'].', '.(element('entryRead', $entry) == true ? 'true' : 'false').', '.(element('entryStarred', $entry) == true ? 'true' : 'false').') ';

		}
		if (count($aQueries) == 0) {
			return;
		}

		$query = 'REPLACE INTO tmp_users_entries (userId, entryId, entryRead, entryStarred) VALUES '.implode(', ', $aQueries).';';
		$this->db->query($query);
		//pr($this->db->last_query());
	}

	function pushTmpUserEntries($userId) { // guarda los cambios en la tabla users_entries
		$this->db->trans_start();

		$entries = $this->db->where('userId', $userId)->get('tmp_users_entries')->result_array();
		//pr($this->db->last_query());

		foreach ($entries as $entry) {
			if ($entry['entryStarred'] == true) {
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
				$this->db->delete('users_entries', array(
					'userId'	=> $userId,
					'entryId'	=> $entry['entryId'],
					'tagId'		=> config_item('tagStar')
				));
			}

			$this->db
				->where(array(
					'userId'	=> $userId,
					'entryId'	=> $entry['entryId'])
				)
				->update('users_entries', array('entryRead' => $entry['entryRead'], 'entryStarred' => $entry['entryStarred'] ));
			//pr($this->db->last_query());
		}

		//pr($this->db->last_query());
		$this->db->delete('tmp_users_entries', array('userId' => $userId));
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
		$tagName  = substr(trim($tagName), 0, 200);
		$tagId    = null;

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
			$this->db->ignore()->insert('users_tags', array( 'tagId'=> $tagId, 'userId' => $userId ));
			//pr($this->db->last_query());
		}

		if ($feedId != null) {
			$this->db->replace('users_feeds_tags', array( 'tagId'=> $tagId, 'feedId'	=> $feedId, 'userId' => $userId ));
			//pr($this->db->last_query());
		}

		if ($userId != null && $feedId != null) {
			$query = ' INSERT IGNORE INTO users_entries
					(userId, entryId, tagId, feedId, entryRead, entryDate)
					SELECT userId, entryId, '.$tagId.', feedId, entryRead, entryDate
					FROM users_entries
					WHERE userId = '.(int)$userId.'
					AND   tagId  = '.(int)config_item('tagAll').'
					AND   feedId = '.(int)$feedId.' ';
			$this->db->query($query);
		}

		return $tagId;
	}

	function addDefaultFeeds() {
		if ($this->session->userdata('addDefaultFeeds') != true) {
			return;
		}

		$userId = $this->session->userdata('userId');

		$query = ' INSERT INTO users_feeds
			(userId, feedId )
			SELECT '.$userId.', feedId
			FROM users_feeds
			WHERE userId = '.USER_ANONYMOUS;
		$this->db->query($query);
		//pr($this->db->last_query());

		$query = ' INSERT INTO users_tags
			(userId, tagId, expanded )
			SELECT '.$userId.', tagId, expanded
			FROM users_tags
			WHERE userId = '.USER_ANONYMOUS;
		$this->db->query($query);
		//pr($this->db->last_query());

		$query = ' INSERT INTO users_feeds_tags
			(userId, feedId, tagId )
			SELECT '.$userId.', feedId, tagId
			FROM users_feeds_tags
			WHERE userId = '.USER_ANONYMOUS;
		$this->db->query($query);
		//pr($this->db->last_query());
	}

	function subscribeFeed($feedId, $userId) {
		$this->db->ignore()->insert('users_feeds', array( 'feedId' => $feedId, 'userId' => $userId ));
		//pr($this->db->last_query()); die;

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
						INNER JOIN feeds_tags ON feeds_tags.tagId = tags.tagId
						INNER JOIN feeds      ON feeds.feedId     = feeds_tags.feedId
						WHERE tags.tagId = '.(INT)$tagId.'
						AND feeds.feedId NOT IN ( SELECT feedId FROM users_feeds WHERE userId = '.(int)$userId.')
						AND feeds.langId IN (\''.implode('\' , \'', $languages).'\')
						ORDER BY feedName ASC LIMIT 50 ';
		$query = $this->db->query($query)->result_array();
		//pr($this->db->last_query());   die;
		foreach ($query as $data) {
			// TODO: harckodeta!!
			$tags = $this->Tags_Model->selectToList(1, 15, array('feedId' => $data['feedId'], 'notOnlyFeedId' => true), array(array('orderBy' =>'countTotal', 'orderDir' =>'desc')));
			foreach ($tags['data'] as $tag) {
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
					'feedId'        => $row->feedId,
					'entryTitle'    => 'titulooooo '.$entryId,
					'entryContent'  => 'contenido del entry <b><test/b>'.$entryId,
					'entryDate'     => date('Y-m-d H:i:s'),
					'entryUrl'      => 'http://saranga.com/dadadad/'.$entryId,
					'entryAuthor'   => 'el autor',
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
		$entryId = null;
		$limit   = 2000;

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
			sleep(1);
			$this->saveEntriesTagByUser($userId);
		}

		return ($rowsAffected > 0);
	}

	function saveEntriesSearch($deleteEntitySearch = false, $onlyUpdates = false, $entryId = null) {
		$this->load->model('Feeds_Model');

		if ($deleteEntitySearch == true) {
			$this->Commond_Model->deleteEntitySearch(config_item('entityTypeEntry'));
		}
		if ($entryId == null) {
			$currentDatetime = $this->Commond_Model->getCurrentDateTime();
		}

		$aWhere = array();
		if ($onlyUpdates == true) {
			$lastUpdate      = $this->Commond_Model->getProcessLastUpdate('saveEntriesSearch');
			$aWhere[]        = ' (entries.lastUpdate > \''.$lastUpdate.'\'  OR entries.feedId IN ( SELECT feedId FROM feeds WHERE feeds.lastUpdate > \''.$lastUpdate.'\') ) ';
			$currentDatetime = $this->Commond_Model->getCurrentDateTime();
		}
		if ($entryId != null) {
			$aWhere[] = ' entries.entryId = '.(int)$entryId;
		}

		$pageSize     = 100;
		$lastEntryId  = 0;
		while ($lastEntryId !== null) {
			$lastEntryId = $this->saveEntriesSearchPage($aWhere, $lastEntryId, $pageSize);
		}

		if ($entryId == null) {
			$this->Commond_Model->updateProcessDate('saveEntriesSearch', $currentDatetime);
		}

		return true;
	}

	function saveEntriesSearchPage($aWhere, $lastEntryId, $pageSize) {
		$aWhere[]    = ' entries.entryId > '.(int)$lastEntryId;
		$lastEntryId = null;
		$values      = array();

		$query = " SELECT entryId, entryTitle, entryContent, entries.feedId
			FROM entries
			WHERE ".implode(' AND ', $aWhere)."
			ORDER BY entryId ASC
			LIMIT ".$pageSize."  ";
		// pr($query); die;
		$query = $this->db->query($query);
		foreach ($query->result_array() as $data) {
			$lastEntryId              = $data['entryId'];
			$searchKey                = 'searchEntries searchInFeedId'.$data['feedId'];
			$feed                     = $this->Feeds_Model->get($data['feedId']);
			$values[$data['entryId']] = array(
				'entityTypeId'     => $this->db->escape(config_item('entityTypeEntry')),
				'entityId'         => $this->db->escape($data['entryId']),
				'entityFullSearch' => $this->db->escape(searchReplace($searchKey.' '.$data['entryTitle'].' '.rip_tags($data['entryContent']).' '.$feed['feedName'])),
				'entityNameSearch' => $this->db->escape(searchReplace($searchKey.' '.$data['entryTitle'].' '.$feed['feedName'])),
				'entityName'       => $this->db->escape($data['entryTitle'])
			);
		}
		$query->free_result();

		if (!empty($values)) {
			$inserts = array();
			foreach ($values as $data) {
				$inserts[] = "( ".implode(", ", $data)." )";
			}

			$query = ' INSERT INTO entities_search
				(entityTypeId, entityId, entityFullSearch, entityNameSearch, entityName)
				VALUES
				'.implode(', ', $inserts).'
				ON DUPLICATE KEY UPDATE
				entityFullSearch = VALUES(entityFullSearch),
				entityNameSearch = VALUES(entityNameSearch),
				entityName       = VALUES(entityName) ';
			//vd($query); die;
			$this->db->query($query);
		}

		unset($values);
		unset($inserts);

		return $lastEntryId;
	}
}
