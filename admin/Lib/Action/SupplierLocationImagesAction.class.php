<?php

// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: awfigq <awfigq@qq.com>
// +----------------------------------------------------------------------

class SupplierLocationImagesAction extends CommonAction {
	public function insert() {
		$name = $this->getActionName();
		$model = D($name);
		$data = $model->create();
		$data['create_time'] = TIME_UTC;
		if (false === $data) {
			$this->error($model->getError());
		}
		$data['create_time'] = get_gmtime();
		
		$list = false;

		//保存当前数据对象
		foreach ($_REQUEST['image'] as $k => $v) {
			if ($v) {
				$data['image'] = $v;
				$data['brief'] = $_REQUEST['brief'][$k];
				$list = $model->add($data);
			}
		}
		if ($list !== false) { //保存成功    
			$supplier_info['id'] = $data['supplier_location_id'];
			syn_supplier_locationcount($supplier_info);
			update_supplier_location_img($supplier_info['id']);
			save_log(L("INSERT_SUCCESS"), 1);
			$this->success(L("INSERT_SUCCESS"));
			;
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log(L("INSERT_FAILED") . $dbErr, 0);
			$this->error(L("INSERT_FAILED") . $dbErr);
		}
	}

	public function add() {
		$images_group = Get_Images_Group_List($_REQUEST['supplier_location_id']);

		$this->assign('images_group', $images_group);
		$this->display();
	}

	public function edit() {
		$id = intval($_REQUEST['id']);
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();
		$images_group = Get_Images_Group_List($vo['supplier_location_id']);
		$this->assign('images_group', $images_group);
		$this->assign('vo', $vo);
		$this->display();
	}

	public function update() {
		//B('FilterString');
		$name = $this->getActionName();
		$model = D($name);
		if (false === $data = $model->create()) {
			$this->error($model->getError());
		}
		// 更新数据
		$list = $model->save($data);
		$id = $data[$model->getPk()];

		if (false !== $list) {
			//判断是否审核如果审核通过并且用户UID 不等于0 则同步到分享
			$supplier_info['id'] = $data['supplier_location_id'];
			syn_supplier_locationcount($supplier_info);
			update_supplier_location_img($supplier_info['id']);
			if (intval($data['supplier_location_id']) <> intval($_REQUEST['old_supplier_location_id'])) {
				$supplier_info['id'] = $_REQUEST['old_supplier_location_id'];
				syn_supplier_locationcount($supplier_info);
				update_supplier_location_img($supplier_info['id']);
			}

			//成功提示
			save_log($data . L("UPDATE_SUCCESS"), 1);
			$this->assign('jumpUrl', Cookie :: get('_currentUrl_'));
			$this->success(L('UPDATE_SUCCESS'));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($data . L("UPDATE_FAILED") . $dbErr, 0);
			$this->error(L('EDIT_ERROR'));
		}
	}

	public function foreverdelete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST['id'];
		if (!empty ($id)) {
			$name = $this->getActionName();
			$model = D($name);
			$pk = $model->getPk();
			$ids = explode(',', $id);
			$condition = array (
				$pk => array (
					'in',
					$ids
				)
			);
			$list = $model->where($condition)->findAll();

			if (false !== $model->where($condition)->delete()) {
				//循环删除数据
				foreach ($list as $k => $v) {
					@ unlink(APP_ROOT . $v['image']);
					$supplier_info['id'] = $v['supplier_location_id'];
					syn_supplier_locationcount($supplier_info);
					update_supplier_location_img($supplier_info['id']);
				}
				save_log($ids . l("FOREVER_DELETE_SUCCESS"), 1);
				$this->success(l("FOREVER_DELETE_SUCCESS"), $ajax);
			} else {
				save_log($ids . l("FOREVER_DELETE_FAILED"), 0);
				$this->error(l("FOREVER_DELETE_FAILED"), $ajax);
			}
		} else {
			$this->error(l("INVALID_OPERATION"), $ajax);
		}
	}

	public function set_sort() {
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=" . $id)->getField("name");
		if (!check_sort($sort)) {
			$this->error(l("SORT_FAILED"), 1);
		}
		M(MODULE_NAME)->where("id=" . $id)->setField("sort", $sort);
		save_log($log_info . l("SORT_SUCCESS"), 1);
		$this->success(l("SORT_SUCCESS"), 1);
	}

	public function getMerchantByName() {
		$name = trim($_REQUEST['name']);

		if (!empty ($name)) {
			$where .= ' AND `name` LIKE \'%' . $name . '%\'';
		}

		$sql = 'SELECT id,name FROM ' . DB_PREFIX . 'supplier_location WHERE 1 = 1 ' . $where;
		$list = M()->query($sql);

		echo json_encode($list);
	}

	public function get_images_group() {
		$supplier_location_id = intval($_REQUEST['supplier_location_id']);
		$id = intval($_REQUEST['id']);
		$images_group = Get_Images_Group_List($supplier_location_id);
		$images_group_id = M("SupplierLocationImages")->where("id=" . $id)->getField("images_group_id");
		$this->assign("images_group", $images_group);
		$this->assign("images_group_id", $images_group_id);
		$this->display();
	}
}

function getUNAME($id) {
	if ($id == 0)
		return "管理员添加";
	else
		return M("User")->where("id=" . $id)->getField("user_name");
}
function getMerchant($id) {
	return M("SupplierLocation")->where("id=" . $id)->getField("name");
}
function getDP($id) {
	if ($id == 0)
		return "非点评图片";
	else
		return "<a href='" . U("SupplierLocationDp/edit", array (
			"id" => $id
		)) . "'>查看点评</a>";
}
function getMerchantOption($id) {
	$merchant = M("SupplierLocation")->getById($id);
	return "<option value='" . $merchant['id'] . "'>" . $merchant['name'] . "</option>";
}
/**
 * 更新商家图片统计
 * @param unknown_type $supplier_location_id
 */
function update_supplier_location_img($supplier_location_id) {
	D("SupplierLocation")->where("id=$supplier_location_id")->setField("image_count", D("SupplierLocationImages")->where("supplier_location_id=$supplier_location_id and status=1 ")->count());
}

function Get_Images_Group_List($supplier_location_id) {
	if (intval($supplier_location_id) > 0) {
		$deal_cate_id = M("SupplierLocation")->where("id=" . intval($supplier_location_id))->getField("deal_cate_id");
		$list = M()->query("SELECT * FROM " . DB_PREFIX . "images_group WHERE id in(SELECT images_group_id FROM " . DB_PREFIX . "images_group_link WHERE category_id=" . $deal_cate_id . ")");
	}
	return $list;
}
?>