<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserController extends AdminbaseController{
	protected $users_model,$role_model;
	
	function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
		$this->role_model = D("Common/Role");
	}
	function index(){
		$username = I('keyword');
		$role_id = I('role_id');
		$map['user_type'] = 1;
		
		if($username != ''){
			$map['user_login'] = array('like','%'.$username.'%');
			$parameters['keyword'] = $username;
		}
		if($role_id != '' && $role_id != 0){
			$user_ids = M("RoleUser")->where(array("role_id"=>$role_id))->getField("user_id",true);
			if($role_id == 1){
				$user_ids[] = 1;
			}
			$user_ids = implode(',',$user_ids);
			$map['id'] = array('in',$user_ids);
			
			$parameters['role_id'] = $role_id;
		}
		
		$count=$this->users_model->where($map)->count();
		$page = $this->page($count, 20,$parameters);
		/*
			->field('*,count')
		->join(sprintf('(SELECT userid,COUNT(*) AS count FROM  mbl_mobile WHERE status=1 and userid>0 and updatetime>%d and updatetime<%d GROUP BY userid) as om on om.userid=ul.id',strtotime(date("Y-m-d",time())),strtotime(date("Y-m-d 23:59:59",time()))),'left')		
		->where($map)
		->order("create_time DESC")
		*/

		$users = $this->users_model->alias("ul")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$now=strtotime(date('Y-m-d', time()));

		foreach($users as $k=>$v){
			$users[$k]['count'] = M('usermobile')->where(array('uid'=>$v['id'],'now'=>$now))->getField('count');
			$users[$k]['ucounts'] = M('usermobile')->where(array('uid'=>$v['id']))->sum('count');
		}

		// $usercates = $this->usercates();		
		// foreach($users as $k=>$v){
		//}
		// 	$ucounts = $this->GetOperateCount(4,$v['id']);
		// 	$users[$k]['ucounts'] = $ucounts;
		// 	$users[$k]['cate_name'] = $usercates[$v['cate_id']]['cate_name'];			
		// 	$role_user_model=M("RoleUser");
		// 	$role_ids=$role_user_model->where(array("user_id"=>$v['id']))->getField("role_id",true);
		// 	$role_ids = implode(',',$role_ids);
		// 	if($v['id'] != 1 && $role_ids != ''){
		// 		$cur_roles=$this->role_model->where("status=1 and id in(".$role_ids.")")->getField("name",true);
		// 		$cur_roles = implode(',',$cur_roles);
		// 		$users[$k]['cur_roles'] = $cur_roles;
		// 	}else if($v['id'] == 1){
		// 		$users[$k]['cur_roles'] = '超级管理员';
		// 	}
		// }
		
		// $allcountlist['allcounts'] = $this->GetOperateCount();
		// $allcountlist['todaycounts'] = $this->GetOperateCount(2);
		// $allcountlist['machcounts'] = $this->GetOperateCount(3);
		
		$roles_src=$this->role_model->select();
		$roles=array();
		foreach ($roles_src as $r){
			$roleid=$r['id'];
			$roles["$roleid"]=$r;
		}
		
		$this->assign("parameters",$parameters);
		$this->assign("allcountlist",$allcountlist);
		$this->assign("page", $page->show('Admin'));
		$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->display();
	}
	/**
	 *获取操作个数
	 *$type:1-默认所有,2-今天所有,3-机器操作所有,4-用户所有
	 *$userid:用户id
	 */
	protected function GetOperateCount($type='1',$userid){
		$map['status'] = 1;
		if($type == 2){
			$map["updatetime"] = array('gt',strtotime(date("Y-m-d",time())));
			$map2["updatetime"] = array('elt',strtotime(date("Y-m-d 23:59:59",time())));
			$map['_complex'] = $map2;
			
		}else if($type == 3){
			$map['userid'] = 0;
		}else if($type == 4 && $userid > 0){
			$map['userid'] = $userid;
		}
		$operatecounts = D('mobile')->where($map)->count();
		return $operatecounts;
	}
	
	function add(){
		$roles=$this->role_model->where("status=1")->order("id desc")->select();
		$this->assign("roles",$roles);
		$this->display();
	}
	
	function add_post(){
		if(IS_POST){
			if(!empty($_POST['role_id']) && is_array($_POST['role_id'])){
				$role_ids=$_POST['role_id'];
				unset($_POST['role_id']);
				if ($this->users_model->create()) {
					$result=$this->users_model->add();
					if ($result!==false) {
						$role_user_model=M("RoleUser");
						foreach ($role_ids as $role_id){
							$role_user_model->add(array("role_id"=>$role_id,"user_id"=>$result));
						}
						$this->success("添加成功！", U("user/index"));
					} else {
						$this->error("添加失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}
			
		}
	}
	
	
	function edit(){
		$id= intval(I("get.id"));
		$roles=$this->role_model->where("status=1")->order("id desc")->select();
		$this->assign("roles",$roles);
		$role_user_model=M("RoleUser");
		$role_ids=$role_user_model->where(array("user_id"=>$id))->getField("role_id",true);
		$this->assign("role_ids",$role_ids);
			
		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if(!empty($_POST['role_id']) && is_array($_POST['role_id'])){
				if(empty($_POST['user_pass'])){
					unset($_POST['user_pass']);
				}
				$role_ids=$_POST['role_id'];
				unset($_POST['role_id']);
				if ($this->users_model->create()) {
					$result=$this->users_model->save();
					if ($result!==false) {
						$uid=intval($_POST['id']);
						$role_user_model=M("RoleUser");
						$role_user_model->where(array("user_id"=>$uid))->delete();
						foreach ($role_ids as $role_id){
							$role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));
						}
						$this->success("保存成功！");
					} else {
						$this->error("保存失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}
			
		}
	}
	
	/**
	 *  删除
	 */
	function delete(){
		$id = intval(I("get.id"));
		if($id==1){
			$this->error("最高管理员不能删除！");
		}
		
		if ($this->users_model->where("id=$id")->delete()!==false) {
			M("RoleUser")->where(array("user_id"=>$id))->delete();
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	
	function userinfo(){
		$id=get_current_admin_id();
		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}
	
	function userinfo_post(){
		if (IS_POST) {
			$_POST['id']=get_current_admin_id();
			$create_result=$this->users_model
			->field("user_login,user_email,last_login_ip,last_login_time,create_time,user_activation_key,user_status,role_id,score,user_type",true)//排除相关字段
			->create();
			if ($create_result) {
				if ($this->users_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
		}
	}
	
	    function ban(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','0');
    		if ($rst) {
    			$this->success("管理员停用成功！", U("user/index"));
    		} else {
    			$this->error('管理员停用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','1');
    		if ($rst) {
    			$this->success("管理员启用成功！", U("user/index"));
    		} else {
    			$this->error('管理员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
	
	public function usercate(){
		$id = I('id');
		$userinfo = $this->Getuserbyid($id);
		
		$data = $this->usercates();
		$this->assign('data',$data);
		$this->assign('userinfo',$userinfo);
		$this->assign('id',$id);
		$this->display();
	}
	
	protected function usercates(){
		$data=M('UserCate')->where('status=1')->getfield('id,cate_name,pid,status,authorid',true);
		return $data;
	}
	
	public function saveusercate(){
		$id = I('id');
		$data['cate_id'] = I('cate_id');
		$result = D('users')->where('id=%d',array($id))->save($data);
		if($result){
			$this->success("设置成功", U("user/index"));
		}else{
			$this->success("设置失败");
		}
	}
}