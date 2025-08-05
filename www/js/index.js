
function recover_show(){
	$('#email').val('');
	$('#login1').hide();
	$('#login2').show();
	$('#email').focus();
}

function login_show(){
	$('#user').val('');
	$('#password').val('');
	$('#login2').hide();
	$('#login1').show();
	$('#user').focus();
}




function login(){

	

	var vuser=$.trim($('#user').val());
	var vpass=$.trim($('#password').val());
	var vmsg="";
	
	
	
	if (vuser=="") {vmsg=vmsg+"Ingrese su usuario"+'. ';  }
	if (vpass=="") {vmsg=vmsg+' '+"Ingrese su contraseña"+'. ';  }
	
	if (vmsg=="") {
		
		
	$("#login-btn").addClass("disabled");

	data={ a: "201", u: vuser, p: vpass };

	 $.post( "index.php",data, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				// $('#user').val('');
				 $('#password').val('');
				 $("#login-btn").removeClass("disabled");
				 mymodal('error','Oops...',json[0].pmsg); 
			}
			if (json[0].pcode == 1) {

					window.location='main.php';

			}
		} else {
		 $("#login-btn").removeClass("disabled");
		mymodal('error','Oops...',"Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar");
		}
		  
	})
	  .done(function() {
	   	$("#login-btn").removeClass("disabled");
	  })
	  .fail(function(xhr, status, error) {
	  	 $("#login-btn").removeClass("disabled");
	 	 mymodal('error','Oops...',"Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar");
	  })
	  .always(function() {
	    
	  });

}  else {
		 $("#login-btn").removeClass("disabled");
		 mymodal('error','Oops...',vmsg);
		 
}

}



function recover(){

	var vemail=$.trim($('#email').val());
	var vmsg="";
	
	
	
	if (vemail=="") {vmsg=vmsg+"Ingrese su email"+'. ';  }
	
	if (vmsg=="") {
		
	$("#email-btn").addClass("disabled");	
	//$.mobile.loading('show');	

	data={ a: "301", m: vemail};

	 $.post( "index.php",data, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {

				 mymodal('error','Oops...',json[0].pmsg); 
				 $("#email-btn").removeClass("disabled");
			}
			if (json[0].pcode == 1) {

					mymodal('success','',json[0].pmsg);

			}
		} else { 
			$("#email-btn").removeClass("disabled");
		 mymodal('error','Oops...',"Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar");}
		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
		  	
		 $("#email-btn").removeClass("disabled");
   
	 	 mymodal('error','Oops...',"Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar");
	  })
	  .always(function() {
 
	  });

}  else {
	
		 mymodal('error','Oops...',vmsg);
		 
}

}

 

function mymodal(icono,titulo,mensaje) {
	Swal.fire({
	  icon: icono,
	  title: titulo,
	  text: mensaje
	})

}

// success , error , warning , info , question;  timer 0=dont auto dismiss
function mytoast(icono,titulo,timer) {
  
if (timer>0) { 
  const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: timer,
  timerProgressBar: false,
  onOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
  })

  Toast.fire({
  icon: icono,
  title: titulo
	})

} else {

  const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false

  })	

  Toast.fire({
  icon: icono,
  title: titulo
	})
}



}