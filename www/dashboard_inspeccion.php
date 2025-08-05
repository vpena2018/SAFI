<?php
require_once ('include/framework.php');
pagina_permiso(1);


//vehículos en mantenimiento
//asignación de trabajo y actividades de mecánicos y lavadores


$result = sql_select("SELECT 
count(*) AS total
  ,SUM(if(tipo_inspeccion=1,1,0)) AS renta
  ,SUM(if(tipo_inspeccion=2,1,0)) AS taller
     FROM inspeccion
     WHERE id_tienda=".$_SESSION['tienda_id']." AND id_estado<>4

  and fecha>='".date('Y-m')."-01'

  ");

if ($result!=false){
    if ($result -> num_rows > 0) { 
      $row = $result -> fetch_assoc(); 
     }
  }



 


//$ordenes_servicio=get_dato_sql('servicio','count(*)',' where );







?>
<div class="card-body">

  <div class="row">
          <div class="col-12 col-sm-6 col-md">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calculator" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Inspecciones</span>
                <span class="info-box-number">
                  <?php echo formato_numero($row["total"]); ?>
                  
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-car-side" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Renta</span>
                <span class="info-box-number"><?php echo formato_numero($row["renta"]); ?>
   
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-tools" style="color:white"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Taller</span>
                <span class="info-box-number"><?php echo formato_numero($row["taller"]); ?>

                </span>
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
            $meses=5; // maximo 12 meses

            $gaereo="";
            $gmaritimo="";
            $sqladd="";
            $sqlcampos="";
            
            for ($i = $meses; $i >= 1; $i--) 
            {
              echo  "'".date("F", strtotime( date( 'Y-m-01' )." -$i months"))."'" ; //Y-m%
              $mesactual=date("m", strtotime( date( 'Y-m-01' )." -$i months"));
              $sqlcampos.=" ,SUM(if(tipo_inspeccion=1 AND MONTH(fecha)=".$mesactual." ,1,0)) AS aereo_mes".($i+1)."
                            ,SUM(if(tipo_inspeccion=2 AND MONTH(fecha)=".$mesactual." ,1,0)) AS maritimo_mes".($i+1) ;
              if ($i >= 1) { echo ",";  } 
            }
            $i=1;
            echo  "'".date("F", strtotime( date( 'Y-m-01' )." "))."'" ; //Y-m%
            $mesactual=date("m", strtotime( date( 'Y-m-01' ).""));
            $sqlcampos.=" ,SUM(if(tipo_inspeccion=1 AND MONTH(fecha)=".$mesactual." ,1,0)) AS aereo_mes".$i."
                          ,SUM(if(tipo_inspeccion=2 AND MONTH(fecha)=".$mesactual." ,1,0)) AS maritimo_mes".$i ;

                         

            $sqladd=" and fecha>='".date("Y-m-d", strtotime( date( 'Y-m-01' )." -$meses months"))."'";

            $result_grafico = sql_select("SELECT 1 as campo
           
               $sqlcampos
              
              FROM inspeccion 
              WHERE  id_tienda=".$_SESSION['tienda_id']." AND id_estado<>4 $sqladd
             
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
      },
      {
        label               : 'TALLER',
        backgroundColor     : 'rgba(210, 214, 222, 1)',
        borderColor         : 'rgba(210, 214, 222, 1)',
        pointRadius         : false,
        pointColor          : 'rgba(210, 214, 222, 1)',
        pointStrokeColor    : '#c1c7d1',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(220,220,220,1)',
        data                : [<?php echo $gmaritimo; ?>]
      },
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
