<?php
class Agreement 
{
	private static $table = 'agreement_log';
	private static $bitrix_id;
	private static $edit_time = 3600;
	private static $from_ids = [
		265 => 'ИЕ',
		404 => 'ФБ',
		409 => 'СА',
		410 => 'ЭК',
	];

	public static function delete_file($entry_id, $file_name) {
		$user_data = BitrixUser::verify_auth_token();
		// $request = new HttpRequest();
		// $data = $request->body;
		// $id = $data['id'] ?? HttpResponse::send_status(false, 'permission denied');
		// $name = $data['name'] ?? HttpResponse::send_status(false, 'permission denied');
		Conf::add_db('admin');
		Conf::select_db('admin');
		$role = BitrixUser::get_role($user_data->id);
		$role == 'super_admin' || $role == 'admin' || $role == 'editor' ?: HttpResponse::send_status(false, 'permission denied');
		$b = R::findOne(self::$table, ' id = ?', [$entry_id]);
		$b ?? HttpResponse::send_status(false, 'permission denied');
		$now = time();
		$name = urldecode($file_name);
		if ($role == 'editor' && $now - strtotime($b->updated_date) > self::$edit_time) {
			HttpResponse::send_status(false, 'permission denied 2');
		}
		try {
			$path = rawurldecode($_SERVER['DOCUMENT_ROOT'].'/uploads/any/agreement/'.date('Y',strtotime($b->created_date)).'/'.$entry_id.'/'.$name);
			if (unlink($path)) {
				if ($b->files) {
					$new = [];
					$old = json_decode($b->files, JSON_UNESCAPED_UNICODE);
					foreach ($old as $item) {
						if($item['name'] != $name) {
							$new[] = $item;
						}
					}
					if($new != []) {
						$b->files = json_encode($new, JSON_UNESCAPED_UNICODE);
					} else {
						$b->files = null;
					}
					R::store($b);
					HttpResponse::send_status(true, 'ok');
				}
			} else {
				HttpResponse::send_status(false, 'permission denied');
			}
		} catch (Exception $e) {
			HttpResponse::send_status(false, $e->getMessage());
		}
	}

	public static function action($params) {
		$params['case'] == 'archive' || $params['case'] == 'unarchive' ?: HttpResponse::send_status(false, 'permission denied');
		$user_data = BitrixUser::verify_auth_token();
		Conf::add_db('admin');
		Conf::select_db('admin');
		$role = BitrixUser::get_role($user_data->id);
		$role == 'super_admin' || $role == 'admin' || $role == 'editor' ?: HttpResponse::send_status(false, 'permission denied');
		$b = R::findOne(self::$table,' id = ?', [$params['id']]);
		$now = time();
		if ($b) {
			if ($role == 'editor' && $now - strtotime($b->updated_date) > self::$edit_time) {
				HttpResponse::send_status(false, 'permission denied 2');
			}
			$b->removed = $params['case'] == 'archive' ? 1 : 0;
			$b->editor_id = $user_data->bitrix_id;

			R::store($b);
			HttpResponse::send_status(true, 'ok', $b->agreement_num);
		} else {
			HttpResponse::send_status(false, 'permission denied');
		}
	}

