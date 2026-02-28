
var mnu_opening=false;

function _safi_parse_json(value){
	if (!value || value === '' || value === '[object Object]') { return null; }
	try { return JSON.parse(value); } catch(e) { return null; }
}

function _safi_insp_init_canvases() {
	if (typeof window.fabric === 'undefined') { return; }
	if (!document.getElementById('c') || !document.getElementById('firma1') || !document.getElementById('firma2')) { return; }

	if (typeof window.cv_cargar !== 'function') {
		window.cv_cargar = function(thecanvas, json) {
			var parsed = (typeof json === 'string') ? _safi_parse_json(json) : json;
			if (!parsed) { return; }
			thecanvas.loadFromJSON(parsed, function() {
				thecanvas.selection = false;
				thecanvas.forEachObject(function(o) {
					o.selectable = false;
					o.evented = false;
					o.hoverCursor = 'default';
				});
				thecanvas.renderAll();
			});
		};
	}

	if (!window.canvas || !window.canvas.lowerCanvasEl || window.canvas.lowerCanvasEl.id !== 'c') {
		window.canvas = new fabric.Canvas('c');
	}
	if (!window.canvas_firma1 || !window.canvas_firma1.lowerCanvasEl || window.canvas_firma1.lowerCanvasEl.id !== 'firma1') {
		window.canvas_firma1 = new fabric.Canvas('firma1');
	}
	if (!window.canvas_firma2 || !window.canvas_firma2.lowerCanvasEl || window.canvas_firma2.lowerCanvasEl.id !== 'firma2') {
		window.canvas_firma2 = new fabric.Canvas('firma2');
	}

	window.canvas_firma1.isDrawingMode = true;
	window.canvas_firma2.isDrawingMode = true;
	if (window.canvas_firma1.freeDrawingBrush) { window.canvas_firma1.freeDrawingBrush.width = 2; }
	if (window.canvas_firma2.freeDrawingBrush) { window.canvas_firma2.freeDrawingBrush.width = 2; }

	var plantilla = $('#plantilla_vehiculo').val() || 'turismo';
	var bg = 'img/hoja_inspeccion/' + plantilla + '.jpg';
	window.canvas.setBackgroundImage(bg, window.canvas.renderAll.bind(window.canvas), { originX: 'left', originY: 'top' });

	var d = $('#detalles_canvas').val();
	if (d && d !== '' && d !== '[object Object]') { window.cv_cargar(window.canvas, d); }
	var f1 = $('#firma1_canvas').val();
	if (f1 && f1 !== '') { window.cv_cargar(window.canvas_firma1, f1); }
	var f2 = $('#firma2_canvas').val();
	if (f2 && f2 !== '') { window.cv_cargar(window.canvas_firma2, f2); }
}

// Fallback global para ambientes donde el script inline del modulo no se ejecuta.
if (typeof window.insp_cambiartab !== 'function') {
	window.insp_cambiartab = function(eltab) {
		var codigo = $('#id').val();
		var continuar = true;
		$('.tab-pane').hide();

		if (eltab !== 'nav_detalle') {
			if (codigo === "0" || codigo === "" || codigo === undefined) {
				continuar = false;
				if ($('#nav_deshabilitado').length) {
					$('#nav_deshabilitado').show();
					$('#nav_deshabilitado').tab('show');
				}
			}
		}

		if (eltab === 'nav_fotos' && typeof window.procesar_inspeccion_foto === 'function') {
			window.procesar_inspeccion_foto('nav_fotos');
		}
		if (eltab === 'nav_fotos' && typeof window.procesar_inspeccion_foto !== 'function') {
			var pidf = ($('#id_producto').val() || $('#pid').val() || 0);
			var urlf = 'inspeccion_fotos.php?cid=' + (codigo || 0) + '&pid=' + (pidf || 0);
			$('#nav_fotos').html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span>Cargando</span></div>');
			$('#nav_fotos').load(urlf, function(response, status, xhr) {
				if (status == "error") {
					$('#nav_fotos').html('<p>&nbsp;</p>');
					mytoast('error','Error al cargar la pagina...',6000);
				}
			});
		}
		if (eltab === 'nav_historial' && typeof window.procesar_inspeccion_historial === 'function') {
			window.procesar_inspeccion_historial('nav_historial');
		}
		if (eltab === 'nav_historial' && typeof window.procesar_inspeccion_historial !== 'function') {
			var pidh = ($('#id_producto').val() || $('#pid').val() || 0);
			var urlh = 'inspeccion_historial.php?cid=' + (codigo || 0) + '&pid=' + (pidh || 0);
			$('#nav_historial').html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span>Cargando</span></div>');
			$('#nav_historial').load(urlh, function(response, status, xhr) {
				if (status == "error") {
					$('#nav_historial').html('<p>&nbsp;</p>');
					mytoast('error','Error al cargar la pagina...',6000);
				}
			});
		}

		if (continuar === true) {
			$('#'+eltab).show();
			$('#'+eltab).tab('show');
		}
	};
}

