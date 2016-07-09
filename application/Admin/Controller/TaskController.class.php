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
		$list=D('runcode')->select();
		foreach ($list as $k => $v) {			
			$list[$k]['mustt']=implode(unserialize($v['mustt']),',');
			$list[$k]['mingle']=implode(unserialize($v['mingle']),',');
		}
		$this->assign('list',$list);
		$this->display();
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

			$parame['vpnuser']=I('vpnuser');
			$parame['vpnpwd']=I('vpnpwd');
			$parame['pwd']=$_POST['pwd'];
			$parame['photo']=$_POST['photo'];
			$parame['nickename']=$_POST['nickename'];

			$data['parame']=serialize($parame);

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
		$list=D('instruct')->select();
        $this->assign('instruct',$list);		
        $this->display();
	}
	public function mobile(){
		$this->display();
	}

}

?>