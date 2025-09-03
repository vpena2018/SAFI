
var mnu_opening=false;




function system_online(activo){

	if (activo==true) {
		$('#estadooffline').hide();
		$('#estadoonline').show();
		
		
	} else {
		$('#estadoonline').hide();
		$('#estadooffline').show();
	}

	
}

function imprimir(){
	window.print();
}

function get_box(campo,url) {
	$("#"+campo).html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span class="">'+'Cargando'+'</span></div>');			
	$("#"+campo).load(url, function(response, status, xhr) {
		
		if (xhr.status == 0) {	}

		if (status == "error") { 

			//$("#"+campo).html("Error"; // xhr.status + " " + xhr.statusText
			$("#"+campo).html('');
			mytoast('error','Error al cargar...',6000) ;
		}

	});

}

function get_page(campo,url,titulo,limpiar_subpagina=true) {

	if (campo=="subpagina") {
		$("#pagina").html("");
		$("#subpagina").show();
	} else {
		if (limpiar_subpagina==true) {
			$("#subpagina").html("");
		}
	}
	
$('#pagina_externa').html('');	

if (mnu_opening==false) {
	mnu_opening=true;
	if ($('.page-wrapper').hasClass('pinned')) {
	    $('.page-wrapper').removeClass('sidebar-hovered');
	 }

	if ($(window).width() < 768) {
        $('.page-wrapper').removeClass('toggled');
    }


    $('#pagina-titulo').html( titulo);
    $('#pagina-botones').html('');	

	$(window).scrollTop(0);
	//$("#"+campo).html(' <div align="center" valign="middle"><br><p><img src="img/load.gif"/></p>'+ml('Cargando','Loading')+'</div>');			
	$("#"+campo).html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span class="">'+'Cargando'+'</span></div>');			
	

	$("#"+campo).load(url, function(response, status, xhr) {
		mnu_opening=false;
			
		 
		system_online(true);
		if (xhr.status == 0) {	system_online(false);	}

		if (status == "error") { 

			//$("#"+campo).html("Error"; // xhr.status + " " + xhr.statusText
			$("#"+campo).html('<p>&nbsp;</p>');
			mytoast('error','Error al cargar la pagina...',6000) ;
		}

	});


	
 } else {
	mytoast('warning','Abriendo pagina, espere...',3000) ;
 }



}




function get_page_switch(campo,url,titulo) {	
		
	get_page(campo,url,titulo,false) ;
	$("#subpagina").hide();
	
}
function get_page_regresar(campo,url,titulo) {
	if ($("#subpagina").html()=="") {
		get_page('subpagina',url,titulo,false) ;
	}

	$("#pagina").html("");
	$("#subpagina").show();	
}





function modalwindow(titulo,contenido_url) {
$('#ModalWindowTitle').html(titulo);
    $('#ModalWindowBody').html('<div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>');
    // <button type="button" class="btn btn-secondary xfrm" data-dismiss="modal">Close</button>        
    // <button type="button" class="btn btn-primary xfrm">Guardar</button>

 
    $.post( contenido_url, function(data) {

				$('#ModalWindowBody').html(data); 
 		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
	   		$('#ModalWindowBody').html("Se produjo un error. Favor vuelva a intentar"+'<br><br><button type="button" class="btn btn-secondary" data-dismiss="modal">'+'Cerrar'+'</button>'); 
	  })
	  .always(function() {

	  });

    $('#ModalWindow').modal('show');

}

function modalwindow2(titulo,contenido_url,datos=null) {
	$('#ModalWindow2Title').html(titulo);
		$('#ModalWindow2Body').html('<div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>');
		// <button type="button" class="btn btn-secondary xfrm" data-dismiss="modal">Close</button>        
		// <button type="button" class="btn btn-primary xfrm">Guardar</button>
	
	 
		$.post( contenido_url,datos, function(data) {
	
					$('#ModalWindow2Body').html(data); 
			   
		})
		  .done(function() {
		   
		  })
		  .fail(function(xhr, status, error) {
				   $('#ModalWindow2Body').html("Se produjo un error. Favor vuelva a intentar"+'<br><br><button type="button" class="btn btn-secondary" data-dismiss="modal">'+'Cerrar'+'</button>'); 
		  })
		  .always(function() {
	
		  });
	
		$('#ModalWindow2').modal('show');
	
	}

