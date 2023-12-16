<?php

class db {
	var $connect_id;

	//----- executa uma expressão SQL
	function execute($strSQL) {
		@mysqli_query($strSQL, $this->connect_id);
		return @mysqli_insert_id($this->connect_id);
	}

	//----- begin transaction
	function begin() {
		@mysqli_query("BEGIN",$this->connect_id);
	}

	//----- commit transaction
	function commit() {
		@mysqli_query("COMMIT",$this->connect_id);
	}

	//----- rollback transaction
	function rollback() {
		@mysqli_query("ROLLBACK",$this->connect_id);
	}

	//----- abertura do banco de dados
	//----- configure a conexão conforme suas necessidades
	function open($database=DB_DATABASE, $host=DB_HOST, $user=DB_USER, $password=DB_PASSWORD) {

		$this->connect_id=@mysqli_connect($host, $user, $password);

		if ($this->connect_id) {
			$result=@mysqli_select_db($database);
			if (!$result) {
				@mysqli_close($this->connect_id);
				$this->connect_id=$result;
			}
		}
		return $this->connect_id;
	}

	//----- efetua lock na tabela
	function lock($table, $mode="write") {
		$query=new query($this, "lock tables $table $mode");
		$result=$query->result;
		return $result;
	}

	//----- efetua unlock nas tabelas em lock
	function unlock() {
		$query=new query($this, "unlock tables");
		$result=$query->result;
		return $result;
	}

	/*
		Affected Rows
		Retorna o ultimo ID alterado.
	*/
	function affected_rows(){
		return mysqli_affected_rows($this->connect_id);
	}

	//----- retorna mensagem de erro
	function error($string_erro="") {
		//----- caso ocorra erro, envia mensagem
		if (@mysqli_errno($this->connect_id)!=0) {
			@mail(SYS_SUPPORT_EMAIL,"Erro " . date("d-m-Y"), mysql_errno($this->connect_id) . " - " . mysql_error($this->connect_id) . " - " . $string_erro);
		}
		return @mysqli_errno($this->connect_id);
	}

	//----- encerra conexão e todos recorsets abertos
	function close() {
			if ($this->query_id && is_array($this->query_id)) {
				while (list($key,$val)=each($this->query_id)) {
					@mysqli_free_result($val);
				}
			}
		if (DB_PERSISTENT) {
			$result=@mysqli_close($this->connect_id);
			return $result;
		}
	}

	//----- gera pool de recordsets. método privado, não utilizar !!!
	function addquery($query_id) {
		$this->query_id[]=$query_id;
	}

};

class query {
	var $result;
	var $row;
	var $numrows;
	var $totalpages=0;

	//----- construtor, retorna recordset
	function query(&$db, $query="", $initial_page=0, $page_length=0) {
		if ($query) {
			if ($this->result) {
				$this->free();
			}
			$this->result = @mysqli_query($query, $db->connect_id);
			$this->numrows = @mysqli_num_rows($this->result);

			if (($initial_page+$page_length) > 0) {
				$this->totalpages = ceil($this->numrows() / $page_length);
				$query .= " limit " . ($initial_page-1)*$page_length . ", $page_length";
			}
			$this->result=@mysqli_query($query, $db->connect_id);
			$db->addquery($this->result);
		}
	}

	function totalpages() {
		return $this->totalpages;
	}

	//----- retorna array com os campos e avança o registro
	function getrow() {
		if ($this->result) {
			$this->row=@mysqli_fetch_array($this->result);
		} else {
			$this->row=0;
		}
		return $this->row;
	}

	//----- retorna o valor do campo
	function field($field) {
		if(get_magic_quotes_gpc()) {
			$result=stripslashes($this->row[$field]);
		} else {
		 	$result=$this->row[$field];
		}
		return $result;
	}

	//----- retorna o nome do campo
	function fieldname($fieldnum) {
		return @mysqli_field_name( $this->result, $fieldnum );
	}

	//----- retorna primeira linha do recordset
	function firstrow() {
		$result=@mysqli_data_seek($this->result,0);
		if ($result) {
			$result=$this->getrow();
		}
		return $this->row;
	}

	//----- fecha o recordset
	function free() {
		return @mysqli_free_result($this->result);
	}

	//----- retorna a quantidade de registros
	function numrows() {
		return $this->numrows;
	}
}
?>