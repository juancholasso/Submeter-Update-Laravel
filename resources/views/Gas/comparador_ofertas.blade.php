@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 8])
@endsection

@section('content')
	@if($user->tipo == 2)
		<div class="row">
			<div class="column">
				<form class="hidden" action="{{route('calculo.comparador.ofertas', $user->id)}}" id="comparador" method="post" autocomplete="off" accept-charset="UTF-8">
					<input type="hidden" name="date_from" value="{{$date_from}}">
					<input type="hidden" name="date_to" value="{{$date_to}}">
				</form>

				<div class="table-container">
					<table class="table-responsive table-striped text-center">
						<thead>
							<tr class="row-header">
								<th colspan="2">CONTRATO ACTUAL</th>
								<th colspan="2">CONTRATO PROPUESTO</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Precio T. Variable €/kWh</td>
								<td>Precio T. Fijo €/kWh</td>
								<td>Precio T. Variable €/kWh</td>
								<td>Precio T. Fijo €/kWh</td>
							</tr>
							<tr>
								<td>{{number_format($precio_variable->Precio,5,',','.')}} €/kWh</td>
								<td>{{number_format($precio_fijo->Precio,5,',','.')}} €/kWh</td>
								<td><input type="text" class="form-control text-center" form="comparador" name="precio_variable_propuesto" value="{{number_format($precio_variable->Precio_propuesto,5,',','.')}}"></td>
								<td><input type="text" class="form-control text-center" form="comparador" name="precio_fijo_propuesto" value="{{number_format($precio_fijo->Precio_propuesto,5,',','.')}}"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="btn-container flex-row-reverse">
					<button type="submit" class="btn" form="comparador">Calcular</button>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="column col-50">
				<h3 class="column-title">T&eacute;rmino Variable</h3>
				<div class="table-container">
					<table class="table-responsive text-center">
						<thead>
							<tr class="row-header">
								<th>Coste Actual (€)</th>
								<th>Coste Propuesto (€)</th>
								<th>Diferencia (€)</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{number_format($coste_termino_variable->coste,'2',',','.')}} €</td>
								<td>{{number_format($coste_termino_variable_propuesto->coste,'2',',','.')}} €</td>
								<td>{{number_format($coste_termino_variable->coste-$coste_termino_variable_propuesto->coste,'2',',','.')}} €</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="column col-50">
				<h3 class="column-title">T&eacute;rmino Fijo</h3>
				<div class="table-container">
					<table class="table-responsive text-center">
						<thead>
							<tr class="row-header">
								<th>Coste Actual (€)</th>
								<th>Coste Propuesto (€)</th>
								<th>Diferencia (€)</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{number_format($coste_termino_fijo->coste,'2',',','.')}} €</td>
								<td>{{number_format($coste_termino_fijo_propuesto->coste,'2',',','.')}} €</td>
								<td>{{number_format($coste_termino_fijo->coste-$coste_termino_fijo_propuesto->coste,'2',',','.')}} €</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		@php
			$aux_cont = implode('_', explode(' ', $contador2->count_label));
		@endphp
		<div class="row">
			<div class="column">
				<div class="graph shadow plot-tab">
					@include('Dashboard.Graficas.comparador_ofertas', ['id_var' => 'Comparativa_'.$aux_cont])
				</div>
				<div class="btn-container flex-row-reverse">
					<button class="btn" id="exportButton">Generar PDF</button>
				</div>
			</div>
		</div>
	@endif
			
	<form class="hidden" method="post" id="form-pdf" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$contador_label])}}">
		{{ csrf_field() }}
	</form>
@endsection

@section('modals')
  @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')	
@include('Dashboard.includes.scripts_modal_interval')
@include('Dashboard.includes.script_intervalos')
<script src="{{asset('js/jquery.min.js')}}"> </script>
<script src="{{asset('js/bootstrap.min.js')}}"> </script>
<script src="{{asset('js/canvas.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@include('Dashboard.includes.comparador_ofertas_gas')
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
@endsection
