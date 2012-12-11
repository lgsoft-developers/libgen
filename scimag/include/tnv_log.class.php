<?php
class tnv_log
{
    var $dbTable = 'tnv_logs';
    var $mysql = '';

    function tnv_log($pars)
    {
        if ($pars['dbTable'] != '')
            $this->dbTable = $pars['dbTable'];
        //$this->mysql = $pars['mysql'];
        global $mysql;
        $this->mysql=$mysql;
    }

    function log($proc, $message, $is_error = false)
    {	if ($is_error) $is_error=1; else $is_error=0;
        $sql = "insert into $this->dbTable (insert_time,proc,message,is_error,status) values (NOW(),'$proc','$message',$is_error,2)";
        if (!$this->mysql->query($sql))
            return false;
        return true;
    }

    function message($proc, $message, $is_error = false, $log = true)
    {

        $msg = "[$proc]\t$message";
        if ($is_error)
            $msg = '<font color="red">' . $msg . '</font>';
        $msg .= "\n<br>";
        if ($log) {
			$l=$this->log($proc, $message, $is_error);
        	if (!$l) return false;
        	}
        echo $msg;
        return true;
    }
    
    function reduce_log($days=14){
		$sql="delete from $this->dbTable where insert_time<DATE_SUB(NOW(),INTERVAL $days day)";
		if (!$this->mysql->query($sql)) return false;
		return true;	
	}

	function getError(){
		return $this->mysql->getError();
	}
}
?>