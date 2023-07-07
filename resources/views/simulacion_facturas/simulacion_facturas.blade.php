@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 9])
@endsection

@section('content')
	@if($user->tipo == 2)		
		<div class="hidden">
			<div class="pdf-header" style="margin-bottom:-50px;">
				<div class="container" style="width:100%; display: inline-block">
					<div class="row">
						<div class="col">
							<img class="float-left" width="60" height="60" src="{{asset($dir_image_count)}}">
						</div>
						<div class="col">
							<h5 style="text-align: center;">Simulacion de factura<h5>
						</div>
						<div class="col">
							<img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
						</div>
					</div>
				</div>
				<div>
					<table class="table table-bordered" id="pdf_encabezado" style="margin-top: 5px;">
						<tr>
							<th class="font-weight-bold ">Cliente</th>
							<td>{{$domicilio->denominacion_social}}</td>
							<th class="font-weight-bold">CIF</th>
							<td>{{$domicilio->CIF}}</td>
						</tr>
						<tr>
							<th class="font-weight-bold">Contador</th>
							<td>{{$contador_label}}</td>
							<th class="font-weight-bold ">CUPS</th>
							<td>{{$domicilio->CUPS}}</td>
						</tr>
						<tr>
							<th class="font-weight-bold">Direccion del suministro</th>
							<td>{{$domicilio->suministro_del_domicilio}}</td>
							<th class="font-weight-bold">Intervalo</th>
							<td>Desde {{$date_from}} hasta {{$date_to}}</td>
						</tr>
					</table><br><br>
				</div>
			</div>
		</div>
					
		<div class="row row-2">
			<div class="column">
				<div class="table-container no-shadow">
					<table class="table-responsive table-invoice-card table-transparent">
						<tr><td colspan="2"><h2>Datos del Cliente</h2></td></tr>
						<tr><td colspan="2"><strong>Cliente:</strong> {{$user->name}}</td></tr>
						<tr><td colspan="2"><strong>CIF:</strong> {{$domicilio->CIF}}</td></tr>
						@if(isset($domicilio->suministro_del_domicilio))
							<tr><td colspan="2"><strong>Dirección:</strong> {{$domicilio->suministro_del_domicilio}}</td></tr>
						@else
							<tr><td colspan="2"><strong>Dirección:</strong>Sin dirección</td></tr>
						@endif
						<tr><td colspan="2"><strong>CUP:</strong> @if(isset($domicilio->CUPS)){{$domicilio->CUPS}}@endif - <strong>Tarifa:</strong> @if(isset($domicilio->TARIFA)) {{$domicilio->TARIFA}}@endif</td></tr>
						{{-- <tr></tr> --}}
						<tr><td colspan="2"><strong>Empresa Distribuidora:</strong> {{$domicilio->distribuidora_empresa}}</td></tr>
						<tr><td colspan="2"><strong>Empresa Comercializadora:</strong> {{$domicilio->comercializadora_empresa}}</td></tr>
						{{-- <tr></tr> --}}
						<tr><td colspan="2"><strong>Intervalo:</strong> Desde {{$date_from}} Hasta: {{$date_to}}</td></tr>
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
									<td class="text-left">
										<label>Término Energía</label>
									</td>
									<td></td>
									<td>
										<label>{{number_format($total1+$total_,2,',','.')}} <i class="fa fa-eur"></i></label>
										@php
										array_push($Pie1,$total1+$total_)
										@endphp
									</td>
								</tr>
								@foreach($E_Activa as $it)
									<tr>
										@if($loop->first)
											<td rowspan="{{$loop->count}}">Energía Activa</td>
										@endif
										<td class="text-left">
											@if(isset($precio_energia[$loop->index%6]))
												{{$precio_energia[$loop->index%6]->Periodo}}: {{number_format($it->Activa*1,'0',',','.')}} kWh x {{number_format($precio_energia[$loop->index%6]->precio,'5',',','.')}} €/kWh = {{number_format($it->Activa*$precio_energia[$loop->index%6]->precio,'2',',','.')}} €
											@else
												P{{($loop->index%6)+1}}: {{number_format($it->Activa*1,'0',',','.')}} kWh x {{number_format(0,'5',',','.')}} €/kWh = {{number_format($it->Activa*0,'2',',','.')}} €
											@endif
										</td>
										@if($loop->first)
											<td rowspan="{{$loop->count}}">{{number_format($total1,'2',',','.')}} €</td>
										@endif
									</tr>
								@endforeach
								@foreach($coste_reactiva[0] as $it)
									<tr>
										@if($loop->first)
											<td  rowspan="{{$loop->count}}">Energía Reactiva</td>
										@endif
										<td class="text-left">
											@if(isset($precio_energia[$loop->index%6]))
												{{$precio_energia[$loop->index%6]->Periodo}}: {{number_format($it*1,'2',',','.')}} €
											@else
												P{{($loop->index%6)+1}}: {{number_format($it*0,'2',',','.')}} €
											@endif
										</td>
										@if($loop->first)
											<td rowspan="{{$loop->count}}">{{number_format($total_,2,',','.')}} €</td>
										@endif
									</tr>
								@endforeach
								<tr class="row-highlight">
									<td class="text-left">
										<label>Término Potencia</label>
									</td>
									<td></td>
									<td>
										<label>{{number_format($data_analisis["totalFP"],2,',','.')}} <i class="fa fa-eur"></i></label>
										@php
											array_push($Pie1,$data_analisis["totalFP"])
										@endphp
									</td>
								</tr>
								@if($tipo_tarifa == 1)
									@foreach($data_calculos["vector_potencia"] as $index => $potencia)
										<tr>
											@if($loop->first)
												<td rowspan="{{$loop->count}}">Potencia Contratada</td>
											@endif
											<td class="text-left">
												P{{$index + 1}}: {{number_format($potencia,'0',',','.')}} kW x {{number_format($data_analisis["costoDias"][$index],'5',',','.')}} €/kW = {{number_format($data_analisis['dataFPC'][$index + 1],'2',',','.')}} €
											</td>
											@if($loop->first)
												<td rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFC"],'2',',','.')}} €</td>
											@endif
										</tr>
									@endforeach
									@foreach($data_analisis["dataFPE"] as $index=>$exceso)
										<tr>
											@if($loop->first)
												<td rowspan="{{$loop->count}}">Excesos Potencia</td>
											@endif
											<td class="text-left">
												P{{$index}}: {{number_format($exceso,'2',',','.')}} €
											</td>
											@if($loop->first)
												<td rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFPE"],'2',',','.')}} €</td>
											@endif
										</tr>
									@endforeach
								@else
									@foreach($data_calculos["vector_potencia"] as $index => $potencia)
										<tr>
											@if($loop->first)
												<td rowspan="{{$loop->count}}"></td>
											@endif
											<td class="text-left">
												P{{$index + 1}}: {{number_format($potencia,'0',',','.')}} kW (P{{$index + 1}} max registrada {{number_format($data_analisis["dataFP_max"][$index],'0',',','.')}} kW) = {{number_format($data_analisis['dataFP'][$index + 1],'2',',','.')}} €
											</td>
											@if($loop->first)
												<td rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFP"],2,',','.')}} €</td>
											@endif
										</tr>
									@endforeach
								@endif
								<tr class="row-highlight">
									<td class="text-left">
										<b>I.E.E.</b>
									</td>
									<td></td>
									<td>
										<b>{{number_format($impuesto,'2',',','.')}} €</b>
										@php
											array_push($Pie1,$impuesto)
										@endphp
									</td>
								</tr>
								<tr>
									<td></td>
									<td class="text-left">
										IEE: {{number_format($sumatoria,'2',',','.')}} € x 5.11269632 % = {{number_format($impuesto,'2',',','.')}} €
									</td>
									<td></td>
								</tr>
								<tr class="row-highlight">
									<td class="text-left">
										<b>Equipo de Medida</b>
									</td>
									<td></td>
									<td>
										@if(empty($equipo))
											<b>{{number_format(0,'2',',','.')}} €</b>
											@php
												array_push($Pie1,0)
											@endphp
										@else
											<b>{{number_format($equipo[0]->valor*($diasDiferencia+1),'2',',','.')}} €</b>
											@php
												array_push($Pie1,$equipo[0]->valor*($diasDiferencia+1))
											@endphp
										@endif
									</td>
								</tr>
								<tr>
									<td></td>
									<td class="text-left">
										Alquiler Equipo Medida:  {{number_format($equipo[0]->valor,'2',',','.')}} €/dia x {{$diasDiferencia+1}} dias = {{number_format($equipo[0]->valor*($diasDiferencia+1),'2',',','.')}} €
									</td>
									<td></td>
								</tr>
								<tr class="row-highlight">
									<td class="text-left">
										<b>I.V.A. (21%)</b>
									</td>
									<td></td>
									<td>
										<b>{{number_format($IVA,'2',',','.')}} €</b>
										@php
											array_push($Pie1,$IVA)
										@endphp
									</td>
								</tr>
								<tr>
									<td></td>
									<td class="text-left">
										@if(empty($equipo))
											IVA: {{number_format(($sumatoria + $impuesto),'2',',','.')}} € x 21 % = {{number_format($IVA,'2',',','.')}} €
										@else
											IVA: {{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1))),'2',',','.')}} € x 21 % =  {{number_format($IVA,'2',',','.')}} €
										@endif
									</td>
									<td></td>
								</tr>
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th></th>
									<th class="text-right">
										<b>TOTAL</b>
									</th>
									<th>
										@if(empty($equipo))
											{{number_format(($sumatoria + $impuesto + $IVA),'2',',','.')}} €
										@else
											{{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1)) + $IVA),'2',',','.')}} €
										@endif
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="btn-container reverse">
						<button class="btn" id="exportButton"> Generar PDF</button>
					</div>
				</div>										
			</div>
		</div>
		
		<div class="d-none">
			<table class="table table-condensed table-responsive tabla1 table-analisis-comparacion export-pdf" data-pdforder="1" style="width: 70%; margin: 0px auto">
				<thead>
					<tr>
						<th  class="text-center">
							Concepto
						</th>
						<th  class="text-center">
							Cálculo
						</th>
						<th  class="text-center">
							Importe
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="text-left" style="background: #C1CCD3;">
						<td>
							<label style="color: #004165;">Término Energía</label>
						</td>
						<td></td>
						<td class="text-center">
							<label style="color: #004165;">{{number_format($total1+$total_,2,',','.')}} <i class="fa fa-eur"></i></label>
							@php
							array_push($Pie1,$total1+$total_)
							@endphp
						</td>
					</tr>
					@foreach($E_Activa as $it)
						<tr class="text-left">
							@if($loop->first)
								<td style="color: #004165; border-bottom: 1px solid #cccccc;" class="text-center"  rowspan="{{$loop->count}}">Energía Activa</td>
							@endif
							@if($loop->last)
								<td style="color: #004165; line-height: 150%; font-size: 0.8em; border-bottom: 1px solid #cccccc;">
							@else
								<td style="color: #004165; line-height: 150%; font-size: 0.8em;">
							@endif
							@if(isset($precio_energia[$loop->index%6]))
								{{$precio_energia[$loop->index%6]->Periodo}}: {{number_format($it->Activa*1,'0',',','.')}} kWh x {{number_format($precio_energia[$loop->index%6]->precio,'5',',','.')}} €/kWh = {{number_format($it->Activa*$precio_energia[$loop->index%6]->precio,'2',',','.')}} €
							@else
								P{{($loop->index%6)+1}}: {{number_format($it->Activa*1,'0',',','.')}} kWh x {{number_format(0,'5',',','.')}} €/kWh = {{number_format($it->Activa*0,'2',',','.')}} €
							@endif
							</td>
							@if($loop->first)
								<td style="color: #004165; font-size: 0.8em;" class="text-center" rowspan="{{$loop->count}}">{{number_format($total1,'2',',','.')}} €</td>
							@endif
						</tr>
					@endforeach
					@foreach($coste_reactiva[0] as $it)
						<tr class="text-left">
							@if($loop->first)
								<td style="color: #004165;" class="text-center"  rowspan="{{$loop->count}}">Energía Reactiva</td>
							@endif
							<td style="color: #004165; line-height: 150%; font-size: 0.8em;">
								@if(isset($precio_energia[$loop->index%6]))
									{{$precio_energia[$loop->index%6]->Periodo}}: {{number_format($it*1,'2',',','.')}} €
								@else
									P{{($loop->index%6)+1}}: {{number_format($it*0,'2',',','.')}} €
								@endif
							</td>
							@if($loop->first)
								<td style="color: #004165; font-size: 0.8em;" class="text-center" rowspan="{{$loop->count}}">{{number_format($total_,2,',','.')}} €</td>
							@endif
						</tr>
					@endforeach
					<tr class="text-left" style="background: #C1CCD3;">
						<td>
							<label style="color: #004165;">Término Potencia</label>
						</td>
						<td></td>
						<td class="text-center">
							<label style="color: #004165;">{{number_format($data_analisis["totalFP"],2,',','.')}} <i class="fa fa-eur"></i></label>
							@php
								array_push($Pie1,$data_analisis["totalFP"])
							@endphp
						</td>
					</tr>
					@if($tipo_tarifa == 1)
						@foreach($data_calculos["vector_potencia"] as $index => $potencia)
							<tr class="text-left">
								@if($loop->first)
									<td style="color: #004165; border-bottom: 1px solid #cccccc;" class="text-center" rowspan="{{$loop->count}}">Potencia Contratada</td>
								@endif
								@if($loop->last)
									<td style="color: #004165; line-height: 150%; font-size: 0.8em; border-bottom: 1px solid #cccccc;">
								@else
									<td style="color: #004165; line-height: 150%; font-size: 0.8em;">
								@endif
									P{{$index + 1}}: {{number_format($potencia,'0',',','.')}} kW x {{number_format($data_analisis["costoDias"][$index],'5',',','.')}} €/kW = {{number_format($data_analisis['dataFPC'][$index + 1],'2',',','.')}} €
								</td>
								@if($loop->first)
									<td style="color: #004165; font-size: 0.8em;" class="text-center" rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFC"],'2',',','.')}} €</td>
								@endif
							</tr>
						@endforeach
						@foreach($data_analisis["dataFPE"] as $index=>$exceso)
							<tr class="text-left">
								@if($loop->first)
									<td style="color: #004165;" class="text-center" rowspan="{{$loop->count}}">Excesos Potencia</td>
								@endif
								<td style="color: #004165; line-height: 150%; font-size: 0.8em;">
									P{{$index}}: {{number_format($exceso,'2',',','.')}} €
								</td>
								@if($loop->first)
									<td style="color: #004165; font-size: 0.8em;" class="text-center" rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFPE"],'2',',','.')}} €</td>
								@endif
							</tr>
						@endforeach
					@else
						@foreach($data_calculos["vector_potencia"] as $index => $potencia)
							<tr class="text-left">
								@if($loop->first)
									<td style="color: #004165;" class="text-center" rowspan="{{$loop->count}}"></td>
								@endif
								<td style="color: #004165; line-height: 150%; font-size: 0.8em;">
									P{{$index + 1}}: {{number_format($potencia,'0',',','.')}} kW (P{{$index + 1}} max registrada {{number_format($data_analisis["dataFP_max"][$index],'0',',','.')}} kW) = {{number_format($data_analisis['dataFP'][$index + 1],'2',',','.')}} €
								</td>
								@if($loop->first)
									<td style="color: #004165; font-size: 0.8em;" class="text-center" rowspan="{{$loop->count}}">{{number_format($data_analisis["totalFP"],2,',','.')}} €</td>
								@endif
							</tr>
						@endforeach
					@endif
					<tr class="text-left" style="background: #C1CCD3;">
						<td style="color: #004165;">
							<b>I.E.E.</b>
						</td>
						<td></td>
						<td style="color: #004165;" class="text-center">
							<b>{{number_format($impuesto,'2',',','.')}} €</b>
							@php
								array_push($Pie1,$impuesto)
							@endphp
						</td>
					</tr>
					<tr class="text-left">
						<td></td>
						<td style="color: #004165; font-size: 0.8em;">
							IEE: {{number_format($sumatoria,'2',',','.')}} € x 5.11269632 % = {{number_format($impuesto,'2',',','.')}} €
						</td>
						<td></td>
					</tr>
					<tr class="text-left" style="background: #C1CCD3;">
						<td style="color: #004165;">
							<b>Equipo de Medida</b>
						</td>
						<td></td>
						<td style="color: #004165;" class="text-center">
							@if(empty($equipo))
								<b>{{number_format(0,'2',',','.')}} €</b>
								@php
									array_push($Pie1,0)
								@endphp
							@else
								<b>{{number_format($equipo[0]->valor*($diasDiferencia+1),'2',',','.')}} €</b>
								@php
									array_push($Pie1,$equipo[0]->valor*($diasDiferencia+1))
								@endphp
							@endif
						</td>
					</tr>
					<tr class="text-left">
						<td></td>
						<td style="color: #004165; font-size: 0.8em;">
							Alquiler Equipo Medida:  {{number_format($equipo[0]->valor,'2',',','.')}} €/dia x {{$diasDiferencia+1}} dias = {{number_format($equipo[0]->valor*($diasDiferencia+1),'2',',','.')}} €
						</td>
						<td></td>
					</tr>
					<tr class="text-left" style="background: #C1CCD3;">
						<td style="color: #004165;">
							<b>I.V.A. (21%)</b>
						</td>
						<td></td>
						<td style="color: #004165;" class="text-center">
							<b>{{number_format($IVA,'2',',','.')}} €</b>
							@php
								array_push($Pie1,$IVA)
							@endphp
						</td>
					</tr>
					<tr class="text-center">
						<td></td>
						<td style="color: #004165; font-size: 0.8em;" class="text-left">
							@if(empty($equipo))
								IVA: {{number_format(($sumatoria + $impuesto),'2',',','.')}} € x 21 % = {{number_format($IVA,'2',',','.')}} €
							@else
								IVA: {{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1))),'2',',','.')}} € x 21 % =  {{number_format($IVA,'2',',','.')}} €
							@endif
						</td>
						<td></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th  class="text-center"></th>
						<th  class="text-right">
							<b>TOTAL</b>
						</th>
						<th  class="text-center" style="color: #fff;">
							@if(empty($equipo))
								{{number_format(($sumatoria + $impuesto + $IVA),'2',',','.')}} €
							@else
								{{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1)) + $IVA),'2',',','.')}} €
							@endif
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	@endif	


	<form class="d-none" method="post" id="form-pdf" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$contador_label])}}">
		{{ csrf_field() }}
	</form>
