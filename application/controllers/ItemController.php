<?php
class ItemController extends Controller
{	
	public function index()
	{	
		$list = (new ItemModel())->where(array('userID in(5000, 5002, 5003)', 'and userType = 0'))->selectAll();		
		//print_r($list);exit;
		
		$this->assign('title', '首页');
		$this->render();
	}
}