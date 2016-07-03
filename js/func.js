// JavaScript Document
function notEmpty(){
	var myTextField = document.getElementById('username');
	var myTextField2 = document.getElementById('password');
	if( (myTextField.value == "") || (myTextField2.value == "") )
		alert("Username and Password cannot be blanks!")		
}

function notEmpty2(){
	var myTextField = document.getElementById('dusername');
	var myTextField2 = document.getElementById('dpassword');
	var myTextField3 = document.getElementById('dname');
	var myTextField4 = document.getElementById('dsurname');
	var myTextField5 = document.getElementById('demail');
	var myTextField6 = document.getElementById('dpassword2');
	
	if( (myTextField.value == "") || (myTextField2.value == "") || (myTextField3.value == "") || (myTextField4.value == "") || (myTextField5.value == "") )
		alert("User details cannot be blanks!")
		
}
