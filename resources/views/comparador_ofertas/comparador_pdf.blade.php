<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link href="{{asset('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' />
        <script src="{{asset('js/Chart.js')}}"></script>
        <style>		    
		    header { position: fixed; left: 0px; right: 0px; height: 50px;}
		    main { margin-top: 250px !important;}
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
					<table id="lista-producto" class="table table-hover table-responsive table-bordered" style="margin: 0px auto;">
						<thead>
							<tr>
								<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
									
								</th>
								<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;" colspan="2" class="text-center">
									CONTRATO ACTUAL
								</th>																	
								<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;" colspan="2" class="text-center">
									CONTRATO PROPUESTO
								</th>																	
							</tr>
						</thead>
						<tbody>
							<tr class="text-center">
								<td style="text-align: center; font-size: 12pt;">
								</td>
								<td style="text-align: center; font-size: 12pt;" style="padding: 0 !important;">
									Precio Energía (€/kWh)
								</td>
								<td style="text-align: center; font-size: 12pt;">
									Precio Potencia (€/kW·mes)
								</td>
								<td style="text-align: center; font-size: 12pt;">
									Precio Energía (€/kWh)
								</td>
								<td style="text-align: center; font-size: 12pt;">
									Precio Potencia (€/kW·mes)
								</td>
							</tr>
							<?php $k = 1; ?>
							@foreach($precio_energia as $value)								
								<tr class="text-center">
									<td style="text-align: center; font-size: 12pt;" >
										P{{$k}}
									</td>
									<td style="text-align: center; font-size: 12pt;">
										@if($value)
											{{number_format($value->precio_energia,5,',','.')}}
					                  	@endif
									</td>
									<td style="text-align: center; font-size: 12pt;">
										@if($precio_potencia)
											{{number_format($precio_potencia[$k-1]->precio_potencia,5,',','.')}}
					                  	@endif
									</td>
									<td style="text-align: center; font-size: 12pt;">
										{{number_format($precio_energia_propuesta[$k-1]->precio_energia_propuesta,'5',',','.')}}
									</td>
									<td style="text-align: center; font-size: 12pt;">
										{{number_format($precio_potencia_propuesta[$k-1]->precio_potencia_propuesta,'5',',','.')}}
									</td>
								</tr>
								<?php $k++; ?>
							@endforeach							
						</tbody>
					</table>						
						<h3 class="title-1 title-analisis">Término Energía</h3>
						<table class="table table-hover table-responsive table-bordered" style="margin: 0px auto;">
							<thead>
								<tr>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Coste Actual (€)
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Coste Propuesto (€)
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Diferencia (€)
									</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									$i = 1;
									$data = \Session::get('total_e');
									$propuesto_energia = \Session::get('propuesto_e');
									$suma_actual_e = \Session::get('total_actual_energia');
									$suma_propuesto_e = \Session::get('total_propuesto_energia');
									$suma_total_e = \Session::get('suma_total_e');
								?>
								@if($tipo_tarifa == 1)
									@foreach($precio_energia as $precio_e)
										<tr>
											<td class="text-center">
												{{$precio_e->eje}}
											</td>
											@if(!empty($coste_actual_energia[$i-1]->coste_energia))
												<td class="text-center">
													{{number_format($coste_actual_energia[$i-1]->coste_energia,'2',',','.')}}
												</td>
											@else
												<td class="text-center">
													0
												</td>
											@endif
											<td class="text-center">
												@if($tipo_tarifa == 1)
													{{number_format($coste_actual_energia[$i-1]->coste_energia_propuesto,'2',',','.')}}
												@else
													{{number_format($coste_actual_energia[$i-1],'2',',','.')}}
												@endif
											</td>
											<td class="text-center">
												@if($tipo_tarifa == 1)
													{{number_format($coste_actual_energia[$i-1]->diferencia,'2',',','.')}}
												@else
												@endif
											</td>
										</tr>	
										<?php $i++; ?>					
									@endforeach
								@else
								<?php $i = 1; ?>
									@foreach($precio_energia as $precio_e)
										<tr>
											<td class="text-center">
												{{$precio_e->eje}}
											</td>
											@if(!empty($coste_actual_energia[$i-1]))
												<td class="text-center">
													{{number_format($coste_actual_energia[$i-1],'2',',','.')}}
												</td>
											@else
												<td class="text-center">
													0
												</td>
											@endif
											<td class="text-center">
												{{number_format($coste_propuesto_energia[$i-1],'2',',','.')}}
											</td>
											<td class="text-center">
												{{number_format($coste_actual_energia[$i-1]-$coste_propuesto_energia[$i-1],'2',',','.')}}
											</td>
										</tr>	
										<?php $i++; ?>					
									@endforeach
								@endif
								
							</tbody>
							<tfoot>
								<tr >
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										TOTAL
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_actual_energia,'0',',','.')}} €
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_propuesto_energia,'0',',','.')}} €
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_diferencia_energia,'0',',','.')}} €
									</th>
								</tr>
							</tfoot>
						</table>
						<br><br><br><br><br><br><br><br><br><br><br><br><br>
						@if($tipo_tarifa == 1)
							<h3 class="title-1 title-analisis">Término Potencia</h3>
						@else
							<h3 class="title-1 title-analisis" style="margin-top: 130px;">Término Potencia</h3>
						@endif
						<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
							<thead>
								<tr>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Coste Actual (€)
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Coste Propuesto (€)
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										Diferencia (€)
									</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									$j = 1; 
									$data = \Session::get('total_p');
									$propuesto_potencia = \Session::get('propuesto_p');
									$suma_actual_p = \Session::get('suma_actual_p');
									$suma_propuesto_p = \Session::get('suma_propuesto_p');
									$suma_total_p = \Session::get('suma_total_p');
								?>
								@if($tipo_tarifa == 1)
									@foreach($coste_actual_potencia as $precio_e)
										@if(!is_null($precio_e->eje))
											<tr>
												<td class="text-center">
													{{$precio_e->eje}}
												</td>
												@if(!empty($precio_e->coste_potencia))
													<td class="text-center">
														{{number_format($coste_actual_potencia[$j - 1]->coste_potencia,'2',',','.')}}
													</td>
												@else
													<td class="text-center">
														0
													</td>
												@endif
												<td class="text-center">
													{{number_format($coste_actual_potencia[$j - 1]->coste_potencia_propuesto,'2',',','.')}}
												</td>
												<td class="text-center">
													@if($tipo_tarifa == 1)
														{{number_format($coste_actual_potencia[$j - 1]->diferencia,'2',',','.')}}
													@else
													@endif
												</td>
											</tr>
											<?php $j++; ?>
										@else
											<?php $j++; ?>					
										@endif
									@endforeach
								@else
									<?php $j = 1; ?>
									@foreach($coste_actual_potencia as $precio_e)
										<tr>
											<td class="text-center">
												P{{$j}}
											</td>
											@if(!empty($precio_e))
												<td class="text-center">
													{{number_format($precio_e,'2',',','.')}}
												</td>
											@else
												<td class="text-center">
													<input type="text" readonly class="input-trans" value="0" style="text-align: center;">
												</td>
											@endif
											<td class="text-center">
												{{number_format($coste_propuesto_potencia[$j-1],'2',',','.')}}
											</td>
											<td class="text-center">
												@if($tipo_tarifa == 1)
													{{number_format($coste_actual_potencia[$j - 1]->diferencia,'2',',','.')}}
												@else
													{{number_format($precio_e - $coste_propuesto_potencia[$j-1],'2',',','.')}}
												@endif
											</td>
										</tr>
										<?php $j++; ?>								
									@endforeach
								@endif
							</tbody>
							<tfoot>
								<tr >
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										TOTAL
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_actual_potencia,'0',',','.')}} €
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_propuesto_potencia,'0',',','.')}} €
									</th>
									<th style="text-align: center; font-size: 12pt; background-color: #004165;
	  color: white;">
										{{number_format($total_diferencia_potencia,'0',',','.')}} €
									</th>
								</tr>
							</tfoot>
						</table>
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
	