@endsection

@section('modals')
		@include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')
@include('Dashboard.includes.scripts_modal_interval')
@include('Dashboard.includes.script_intervalos')
<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
{{-- <script src="{{asset('js/jquery.slimscroll.min.js')}}"></script> --}}
<script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/screenfull.js')}}"></script>
<script src="{{asset('js/scripts.js')}}"></script>
{{-- <script src="{{asset('js/jquery.nicescroll.js')}}"></script> --}}
<script src="{{asset('js/bootstrap.min.js')}}"> </script>
<script src="{{asset('js/canvas.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
<script type="text/javascript">
	var Pie_label = ["Término Energía", "Término Potencia", "I.E.E.", "Equipo de Medida", "I.V.A. (21%)"];
	var Pie1 = @php echo json_encode($Pie1) @endphp;
	var datapoins1 = [];

	for (var i = 0; i < Pie_label.length; i++) {
		datapoins1.push({ label: Pie_label[i], y: parseFloat(Pie1[i])});
	}
	window.onload = function() {
		/*
		CanvasJS.addColorSet("blueShades",
			[//colorSet Array
				"#24437E",
				"#656565",
				"#426D9D",
				"#638CD4",
				"#B7B7B7",
				"#75ADE0",
				"#2F5AA6",
				"#4C78CA",
				"#507ACB",
				"#547DCB",
				"#A9A9A9"
			]);
		*/
		var chart1 = new CanvasJS.Chart("pie_factura", {
		//colorSet: "blueShades",
			animationEnabled: true,
			backgroundColor: "transparent",
			data: [{
				type: "pie",
				radius: 100,
				startAngle: 270,
				toolTipContent: "{label} - #percent%",
				indexLabel: "{label} - #percent%",
				indexLabelFontSize: 13,
				dataPoints: datapoins1
			}]
		});
		chart1.render();
	}

	$("#exportButton").click(function(){
		var idxBreak = "";
		var tokenInput = $("#form-pdf input[name='_token']")[0].outerHTML;
		$("#form-pdf").html("");
		$("#form-pdf").append(tokenInput);
		var header = $(".pdf-header")[0].outerHTML;
		var input = $("<input name='elements[]' type='hidden' value='"+btoa(unescape(encodeURIComponent(header)))+"' />");
		var type = $("<input name='type_elements[]' value='2' type='hidden' />");
		$("#form-pdf").append(input);
		$("#form-pdf").append(type);
		var objActive = $(".active.plot-tab .graph-1");
		var width = parseInt(objActive.width());
		var height = 350;
		var cntChart = $(".plot-tab");
		var handleCharts = [];
		var dataCharts = [];
		var idxElement = 1;
		var canvas = $("#pie_factura .canvasjs-chart-canvas").get(0);
		var data = canvas.toDataURL('image/png', 1.0);
		dataCharts.push(data);
		for(var i = 0; i < dataCharts.length; i++) {
			var input = $("<input name='elements[]' type='hidden' value='"+dataCharts[i]+"' />");
			var type = $("<input name='type_elements[]' value='1' type='hidden' />");
			$("#form-pdf").append(input);
			$("#form-pdf").append(type);
			if(idxBreak.indexOf("," + idxElement + ",") > 0) {
				var input = $("<input name='elements[]' type='hidden' value='break' />");
				var type = $("<input name='type_elements[]' value='3' type='hidden' />");
				$("#form-pdf").append(input);
				$("#form-pdf").append(type);
			}
			idxElement++;
		}

		var htmlData = $(".export-pdf");
		/*donde pone el uno i < 1;  <--- es el numero de ".export-pdf que hay htmlData.length no funcciona correctamente por eso lo he puesto manual*/
		var arrIdx = [];
		for(var i = 0; i < htmlData.length; i++) {
			var idxPdf = $(htmlData[i]).data("pdforder");
			arrIdx.push([parseInt(idxPdf), i]);
		}

		arrIdx.sort(function(left, right) {
			return left[0] < right[0] ? -1 : 1;
		});

		for(var i = 0; i < arrIdx.length; i++) {
			var idx = arrIdx[i][1];
			var input = $("<input name='elements[]' type='hidden' value='"+btoa(unescape(encodeURIComponent(htmlData[idx].outerHTML)))+"' />");
			var type = $("<input name='type_elements[]' value='2' type='hidden' />");
			$("#form-pdf").append(input);
			$("#form-pdf").append(type);
			var idxPDF = arrIdx[i][0];
			if(idxBreak.indexOf("," + idxPDF + ",") >= 0) {
				var input = $("<input name='elements[]' type='hidden' value='break' />");
				var type = $("<input name='type_elements[]' value='3' type='hidden' />");
				$("#form-pdf").append(input);
				$("#form-pdf").append(type);
			}
		}

		$("#form-pdf").submit();
		return false;
	});
</script>
{{-- <script>
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
</script> --}}
@endsection