	public static function get() {
		$user_data = BitrixUser::verify_auth_token();
		$request = new HttpRequest();
		$limit = $request->limit ?? 10;
		$page = $request->page ?? 1;
		$offset = ($page-1)*$limit;
		$search_target = $request->search_target ?? '';

		$search_by_type = $request->search_by_type 
			? $request->split_param(',','search_by_type') 
			: [1,2,3,4,5,6];

		$search_by_entity = $request->search_by_entity 
			? $request->split_param(',', 'search_by_entity') 
			: [1,2,3,4,5];

		$search_by_implementor = $request->search_by_implementor
			? $request->split_param(',', 'search_by_implementor') 
			: [0,1,2,3,4,5,6];


		$today = date('Y-m-d');
		Conf::add_db('admin');
		Conf::select_db('admin');
		// $date_from = $request->from ?? R::getAll("SELECT DATE_SUB(CURDATE(), INTERVAL 30 DAY) AS `from` FROM correspondence")[0]['from'];
		$date_from = $request->from ?? R::getAll("SELECT MIN(DATE(date)) AS `date_from` FROM agreement_log")[0]['date_from'];
		$signed = null;
		$order = null;
		$date_to = $request->to ?? $today;
		
		$columns = ["a.contractor_name", "a.agreement_num", "a.agreement_title", "CONCAT(b.name, ' ', b.second_name, ' ', b.last_name)", "atypes.name", "a.agreement_sum"];		

		switch ($request->archived) {
			case 'true':
				$removed = "AND a.removed IN(1)";
				break;
			case 'false':
				$removed = "AND a.removed IN(0)";
				break;    
			default: 
				$removed = "AND a.removed IN(0, 1)";
		}

		switch ($request->signed) {
			case 'all':
				$signed = "";
				break;
			case '1':
				$signed = " AND a.signed IN(1) ";
				break;  
			case '0':
				$signed = " AND a.signed IN(0) ";
				break;    
		}

		switch($request->sort) {
			case 'date_desc':
				$order = 'ORDER BY a.created_date DESC';
				break;  
			case 'date_asc':
				$order = 'ORDER BY a.created_date ASC';
				break; 
			case 'agreement_sum_desc':
				$order = 'ORDER BY a.agreement_sum DESC';
				break;  
			case 'agreement_sum_asc':
				$order = 'ORDER BY a.agreement_sum ASC';
				break; 
			case 'conclusion_date_desc':
				$order = 'ORDER BY a.conclusion_date DESC';
				break;  
			case 'conclusion_date_asc':
				$order = 'ORDER BY a.conclusion_date ASC';
				break; 
			case 'execution_date_desc':
				$order = 'ORDER BY a.execution_date DESC';
				break;  
			case 'execution_date_asc':
				$order = 'ORDER BY a.execution_date ASC';
				break; 
			case 'specification_num_desc':
				$order = 'ORDER BY a.specification_num DESC';
				break;  
			case 'specification_num_asc':
				$order = 'ORDER BY a.specification_num ASC';
				break; 
			default:
				$order = 'ORDER BY a.id DESC';
		}       
		
        $select = "SELECT a.*, DATE(a.date) AS date, CONCAT(b.name, ' ', b.second_name, ' ', b.last_name) AS employee_full_name, atypes.name AS agreement_type_name";
        $from = "FROM agreement_log a LEFT JOIN agreement_types atypes ON a.agreement_type = atypes.id LEFT JOIN bitrix_user b ON a.employee = b.id";
		
		$search_type_request = '';
		if($search_by_type) {
			$search_type_request .= "AND a.agreement_type IN(".R::genSlots($search_by_type).")";
		}
		$search_entity_request = '';
		if($search_by_entity) {
			$search_entity_request .= "AND a.legal_entity IN(".R::genSlots($search_by_entity).")";
		}
		$search_implementor_request = '';
		if($search_by_implementor) {
			$search_implementor_request .= "AND a.implementor IN(".R::genSlots($search_by_implementor).")";
		}

		if($search_target != '') {
        
			foreach ($columns as $value) {
				$like[] = " $value LIKE ? ";
				$placeholder[] = "%$search_target%";
			}

			// $where = "WHERE a.removed IN(".R::genSlots($removed).")";
			$where = "WHERE a.date >=".R::getAll("SELECT MIN(DATE(date)) AS `date_from` FROM agreement_log")[0]['date_from']." AND a.date <= ".R::getAll("SELECT MAX(DATE(date)) AS `date_from` FROM agreement_log")[0]['date_from'];
			$count = R::getAll("SELECT COUNT(a.id) AS `count` $from $where $removed $signed $search_type_request $search_entity_request $search_implementor_request AND".implode('OR', $like)." ", R::flat([$search_by_type, $search_by_entity, $search_by_implementor, $placeholder]))[0]['count'];
			$rows = R::getAll("$select $from $where $removed $signed $search_type_request $search_entity_request $search_implementor_request AND".implode('OR', $like)." $order LIMIT ? OFFSET ?", R::flat([$search_by_type, $search_by_entity, $search_by_implementor, $placeholder, $limit, $offset]));
		} else {
			$where = "WHERE a.date >= ? AND a.date <= ?";
            $count = R::getAll("SELECT COUNT(a.id) AS `count` $from $where $removed $signed $search_type_request $search_entity_request $search_implementor_request", R::flat([$date_from, $date_to, $search_by_type, $search_by_entity, $search_by_implementor]))[0]['count'];
            $rows = R::getAll("$select $from $where $removed $signed $search_type_request $search_entity_request $search_implementor_request $order LIMIT ? OFFSET ?", R::flat([$date_from, $date_to, $search_by_type, $search_by_entity, $search_by_implementor, $limit, $offset]));
        } 
        
        foreach ($rows as &$item) {
            $item['files'] = json_decode($item['files'], JSON_UNESCAPED_UNICODE);
            $arr = [];
            if ($item['files']) {
			
                foreach ($item['files'] as $i => $f) {
                    $arr[$i] = [
						'name' => $f['name'],
						'size' => $f['size'],
						'path' => 'https://journal.gk-venture.ru/uploads/any/agreement/'.date('Y',strtotime($item['created_date'])).'/'.$item['id'].'/'.$f['name']
					];
                }
            }
            $item['files'] = $arr;
        }
        unset($item);


		$types = R::getAll("SELECT * FROM agreement_types");

        $payload = [
            'pages' => ceil($count/$limit),
            'data' => $rows,
			'types' => $types,
        ];
		
		HttpResponse::send_status(true, 'ok', $payload);
	}

