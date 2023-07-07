@extends('Dashboard.layouts.global')

@section('content')
<div id="wrapper">        
    <div id="page-wrapper" class="gray-bg dashbard-1">
   		<div class="content-main">
			
			<div class="content-mid">					
				<div class="grid_3 col-md-12">							
	     				<h3 class="head-top">Comparador de Ofertas</h3>
	     				<div class="but_list">
	       					
		   						
		  		  								@if(isset($user->_perfil))
		  		  									<h3>{{$user->_perfil->direccion}}</h3>
		  		  								@else
		  		  									<h3>Sin ubicación</h3>
		  		  								@endif	

		  		  								<div class="col-md-12">
														<table class="table table-bordered table-striped table-hover table-condensed table-responsive table-analisis-comparacion2 tabla1" style="width: 60%; margin: 0 auto;" id="comparacion-table">
															<thead>
																<tr>
																	<th>
																		
																	</th>
																	<th colspan="2" class="text-center">
																		CONTRATO ACTUAL
																	</th>																	
																	<th colspan="2" class="text-center">
																		CONTRATO PROPUESTO
																	</th>																	
																</tr>
															</thead>
															<tbody>
																<tr class="text-center">
																	<td>
																	</td>
																	<td>
																		Precio Energía (€/kWh)
																	</td>
																	<td>
																		Precio Potencia (€/kW·mes)
																	</td>
																	<td>
																		Precio Energía (€/kWh)
																	</td>
																	<td>
																		Precio Potencia (€/kW·mes)
																	</td>
																</tr>
																<tr class="text-center">
																	<td >
																		P1
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia1" value="{{number_format($precio_energia[0]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia1" value="{{number_format($precio_potencia[0]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia1','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia1','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
																<tr class="text-center">
																	<td>
																		P2
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia2" value="{{number_format($precio_energia[1]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia2" value="{{number_format($precio_potencia[1]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia2','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia2','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
																<tr class="text-center">
																	<td>
																		P3
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia3" value="{{number_format($precio_energia[2]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia3" value="{{number_format($precio_potencia[2]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia3','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia3','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
																<tr class="text-center">
																	<td>
																		P4
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia4" value="{{number_format($precio_energia[3]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia4" value="{{number_format($precio_potencia[3]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia4','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia4','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
																<tr class="text-center">
																	<td>
																		P5
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia5" value="{{number_format($precio_energia[4]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia5" value="{{number_format($precio_potencia[4]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia5','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia5','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
																<tr class="text-center">
																	<td>
																		P6
																	</td>
																	<td>
																		@if($precio_energia)
																			<input type="text" readonly="true" name="actual_energia6" value="{{number_format($precio_energia[5]->precio_energia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		@if($precio_potencia)
																			<input type="text" readonly="true" name="actual_potencia6" value="{{number_format($precio_potencia[5]->precio_potencia,5,',','.')}}" style="text-align: center;" class="input-trans">
													                  	@endif
																	</td>
																	<td>
																		{{Form::text('energia6','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																	<td>
																		{{Form::text('potencia6','',['class' => 'form-control', 'style' => 'text-align: center'])}}
																	</td>
																</tr>
															</tbody>
														</table>
														<div class="col-md-12 text-center">
															<br>
															{!! Form::submit('Calcular', array('class' => 'btn btn-lg color-127 pull-right')) !!}
														</div>
													{!! Form::close() !!}
												</div>														
												<div class="col-md-12">
													<div class="col-md-6">
														<h4 class="title-1 title-analisis">Término Energía</h4>
														<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
															<thead>
																<tr>
																	<th>
																		
																	</th>
																	<th class="text-center" style="vertical-align: middle;">
																		Coste Actual (€)
																	</th>
																	<th class="text-center">
																		Coste Propuesto (€)
																	</th>
																	<th class="text-center" style="vertical-align: middle;">
																		Diferencia (€)
																	</th>
																</tr>
															</thead>
															<tbody>
																<?php 
																	$i = 1;
																	$data = \Session::get('total_e');
																	$propuesto_energia = \Session::get('propuesto_e');
																	$suma_actual_e = \Session::get('suma_actual_e');
																	$suma_propuesto_e = \Session::get('suma_propuesto_e');
																	$suma_total_e = \Session::get('suma_total_e');
																?>
																@foreach($precio_energia as $precio_e)
																	<tr>
																		<td class="text-center">
																			{{$precio_e->eje}}
																		</td>
																		@if(!empty($suma[$i-1]->coste_energia))
																			<td class="text-center">
																				<input name="actual_e{{$i}}" type="text" readonly class="input-trans" value="{{number_format($suma[$i-1]->coste_energia,'5',',','.')}}" style="text-align: center;">
																			</td>
																		@else
																			<td class="text-center">
																				<input name="actual_e{{$i}}" type="text" readonly class="input-trans" value="0" style="text-align: center;">
																			</td>
																		@endif
																		<td class="text-center">
																			<input name="propuesto_e{{$i}}" readonly="" type="text" class="form-control input-trans" value="{{number_format(floatval($propuesto_energia[$i - 1]),'5',',','.')}}" style="text-align: center;">
																		</td>
																		<td class="text-center">
																			{{number_format($data[$i - 1],'5',',','.')}}
																		</td>
																	</tr>	
																	<?php $i++; ?>					
																@endforeach
															</tbody>
															<tfoot>
																<tr >
																	<th class="text-center">
																		TOTAL
																	</th>
																	<th class="text-center">
																		{{number_format($suma_actual_e,'0',',','.')}} €
																	</th>
																	<th class="text-center">
																		{{number_format($suma_propuesto_e,'0',',','.')}} €
																	</th>
																	<th class="text-center">
																		{{number_format($suma_total_e,'0',',','.')}} €
																	</th>
																</tr>
															</tfoot>
														</table>
													</div>
													<div class="col-md-6">
														<h4 class="title-1 title-analisis">Término Potencia</h4>
														<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
															<thead>
																<tr>
																	<th>
																		
																	</th>
																	<th class="text-center" style="vertical-align: middle;">
																		Coste Actual (€)
																	</th>
																	<th class="text-center">
																		Coste Propuesto (€)
																	</th>
																	<th class="text-center" style="vertical-align: middle;">
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
																@foreach($precio_energia as $precio_e)
																	<tr>
																		<td class="text-center">
																			{{$precio_e->eje}}
																		</td>
																		@if(!empty($suma2[$j-1]->coste_potencia))
																			<td class="text-center">
																				<input name="actual_p{{$j}}" type="text" readonly class="input-trans" value="{{number_format($suma2[$j-1]->coste_potencia,'5',',','.')}}" style="text-align: center;">
																			</td>
																		@else
																			<td class="text-center">
																				<input name="actual_p{{$j}}" type="text" readonly class="input-trans" value="0" style="text-align: center;">
																			</td>
																		@endif
																		<td class="text-center">
																			<input name="propuesto_p{{$j}}" type="text" readonly="true" class="form-control input-trans" value="{{number_format($propuesto_potencia[$j - 1],'5',',','.')}}" style="text-align: center;">
																		</td>
																		<td class="text-center">
																			{{number_format($data[$j - 1],'5',',','.')}}
																		</td>
																	</tr>
																	<?php $j++; ?>						
																@endforeach
															</tbody>
															<tfoot>
																<tr >
																	<th class="text-center">
																		TOTAL
																	</th>
																	<th class="text-center">
																		{{number_format($suma_actual_p,'0',',','.')}} €
																	</th>
																	<th class="text-center">
																		{{number_format($suma_propuesto_p,'0',',','.')}} €
																	</th>
																	<th class="text-center">
																		{{number_format($suma_total_p,'0',',','.')}} €
																	</th>
																</tr>
															</tfoot>
														</table>
													</div>
												</div>												
												<div class="col-md-6 graph-1 margin-top-graph">
													
												</div>

												<div class="col-md-6 graph-1 margin-top-graph">
													
												</div>
												
  		  									</div>
		  						
   						
   					</div>
  				</div>

			</div>
			<div class="clearfix"> </div>				
		</div>		
	</div>	
</div>
@endsection
@section('scripts')	
	<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
    <script src="{{asset('js/jquery.slimscroll.min.js')}}"></script>        
    <script src="{{asset('js/custom.js')}}"></script>
    <script src="{{asset('js/screenfull.js')}}"></script>
    <script src="{{asset('js/jquery.nicescroll.js')}}"></script>	
	<script src="{{asset('js/scripts.js')}}"></script>
	<script src="{{asset('js/bootstrap.min.js')}}"> </script>
	<script src="{{asset('js/canvas.js')}}"></script>
	<script>
        $(function () {
            $('#supported').text('Supported/allowed: ' + !!screenfull.enabled);

            if (!screenfull.enabled) {
                return false;
            }

            

            $('#toggle').click(function () {
                screenfull.toggle($('#container')[0]);
            });
            

            
        });
    </script>
    <!-- <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script> -->
    <script src="{{asset('js/skycons.js')}}"></script>

    
	
@endsection