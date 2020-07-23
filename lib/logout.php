<?php 
	include 'vars.inc.php';
	include 'template.inc.php';
	
	//start the session
	session_start();
	
	if(isset($_SESSION['username'])) {
		//session variable is registered, the user is ready to logout
		session_unset();
		session_destroy();
	} else {
		header( "Location: index.html" ); 
	}
?>

<html>
<head>
<title>PHP Mass Mailer - Login Screen</title>
<META HTTP-EQUIV='Content-Type: text/html; Charset=iso-8859-1 http-equiv=Content-Type'>
<script type='text/javascript' language='JavaScript'>
</script>

<style type='text/css'>

        <!--

        INPUT, TEXTAREA, SELECT {
                font:10px Verdana, Times, Arial, serif, sans-serif;
                background-color: white;
                border:1px solid silver
        }

        TABLE, #x78y {
                font:12px Verdana, Times, Arial, serif, sans-serif;
        }

        BODY {
                background-color: white
        }

        #mybr {
                clear: left
        }

        #x34y  {
                float:left
        }

        #x35y {
                 border:solid silver 1.0pt;
        }

        #x77y {
                color:white;
                text-align: center
        }

        #x78y {
                color:white;
                text-align: right
        }

        #x79y {
                background-color: gray;
                border-style:none
        }

        A:link, A:visited,  A:active {
                font:10px Verdana, Times, Arial, serif, sans-serif;
                color: white;
                font-weight: bold
        }

        -->

</style>
</head>
<body>

<table id ='x79y' height=22 width=100%>
<tr>
    <td id = 'x79y'>
         <DIV id ='x77y'>
         </DIV>
    </td>
</tr>
</table> <br />

<center>

<table width=30%>

<?php

echo 	
'<p>
	Thank you for using PHP Mass Mailer!Click <a href="index.html" style="color:blue;text-align:center">here</a> to log in again!
</p>';

?>

</table>

</center>

<p></p>

<center>

<table id ='x79y' height=22 width=100%>
<tr>
   <td id = 'x79y'>
      <DIV id ='x78y'>PHP Mass Mailer &copy; Papakirikou Evangelos </DIV>
   </td>
</tr>
</table>

</center>

</body>
</html> 