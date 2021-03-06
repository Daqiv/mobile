<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TaskController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}

	public function index(){
		$keyword = I('keyword');
		if($keyword != ''){
			$map['taskname'] = array('like','%'.$keyword.'%');
			$parameters['keyword'] = $keyword;
		}
		$count=D('runcode')->where($map)->count();
		$Page = new \Think\Page($count,13,$parameters);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		$list=D('runcode')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$data=D('instruct')->getfield('code,name',true);
		foreach ($list as $k => $v) {	
			$mustt_names = unserialize($v['mustt']);
			$list[$k]['mustt'] = $mustt_names;
			$mingle_names = unserialize($v['mingle']);
			$list[$k]['mingle'] = $mingle_names;
			$userinfo = $this->Getuserbyid($v['authorid']);
			$list[$k]['username'] = $userinfo['user_login'];
		}
		$this->assign('data',$data);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}	
	
	public function taskinfo(){
		$id = I('id');
		$data=D('runcode')->where('id=%d',array($id))->find();
		$data['mustt'] = unserialize($data['mustt']);
		$data['mingle'] = unserialize($data['mingle']);
		$data['parame'] = unserialize($data['parame']);
		$instruct=D('instruct')->getfield('code,name',true);
		
		$this->assign('data',$data);
		$this->assign('instruct',$instruct);
		$this->display();
	}
	
	public function edit(){
		$id = I('id');
		$data=D('runcode')->where('id=%d',array($id))->find();
		$data['mustt'] = implode(unserialize($data['mustt']),',');
		$data['mingle'] = implode(unserialize($data['mingle']),',');
		$data['parame'] = unserialize($data['parame']);
		$instruct=D('instruct')->order('code asc')->select();
		foreach($instruct as $k=>$v){
			$instruct[$k]['parame'] = unserialize($v['parame']);
			foreach($instruct[$k]['parame'] as $k1=>$v1){
				$mustts_column[$k][$k1] = 'mustt_'.$v1['column']['name'];
				$mingle_column[$k][$k1] = 'mingle_'.$v1['column']['name'];
			}
		}
		
        $this->assign('instruct',$instruct);	
		$this->assign('data',$data);
		$this->assign('mustts_column',$mustts_column);	
		$this->assign('mingle_column',$mingle_column);				
		$this->display();
	}
	
	public function savetask(){
		$id = I('id');
		$data['taskname']=I('taskname');			
		$data['alterip']=I('alterip');
		$data['addtime']=time();
		$data['mustt']=serialize($_POST['mustt']);
		$data['mingle']=serialize($_POST['mingle']);
		


		$data['onmoble']=$_POST['onmoble'];

		if(I('onmoble')==132){
			$data['weixicut']=123;
		}
		$parame['vpnuser']=I('vpnuser');
		$parame['vpnpwd']=I('vpnpwd');
		$parame['pwd']=$_POST['pwd'];
		$parame['photo']=$_POST['photo'];
		$parame['abcuser']=$_POST['abcuser'];
		$parame['abcpwd']=$_POST['abcpwd'];
		$parame['joinbusi']=$_POST['joinbusi'];
		
		$parame_mustts = $this->coldata('mustt');
		$parame_mingle = $this->coldata('mingle');
		
		foreach($parame_mustts as $k=>$v){
			if(isset($parame_mustts[$k]) && $v !=''){
				$parame[$k] = $v;
			}
		}
		
		foreach($parame_mingle as $k=>$v){
			if(isset($parame_mingle[$k]) && $v !=''){
				$parame[$k] = $v;
			}
		}	
		$data['weixicut']=$_POST['weixicut'];
		
		$data['parame']=serialize($parame);
		
		$data['authorid'] = session("ADMIN_ID");
	

		$runcode=D('runcode');			
		$data=$runcode->create($data);

		if($data){
			$sult=$runcode->where('id=%d',array($id))->save($data);
			if($sult){
				$this->success('修改成功');
			}else{
				$this->error('修改错误'.$runcode->getError());
			}
			
		}else{
			$this->error('数据错误'.$runcode->getError());
		}
	}
	
	public function add(){
		if(IS_POST){
			$data['taskname']=I('taskname');			
			$data['alterip']=I('alterip');
			$data['addtime']=time();
			
			
			$data['mustt']=serialize($_POST['mustt']);
			$data['mingle']=serialize($_POST['mingle']);
			$data['weixicut']=$_POST['weixicut'];
			$data['onmoble']=$_POST['onmoble'];
			if(I('onmoble')=='132' and I('weixicut')=='121'){
				$data['weixicut']=123;
			}
			$parame['vpnuser']=I('vpnuser');
			$parame['vpnpwd']=I('vpnpwd');
			$parame['pwd']=$_POST['pwd'];
			$parame['photo']=$_POST['photo'];
			$parame['abcuser']=$_POST['abcuser'];
			$parame['abcpwd']=$_POST['abcpwd'];
			$parame['joinbusi']=$_POST['joinbusi'];
			$parame_mustts = $this->coldata('mustt');
			$parame_mingle = $this->coldata('mingle');
			foreach($parame_mustts as $k=>$v){
				if(isset($parame_mustts[$k]) && $v !=''){
					$parame[$k] = $v;
				}
			}
			foreach($parame_mingle as $k=>$v){
				if(isset($parame_mingle[$k]) && $v !=''){
					$parame[$k] = $v;
				}
			}
			
			$data['parame']=serialize($parame);
			$data['authorid'] = session("ADMIN_ID");

			$runcode=D('runcode');			
			$data=$runcode->create($data);
			if($data){
				$sult=$runcode->add($data);
				if($sult){
					$this->success('添加成功');
				}else{
					$this->error('增加错误'.$runcode->getError());
				}
				
			}else{
				$this->error('增加错误'.$runcode->getError());
			}

			exit();
		}
		$list=D('instruct')->order('code asc')->select();
		foreach($list as $k=>$v){
			$list[$k]['parame'] = unserialize($v['parame']);
		}
		
        $this->assign('instruct',$list);		
        $this->display();
	}
	
	protected function coldata($col='mustt'){
		$mustt = I($col);
		foreach($mustt as $k=>$v){
			$mustts[][] = $v;
		}
		
		foreach($mustts as $k=>$v){
			$column_name = I($col.'_colname_'.$v[0]);
			foreach($column_name as $k1=>$v1){
				$mustts[$col.'_'.$v1] = I($col.'_'.$v1.'_'.$v[0]);
			}
			unset($mustts[$k]);
		}
		return $mustts;
	}
	
	public function mobile(){
		$this->display();
	}
	function delete(){
		$id=intval(I('id'));
		if($id){
			$result=D('runcode')->where('id=%d',array($id))->delete();
			if($result){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('删除失败');
		}
		
	}

}

?>