if (typeof window.insp_limpiar_firma !== 'function') {
	window.insp_limpiar_firma = function(objeto) {
		try {
			if (parseInt(objeto, 10) === 1 && window.canvas_firma1 && typeof window.canvas_firma1.clear === 'function') {
				window.canvas_firma1.clear();
			}
			if (parseInt(objeto, 10) === 2 && window.canvas_firma2 && typeof window.canvas_firma2.clear === 'function') {
				window.canvas_firma2.clear();
			}
		} catch (e) {}
	};
}

if (typeof window.insp_canvas_zoom !== 'function') {
	window.insp_canvas_zoom = function(inout, valor) {
		if (!window.canvas || typeof window.canvas.setZoom !== 'function') { return; }
		if (inout === 'OUT') {
			window.canvas.setZoom(1);
			return;
		}
		var z = parseFloat(valor || 2);
		if (isNaN(z) || z <= 0) { z = 1; }
		window.canvas.setZoom(z);
	};
}

if (typeof window.procesar_inspeccion !== 'function') {
	window.procesar_inspeccion = function(url, forma, adicional) {
		_safi_insp_init_canvases();
		if (window.canvas && $('#detalles_canvas').length) {
			window.canvas.includeDefaultValues = false;
			$('#detalles_canvas').val(JSON.stringify(window.canvas.toJSON()));
		}
		if (window.canvas_firma1 && $('#firma1_canvas').length) {
			$('#firma1_canvas').val(JSON.stringify(window.canvas_firma1.toJSON()));
		}
		if (window.canvas_firma2 && $('#firma2_canvas').length) {
			$('#firma2_canvas').val(JSON.stringify(window.canvas_firma2.toJSON()));
		}
		procesar(url, forma, adicional);
	};
}

