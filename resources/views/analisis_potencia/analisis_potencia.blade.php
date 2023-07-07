@extends('Dashboard.layouts.global')

@section('content')
<div id="wrapper">        
    <div id="page-wrapper" class="gray-bg dashbard-1">
   		<div class="content-main">
			<div class="banner col-md-12">
				<div class="col-md-6 text-center">
						<button id = "button_interval" type="button" class="btn btn-lg btn-primary float-left" data-toggle="modal" data-target="#myModal" style="margin-right: 2em;"><i class="fa fa-plus"></i>  Intervalos de Períodos ({{$label_intervalo}})</button>
					</div>
			    <div class="col-md-6 text-right" style="margin-top: 5px">
					@if($label_intervalo != 'Personalizado')
						<form id="form_navegation" action="{{route('config.navigation')}}" method="POST">
							{{ csrf_field() }}
							<input type="hidden" name="option_interval" value="9">
							<input type="hidden" name="label_intervalo" value="{{$label_intervalo}}">
							<input type="hidden" name="date_from_personalice" value="{{$date_from}}">
							<input type="hidden" name="date_to_personalice" value="{{$date_to}}">
							<input type="hidden" name="before_navigation" id="before_navigation" value="0">
				    		<button type="submit" class="btn  btn-primary btn-arrow-left" onclick="anterior()">Ant.</button>
							@if(isset(Session::get('_flash')['current_date']))
								<button type="button" class="btn  btn-link">{{Session::get('_flash')['current_date']}}</button>
							@else
				    			<button type="button" class="btn  btn-link">{{$label_intervalo}}</button>
				    		@endif
				    		<button type="submit" class="btn  btn-primary btn-arrow-right" onclick="siguiente()">Sig.</button>
						</form>
					@endif
				</div>
			    @if(isset($ctrl) && $ctrl == 1)
     					<a href="{!! route('admin.users',[2, $id]) !!}" class="btn btn-info btn-lg float-right"><i class="fa fa-undo"></i></a>
     			@endif     			
			</div>

			<div class="content-mid">					
				<div class="grid_3 col-md-12">
	     				<div class="but_list">
	       					<div class="bs-example bs-example-tabs" role="tabpanel" data-example-id="togglable-tabs">
		   						<ul id="myTab" class="nav nav-tabs" role="tablist">
		   							<?php $i = 1; ?>
		   							@foreach($user->_count as $contador)
			   							@if($i == 1 && $contador_label == $contador->count_label && $contador->tipo < 3)
		  			  						<li role="presentation" class="active">
		  			  							<a href="{{route('analisis.potencia', $id) . '?contador=' . $contador->count_label . '&tipo=' . $contador->tipo}}" id="home-tab" style="font-size: 14pt"><i class="fa fa-clock-o"></i>{{$contador->count_label}}</a>
		  			  						</li>
	  			  						@else
	  			  							@if($contador_label == $contador->count_label && $contador->tipo < 3)
		  			  							<li role="presentation" class="active">
			  			  							<a href="{{route('analisis.potencia', $id) . '?contador=' . $contador->count_label . '&tipo=' . $contador->tipo}}" id="profile-tab" style="font-size: 14pt"><i class="fa fa-clock-o"></i>{{$contador->count_label}}</a>
			  			  						</li>
		  			  						@elseif($contador->tipo < 3)
		  			  							<li role="presentation">
			  			  							<a href="{{route('analisis.potencia', $id) . '?contador=' . $contador->count_label . '&tipo=' . $contador->tipo}}" id="profile-tab" style="font-size: 14pt"><i class="fa fa-clock-o"></i>{{$contador->count_label}}</a>
			  			  						</li>
		  			  						@endif
	  			  						@endif
			  						<?php $i++; ?>
			  					@endforeach
								</ul>
		  						<div id="myTabContent" class="tab-content">
		  							<?php $j = 1; ?>
		  							@foreach($user->_count as $contador)
		  								@if($j == 1)
	  		  								<div role="tabpanel" class="tab-pane fade in active col-md-12" id="Contador{{$j}}" aria-labelledby="Contador{{$j}}">
	  		  							@else
	  		  								<div role="tabpanel" class="tab-pane fade" id="Contador{{$j}}" aria-labelledby="Contador{{$j}}">
	  		  							@endif
	  		  								@if(isset($domicilio->suministro_del_domicilio))
	  		  									<label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">{{$domicilio->suministro_del_domicilio}}</label></label>
	  		  								@else
	  		  									<label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">sin ubicación</label></label>
	  		  								@endif
	  		  								<br>
	  		  								<label class="title-ubicacion">Intervalo: <label class="title-ubicacion2"> Desde {{$date_from}} hasta {{$date_to}}</label></label>
	  		  								<br>
											<div class="col-md-12 graph-1">
												<?php 
													$aux_cont = implode('_', explode(' ', $contador->count_label))
												 ?>
												 @if($tipo_tarifa == 1)
													@include('Dashboard.Graficas.consumo_diario_energia',array('id_var' => 'AnalisisPotencia_'.$aux_cont))
												@elseif($tipo_tarifa == 2 || $tipo_tarifa == 3)
													@include('Dashboard.Graficas.consumo_diario_energia',array('id_var' => 'AnalisisPotencia3P_'.$aux_cont))
												@endif
											</div>

  		  								</div>
		  								<?php $j++; ?>
		  							@endforeach
		  						</div>
   						</div>
   					</div>
	  				<div class="col-md-12">
	  					<button type="button" class="btn color-127 pull-left" id="button_optimizacion" onclick="mostrar_Optimizacion({{$id}})">OPTIMIZACIÓN POTENCIA CONTRATADA</button>
	  					<button type = "button" class="btn color-127 pull-right" id="exportButton"> GENERAR PDF</button>
	  				</div>
					<!-- AQUI -->
						@if($tipo_tarifa == 1)
		  					<div class="col-md-12" id="optimizacion" style="display: none;">
		  						<div class="col-md-12">
									<div class="col-md-6">
										<h4 class="title-1 title-analisis">Situación Actual</h4>
										<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
											<thead>
												<tr>
													<th>
														
													</th>
													@foreach($potencia_contratada as $poten)
														<th class="text-center" style="vertical-align: middle;">
															{{$poten->columna}}<br> (kW)
														</th>	
													@endforeach
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="text-center" style="vertical-align: middle;">
														POTENCIA<br> CONTRATADA											
													</td>
													@foreach($potencia_contratada as $poten)				
														<td class="text-center" style="vertical-align: middle;">
															 {{number_format($poten->potencia_contratada,0,',','.')}}
														</td>											
													@endforeach
												</tr>
											</tbody>							
										</table>
									</div>
									<div class="col-md-6">
										<h4 class="title-1 title-analisis">Situación Óptima</h4>
										<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
											<thead>
												<tr>
													<th>
														
													</th>
													@foreach($potencia_optima_tabla as $poten)
														<th class="text-center" style="vertical-align: middle;">
															{{$poten->eje}}<br> (kW)
														</th>	
													@endforeach										
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="text-center" style="vertical-align: middle;">
														POTENCIA<br> ÓPTIMA
													</td>
													@foreach($potencia_optima_tabla as $poten)				
														<td class="text-center" style="vertical-align: middle;">
															{{number_format($poten->p_optima,'0',',','.')}} 
														</td>											
													@endforeach
												</tr>
											</tbody>							
										</table>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12">
										<div class="col-md-6">
											<br><br><br>
											<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
												<thead>
													<tr>
														<th>
															
														</th>
														<th class="text-center" style="vertical-align: middle;">
															Potencia Contratada
														</th>
														<th class="text-center">
															Excesos Potencia
														</th>
														<th class="text-center" style="vertical-align: middle;">
															Término Potencia
														</th>										
													</tr>
												</thead>
												<tbody>
													<tr>
															
													</tr>
													<?php 
														$total_contratada1 = 0;
														$total_exceso1 = 0;
														$total_termino1 = 0;
														$k = 0;
													 ?>
													@foreach($situacion_actual as $actual)
														<tr class="text-center" style="vertical-align: middle;">
															<td>
																{{$actual->Periodo}}
															</td>
															<td>
																{{number_format($actual->coste_contratada,2,',','.')}} €
																<?php 
																	$total_contratada1 += $actual->coste_contratada;
																 ?>
															</td>
															<td>
																{{number_format($coste_exceso_potencia_actual_[$k],2,',','.')}} €
																<?php 
																	$total_exceso1 += $coste_exceso_potencia_actual_[$k];
																 ?>
															</td>
															<td>
																{{number_format($coste_exceso_potencia_actual_[$k]+$actual->coste_contratada,2,',','.')}} €
																<?php 
																	$total_termino1 += $coste_exceso_potencia_actual_[$k]+$actual->coste_contratada;
																 ?>
															</td>
														</tr>
														<?php $k++; ?>
													@endforeach									
												</tbody>
												<tfoot>
														<th>
															TOTAL
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_contratada1,0,',','.')}} €
														</th>
														<th class="text-center">
															{{number_format($total_exceso1,0,',','.')}} €
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_termino1,0,',','.')}} €
														</th>
												</tfoot>							
											</table>
										</div>
										<div class="col-md-6">							
											<br><br><br>
											<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
												<thead>
													<tr>
														<th>
															
														</th>
														<th class="text-center" style="vertical-align: middle;">
															Potencia Contratada
														</th>
														<th class="text-center">
															Excesos Potencia
														</th>
														<th class="text-center" style="vertical-align: middle;">
															Término Potencia
														</th>
													</tr>
												</thead>
												<tbody>
													<tr>
															
													</tr>
													<?php 
														$total_contratada2 = 0;
														$total_exceso2 = 0;
														$total_termino2 = 0;
														$k = 0;
													 ?>
													@foreach($situacion_optima as $optima)
														<tr class="text-center" style="vertical-align: middle;">
															<td>
																{{$optima->Periodo}}
															</td>
															<td>
																{{number_format($optima->coste_contratada,2,',','.')}} €
																<?php 
																	$total_contratada2 += $optima->coste_contratada;
																 ?>
															</td>
															<td>
																{{number_format($coste_exceso_potencia_optima_[$k],2,',','.')}} €
																<?php 
																	$total_exceso2 += $coste_exceso_potencia_optima_[$k];
																 ?>
															</td>
															<td>
																{{number_format($coste_exceso_potencia_optima_[$k]+$optima->coste_contratada,2,',','.')}} €
																<?php 
																	$total_termino2 += $coste_exceso_potencia_optima_[$k]+$optima->coste_contratada;
																 ?>
															</td>
														</tr>
														<?php $k++; ?>
													@endforeach
												</tbody>
												<tfoot>
														<th>
															TOTAL
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_contratada2,0,',','.')}} €
														</th>
														<th class="text-center">
															{{number_format($total_exceso2,0,',','.')}} €
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_termino2,0,',','.')}} €
														</th>
												</tfoot>							
											</table>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="row" style="margin-top:100px;">
										<div class="col-md-12">
											<a class="btn btn-lg btn-primary" href="{{route('analisis_potencia_envio_email')}}">Solicitar Informe Ampliado</a>
	    								</div>
									</div>
								</div>
								<div class="col-md-6">
									<br><br>
									<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
										<thead>
											<tr>
												<th>
													
												</th>
												<th class="text-center" style="vertical-align: middle;">
													Potencia Contratada
												</th>
												<th class="text-center">
													Excesos Potencia
												</th>
												<th class="text-center" style="vertical-align: middle;">
													Término Potencia
												</th>

												<th class="text-center" style="vertical-align: middle;">
													AHORRO
												</th>								
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-center" style="vertical-align: middle;">SITUACIÓN ACTUAL</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_contratada1,0,',','.')}} €</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_exceso1,0,',','.')}} €</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_termino1,0,',','.')}} €</td>
												<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($total_termino1-$total_termino2,0,',','.')}} €</td>
											</tr>

											<tr>
												<td class="text-center" style="vertical-align: middle;">SITUACIÓN ÓPTIMA</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_contratada2,0,',','.')}} €</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_exceso2,0,',','.')}} €</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_termino2,0,',','.')}} €</td>
											</tr>
										</tbody>
										
									</table>
								</div>
								<div class="col-md-3">
									<div class="row" style="margin-top:100px;">
										<div class="col-md-12">
											@if($diff_dates->days >= 27)
	        								<form method="POST" action="{{route('analisis.potencia.optima')}}">
	        									{{ csrf_field() }}
	        									<input type="hidden" name="date_from" value="{{$date_from}}" />
	        									<input type="hidden" name="date_to" value="{{$date_to}}" />
	        									<button type="submit" class="btn btn-lg btn-primary">Calcular Potencia Óptima</button>
	        								</form>
	        								@endif
	    								</div>
									</div>
								</div>
		  					</div>
	  					@else
	  						<div class="col-md-12" id="optimizacion" style="display: none;">
		  						<div class="col-md-12">
									<div class="col-md-6">
										<h4 class="title-1 title-analisis">Situación Actual</h4>
										<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
											<thead>
												<tr>
													<th>
														
													</th>
													@foreach($potencia_contratada as $poten)
														<th class="text-center" style="vertical-align: middle;">
															{{$poten->columna}}<br> (kW)
														</th>	
													@endforeach
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="text-center" style="vertical-align: middle;">
														POTENCIA<br> CONTRATADA											
													</td>
													@foreach($potencia_contratada as $poten)				
														<td class="text-center" style="vertical-align: middle;">
															 {{number_format($poten->potencia_contratada,0,',','.')}}
														</td>											
													@endforeach
												</tr>
											</tbody>							
										</table>
									</div>
									<div class="col-md-6">
										<h4 class="title-1 title-analisis">Situación Óptima</h4>
										<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
											<thead>
												<tr>
													<th>
														
													</th>
													@foreach($potencia_optima_tabla as $poten)
														<th class="text-center" style="vertical-align: middle;">
															{{$poten->eje}}<br> (kW)
														</th>	
													@endforeach										
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="text-center" style="vertical-align: middle;">
														POTENCIA<br> ÓPTIMA
													</td>
													@foreach($potencia_optima_tabla as $poten)				
														<td class="text-center" style="vertical-align: middle;">
															{{number_format($poten->p_optima,'0',',','.')}} 
														</td>											
													@endforeach
												</tr>
											</tbody>							
										</table>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12">
										<div class="col-md-6">
											<br><br><br>
											<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
												<thead>
													<tr>
														<th>
															
														</th>														
														<th class="text-center" style="vertical-align: middle;">
															Término Potencia
														</th>										
													</tr>
												</thead>
												<tbody>
													<tr>
															
													</tr>
													<?php 
														$total_contratada1 = 0;
														$total_exceso1 = 0;
														$total_termino1 = 0;
														$k = 0;
													 ?>
													@foreach($coste_termino_potencia_actual_ as $actual)
														<tr class="text-center" style="vertical-align: middle;">
															<td>
																P{{$k+1}}
															</td>
															<td>
																{{number_format($actual,2,',','.')}} €
																<?php 
																	$total_contratada1 += $actual;
																 ?>
															</td>															
														</tr>
														<?php $k++; ?>
													@endforeach									
												</tbody>
												<tfoot>
														<th>
															TOTAL
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_contratada1,0,',','.')}} €
														</th>
												</tfoot>							
											</table>
										</div>
										<div class="col-md-6">							
											<br><br><br>
											<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
												<thead>
													<tr>
														<th>
															
														</th>
														<th class="text-center" style="vertical-align: middle;">
															Término Potencia
														</th>
													</tr>
												</thead>
												<tbody>
													<tr>
															
													</tr>
													<?php 
														$total_contratada2 = 0;
														$total_exceso2 = 0;
														$total_termino2 = 0;
														$k = 0;
													 ?>
													@foreach($termino_potencia_optima_ as $optima)
														<tr class="text-center" style="vertical-align: middle;">
															<td>
																P{{$k+1}}
															</td>
															<td>
																{{number_format($optima,2,',','.')}} €
																<?php 
																	$total_contratada2 += $optima;
																 ?>
															</td>															
														</tr>
														<?php $k++; ?>
													@endforeach
												</tbody>
												<tfoot>
														<th>
															TOTAL
														</th>
														<th class="text-center" style="vertical-align: middle;">
															{{number_format($total_contratada2,0,',','.')}} €
														</th>														
												</tfoot>							
											</table>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="row" style="margin-top:100px;">
										<div class="col-md-12">
											<a class="btn btn-lg btn-primary" href="{{route('analisis_potencia_envio_email')}}">Solicitar Informe Ampliado</a>
	    								</div>
									</div>
								</div>
								<div class="col-md-6">
									<br><br>
									<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
										<thead>
											<tr>
												<th>
													
												</th>
												<th class="text-center" style="vertical-align: middle;">
													Término Potencia
												</th>

												<th class="text-center" style="vertical-align: middle;">
													AHORRO
												</th>								
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-center" style="vertical-align: middle;">SITUACIÓN ACTUAL</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_contratada1,0,',','.')}} €</td>						
												<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($total_contratada1-$total_contratada2,0,',','.')}} €</td>
											</tr>

											<tr>
												<td class="text-center" style="vertical-align: middle;">SITUACIÓN ÓPTIMA</td>
												<td class="text-center" style="vertical-align: middle;">{{number_format($total_contratada2,0,',','.')}} €</td>					
											</tr>
										</tbody>
										
									</table>
								</div>
								<div class="col-md-3">
									<div class="row" style="margin-top:100px;">
										<div class="col-md-12">
											@if($diff_dates->days >= 27)
	        								<form method="POST" action="{{route('analisis.potencia.optima')}}">
	        									{{ csrf_field() }}
	        									<input type="hidden" name="date_from" value="{{$date_from}}" />
	        									<input type="hidden" name="date_to" value="{{$date_to}}" />
	        									<button type="submit" class="btn btn-lg btn-primary">Calcular Potencia Óptima</button>
	        								</form>
	        								@endif
	    								</div>
									</div>
								</div>
		  					</div>
	  					@endif
					<!-- hasta aqui -->					
  				</div>
			</div>
			<div class="clearfix"> </div>				
		</div>
		<div class="content-bottom">				
	  		@include('Dashboard.modals.modal_intervalos')
		</div>
		<div class="copy">
	        <p> &copy; 2020 Submeter 4.0. Todos los derechos reservados</p>
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
	    function changeFunc() 
	    {
		    var selectBox = document.getElementById("option_interval");
		    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
		    if(selectedValue == 9)
		    {
		    	$('#div_datatimes').show();
		    	$('#datepicker').prop('required',true);
		    	$('#datepicker2').prop('required',true);
		    }else{
		    	$('#div_datatimes').hide();
		    	$('#datepicker').val('');
		    	$('#datepicker2').val('');
		    	$('#datepicker').prop('required',false);
		    	$('#datepicker2').prop('required',false);
		    }
	    }

	    function mostrar_Optimizacion(id)
	    {
	    	console.log(id);
	    	$('#optimizacion').toggle();
	    }
	</script>
	<script>
		$('#div_datatimes').hide();
		$('#datepicker').val('');
		$('#datepicker2').val('');
		$('#datepicker').prop('required',false);
		$('#datepicker2').prop('required',false);
		
		$( function() {
		    $( "#datepicker" ).datepicker({
		    	dateFormat:'yy-mm-dd',
		    	changeMonth: true,
	      		changeYear: true,
		    });
		  } );
	    $( function() {
		    $( "#datepicker2" ).datepicker({
		    	dateFormat:'yy-mm-dd',
		    	changeMonth: true,
	      		changeYear: true,
		    });
		  } );
	</script>
	
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

    <script>
        function anterior()
        {        	
        	$('#before_navigation').val("-1");
        }
        function siguiente()
        {        	
        	$('#before_navigation').val("1");
        }
        function volver()
        {        	
        	$('#before_navigation').val("0");
        }
    </script>

    <!-- <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script> -->
    <script src="{{asset('js/skycons.js')}}"></script>

    @include('Dashboard.includes.scripts_analisis_potencia')
	
@endsection