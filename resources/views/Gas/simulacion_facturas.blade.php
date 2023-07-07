@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 9])
@endsection

@section('content')
	@if($user->tipo == 2)
		<div class="row row-2">		
			<div class="column">
				<div class="table-container no-shadow">
					<table class="table-responsive table-invoice-card table-transparent">
						<tr><td><h2>Datos del Cliente</h2></td></tr>
						<tr><td><strong>Cliente:</strong> {{$user->name}}</td></tr>
						<tr><td><strong>CIF:</strong> {{$domicilio->CIF}}</td></tr>
						<tr>
							<td><strong>Direcci&oacute;n:</strong> 
								@if(isset($domicilio->suministro_del_domicilio))
									{{$domicilio->suministro_del_domicilio}}
								@else
									Sin direcci&oacute;n
								@endif
							</td>
						</tr>
						<tr><td><strong>CUP:</strong> @if(isset($domicilio->CUPS)){{$domicilio->CUPS}}@endif - <strong>Tarifa:</strong> @if(isset($domicilio->TARIFA)) {{$domicilio->TARIFA}}@endif</td></tr>
						<tr><td><strong>Empresa Distribuidora:</strong> {{$domicilio->distribuidora_empresa}}</td></tr>
						<tr><td><strong>Empresa Comercializadora:</strong> {{$domicilio->comercializadora_empresa}}</td></tr>
						<tr><td><strong>Intervalo:</strong> Desde {{$date_from}} Hasta: {{$date_to}}</td></tr>
					</table>
				</div>
			</div>
			<div class="column column-center">				
				<div class="graph no-shadow transparent">					
					<div id="pie_factura" style="height: 270px; width: 100%;"></div>					
				</div>
			</div>
		</div>

		<div class="row">
			<div class="column">
				<div class="wrapper-lg">
					<div class="table-container">
						<table class="table-responsive table-invoice-sim text-center">
							<thead>
								<tr class="row-header">
									<th>Concepto</th>
									<th>Cálculo</th>
									<th>Importe</th>
								</tr>
							</thead>
							<tbody>
								<tr class="row-highlight">
									<td colspan="2" class="text-left">Término Variable</td>
									<?php $total1 = 0; ?>
									@if(isset($consumo_GN_kWh[0]) && isset($precio_variable))
										<?php
											$total1 += $consumo_GN_kWh[0]->consumo*$precio_variable->Precio;
											?>
									@endif
									@if(isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
										<?php
											$total1 += $consumo_GN_kWh[0]->consumo*(-1)*$descuento_variable->Descuento;
										?>
									@endif
									<td>{{number_format($total1,2,',','.')}} €</td>
								</tr>
								<?php
									$i = 0;
								?>
								<tr>
									<td></td>
									<td colspan="2" class="text-left">
										@if(isset($consumo_GN_kWh[0]) && isset($precio_variable))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x {{number_format($precio_variable->Precio,5,',','.')}} €/kWh = {{number_format($consumo_GN_kWh[0]->consumo*$precio_variable->Precio,2,',','.')}} €
										@elseif(isset($consumo_GN_kWh[0]) && !isset($precio_variable))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x 0 €/kWh = 0 €
										@elseif(!isset($consumo_GN_kWh[0]) && isset($precio_variable))
											0 kWh x {{number_format($precio_variable->Precio,5,',','.')}} €/kWh = {{0*$precio_variable->Precio,2,',','.'}} €
										@else
											0 kWh x €/kWh = 0 €
										@endif <br>
										@if(isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x {{number_format($descuento_variable->Descuento*-1,5,',','.')}} €/kWh = {{number_format($consumo_GN_kWh[0]->consumo*$descuento_variable->Descuento*-1,2,',','.')}} €
										@elseif(isset($consumo_GN_kWh[0]) && !isset($descuento_variable->Descuento))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x 0,00000 €/kWh = 0,00 €
										@elseif(!isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
											0 kWh x {{number_format($descuento_variable->Precio,5,',','.')}} €/kWh = {{0*$precio_variable->Precio,2,',','.'}} €
										@else
											0 kWh x 0,00 €/kWh = 0 €
										@endif
										<?php
											$i++;
										?>
									</td>
								</tr>
								<tr class="row-highlight">
									<td colspan="2" class="text-left">Término Fijo</td>
									<?php
										$total2 = 0;
									?>
									@if(isset($coste_termino_fijo))
										<?php
											$total2 = $coste_termino_fijo->Precio;
											?>
									@endif
									<td>{{number_format($total2,2,',','.')}} €</td>
								</tr>
								<?php
									$i = 0;
								?>
								<tr>
									<td></td>
									<td colspan="2" class="text-left">
										@if(isset($consumo_GN_kWh_diario[0]) && isset($precio_fijo->Precio))
											{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día x {{number_format($precio_fijo->Precio,5,',','.')}} €/kWh = {{number_format($coste_precio_fijo->Precio,2,',','.')}} €
										@elseif(isset($consumo_GN_kWh_diario[0]) && !isset($precio_fijo->Precio))
											{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día x 0,00 €/kWh = 0,00 €
										@elseif(!isset($consumo_GN_kWh_diario[0]) && isset($precio_fijo->Precio))
											0 kWh/día x {{number_format($precio_fijo->Precio,5,',','.')}} €/kWh = {{0*$precio_fijo->Precio,2,',','.'}} €
										@else
											0 kWh/día x 0,00 €/kWh = 0,00 €
										@endif <br>
										@if(isset($consumo_GN_kWh_diario[0]) && isset($descuento->Descuento))
											{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día x {{number_format($descuento->Descuento*-1,5,',','.')}} €/kWh = {{number_format($coste_descuento_fijo->Descuento,2,',','.')}} €
										@elseif(isset($consumo_GN_kWh_diario[0]) && !isset($descuento->Descuento))
											{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día x 0 €/kWh = 0 €
										@elseif(!isset($consumo_GN_kWh_diario[0]) && isset($descuento->Descuento))
											0 kWh/día x {{number_format($descuento->Descuento,5,',','.')}} €/kWh = {{number_format(0,2,',','.')}} €
										@else
											0 kWh/día x 0,00000 €/kWh = 0,00 €
										@endif
									</td>
								</tr>
								<tr class="row-highlight">
									<td colspan="2" class="text-left">I.E.HC</td>
									<?php
										$total3 = 0;
									?>
									@if(isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
										<?php
											$total3 = $consumo_GN_kWh[0]->consumo*$I_E_HC->valor;
										?>
									@endif
									<td>{{number_format($total3,2,',','.')}} €</td>
								</tr>
								<?php
									$i = 0;
								?>
								<tr>
									<td></td>
									<td colspan="2" class="text-left">
										@if(isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x {{number_format($I_E_HC->valor,5,',','.')}} €/kWh = {{number_format($consumo_GN_kWh[0]->consumo*$I_E_HC->valor,2,',','.')}} €
										@elseif(!isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
											{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh x {{number_format(0,5,',','.')}} €/kWh = {{number_format($consumo_GN_kWh[0]->consumo*0,2,',','.')}} €
										@elseif(isset($I_E_HC->valor) && !isset($consumo_GN_kWh[0]->consumo))
											{{number_format(0,0,',','.')}} kWh x {{number_format($I_E_HC->valor,5,',','.')}} €/kWh = {{number_format(0*$I_E_HC->valor,2,',','.')}} €
										@else
											0 kWh x 0,00 €/kWh = 0,00 €
										@endif
									</td>
								</tr>
								<tr class="row-highlight">
									<td colspan="2" class="text-left">Equipo de Medida</td>
									<td>
										<?php
											$total4 = 0;
										?>
										@if(isset($equipo_medida->valor))
											<?php
												$total4 = $equipo_medida->valor*($diasDiferencia+1);
												?>
											{{number_format($total4,2,',','.')}} €
										@else
											0,00 €
										@endif
									</td>
								</tr>
								<tr>
									<td></td>
									<td colspan="2" class="text-left">Alquiler Equipo Medida: 
										<?php
											$total4 = 0;
										?>
										@if(isset($equipo_medida->valor))
											<?php
												$total4 = $equipo_medida->valor*($diasDiferencia+1);
												?>
											{{number_format($equipo_medida->valor,2,',','.')}} € x {{$diasDiferencia+1}} d&iacute;as = {{number_format($total4,2,',','.')}} €
										@else
											0,00 €
										@endif
									</td>
								</tr>
								<tr class="row-highlight">
									<td colspan="2" class="text-left">I.V.A.</td>
									<?php
										$total5 = ($total1+$total2+$total3+$total4)*0.21;
									?>
									<td>{{number_format(($total1+$total2+$total3+$total4)*0.21,2,',','.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td colspan="2" class="text-left">{{number_format(($total1+$total2+$total3+$total4),2,',','.')}} € x 21 %</td>
								</tr>
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th colspan="2" class="text-right">TOTAL</th>
									<th>{{number_format($total1+$total2+$total3+$total4+$total5,0,',','.')}} €</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="btn-container flex-row-reverse">
						<a href="{{route('simulacion.facturas.pdf', $user->id)}}" class="btn">Generar PDF</a>
					</div>
				</div>
			</div>
		</div>								
	@endif
		
@endsection

@section('scripts')
	<script src="{{asset('js/jquery.min.js')}}"></script>
	<script src="{{asset('js/bootstrap.min.js')}}"> </script>
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
	<script src="{{asset('js/pie-chart.js')}}"></script>
	{{-- <script>
			$(document).ready(function () {
					$('#demo-pie-1').pieChart({
							barColor: '#3bb2d0',
							trackColor: '#eee',
							lineCap: 'round',
							lineWidth: 8,
							onStep: function (from, to, percent) {
									$(this.element).find('.pie-value').text(Math.round(percent) + '%');
							}
					});

					$('#demo-pie-2').pieChart({
							barColor: '#fbb03b',
							trackColor: '#eee',
							lineCap: 'butt',
							lineWidth: 8,
							onStep: function (from, to, percent) {
									$(this.element).find('.pie-value').text(Math.round(percent) + '%');
							}
					});

					$('#demo-pie-3').pieChart({
							barColor: '#ed6498',
							trackColor: '#eee',
							lineCap: 'square',
							lineWidth: 8,
							onStep: function (from, to, percent) {
									$(this.element).find('.pie-value').text(Math.round(percent) + '%');
							}
					});               
			});
	</script> --}}
	<script>
		const chartDatapoints = [
			{ label: "Término Variable", y: {{ $total1 }} },
			{ label: "Término Fijo", y: {{ $total2 }} },
			{ label: "I.E.HC", y:{{ $total3 }}  },
			{ label: "Equipo de Medida", y: {{ $total4 }} },
			{ label: "I.V.A. (21%)", y: {{ $total5 }} }
		]

		window.onload = function() {
			const chart = new CanvasJS.Chart("pie_factura", {
				animationEnabled: true,
				backgroundColor: "transparent",
				data: [{
					type: "pie",
					radius: 100,
					startAngle: 270,
					toolTipContent: "{label} - #percent%",
					indexLabel: "{label} - #percent%",
					indexLabelFontSize: 13,
					dataPoints: chartDatapoints
				}]
			})
			chart.render();
		}
	</script>
@endsection