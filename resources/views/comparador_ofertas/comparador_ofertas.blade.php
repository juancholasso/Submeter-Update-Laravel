 @extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 8])
@endsection

@section('content')	
	<div class="d-none">
		<div class="pdf-header">
			<div class="container" style="width:100%; display: inline-block">
				<div class="row">
					<div class="col">
						<img class="float-left" width="60" height="60" src="{{asset($dir_image_count)}}">
					</div>
					<div class="col">
						<h5 style="text-align: center;">Comparador de Ofertas<h5>
					</div>
					<div class="col">
						<img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
					</div>
				</div>
			</div>
			<div>
				<table class="table table-bordered" id="pdf_encabezado">
					<tr>
						<th class="text-left font-weight-bold ">Cliente</th>
						<td>{{$domicilio->denominacion_social}}</td>
						<th class="text-left font-weight-bold">CIF</th>
						<td>{{$domicilio->CIF}}</td>
					</tr>
					<tr>
						<th class="text-left font-weight-bold">Contador</th>
						<td>{{$contador_label}}</td>
						<th class="text-left font-weight-bold ">CUPS</th>
						<td>{{$domicilio->CUPS}}</td>
					</tr>
					<tr>
						<th class="text-left font-weight-bold">Direccion del suministro</th>
						<td>{{$domicilio->suministro_del_domicilio}}</td>
						<th class="text-left font-weight-bold">Intervalo</th>
						<td>Desde {{$date_from}} hasta {{$date_to}}</td>
					</tr>
				</table><br><br>
			</div>
			{{-- Esta es la version antigua del codigo de arriba
			<h3 style="margin-bottom:10px;">Empresa: {{$user->name}}</h3>
			<p>
				@if(isset($domicilio->suministro_del_domicilio))
					{{$domicilio->suministro_del_domicilio}}
				@else
					sin ubicación
				@endif
			</p>
			<p>Contador: {{$contador_label}}</p>
			<p>Email: {{$user->email}}</p>
			<p>Intervalo: Desde {{$date_from}} hasta {{$date_to}}</p> --}}
		</div>
	</div>			
					
	<div class="d-none">
		<label>Ubicación: <label>
			@if(isset($domicilio->suministro_del_domicilio))
				{{$domicilio->suministro_del_domicilio}}
			@else
				sin ubicación
			@endif
		</label></label>
		<label>Intervalo: <label> Desde {{$date_from}} hasta {{$date_to}}</label></label><br>
	</div>

	<div class="row">
		<div class="column">
			{!! Form::open(['route' => ['calculo.comparador.ofertas', $user->id], 'method' => 'POST', 'id' => 'comparador', 'autocomplete' => 'off', 'class' => 'table-container']) !!}
				<input type="hidden" name="cont_aux" value="{{$cont}}">
					<table class="table-responsive table-striped column-header text-center" id="comparacion-table">
						<thead>
							<tr class="row-header">
								<th></th>
								<th colspan="2">CONTRATO ACTUAL</th>
								<th colspan="2">CONTRATO PROPUESTO</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td></td>
								<td>Precio Energía (€/kWh)</td>
								<td>Precio Potencia (€/kW·mes)</td>
								<td>Precio Energía (€/kWh)</td>
								<td>Precio Potencia (€/kW·mes)</td>
							</tr>
							@php $k = 1; @endphp
							@foreach($precio_energia as $energia_price)
								<tr>
									<td>P{{$k}}</td>
									<td>
										@if($precio_energia)
											<input type="text" class="text-center" readonly="true" name="actual_energia{{$k}}" value="{{number_format($energia_price->precio_energia,5,',','.')}}">
										@endif
									</td>
									<td>
										@if(isset($precio_potencia[0]))
											<input type="text" class="text-center" readonly="true" name="actual_potencia{{$k}}" value="{{number_format($precio_potencia[$k-1]->precio_potencia,5,',','.')}}">
										@endif
									</td>
									<td>
										{{Form::text('energia'.$k,number_format($precio_energia_propuesta[$k-1]->precio_energia_propuesta,'5',',','.'), ['class' => "text-center"])}}
									</td>
									<td>
										{{Form::text('potencia'.$k,number_format($precio_potencia_propuesta[$k-1]->precio_potencia_propuesta,'5',',','.'), ['class' => "text-center"])}}
									</td>
								</tr>
								@php $k++; @endphp
							@endforeach
						</tbody>
					</table>
				{!! Form::close() !!}
				<div class="btn-container reverse">
					{!! Form::submit('Calcular', array('class' => 'btn', 'form' => 'comparador')) !!}
				</div>
		</div>
	</div>

	<div class="row row-2">
		<div class="column">
			<h4 class="column-title">Término Energía</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>Coste Actual (€)</th>
							<th>Coste Propuesto (€)</th>
							<th>Diferencia (€)</th>
						</tr>
					</thead>
					<tbody>
						@php
							$i = 1;
							$data = \Session::get('total_e');
							$propuesto_energia = \Session::get('propuesto_e');
							$suma_actual_e = \Session::get('total_actual_energia');
							$suma_propuesto_e = \Session::get('total_propuesto_energia');
							$suma_total_e = \Session::get('suma_total_e');
						@endphp
						@if($tipo_tarifa == 1)
							@foreach($precio_energia as $precio_e)
								<tr>
									<td>
										{{$precio_e->eje}}
									</td>
									@if(!empty($coste_actual_energia[$i-1]->coste_energia))
										<td>
											{{number_format($coste_actual_energia[$i-1]->coste_energia,'2',',','.')}}
										</td>
									@else
										<td>
											{{number_format(0,'2',',','.')}}
										</td>
									@endif
									<td>
										@if($tipo_tarifa == 1)
											@if(isset($coste_actual_energia[$i-1]->coste_energia_propuesto))
												{{number_format($coste_actual_energia[$i-1]->coste_energia_propuesto,'2',',','.')}}
											@else
												{{number_format(0,'2',',','.')}}
											@endif
										@else
											{{number_format($coste_actual_energia[$i-1],'2',',','.')}}
										@endif
									</td>
									<td>
										@if($tipo_tarifa == 1)
											@if(isset($coste_actual_energia[$i-1]->diferencia))
												{{number_format($coste_actual_energia[$i-1]->diferencia,'2',',','.')}}
											@else
												{{number_format(0,'2',',','.')}}
											@endif
										@else
										@endif
									</td>
								</tr>
								@php $i++; @endphp
							@endforeach
						@else
							@php $i = 1; @endphp
							@foreach($precio_energia as $precio_e)
								<tr>
									<td>
										{{$precio_e->eje}}
									</td>
									@if(!empty($coste_actual_energia[$i-1]))
										<td>
											<input type="text" class="text-center" readonly value="{{number_format($coste_actual_energia[$i-1],'2',',','.')}}">
										</td>
									@else
										<td>
											<input type="text" class="text-center" readonly value="0">
										</td>
									@endif
									<td>
										<input readonly="" class="text-center" type="text" value="{{number_format($coste_propuesto_energia[$i-1],'2',',','.')}}">
									</td>
									<td>
										{{number_format($coste_actual_energia[$i-1]-$coste_propuesto_energia[$i-1],'2',',','.')}}
									</td>
								</tr>
								@php $i++; @endphp
							@endforeach
						@endif
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>TOTAL</th>
							<th>{{number_format($total_actual_energia,'0',',','.')}} €</th>
							<th>{{number_format($total_propuesto_energia,'0',',','.')}} €</th>
							<th>{{number_format($total_diferencia_energia,'0',',','.')}} €</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="column">
			<h4 class="column-title">Término Potencia</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Coste Actual (€)
							</th>
							<th>
								Coste Propuesto (€)
							</th>
							<th>
								Diferencia (€)
							</th>
						</tr>
					</thead>
					<tbody>
						@php
							$j = 1;
							$data = \Session::get('total_p');
							$propuesto_potencia = \Session::get('propuesto_p');
							$suma_actual_p = \Session::get('suma_actual_p');
							$suma_propuesto_p = \Session::get('suma_propuesto_p');
							$suma_total_p = \Session::get('suma_total_p');
						@endphp
						@if($tipo_tarifa == 1)
							@foreach($precio_energia as $precio_e)
								@if(!is_null($precio_e->eje))
									<tr>
										<td>
											{{$precio_e->eje}}
										</td>
										<td>
											@if(isset($coste_actual_potencia[$j - 1]->coste_potencia))
												{{number_format($coste_actual_potencia[$j - 1]->coste_potencia,'2',',','.')}}
											@else
												{{number_format(0,'2',',','.')}}
											@endif
										</td>
										<td>
											@if(isset($coste_actual_potencia[$j - 1]->coste_potencia_propuesto))
												{{number_format($coste_actual_potencia[$j - 1]->coste_potencia_propuesto,'2',',','.')}}
											@else
												{{number_format(0,'2',',','.')}}
											@endif
										</td>
										<td>
											@if($tipo_tarifa == 1)
												@if(isset($coste_actual_potencia[$j - 1]->diferencia))
													{{number_format($coste_actual_potencia[$j - 1]->diferencia,'2',',','.')}}
												@else
													{{number_format(0,'2',',','.')}}
												@endif
											@endif
										</td>
									</tr>
									@php $j++; @endphp
								@else
									@php $j++; @endphp
								@endif
							@endforeach
						@else
							@php $j = 1; @endphp
							@foreach($coste_actual_potencia as $precio_e)
								<tr>
									<td>P{{$j}}</td>
									<td>
										<input type="text" class="text-center" readonly value="{{number_format($precio_e,'2',',','.')}}">
									</td>
									<td>
										<input type="text" class="text-center" readonly="true" value="{{number_format($coste_propuesto_potencia[$j-1],'2',',','.')}}">
									</td>
									<td>
										@if($tipo_tarifa == 1)
											{{number_format($coste_actual_potencia[$j - 1]->diferencia,'2',',','.')}}
										@else
											{{number_format($precio_e - $coste_propuesto_potencia[$j-1],'2',',','.')}}
										@endif
									</td>
								</tr>
								@php $j++; @endphp
							@endforeach
						@endif
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>TOTAL</th>
							<th>{{number_format($total_actual_potencia,'0',',','.')}} €</th>
							<th>{{number_format($total_propuesto_potencia,'0',',','.')}} €</th>
							<th>{{number_format($total_diferencia_potencia,'0',',','.')}} €</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	@php
		$aux_cont = implode('_', explode(' ', $contador_label))
	@endphp

	<div class="row row-2 row-md">
		<div class="column">
			<div class="plot-tab graph shadow">
				<div id="TerminoEnergia_{{$aux_cont}}" class="plot-container"></div>
			</div>
		</div>
		<div class="column">
			<div class="plot-tab graph shadow">
				<div id="TerminoPotencia_{{$aux_cont}}" class="plot-container"></div>
			</div>
			<div class="btn-container reverse">
				<form action="{{route('comparador.ofertas.pdf', $user->id)}}" method="GET" >
					<input type="hidden" name="conta" value="{{$cont}}">
					<button class="btn" id="exportButton" type="submit"> Generar PDF</button>
				</form>
			</div>
		</div>
	</div>

	<div class="d-none">
		<div class="col-md-12 export-pdf" data-pdforder="1" style="margin-top:50px">
			<table class="table table-bordered table-striped" style="margin: 0 auto;">
				<thead>
					<tr>
						<th></th>
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
						<td></td>
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
					<?php $k = 1; ?>
					@foreach($precio_energia as $energia_price)
						<tr class="text-center">
							<td >
								P{{$k}}
							</td>
							<td>
								@if($precio_energia)
									{{ number_format($energia_price->precio_energia,5,',','.') }}
								@endif
							</td>
							<td>
								@if(isset($precio_potencia[0]))
									{{number_format($precio_potencia[$k-1]->precio_potencia,5,',','.')}}
								@endif
							</td>
							<td>
								{{number_format($precio_energia_propuesta[$k-1]->precio_energia_propuesta,'5',',','.')}}
							</td>
							<td>
								{{number_format($precio_potencia_propuesta[$k-1]->precio_potencia_propuesta,'5',',','.')}}
							</td>
						</tr>
						<?php $k++; ?>
					@endforeach
				</tbody>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="2">
			<h4 class="title-1 title-analisis">Término Energía</h4>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
						$suma_actual_e = \Session::get('total_actual_energia');
						$suma_propuesto_e = \Session::get('total_propuesto_energia');
						$suma_total_e = \Session::get('suma_total_e');
					?>
					@if($tipo_tarifa == 1)
						@foreach($precio_energia as $precio_e)
							<tr>
								<td class="text-center"  style="text-align: center;">
									{{$precio_e->eje}}
								</td>
								@if(!empty($coste_actual_energia[$i-1]->coste_energia))
									<td class="text-center"  style="text-align: center;">
										{{number_format($coste_actual_energia[$i-1]->coste_energia,'2',',','.')}}
									</td>
								@else
									<td class="text-center" style="text-align: center;">
										{{number_format(0,'2',',','.')}}
									</td>
								@endif
								<td class="text-center" style="text-align: center;">
									@if($tipo_tarifa == 1)
										@if(isset($coste_actual_energia[$i-1]->coste_energia_propuesto))
											{{number_format($coste_actual_energia[$i-1]->coste_energia_propuesto,'2',',','.')}}
										@else
											{{number_format(0,'2',',','.')}}
										@endif
									@else
										{{number_format($coste_actual_energia[$i-1],'2',',','.')}}
									@endif
								</td>
								<td class="text-center input-trans" style="text-align: center;">
									@if($tipo_tarifa == 1)
										@if(isset($coste_actual_energia[$i-1]->diferencia))
											{{number_format($coste_actual_energia[$i-1]->diferencia,'2',',','.')}}
										@else
											{{number_format(0,'2',',','.')}}
										@endif
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
										<input type="text" readonly class="input-trans" value="{{number_format($coste_actual_energia[$i-1],'2',',','.')}}" style="text-align: center;">
									</td>
								@else
									<td class="text-center">
										<input type="text" readonly class="input-trans" value="0" style="text-align: center;">
									</td>
								@endif
								<td class="text-center">
									<input readonly="" type="text" class="input-trans" value="{{number_format($coste_propuesto_energia[$i-1],'2',',','.')}}" style="text-align: center;">
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
					<tr>
						<th class="text-center">
							TOTAL
						</th>
						<th class="text-center">
							{{number_format($total_actual_energia,'0',',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($total_propuesto_energia,'0',',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($total_diferencia_energia,'0',',','.')}} €
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="3">
			<h4 class="title-1 title-analisis">Término Potencia</h4>
			<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
					@if($tipo_tarifa == 1)
						@foreach($precio_energia as $precio_e)
							@if(!is_null($precio_e->eje))
								<tr>
									<td class="text-center">
										{{$precio_e->eje}}
									</td>
									<td class="text-center" style="text-align: center;">
										@if(isset($coste_actual_potencia[$j - 1]->coste_potencia))
											{{number_format($coste_actual_potencia[$j - 1]->coste_potencia,'2',',','.')}}
										@else
											{{number_format(0,'2',',','.')}}
										@endif
									</td>
									<td class="text-center" style="text-align: center;">
										@if(isset($coste_actual_potencia[$j - 1]->coste_potencia_propuesto))
											{{number_format($coste_actual_potencia[$j - 1]->coste_potencia_propuesto,'2',',','.')}}
										@else
											{{number_format(0,'2',',','.')}}
										@endif
									</td>
									<td class="text-center" style="text-align: center;">
										@if($tipo_tarifa == 1)
											@if(isset($coste_actual_potencia[$j - 1]->diferencia))
												{{number_format($coste_actual_potencia[$j - 1]->diferencia,'2',',','.')}}
											@else
												{{number_format(0,'2',',','.')}}
											@endif
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
								<td class="text-center">
									<input type="text" readonly class="input-trans" value="{{number_format($precio_e,'2',',','.')}}" style="text-align: center;">
								</td>
								<td class="text-center">
									<input type="text" readonly="true" class="input-trans" value="{{number_format($coste_propuesto_potencia[$j-1],'2',',','.')}}" style="text-align: center;">
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
					<tr>
						<th class="text-center">
							TOTAL
						</th>
						<th class="text-center">
							{{number_format($total_actual_potencia,'0',',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($total_propuesto_potencia,'0',',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($total_diferencia_potencia,'0',',','.')}} €
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
				
	<form method="post" class="d-none" id="form-pdf" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$contador_label])}}">
		{{ csrf_field() }}
	</form>

@endsection

@section('modals')
  @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')
@include('Dashboard.includes.scripts_modal_interval')
@include('Dashboard.includes.script_intervalos')
<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
{{-- <script src="{{asset('js/jquery.slimscroll.min.js')}}"></script> --}}
<script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/screenfull.js')}}"></script>
{{-- <script src="{{asset('js/jquery.nicescroll.js')}}"></script> --}}
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
{{--<script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script>--}}
<script src="{{asset('js/skycons.js')}}"></script>
@include('Dashboard.includes.scripts_comparador_ofertas')
@endsection
