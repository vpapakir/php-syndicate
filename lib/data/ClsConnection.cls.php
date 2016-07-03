<?php
	class ClsConnection
	{
		var $Host="localhost";
		var $UsrNam="root";
		var $Paswrd="";
		var $DBName="intranet";
		
		//Establish Database Connection While Creating or Inherit this class.
		function ClsConnection()
		{
			mysql_connect($this->Host,$this->UsrNam,$this->Paswrd)or die("Error in localhost connection.");
			mysql_select_db($this->DBName)or die("Error in Database Connections");
			//echo "Connection Established (Connection Class)";
		}

		function UserDefineConnection($HostName,$UserName,$Password,$DatabaseName)
		{
			if($HostName!="")
				$this->Host=$HostName;
			if($UserName!="")
				$this->UsrNam=$UserName;
			if($Password!="")
				$this->Paswrd=$Password;
			if($DatabaseName!="")
				$this->DBName=$DatabaseName;

			mysql_connect($this->Host,$this->UsrNam,$this->Paswrd)or die("Error in localhost connection.");
			mysql_select_db($this->DBName)or die("Error in Database Connections");
			//echo "Connection Established by user defined Data.(Connection Class)";
		}
		
		//Check Table $DatabaseTableName Existance. If Exists return True otherwise False.
		function DataBaseTableExists($DatabaseTableName)
		{
			if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$DatabaseTableName."'"))==1)
				return "True";	//Exists
			else
				return "False";	//Not Exists
		}
		
		//This Function Build " SQL SELECT " Query with A Specified Condition.
		function BuildSelectQuery($TableName,$Condition="",$OrderByCol="")
		{
			if($Condition=="")
				$Query="select * from $TableName";
			else
				$Query="select * from $TableName where $Condition";
			if($OrderByCol!="")
				$Query.=" ORDER BY $OrderByCol";
			return $Query;
		}
	
		//This Function Build " SQL UPDATE " Query with A Specified Condition.
		function BuildUpdateQuery($DatabaseTableName,$UpdateFieldsAndValues,$UpdateCondition="")
		{
			$UpdateQuery="UPDATE $DatabaseTableName SET ";
			foreach($UpdateFieldsAndValues as $ColName=>$UpdateVal)
				$UpdateQuery.="$ColName = $UpdateVal, ";
			$UpdateQuery=substr($UpdateQuery,0,strlen($UpdateQuery)-2);
			if($UpdateCondition!="")
				$UpdateQuery.=" where $UpdateCondition";
			return $UpdateQuery;
		}
		
		function BuildDeleteQuery($TableName,$Condition="")
		{
			$Query="delete from $TableName";
			if($Condition!="")
				$Query.="where $Condition";
			return $Query;
		}
		
		function DeleteFile($ImageName,$FolderLocation="",$SuccessMessage="File Successfully Deleted.",$FailureMessage="Error while deleting file.")
		{
			//If ImageName is Empty and FolderLocation exist then All Files in this Folder will be deleted.
			if($ImageName=="" && $FolderLocation!="")
			{
				foreach (glob("*.*") as $filename) 
				{
				   if(unlink($filename))
				   	echo "<br>Successfully Deleted: $filename <br>";
				}
			}
			else	//Only a Single File will be Deleted.
			{
				if($FolderLocation=="")
					$FileFullLocation=$ImageName;
				else
					$FileFullLocation=$FolderLocation."/".$ImageName;
				if(file_exists($FileFullLocation))
				{
					if(unlink($FileFullLocation)	//Image Deleted.
						return $SuccessMessage;
					else
						return $FailureMessage;
				}
				else
					return $FailureMessage;
			}
		}
	}
?>