function popupWeb(titulo, contenidoHtml) {

	const contenidoEstilizado='<div class="card-body p-3" style="background: linear-gradient(135deg, #f9f9f9, #e0e0e0);' 
                    +'border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">'
              +'<span style="font-size: 18px; color: #333; font-weight: 600;">'
                  +contenidoHtml+'</span>'
          +'</div></br>';								

    $('#ModalWindow2Title').html(titulo);
    $('#ModalWindow2Body').html(contenidoEstilizado);
    $('#ModalWindow2').modal('show');
}

function MostrarNotafinal(checkbox,accion) {


	if(accion=='realiza' && checkbox.alt=='ATM-01044')
	{
		if (checkbox.checked) {
			$('#notaFinal').show();
		} else {
			$('#notaFinal').val('');
			$('#notaFinal').hide(); 
		}
	}
}




// success , error , warning , info , question
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



function logout(){

	Swal.fire({
	  title: 'Salir',
	  text:  'Desea cerrar sesion?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Salir',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	    window.location='index.php?a=logout';
	  }
	})


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


function procesar(url,forma,adicional){
	debugger;
	let flagFormaVentas=false;
	if(forma=='forma_ventas')
	{
		flagFormaVentas=true;
	}
	// var validator =$("#frmreclamo").validate({
	// 	rules: {			
	// 				descripcion: {
	// 					required: true
	// 				}
					
	// 			} 
	// 			});
	// validator.form();
	// if (validator.valid())
	 {

	$("#"+forma+" .xfrm").addClass("disabled");		
	
	cargando(true); 
	
		
	var datos=$("#"+forma).serialize();

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;

					$("#"+forma+' #id').val(json[0].pcid);
			
				//if (forma=='forma_wd') {$('#ModalWindow').modal('hide');}
			
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		  
	})
	  .done(function() {

		if(flagFormaVentas==true)
		{
			procesar_tabla_datatable('tablaver','tabla','ventas_vehiculos_ver.php?a=1','Ventas de Vehiculos')
			$('#ModalWindow2').modal('hide');
		}
	   
	  })
	  .fail(function(xhr, status, error) {
	   		cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {
	   
		$("#"+forma+" .xfrm").removeClass("disabled");	
	  });
		
		
	}
		
}

function procesar_get(url){

	cargando(true); 
		
	

	 $.get( url, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;

			
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
	   		cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {
	   
	  });
		
		
}


function getvalor(texto){
	var salida=0;
	var tmpval=parseFloat(texto);
	
		if (isNaN(tmpval)){salida=0;} else {salida=tmpval;}


	return salida;
}


function buscarfiltro(event,boton) {

     var key = event.which;
     if(key == 13)  
      {
        
        $("#"+boton).click(); 
      }
}


function limpiar_tabla(tabla){
	$("#"+tabla).data("page",0);
	$("#"+tabla + "  tbody").empty();
}

function procesar_tabla(tabla,forma="forma"){

	  $('#cargando').show();
     

	$("#cargando_mas").addClass("disabled");		
	
	
 
	var datos=$("#"+forma).serialize();

	var url = $("#"+tabla).data("url")+ '&pg='+ $("#"+tabla).data("page")  ;

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				  $('#cargando').hide();
				  if (json[0].pmas == 0) {$("#cargando_mas").hide();}
				mytoast('warning',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				  $('#cargando').hide();
				  if (json[0].pmas == 0) {$("#cargando_mas").hide();} else {$("#"+tabla).data("page",parseInt($("#"+tabla).data("page"))+1) ; $("#cargando_mas").show();}
				  $('#'+tabla+' tbody').append(json[0].pdata);
				
			}
		} else {  $('#cargando').hide();  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
	   		  $('#cargando').hide(); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {
	   
		$("#cargando_mas").removeClass("disabled");	
	  });
		
		

		
}



