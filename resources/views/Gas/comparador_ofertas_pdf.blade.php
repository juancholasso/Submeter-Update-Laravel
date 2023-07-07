<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link href="{{asset('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' />
        <script src="{{asset('js/Chart.js')}}"></script>
        <style>		    
		    header { position: fixed; left: 0px; right: 0px; height: 50px; }		    		    
		    main { margin-top: 250px;}		    		    
		</style>
    </head>
    <body>
    	<header class="clearfix">
	      <div class="row">
	      	<div class="col-md-3">
	      		<div id="logo">
			        <img src="{{asset($image)}}" style="width: 90px; height: 90px">
			    </div>
	      	</div>
	      	<div class="col-md-9">
		      <div id="company">
		        <h2 class="name">Empresa: {{$user->name}}</h2>
		        @if(!is_null($user->_perfil))
			        <?php $direccion = $user->_perfil->direccion ?>
			        <div>Ubicación: {{$user->_perfil->direccion}}</div>        
		        @else
		        	<div>Ubicación: Sin ubicación</div>
		        	<?php $direccion = "sin ubicación" ?>
		        @endif
		        <div>Contador: {{$contador_label}}</div>
		        <div>Email: {{$user->email}}</div>
		        <div>
		        	Intervalo del reporte: Desde {{$date_from}} hasta {{$date_to}}
		        </div>
		      </div>
	      	</div>
	      </div>
	    </header>
		<br>
		<main>
			<div class="col-md-12">
				@foreach($user->_count as $contador)
					<div class="col-md-12">
	  		  	 		<div class="col-md-6">
	  		  	 			<h5 style="color: #004165; font-weight: bold;margin: 0px auto; width: 70%" class="text-center">CONTRATO ACTUAL</h5><br>
	  		  	 			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive" id="tablee33" style="width: 70%; margin: 0px auto">
								<thead style="background-color: #004165;">										
									<tr>
										<th class="text-center" style="color: #FFF">Precio Término Variable</th>
										<th class="text-center" style="color: #FFF">Precio Término Fijo</th>
									</tr>
								</thead>

								<tbody>
									<tr class="text-center">
										<td>															
											{{number_format($precio_variable->Precio,5,',','.')}} €/kWh															
										</td>
										<td>															
											{{number_format($precio_fijo->Precio,5,',','.')}} €/kWh															
										</td>
									</tr>														
								</tbody>
							</table>
	  		  	 		</div><br>
	  		  	 		<div class="col-md-6">
	  		  	 			<h5 style="color: #004165; font-weight: bold;margin: 0px auto; width: 70%" class="text-center">CONTRATO PROPUESTO</h5><br>
	  		  	 			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive" id="table2" style="width: 70%; margin: 0px auto">
								<thead style="background-color: #004165;">										
									<tr>
										<th class="text-center" style="color: #FFF">Precio Término Variable</th>
										<th class="text-center" style="color: #FFF">Precio Término Fijo</th>
									</tr>
								</thead>

								<tbody>
									<tr class="text-center">
										<td>															
											{{number_format($precio_variable->Precio_propuesto,5,',','.')}} €/kWh
										</td>
										<td>															
											{{number_format($precio_fijo->Precio_propuesto,5,',','.')}} €/kWh															
										</td>
									</tr>							
								</tbody>
							</table>
	  		  	 		</div>
	  		  	 	</div>
	  		  	 	
	  		  	 	<div class="col-md-12" style="margin-top: 50px;">
	  		  	 		<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive" id="table2" style="width: 70%; margin: 0px auto">
								<thead style="background-color: #004165;">										
									<tr>
										<th class="text-center"></th>
										<th class="text-center" style="color: #FFF; vertical-align: middle;">Coste Término Variable (€)</th>
										<th class="text-center" style="color: #FFF; vertical-align: middle;">Coste Término Fijo (€)</th>
										<th class="text-center" style="color: #FFF; vertical-align: middle;">Coste Total (€)</th>
									</tr>
								</thead>

								<tbody >
									<tr class="text-center">
										<td style="background-color: #004165; color: #FFF !important; font-weight: bold;">ACTUAL</td>
										<td>															
											{{number_format($coste_termino_variable->coste,'2',',','.')}} €														
										</td>
										<td>
											{{number_format($coste_termino_fijo->coste,'2',',','.')}} €
										</td>
										<td>															
											{{number_format($coste_termino_variable->coste+$coste_termino_fijo->coste,'2',',','.')}} €
										</td>
									</tr>
									<tr class="text-center">
										<td  style="background-color: #004165; color: #FFF !important; font-weight: bold;">PROPUESTO</td>
										<td>															
											{{number_format($coste_termino_variable_propuesto->coste,'2',',','.')}} €
										</td>
										<td>															
											{{number_format($coste_termino_fijo_propuesto->coste,'2',',','.')}} €
										</td>
										<td>															
											{{number_format($coste_termino_variable_propuesto->coste+$coste_termino_fijo_propuesto->coste,'2',',','.')}} €
										</td>
									</tr>							
								</tbody>
								<tfoot class="text-center" style="background-color: #004165; color: #FFF !important; font-weight: bold;">
									<th class="text-center">DIFERENCIA</th>
									<th class="text-center">{{number_format($coste_termino_variable->coste-$coste_termino_variable_propuesto->coste,'2',',','.')}} €</th>
									<th class="text-center">{{number_format($coste_termino_fijo->coste-$coste_termino_fijo_propuesto->coste,'2',',','.')}} €</th>
									<th class="text-center">{{number_format(($coste_termino_variable->coste+$coste_termino_fijo->coste)-($coste_termino_variable_propuesto->coste+$coste_termino_fijo_propuesto->coste),'2',',','.')}} €</th>
								</tfoot>
							</table>
	  		  	 	</div>
					@break
				@endforeach
			</div>
		</main>

		<script
          src="https://code.jquery.com/jquery-3.2.1.min.js"
          integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
          crossorigin="anonymous"></script>
		<script src="{{asset('js/bootstrap.min.js')}}"> </script>
		<script src="{{asset('js/canvas.js')}}"></script>
    </body>    
</html>
	