if (typeof window.inspeccion_generar_pdf !== 'function') {
	window.inspeccion_generar_pdf = function() {
		_safi_insp_init_canvases();
		if (!document.getElementById('pdfform')) { return; }
		if (window.canvas && $('#pdfimg1').length) { $('#pdfimg1').val(window.canvas.toDataURL('image/png')); }
		if (window.canvas_firma1 && $('#pdffirma1').length) { $('#pdffirma1').val(window.canvas_firma1.toDataURL('image/png')); }
		if (window.canvas_firma2 && $('#pdffirma2').length) { $('#pdffirma2').val(window.canvas_firma2.toDataURL('image/png')); }
		if ($('#pdfcod').length && $('#id').length) { $('#pdfcod').val($('#id').val()); }
		$('#pdfform').submit();
	};
}




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

		_safi_insp_init_canvases();

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
		$.post(contenido_url, datos, function(data) {
			$('#ModalWindow2Body').html(data);
			_safi_insp_init_canvases();
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
    const contenidoEstilizado =
        '<div class="card-body p-3" style="background: linear-gradient(135deg, #f9f9f9, #e0e0e0);' 
        + 'border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">'
        + '<span style="font-size: 18px; color: #333; font-weight: 600;">'
        + contenidoHtml + '</span>'
        + '</div><br>'
        + '<div style="text-align:center;">'
        + '   <button id="btnCerrarModal" type="button" class="btn btn-primary px-4">cerrar</button>'
        + '</div>';

    $('#ModalWindow2Title').html(titulo);
    $('#ModalWindow2Body').html(contenidoEstilizado);
    $('#ModalWindow2').modal('show');

    // Asignar evento al botón despuÃ©s de inyectarlo en el DOM
    $('#btnCerrarModal').on('click', function () {
		$('#ModalWindow').modal('hide');
        $('#ModalWindow2').modal('hide');
    });
}


function popupconfirmar(titulo, mensaje, onSi) {

    if (document.getElementById('popupSimple')) return;

    const overlay = document.createElement('div');
    overlay.id = 'popupSimple';
    overlay.style = `
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.45);
        display:flex;
        align-items:center;
        justify-content:center;
        z-index:9999;
    `;

    overlay.innerHTML = `
        <div style="
            background:#fff;
            border-radius:12px;
            padding:20px;
            width:340px;
            box-shadow:0 6px 18px rgba(0,0,0,.3);
            font-family:Arial, sans-serif;
        ">
            <div style="
                font-weight:bold;
                font-size:16px;
                margin-bottom:10px;
            ">
                ${titulo}
            </div>

            <div style="
                font-size:14px;
                margin-bottom:18px;
                color:#333;
            ">
                ${mensaje}
            </div>

            <div style="text-align:right;">

                <button id="btnSiSimple" style="
                    background:#0d6efd;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    padding:7px 16px;
                    cursor:pointer;
                    font-weight:bold;
                ">SÃ­</button>

				<button id="btnNoSimple" style="
                    background:#6c757d;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    padding:7px 14px;
                    cursor:pointer;
                    margin-right:8px;
                ">No</button>


            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    document.getElementById('btnNoSimple').onclick = () => overlay.remove();

    document.getElementById('btnSiSimple').onclick = () => {
        overlay.remove();
        if (typeof onSi === 'function') onSi();
    };
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

function procesarAsync(url, forma, adicional) {
    return new Promise((resolve) => {

        const flagFormaVentas = forma === 'forma_ventas';

        $("#" + forma + " .xfrm").addClass("disabled");
        cargando(true);

        const datos = $("#" + forma).serialize();

        $.post(url, datos)
            .done(function (json) {

                // ðŸ”¹ Respuesta vÃ¡lida
                if (Array.isArray(json) && json.length > 0) {

                    // âŒ Error de negocio
                    if (json[0].pcode == 0) {
                        resolve({
                            ok: false,
                            msg: json[0].pmsg
                        });
                        return;
                    }

                    // âœ… Ã‰xito
                    if (json[0].pcode == 1) {
                        $("#" + forma + ' #id').val(json[0].pcid);

                        resolve({
                            ok: true,
                            msg: json[0].pmsg
                        });
                        return;
                    }
                }

                // âŒ Respuesta invÃ¡lida
                resolve({
                    ok: false,
                    msg: 'Respuesta invÃ¡lida del servidor'
                });
            })
            .fail(function () {
                resolve({
                    ok: false,
                    msg: 'Error de comunicación con el servidor'
                });
            })
            .always(function () {
                cargando(false);
                $("#" + forma + " .xfrm").removeClass("disabled");
            });
    });
}


function procesar(url,forma,adicional){
	
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
			//procesar_tabla_datatable('tablaver','tabla','ventas_vehiculos_ver.php?a=1','Ventas de Vehiculos')
			//$('#ModalWindow2').modal('hide');
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
	  

