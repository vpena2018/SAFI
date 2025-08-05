function enviar_solicitud(){

	
	var vmsg="";
	if ($.trim($('#correo').val())=="") {vmsg=' '+"Ingrese el correo electronico"+'. ';  }
	if ($.trim($('#telefono').val())=="") {vmsg=' '+"Ingrese el Numero de telefono"+'. ';  }
	if ($.trim($('#identidad').val())=="") {vmsg=' '+"Ingrese el numero de identidad del contacto"+'. ';  }
	if ($.trim($('#nombre').val())=="") {vmsg=' '+"Ingrese el Nombre del Contacto"+'. ';  }
	if ($.trim($('#kilometraje').val())=="") {vmsg=' '+"Ingrese el kilometraje actual del Vehiculo"+'. ';  }
	if ($.trim($('#tipo').val())=="") {vmsg="Ingrese el tipo de Revision"+'. ';  }
	if ($.trim($('#hora').val())=="") {vmsg=' '+"Seleccione la hora"+'. ';  }
	if ($.trim($('#fecha').val())=="") {vmsg=' '+"Seleccione la fecha"+'. ';  }
	if ($.trim($('#taller').val())=="") {vmsg=' '+"Seleccione el taller"+'. ';  }
	if ($.trim($('#sucursal').val())=="") {vmsg=' '+"Seleccione la sucursal"+'. ';  }
	
	if ($.trim($('#num_inv').val())=="" && $.trim($('#placa').val())=="" && $.trim($('#chasis').val())=="") {vmsg=' '+"Ingrese el numero de inventario, Placa o Vin del Vehiculo"+'. ';  }
	


	if (vmsg=="") {
		
		
	$("#form-btn").addClass("disabled");
	cargando(true);

	var datos=$("#formsol").serialize();

	 $.post( "index.php?a=sol",datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				 $("#form-btn").removeClass("disabled");
				 cargando(false);
				 mytoast('error',json[0].pmsg,6000) ;
			}
			if (json[0].pcode == 1) {
				$("#mensaje-cuerpo").html('<p>&nbsp;</p><p>&nbsp;</p>'+json[0].pmsg+'<p>&nbsp;</p><p>&nbsp;</p>');
		    	$("#form-cuerpo").hide();
				
					cargando(false);

			}
		} else {
		 $("#form-btn").removeClass("disabled");
		 cargando(false);
		
		mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
	}
		  
	})
	  .done(function() {
	   	$("#form-btn").removeClass("disabled");
		  // cargando(false);
	  })
	  .fail(function(xhr, status, error) {
	  	 $("#form-btn").removeClass("disabled");
		   cargando(false);
	 	
		   mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
		})
	  .always(function() {
	    
	  });

}  else {
		 $("#form-btn").removeClass("disabled");
		 cargando(false);
		 mytoast('error',vmsg,6000) ;
		 
}

}
 

function mymodal(icono,titulo,mensaje) {
	Swal.fire({
	  icon: icono,
	  title: titulo,
	  text: mensaje
	})

}

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

	function cargando(mostrar){
		if (mostrar==true) {
			Swal.fire({
					title: 'Procesando...',
					html: 'Espere!',
					allowOutsideClick: false,
					onBeforeOpen: () => {
						Swal.showLoading()
					},
				});
		} else {
			swal.close();
		}
	}

function cargar_sucursales(){
	var $sucursales = $('#sucursal').empty();
	$sucursales.append('<option value = "">Seleccione...</option>');
	for (i in d_tienda) {
	  $sucursales.append('<option value = "' + d_tienda[i][0] + '">' + d_tienda[i][1] + '</option>');
	}
}

function padWithZero(num, targetLength) {
	return String(num).padStart(targetLength, '0');
  }

  function daysInMonth (month, year) {
    return new Date(year, month, 0).getDate();
}