<?php

class Admin {
	
	public function generateAddUser() {
		$result = true;
		
		echo 
		"
		<form id=\"form_add_user\" name=\"form_add_user\" method=\"post\" action=\"\">
		</form>
		";
		
		return $result;
	}
	
	public function generateRemoveUser() {
		$result = true;
		
		echo 
		"
		<form id=\"form_remove_user\" name=\"form_remove_user\" method=\"post\" action=\"\">
		</form>
		";
		
		return $result;
	}

	public function generateEditUser() {
		$result = true;
		
		echo 
		"
		<form id=\"form_edit_user\" name=\"form_edit_user\" method=\"post\" action=\"\">
		</form>
		";
		
		return $result;
	}

}

?>