function procesar_tabla_datatable(tabladiv,tabla,url,titulo,forma="forma"){

	$('#cargando').show();
	
	var datos=$("#"+forma).serialize();

	
	$.post( url,datos, function(json) {
			   
	  if (json.length > 0) {
		  if (json[0].pcode == 0) {
				$('#cargando').hide();
		
			  mytoast('warning',json[0].pmsg,3000) ;   
		  }
		  if (json[0].pcode == 1) {
				$('#cargando').hide();           
				$('#'+tabladiv+'').html(json[0].pdata);
	
				var table=$('#'+tabla).dataTable(     	{
					destroy: true,
					"bAutoWidth": true,
					"bFilter": true,
					"bPaginate": true,
					//	"bSort": false,
					"aaSorting": [],
					//"bInfo": false,
					"bStateSave": false,		
					"responsive": false,   
					"pageLength": 10,
	
					"dom": '<"clear"> rtiplBf',		
					"processing": false,
					"serverSide": false,
		
					buttons: ['excelHtml5', 'csvHtml5',  
					{
						extend: 'print',
						text: 'Imprimir',
						title: titulo
					}
				],					
				   //	"bScrollCollapse": true,			
					"bJQueryUI": false,					
					 "language": { "url": "plugins/datatables/spanish.lang" }					
			});
			  
		  }
	  } else {  $('#cargando').hide();  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		
	})
	.done(function() {
	 
	})
	.fail(function(xhr, status, error) {
			   $('#cargando').hide(); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	})
	.always(function() {
		 
	});
	  
	
	  
	}





	function procesar_cambiar_tienda(url,forma,adicional){

		var validado=false;
		var forms = document.getElementsByClassName('needs-validation');
		var validation = Array.prototype.filter.call(forms, function(form) {
									 
									   
			  if (form.checkValidity() === false) {
					  mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
				  } else {validado=true;}
				  form.classList.add('was-validated');
				  
			  }); 
		
		if(validado==true)
		{
		
		  $("#"+forma+" .xfrm").addClass("disabled");		
		  
		  cargando(true); 
		  
		var datos=$("#"+forma).serialize();
		
		   $.post( url,datos, function(json) {
					   
			  if (json.length > 0) {
				  if (json[0].pcode == 0) {
					  cargando(false);
					  mytoast('error',json[0].pmsg,3000) ;   
				  }
				  if (json[0].pcode == 1) {
					  cargando(false);
					  
					  localStorage.setItem( 'gd_tt', json[0].ptid );
					//   localStorage.setItem( 'gd_ttn', json[0].ptienda );
					  $('#menu_nombre_tienda').html(json[0].ptienda);
					  mytoast('success',json[0].pmsg,3000) ;
					  $('#ModalWindow').modal('hide');
					  
					   
				  
				  }
			  } else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
				
		  })
			.done(function() {
			 
			})
			.fail(function(xhr, status, error) {
					 cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
			})
			.always(function() {
			 
			  $("#"+forma+" .xfrm").removeClass("disabled");	
			});
			  
			  
		  }
			  
		}


function popupwindow(url, title, w, h) {
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no,scrollbars=1, menubar=no,   copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	} 



function rf_fechas(rango,cdesde='rfdesde',chasta='rfhasta') {
        var hoy = new Date();
        
        //hoy
        var desde = hoy;
        var hasta = hoy;     

        switch (rango) {

            case 'semana':      
                var first = hoy.getDate() - hoy.getDay() +1; 
                var last = first + 6; 
                desde = new Date(hoy.setDate(first));
                hasta = new Date(hoy.setDate(last));

            break;

            case 'mes':
                ultmomes=new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();
                desde= new Date(hoy.getFullYear(),hoy.getMonth(),1);
                hasta= new Date(hoy.getFullYear(),hoy.getMonth(),ultmomes);
                
                
            break;

            case 'anio':
                desde= new Date(hoy.getFullYear(),0,1);
                hasta= new Date(hoy.getFullYear(),11,31);
            break;
        
        }

        $('#'+cdesde).val(fechaISOLocal(desde));
        $('#'+chasta).val(fechaISOLocal(hasta));
    }		

	function fechaISOLocal(d) {
		var z  = n =>  ('0' + n).slice(-2);
		var zz = n => ('00' + n).slice(-3);
		var off = d.getTimezoneOffset();
		var sign = off > 0? '-' : '+';
		off = Math.abs(off);
	  
		return d.getFullYear() + '-'
			   + z(d.getMonth()+1) + '-' +
			   z(d.getDate()) ; 
	  }
	  