	public static function add() {
		$user_data = BitrixUser::verify_auth_token();
		self::$bitrix_id = $user_data->bitrix_id;
		
		$request = new HttpRequest();
		$data = $request->body;
		$id = $data['id'] ?? null;
		$files = $request->files ? $request->files['files'] : [];
		$editors_id = $data['editors_id'] ?? null;

		Conf::add_db('admin');
		Conf::select_db('admin');
		$role = BitrixUser::get_role($user_data->id);
		$role == 'super_admin' || $role == 'admin' || $role == 'editor' ?: HttpResponse::send_status(false, 'permission denied 1');

		if ($role == 'super_admin' || $role == 'admin' || $role == 'editor') {
			// $b = R::findOne(self::$table, ' id = ?', [$id]);
			// $b ?? HttpResponse::send_status(false, 'entry not found');
			// $now = time();
			if (!str_contains($editors_id, self::$bitrix_id )) {
				HttpResponse::send_status(false, 'permission denied 2');
			}
		}

		$data['date'] ?? HttpResponse::send_status(false, 'date');
		
		$data['legal_entity'] ?? HttpResponse::send_status(false, 'legal_entity');

		$data['contractor'] ?? HttpResponse::send_status(false, 'contractor');
		
		$data['agreement_num'] = self::trim_request_param($data['agreement_num']);
		
		if (isset($data['contractor_name']) && $data['contractor_name']) {
			$data['contractor_name'] = Utils::trim_str($data['contractor_name']);
		}
		if (isset($data['implementor']) && $data['implementor']) {
			$data['implementor'] = Utils::trim_str($data['implementor']);
		}
		if (isset($data['agreement_sum']) && $data['agreement_sum']) {
			$data['agreement_sum'] = Utils::trim_str($data['agreement_sum']);
		}
		if (isset($data['agreement_title']) && $data['agreement_title']) {
		   $data['agreement_title'] = Utils::trim_str($data['agreement_title']);
		}
		if (isset($data['signed']) && $data['signed']) {
			$data['signed'] = Utils::trim_str($data['signed']);
		}
		if (isset($data['employee']) && $data['employee']) {
			$data['employee'] = Utils::trim_str($data['employee']);
		}
		if (isset($data['conclusion_date']) && $data['conclusion_date']) {
			$data['conclusion_date'] = date("Y-m-d", strtotime($data['conclusion_date']));
		}
		if (isset($data['execution_date']) && $data['execution_date']) {
			$data['execution_date'] = date("Y-m-d", strtotime($data['execution_date']));
		}
		if (isset($data['disagreement_num']) && $data['disagreement_num']) {
			$data['disagreement_num'] = Utils::trim_str($data['disagreement_num']);
		}
		if (isset($data['specification_num']) && $data['specification_num']) {
			$data['specification_num'] = Utils::trim_str($data['specification_num']);
		}

		R::begin();
		try {
			if ($id) {
				self::update($data, $files);
			} else {
				$id = self::create($data, $files);
			}
			if ($files) {
				$year = date('Y');
				@mkdir($_SERVER['DOCUMENT_ROOT']."/uploads/any/agreement/$year");
				@mkdir($_SERVER['DOCUMENT_ROOT']."/uploads/any/agreement/$year/$id");
				foreach ($files['tmp_name'] as $i => $tmp) {
					$move_res = move_uploaded_file($tmp, $_SERVER['DOCUMENT_ROOT']."/uploads/any/agreement/$year/$id/".$files['name'][$i]);
				}
			}
			R::commit();
			HttpResponse::send_status(true, 'ok', $data);
		} catch(Exception $e) {
			R::rollback();
			HttpResponse::send_status(false, $e->getMessage());
		} 
	}

	// static function get_next_output_number($id) {
	//     $user_data = BitrixUser::verify_auth_token();
	//     Conf::add_db('admin');
	//     Conf::select_db('admin');
	//     $count = R::findOne('correspondence_whom', ' id = ?', [$id])->quantity;
	//     HttpResponse::send_status(true, 'ok', ++$count);
	// }

	// private static function update_quantity($id) {
	//     $b = R::findOne('correspondence_whom', ' id = ?', [$id]);
	//     $b->quantity = ++$b->quantity;
	//     R::store($b);
	//     return $b->quantity;
	// }

