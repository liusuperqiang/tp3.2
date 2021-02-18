<?php
namespace Org\Util;

class Page {
	private $total; //数据表中总记录数
	private $listRows; //每页显示行数
	private $limit;
	private $uri;
	private $pageNum; //页数
	private $config=array('header'=>"个记录", "prev"=>"<", "next"=>">", "first"=>"|<", "last"=>">|");
	private $listNum=8;
	/*
	 * $total 
	 * $listRows
	 */
	public function __construct($total, $listRows=10, $pa=""){
		$this->total=$total;
		$this->listRows=$listRows;
		$this->uri=$this->getUri($pa);
		$this->page=!empty($_GET["page"]) ? $_GET["page"] : 1;
		$this->pageNum=ceil($this->total/$this->listRows);
		$this->limit=$this->setLimit();
	}

	private function setLimit(){
		//return "Limit ".($this->page-1)*$this->listRows.", {$this->listRows}";
		return "".($this->page-1)*$this->listRows.", {$this->listRows}";
	}

	private function getUri($pa){
		$url=$_SERVER["REQUEST_URI"].(strpos($_SERVER["REQUEST_URI"], '?')?'':"?").$pa;
		$parse=parse_url($url);

        parse_str($parse['query']);
		if(isset($parse["query"])){
			parse_str($parse['query'],$params);
			unset($params["page"]);
            if(isset($page)){
                if(count($params)==0)
                    $url=$parse['path'].'?'.http_build_query($params);
                else
                    $url=$parse['path'].'?'.http_build_query($params).'&';
            }else{
                $url=$parse['path'].'?'.http_build_query($params).'&';
            }

			
		}

		return $url;
	}

	function __get($args){
		if($args=="limit")
			return $this->limit;
		else
			return null;
	}

	private function start(){
		if($this->total==0)
			return 0;
		else
			return ($this->page-1)*$this->listRows+1;
	}

	private function end(){
		return min($this->page*$this->listRows,$this->total);
	}

	private function first(){
		$html = "";
		if($this->page<3)
			$html.="<li><a href='javascript:void(0);'>{$this->config["first"]}</a></li>\n";
		else
            $html.="<li><a href='{$this->uri}page=1'>{$this->config["first"]}</a></li>\n";
		return $html;
	}

	private function prev(){
		$html = "";
		if($this->page==1)
			$html.="<li class='paginate_button previous disabled'><a href='javascript:void(0);'>{$this->config["prev"]}</a></li>\n";
		else
			$html.="<li class='paginate_button previous'><a href='{$this->uri}page=".($this->page-1)."'>{$this->config["prev"]}</a></li>\n";
		return $html;
	}

	private function pageList(){
		$linkPage="";
		
		$inum=floor($this->listNum/2);
	
		for($i=$inum; $i>=1; $i--){
			$page=$this->page-$i;

			if($page<1)
				continue;

			$linkPage.="<li class='paginate_button'><a href='{$this->uri}page={$page}'>{$page}</a></li>\n";

		}

		$linkPage.="<li class='paginate_button active'><a href='javascript:void(0);'>{$this->page}</a></li>\n";

		for($i=1; $i<=$inum; $i++){
			$page=$this->page+$i;
			if($page<=$this->pageNum)
				$linkPage.="<li class='paginate_button'><a href='{$this->uri}page={$page}'>{$page}</a></li>\n";
			else
				break;
		}

		return $linkPage;
	}

	private function next(){
		$html = "";
		if($this->page==$this->pageNum)
			$html.="<li class='paginate_button next disabled'><a href='javascript:void(0);'>{$this->config["next"]}</a></li>";
		else
			$html.="<li class='paginate_button next'><a href='{$this->uri}page=".($this->page+1)."'>{$this->config["next"]}</a></li>";
		return $html;
	}

	private function last(){
		$html = "";
		if($this->page==$this->pageNum)
            $html.="<li><a href='javascript:void(0);'>{$this->config["last"]}</a></li>\n";
		else
			$html.="<li><a href='{$this->uri}page=".($this->pageNum)."'>{$this->config["last"]}</a></li>\n";
			
		return $html;
	}

	private function goPage(){
		return '<input type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>'.$this->pageNum.')?'.$this->pageNum.':this.value;location=\''.$this->uri.'&page=\'+page+\'\'}" value="'.$this->page.'" class="go"><input type="button" class="go_sub" value="GO" onclick="javascript:var page=(this.previousSibling.value>'.$this->pageNum.')?'.$this->pageNum.':this.previousSibling.value;location=\''.$this->uri.'&page=\'+page+\'\'">&nbsp;&nbsp;';
	}
	function show($display=array(0,1,2,3,4,5,6,7,8)){
		if($this->total == 0) return '';
		//$html[0]="<div class='message'>共<i class='blue'>{$this->total}</i>条记录，当前显示&nbsp;<i class='blue'>{$this->page}/{$this->pageNum}&nbsp;</i></div>";
		//$html[1]="&nbsp;&nbsp;每页显示<b>".($this->end()-$this->start()+1)."</b>条，本页<b>{$this->start()}-{$this->end()}</b>条&nbsp;&nbsp;";
		//$html[2]="&nbsp;&nbsp;<b>{$this->page}/{$this->pageNum}</b>页&nbsp;&nbsp;";

		$html[0]="<ul class='npagination'>\n";
        $html[1]="<li class='paginate_button'><a href='javascript:void(0);'>共{$this->total}条记录</a></li>";
		//$html[2]=$this->first();
		$html[3]=$this->prev();
		$html[4]=$this->pageList();
		$html[5]=$this->next();
		//$html[6]=$this->last();
		//$html[7]=$this->goPage();
		$html[8]="</ul>\n";
		$fpage='';
		foreach($display as $index){
			$fpage.=$html[$index];
		}

		return $fpage;

	}


}
