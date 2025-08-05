<?php
require_once ('include/framework.php');


if (!tiene_permiso(1)) {
  echo '<div class="card-body">
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p ><div class="text-center mb-4 login-titulo">
  <img src="img/logo.png" alt="" class="   mb-2 mt-2"  width="200" >

 </div></p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  </div>
  ';
}


if (tiene_permiso(1)) {

  //vehículos en mantenimiento
//asignación de trabajo y actividades de mecánicos y lavadores



//Orden servicio
  $result_servicio = sql_select("SELECT 
count(*) AS total
  ,SUM(if(id_estado<=2,1,0)) AS pendiente
  ,SUM(if(id_estado=3,1,0)) AS aprobada
  ,SUM(if(id_estado=4,1,0)) AS enproceso
  ,SUM(if(id_estado=7,1,0)) AS paro
  ,SUM(if(id_estado=8 or id_estado=9 ,1,0)) AS externo
     FROM servicio
     WHERE id_tienda=".$_SESSION['tienda_id']." AND id_estado<20

  ");

if ($result_servicio!=false){
    if ($result_servicio -> num_rows > 0) { 
      $row_servicio = $result_servicio -> fetch_assoc(); 
     }
  }


  // actividades repuestos
  $result_repuestos = sql_select("SELECT 
  count(*) AS total
    ,SUM(if(servicio_detalle.producto_tipo=3,1,0)) AS actividad
    ,SUM(if(servicio_detalle.producto_tipo=2,1,0)) AS repuesto
  
       FROM servicio_detalle
       LEFT OUTER JOIN servicio ON (servicio_detalle.id_servicio=servicio.id) 
       WHERE servicio.id_tienda=".$_SESSION['tienda_id']." AND servicio.id_estado<>20
       and servicio_detalle.estado <2

  ");

if ($result_repuestos!=false){
    if ($result_repuestos -> num_rows > 0) { 
      $row_repuestos = $result_repuestos -> fetch_assoc(); 
     }
  }


    // actividades repuestos averia
    $result_repuestos_averia = sql_select("SELECT 
    count(*) AS total
      ,SUM(if(averia_detalle.producto_tipo=3,1,0)) AS actividad
      ,SUM(if(averia_detalle.producto_tipo=2,1,0)) AS repuesto
    
         FROM averia_detalle
         LEFT OUTER JOIN averia ON (averia_detalle.id_maestro=averia.id) 
         WHERE averia.id_tienda=".$_SESSION['tienda_id']." AND averia.id_estado<>20
         and averia_detalle.estado <2
  
    ");
  
  if ($result_repuestos_averia!=false){
      if ($result_repuestos_averia -> num_rows > 0) { 
        $row_repuestos_averia = $result_repuestos_averia -> fetch_assoc(); 
       }
    }

 


//$ordenes_servicio=get_dato_sql('servicio','count(*)',' where );







?>
<div class="card-body">

 





        
        
        
        
        

<!-- <h5 class="text-secondary">Vehiculos en Taller</h5> -->

        <div class="row">
          <div class="col-12 col-sm-4 col-lg">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calculator" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total de Vehiculos en Taller</span>
                <span class="info-box-number">
                  <?php echo formato_numero($row_servicio['total']); ?>
                  
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <div class="col-12 col-sm-4 col-lg">
            <div class="info-box">
              <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-check-double" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pendientes de Autorizar</span>
                <span class="info-box-number">
                  <?php echo formato_numero($row_servicio['pendiente']); ?>
                  
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->


          <div class="col-12 col-sm-4 col-lg">
            <div class="info-box">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-check-double" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Actividades y Repuestos Pendientes de Autorizar</span>
                <span class="info-box-number">
                  Servicio: <?php echo formato_numero($row_repuestos['total']); ?>  Avería: <?php echo formato_numero($row_repuestos_averia['total']); ?>
                  
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>

          <div class="row"> 
        <!-- /.row -->

          <div class="col-12 col-sm-6 col-lg">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-car" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Aprobado</span>
                <span class="info-box-number"><?php echo formato_numero($row_servicio['aprobada']); ?>
   
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-lg">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-car-alt" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">En Proceso</span>
                <span class="info-box-number"><?php echo formato_numero($row_servicio['enproceso']); ?>

                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-lg">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-car-crash" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">En Paro</span>
                <span class="info-box-number"><?php echo formato_numero($row_servicio['paro']); ?> </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

           <!-- /.col -->
           <div class="col-12 col-sm-6 col-lg">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-car-side" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Externo</span>
                <span class="info-box-number"><?php echo formato_numero($row_servicio['externo']); ?> </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->





 



  <div class="row mt-4">
  	 <div class="col-12">

 <div class="card">
              <div class="card-header">
                <h5 class="card-title">
                  <i class="fas fa-chart-line mr-1"></i>
                  Estadisticas Mensuales
                </h5>
            
      
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content p-0">
                  <!-- Morris chart - Sales -->
                  <div class="chart tab-pane active" id="revenue-chart"
                       style="position: relative; height: 300px;">
                      <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>                         
                   </div>
                  <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                    <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>                         
                  </div>  
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
</div>
</div>
 
 




</div>

<script type="text/javascript">


 



  var salesChartCanvas = document.getElementById('revenue-chart-canvas').getContext('2d');

  var salesChartData = {
    labels  : [
    <?php 

              //GRAFICO
            $meses=5; // maximo  meses

            $gaereo="";
            $gmaritimo="";
            $sqladd="";
            $sqlcampos="";
            
            for ($i = $meses; $i >= 1; $i--) 
            {
              echo  "'".date("F", strtotime( date( 'Y-m-01' )." -$i months"))."'" ; //Y-m%
              $mesactual=date("m", strtotime( date( 'Y-m-01' )." -$i months"));
              $sqlcampos.=" ,SUM(if( MONTH(fecha)=".$mesactual." ,1,0)) AS aereo_mes".($i+1)."
                            ,SUM(if( MONTH(fecha)=".$mesactual." ,1,0)) AS maritimo_mes".($i+1) ;
              if ($i >= 1) { echo ",";  } 
            }
            $i=1;
            echo  "'".date("F", strtotime( date( 'Y-m-01' )." "))."'" ; //Y-m%
            $mesactual=date("m", strtotime( date( 'Y-m-01' ).""));
            $sqlcampos.=" ,SUM(if( MONTH(fecha)=".$mesactual." ,1,0)) AS aereo_mes".$i."
                          ,SUM(if( MONTH(fecha)=".$mesactual." ,1,0)) AS maritimo_mes".$i ;

                         

            $sqladd=" and fecha>='".date("Y-m-d", strtotime( date( 'Y-m-01' )." -$meses months"))."'";

            $result_grafico = sql_select("SELECT 1 as campo
           
               $sqlcampos
              
              FROM servicio 
              WHERE  id_tienda=".$_SESSION['tienda_id']."   $sqladd
             
             "
            );

          $coma="";
          if ($result_grafico!=false){
                if ($result_grafico -> num_rows > 0) { 

                    $row_grafico = $result_grafico -> fetch_assoc() ;
                        
                         for ($i = ($meses+1); $i >= 1; $i--) 
                          {
                            $gaereo.= $coma.$row_grafico['aereo_mes'.$i] ;
                            $gmaritimo.= $coma.$row_grafico['maritimo_mes'.$i];
                            $coma=", "; 
                          }

                                     
                  

                }

            } 
     
      ?>
      ],
    datasets: [
      {
        label               : 'RENTA',
        backgroundColor     : 'rgba(60,141,188,0.9)',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#3b8bba',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [<?php echo $gaereo; ?>]
      }
    ]
  }

  var salesChartOptions = {
    maintainAspectRatio : false,
    responsive : true,
    legend: {
      display: false
    },
    scales: {
      xAxes: [{
        gridLines : {
          display : false,
        }
      }],
      yAxes: [{
        gridLines : {
          display : false,
        }
      }]
    }
  }

  // This will get the first returned node in the jQuery collection.
  var salesChart = new Chart(salesChartCanvas, { 
      type: 'line', 
      data: salesChartData, 
      options: salesChartOptions
    }
  )

  

</script>
<?php 
}
?>