	private static function create($data, $files) {
		$b = Utils::xdispense(self::$table);
		$b->date = $data['date'];
		
		$b->legal_entity = $data['legal_entity'];
		$b->contractor = $data['contractor'];
		$b->agreement_num = $data['agreement_num'];
				
		if (isset($data['contractor_name']) && $data['contractor_name']) {
			$b->contractor_name = $data['contractor_name'];
		}	
		if (isset($data['implementor']) && $data['implementor']) {
			$b->implementor = $data['implementor'];
		}	
		if (isset($data['agreement_sum']) && $data['agreement_sum']) {
			$b->agreement_sum = $data['agreement_sum'];
		}
		if (isset($data['agreement_type']) && $data['agreement_type']) {
			$b->agreement_type = $data['agreement_type'];
		}
		if (isset($data['agreement_title']) && $data['agreement_title']) {
			$b->agreement_title = $data['agreement_title'];
		}
		if (isset($data['signed']) && $data['signed']) {
			$b->signed = $data['signed'];
		}
		if (isset($data['employee']) && $data['employee']) {
			$b->employee = $data['employee'];
		}
		if (isset($data['conclusion_date']) && $data['conclusion_date']) {
			$b->conclusion_date = $data['conclusion_date'];
		}
		if (isset($data['execution_date']) && $data['execution_date']) {
			$b->execution_date = $data['execution_date'];
		}
		if (isset($data['disagreement_num']) && $data['disagreement_num']) {
			$b->disagreement_num = $data['disagreement_num'];
		}
		if (isset($data['specification_num']) && $data['specification_num']) {
			$b->specification_num = $data['specification_num'];
		}

		if ($files) {
			$arr = [];
			foreach ($files['name'] as $i => $name) {
    			$arr[$i]['name'] = $name;
			}
			foreach ($files['size'] as $i => $size) {
    			$arr[$i]['size'] = $size;
			}
			$b->files = json_encode($arr, JSON_UNESCAPED_UNICODE);
		}

		$b->creator_id = self::$bitrix_id;
		$b->editor_id = self::$bitrix_id;
		return R::store($b);
	}

	private static function update($data, $files) {

		$b = R::findOne(self::$table, ' id = ?', [$data['id']]);
		$b ?? HttpResponse::send_status(false, 'permission denied 3');
		
		$b->date = $data['date'];

		$b->legal_entity = $data['legal_entity'];
		$b->contractor = $data['contractor'];
		$b->contractor_name = $data['contractor_name'];
		$b->agreement_num = $data['agreement_num'];

		if (isset($data['agreement_type']) && $data['agreement_type']) {
			$b->agreement_type = $data['agreement_type'];
		}		
		if (isset($data['implementor']) && $data['implementor']) {
			$b->implementor = $data['implementor'];
		}	
		if (isset($data['agreement_sum']) && $data['agreement_sum']) {
			$b->agreement_sum = $data['agreement_sum'];
		}			
		if (isset($data['agreement_title']) && $data['agreement_title']) {
			$b->agreement_title = $data['agreement_title'];
		}
		if (isset($data['employee']) && $data['employee']) {
			$b->employee = $data['employee'];
		}		
		if (isset($data['signed']) && $data['signed']) {
			$b->signed = $data['signed'];
		}
		if (isset($data['conclusion_date']) && $data['conclusion_date']) {
			$b->conclusion_date = $data['conclusion_date'];
		}
		if (isset($data['execution_date']) && $data['execution_date']) {
			$b->execution_date = $data['execution_date'];
		}
		if (isset($data['disagreement_num']) && $data['disagreement_num']) {
			$b->disagreement_num = $data['disagreement_num'];
		}
		if (isset($data['specification_num']) && $data['specification_num']) {
			$b->specification_num = $data['specification_num'];
		}
		if ($files) {
			$old = $b->files ? json_decode($b->files, JSON_UNESCAPED_UNICODE) : [];	

			if ($old) {
				foreach($old as $i => $item) {
					$old_names[] = $item['name'];
				}
				foreach($files['name'] as $i => $file){
					if(in_array($file, $old_names)) {
						
					} else {
						array_push($old, 
						[
							'name' => $file,
							'size' => $files['size'][$i]
						]);
					}
				}
			} else {
				foreach($files['name'] as $i => $file){
					$old[] = 
					[
						'name' => $file,
						'size' => $files['size'][$i]
					];
				}
			}
			
			$b->files = json_encode($old, JSON_UNESCAPED_UNICODE);
			
			// $update_data['new_files'] = $old;
			// $update_data['id'] = $b->id;
			// $update_data['agreement_num'] = $b->agreement_num;
			// HttpResponse::send_status(true, 'ok', $old);
		}
		$b->editor_id = self::$bitrix_id;
		R::store($b);
	}   

	
	private static function trim_request_param($param) {
		$param = Utils::trim_str($param);
		if (!$param) {
			HttpResponse::send_status(false, 'permission denied 4');
		} else {
			return $param;
		}
	}
}
?>