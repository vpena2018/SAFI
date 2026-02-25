<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once ('include/framework.php');

get_porcentajes_sistema();
$forzar_cambio_clave = (isset($_SESSION['force_pwd_change']) && intval($_SESSION['force_pwd_change'])===1) ? 1 : 0;


?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,, maximum-scale=1.0, user-scalable=0 shrink-to-fit=no">
    <meta name="description" content="Online System">
    <meta name="author" content="">
    <meta name="robots" content="none" />
    <title><?php echo app_empresa;?></title>
 
       
    <link rel="icon" href="img/favicon.ico">
    
    <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    <link href="plugins/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="plugins/custom-scrollbar/jquery.mCustomScrollbar.min.css" rel="stylesheet">
    <link href="plugins/select2/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="plugins/datatables/datatables.min.css"/>

    <link href="plugins/sidebar/main.css" rel="stylesheet">

    <link href="plugins/datepicker/datepicker.css" rel="stylesheet">
    <link href="plugins/printjs/print.min.css" rel="stylesheet">
    <link href="plugins/fileupload/jquery.fileupload.css" rel="stylesheet">
    <link href="css/app2.css" rel="stylesheet">

    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

  </head>
  <body class="d-flex flex-column h-100" onbeforeunload="return salida();">
 
       <div class="page-wrapper chiller-theme  toggled ">


        <nav id="sidebar" class="sidebar-wrapper d-print-none">
            <div class="sidebar-content">

                <!-- sidebar-header  -->
                <div class="sidebar-item sidebar-header d-flex flex-nowrap "  style="margin-top: 2rem ;">
                    <div class="user-pic">
                        <img class="img-responsive rounded-circle" src="img/logo_2.png" alt="User picture">
                    </div>
                    <div class="user-info">
                        <span class="user-name"><strong><?php echo $_SESSION['usuario_nombre'] ?></strong>
                        </span>
                        <span class="user-role"><span id="menu_nombre_tienda"><?php echo $_SESSION['tienda_nombre'];
                       // $_SESSION['grupo_nombre']
                         ?></span>
                         <a href="#" onclick="cambiar_tienda(<?php echo  $_SESSION['tienda_id']; ?>); return false;"><i class="fa fa-edit"></i></a>
                        </span>
                        <span  class="user-status">
                
                            <i class="fa fa-times-circle cl-offline oculto"></i>
                            <span id="estadoonline"><i class="fa fa-circle cl-online"></i> Conectado  </span> 
                            <span id="estadooffline" class="oculto"><i class="fa fa-times-circle cl-offline"></i> Desconectado</span>
                            <small> &nbsp; v. <?php echo app_version ?></small> 
                        </span>
                    </div>
                </div>
                <!-- sidebar-search  -->
            <!--     <div class="sidebar-item sidebar-search">
                    <div>
                        <div class="input-group">
                            <input type="text" class="form-control search-menu" placeholder="Search...">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!-- sidebar-menu  -->
                <?php if ($forzar_cambio_clave!==1) { ?>
                <div class=" sidebar-item sidebar-menu">
                    <ul>
                        <?php  
                        // Generar Menu
                        $result = sql_select("SELECT  usuario_nivel.id ,usuario_nivel.nombre,usuario_nivel.icono,usuario_nivel.programa,usuario_nivel.etiqueta
                                            ,usuario_nivel.abrir_en
                                            ,usuario_nivel.nivel_padre_id
                                            ,usuario_nivel.nivel_categoria_id
                                            FROM usuario_nivelxgrupo 
                                            LEFT OUTER JOIN usuario_nivel ON (usuario_nivelxgrupo.nivel_id=usuario_nivel.id)
                                            where usuario_nivelxgrupo.grupo_id=".$_SESSION['grupo_id']." 
                                            AND usuario_nivel.activo=1
                                            AND usuario_nivel.nivel_categoria_id<=2

                                            order by usuario_nivel.nivel_padre_id,usuario_nivel.nivel_categoria_id,  usuario_nivel.orden");

                        if ($result!=false){
                            if ($result -> num_rows > 0) { 
                                    $current_menu=""; 
                                    $current_menu_item="";
                                    $current_nivel_padre_id=(-1);
                                   
                                    $n_mnu_item=0;
                                    while ($row = $result -> fetch_assoc()) {
                                      


                                      if ($row['nivel_padre_id']!=$current_nivel_padre_id ) {
                                          if ( $current_nivel_padre_id!=(-1)) {
                                           if ($n_mnu_item>0) {
                                           echo '<li class="sidebar-dropdown">'; 
                                           echo $current_menu; 
                                           echo '<div class="sidebar-submenu"><ul>';
                                           echo $current_menu_item;
                                           echo '</ul></div></li>';

                                          } else {
                                             echo '<li>'; 
                                             echo $current_menu;
                                             echo '</li>'; 

                                          }
                                      }
                                          $current_nivel_padre_id=$row['nivel_padre_id'];
                                          $current_menu=""; 
                                          $current_menu_item="";
                                          $n_mnu_item=0;
                                       } 
                                        
                                        $mnu_icono="";
                                        $mnu_etiqueta="";
                                        $mnu_programa="";

                                        if (!es_nulo($row['icono'])) { $mnu_icono='<i class="fa fa-'.$row['icono'].'"></i>';}
                                        if (!es_nulo($row['etiqueta'])) { $mnu_etiqueta='<span class="badge badge-pill rounded badge-primary">'.$row['etiqueta'].'</span>';}
                                        if (!es_nulo($row['programa'])) { $mnu_programa="get_page('".$row['abrir_en']."','".$row['programa']."','".$row['nombre']."') ; ";}

                                        // Main Menu
                                      if ($row['nivel_categoria_id']==1) {

                                         $current_menu.= '<a href="#" onclick="'.$mnu_programa.' return false;">'.$mnu_icono.' <span class="menu-text">'.$row['nombre'].'</span>'.$mnu_etiqueta.'</a>';

                                      }

                                      //Menu Item
                                      if ($row['nivel_categoria_id']==2) {
                                             $current_menu_item.='<li>';
                                             $current_menu_item.= '<a href="#" onclick="'.$mnu_programa.' return false;">'.$mnu_icono.' <span class="menu-text">'.$row['nombre'].'</span>'.$mnu_etiqueta.'</a>';
                                             $current_menu_item.='</li>';
                                             $n_mnu_item++;
                                      }


                                    }  //while  

                                    
                                          if ($n_mnu_item>0) {
                                           echo '<li class="sidebar-dropdown">'; 
                                           echo $current_menu; 
                                           echo '<div class="sidebar-submenu"><ul>';
                                           echo $current_menu_item;
                                           echo '</ul></div></li>';

                                          } else {
                                             echo '<li>'; 
                                             echo $current_menu;
                                             echo '</li>'; 

                                          }
                             
                            }
                        }
               
                        ?>


                   <!--      <li class="header-menu">
                            <span>Extra</span>
                        </li> -->

                        
                        
                    </ul>
                </div>
                <!-- sidebar-menu  -->
                <?php } ?>
            </div>


            <!-- sidebar-footer  -->
            <div class="sidebar-footer">

               <!--  <div class="dropdown">

                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        <span class="badge badge-pill badge-warning notification">3</span>
                    </a>
                    <div class="dropdown-menu notifications" aria-labelledby="dropdownMenuMessage">
                        <div class="notifications-header">
                            <i class="fa fa-bell"></i>
                            Notifications
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="notification-content">
                                <div class="icon">
                                    <i class="fas fa-check text-success border border-info"></i>
                                </div>
                                <div class="content">
                                    <div class="notification-detail">...</div>
                                    <div class="notification-time">
                                        6 minutes ago
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="notification-content">
                                <div class="icon">
                                    <i class="fas fa-exclamation text-info border border-info"></i>
                                </div>
                                <div class="content">
                                    <div class="notification-detail">...</div>
                                    <div class="notification-time">
                                        Today
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="notification-content">
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle text-info border border-info"></i>
                                </div>
                                <div class="content">
                                    <div class="notification-detail">.....</div>
                                    <div class="notification-time">
                                        Yesterday
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">View all notifications</a>
                    </div>
                </div> -->



                <?php if ($forzar_cambio_clave!==1) { ?>
                <div class="dropdown">
               
                    <a id="pin-sidebar" href="#"  >
                        <i class="fa fa-thumbtack"></i>
                        <span id="pin-sidebar-span" class=""></span>                        
                    </a>
                    
                </div>



                <div class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cog"></i>
                    </a>

                    <div class="dropdown-menu notifications" aria-labelledby="dropdownMenuMessage">
                        <div class="notifications-header bg-color-footer">
                            
                            <i class="fa fa-cog"></i> Mi Configuración
                        </div>
                        <div class="dropdown-divider"></div>


  
                        <a class="dropdown-item" href="#" onclick="get_page('pagina','mnt_perfil.php','Mi perfil') ; return false;">
                            <div class="notification-content">
                                <div class="icon">
                                    <i class="fas fa-user text-secondary border "></i>
                                </div>
                           
                                     <div class="">Mi perfil</div> 
 
                            </div>
                        </a>

                        
                   
                    </div>
                </div>
                <?php } ?>


                <div>
                    <a href="#" onclick="logout(); return false;">
                        <i class="fa fa-power-off"></i>
                    </a>
                </div>
                <div class="pinned-footer">
                    <a href="#">
                        <i class="fas fa-ellipsis-h"></i>
                    </a>
                </div>
            </div>
        </nav>










    <!-- Top Main Toolbar  -->
    <nav id="topmainbar" class="navbar  navbar-dark fixed-top bg-color-bar">
      <a class="navbar-brand ml-3" href="main.php" onclick="return false;"><?php echo app_empresa;?></a>
      <button id="toggle-sidebar" class="navbar-toggler" type="button" >
        <span class="navbar-toggler-icon"></span>
      </button>

    </nav>








        <!-- page-content  -->
        <main class="page-content " style="margin-top: 3.2rem ;">
            <div id="overlay" class="overlay"></div>
            <div class="container-fluid ">


                <!-- Page Header" -->
                <div class="row ">
                    <div class="col-8">
                        <h3 id="pagina-titulo" class=""></h3>
                                     
                    </div>
                    <div class="col-4">
                        <div id="pagina-botones" class="text-right"></div>
                                     
                    </div>
                </div>

                
 
                 <div id="pagina_externa">
                 
               

                               
                  </div>

                <!-- Page Body" -->
                <div class="card shadow-sm mb-2"> 
                  <div id="pagina">
            
                  </div>
                  <div id="subpagina">
            
                  </div>

                </div>

               
   <!-- Modal -->
<div class="modal fade" id="ModalWindow"  tabindex="-1" role="dialog" aria-labelledby="ModalWindow" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalWindowTitle"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="ModalWindowBody" class="modal-body">
       
      </div>

    </div>
  </div>
</div>         



 <!-- Modal 2 -->
 <div class="modal fade" id="ModalWindow2" data-keyboard="false"  data-backdrop="static"  tabindex="-1" role="dialog" aria-labelledby="ModalWindow2" aria-hidden="true">
  <div class="modal-dialog modal-xl2" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalWindow2Title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="ModalWindow2Body" class="modal-body">
       
      </div>

    </div>
  </div>
</div>        



  
                <!-- Page Footer" -->
                <p>&nbsp;</p>
                <hr>
                 <div class="row">
                    
                    <div class="col-10 text-left">
                        <p class="text-muted ">&copy; 2021 - <?php echo app_title." <small>v. ".app_version."</small>";?></p>
                    </div>
                    <div class="col-2 text-right">
                         <a href="#" onclick="$(window).scrollTop(0); return false;" class="d-print-none text-secondary shadow-sm "><i class="fa fa-lg fa-chevron-circle-up" aria-hidden="true"></i></a>                
                    </div>
                </div>



            </div> <!-- page-container" -->

        </main> <!-- page-content" -->
        
    </div> <!-- page-wrapper -->
    



<script type="text/javascript" src="plugins/jquery/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="plugins/sweetalert2/sweetalert2.min.js"></script>
<script type="text/javascript" src="plugins/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="plugins/sidebar/main.js"></script>
<script type="text/javascript" src="plugins/select2/select2.min.js"></script> 
<script type="text/javascript" src="plugins/select2/i18n/es.js"></script> 
<script type="text/javascript" src="plugins/datatables/datatables.min.js"></script>
<script type="text/javascript" src="plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="plugins/printjs/print.min.js"></script>
<script type="text/javascript" src='plugins/fabricjs/fabric.min.js'></script>
<script type="text/javascript" src="plugins/fileupload/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="plugins/fileupload/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="plugins/fileupload/jquery.fileupload.js"></script>


<script type="text/javascript" src="js/app5.js"></script>

<script src="plugins/chart/Chart.min.js"></script>




<script type="text/javascript">

    let d_taller = [];
    let d_tienda = [];
    let d_horas = [];

    d_tienda = [
            <?php echo get_array_tiendas(); ?>
        ];

    d_taller = [
            <?php echo get_array_talleres(); ?>
        ];

    d_horas = [
            <?php echo get_array_horario(); ?>
	];
        
    $(document).ready(function() {
        
        $.ajaxSetup({
            cache: false
        });

       <?php
        $dashboard_pagina='dashboard.php';
        if (!tiene_permiso(1) and tiene_permiso(0)) {
            $dashboard_pagina='dashboard_seguimiento.php';
        }
        ?>
        <?php if ($forzar_cambio_clave===1) { ?>
            get_page('pagina','mnt_password_forzado.php','Cambio de Clave Obligatorio') ;
            Swal.fire({
                icon: 'warning',
                title: 'Cambio de clave requerido',
                text: 'Por seguridad, debe cambiar su clave antes de continuar.',
                confirmButtonText: 'Entendido'
            });
        <?php } else { ?>
            get_page('pagina','<?php echo $dashboard_pagina; ?>','Dashboard') ;
        <?php } ?>
        
        if (typeof(Storage) !== "undefined") {

                var tt = localStorage.getItem('gd_tt');
                var ts = <?php echo $_SESSION['tienda_id']; ?> ;
                if(tt != null)  {
                    if (tt!=ts) {
                        cambiar_tienda(tt); 
                    }                     

                } else { 
                   // cambiar_tienda(ts);
                   localStorage.setItem( 'gd_tt', ts );    
                        }					
			} else {
				
				// dispositivo no es compatible
			}

    });


    function cambiar_tienda(tienda){
        modalwindow('Seleccione la Tienda','cambiar_tienda.php?a=v&tid='+tienda) ;
    }

    function salida(){
        return true;
    }

</script>
    

</body>
</html>
