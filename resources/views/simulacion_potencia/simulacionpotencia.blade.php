@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 5])
@endsection

@section('content')

<div class="d-none">
	<div class="pdf-header">
		<div class="container" style="width:100%; display: inline-block">
			<div class="row">
				<div class="col">
					<img class="float-left" width="60px" height="60px" src="{{asset($dir_image_count)}}">
				</div>
				<div class="col">
					<h5 style="text-align: center;">Simulación de Potencia<h5>
				</div>
				<div class="col">
					<img class="float-right" width="60px" height="60px" src="{{asset('images/Logo_WEB_Submeter.png')}}">
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
	</div>
</div>		
				
<div class="row">
	<div class="column">
		<div class="tab-panel">
			<div class="tabs">
				<button data-tab-id="tab0" class="tab-link active">Total</button>				
				@foreach($arreglo_potencia as $index=>$potencia)
					<button data-tab-id="tab{{$index + 1}}" class="tab-link">{{$potencia["periodo"]}}</button>
				@endforeach
			</div>
			<div class="wrapper-tab-content">
				<div id="grafTotal" data-tab-id="tab0" class="tab-content active graph plot-tab">
					<input type="hidden" class="plot-name" value='{{ $dataPlotting["total"]["name"] }}' />
					<input type="hidden" class="plot-labels" value='{!! $dataPlotting["total"]["labels"] !!}' />
					<input type="hidden" class="plot-max" value='{{ $dataPlotting["total"]["max"] }}' />
					@foreach($dataPlotting["total"]["series"] as $serie)
						<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
						<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
						<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
						<input type="hidden" class="serie-aux_label" value='{{ $serie["aux_label"] }}' />
						<input type="hidden" class="serie-interval" value='{{ $serie["interval"] }}' />
					@endforeach
					<div class="col-md-12 graph-1">
						<div class="grid-1">
							<div id="graphTotal" class="plot-container"></div>
						</div>
					</div>					
				</div>
				@foreach($dataPlotting["periodos"] as $index=>$dataPeriodo)
					<div id="grafPotencia{{$index}}" data-tab-id="tab{{$index + 1}}" class="tab-content graph plot-tab">
						<input type="hidden" class="plot-name" value='{{ $dataPeriodo["name"] }}' />
						<input type="hidden" class="plot-labels" value='{!! $dataPeriodo["labels"] !!}' />
						<input type="hidden" class="plot-max" value='{{ $dataPeriodo["max"] }}' />
						@foreach($dataPeriodo["series"] as $serie)
							<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
							<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
							<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
							<input type="hidden" class="serie-aux_label" value='{{ $serie["aux_label"] }}' />
							<input type="hidden" class="serie-interval" value='{{ $serie["interval"] }}' />
						@endforeach
						<div class="col-md-12 graph-1">
							<div class="grid-1">
								<div id="graphPeriodo_{{$index}}" class="plot-container"></div>		
							</div>
						</div>				
					</div>
				@endforeach
			</div>
		</div>
		<div class="btn-container reverse">
			<button type="button" class="btn" id="exportButton"> GENERAR PDF</button>
		</div>
	</div>
</div>

@if($tipo_tarifa == 1)
	<div class="row row-2">
		<div class="column">
			<h4 class="column-title">Situación Actual</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($arreglo_potencia as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								POTENCIA<br> CONTRATADA
							</td>
							@foreach($arreglo_potencia as $potencia)
								<td>
									{{number_format($potencia["potencia"],0,',','.')}}
								</td>
							@endforeach
						</tr>
						<tr class="row-highlight">
							<td>
								POTENCIA<br> MAXIMA REGISTRADA
							</td>
							@foreach($data_analisis["dataFP_max"] as $potencia)
								<td>
									{{number_format($potencia,0,',','.')}}
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="column">
			<h4 class="column-title">Simulación Potencia</h4>
			<form method="POST" id="simulacion-potencia" class="d-none" action="{{route('simulacion.potencia.save')}}">
				{{ csrf_field() }}
				<input type="hidden" name="user" value="{{$user->id}}" />
				<input type="hidden" name="count" value="{{$contador2->id}}" />
			</form>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($arreglo_potencia_simulada as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>						
						<tr>
							<td>
								NUEVA<br> POTENCIA
							</td>
							@foreach($arreglo_potencia_simulada as $index=>$potencia)
								<td class="td-number">
									<input type="number" name="simulatedv[]" form="simulacion-potencia" value="{{round($potencia["potencia"], 0)}}" />
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-container reverse">
				<button type="submit" class="btn" form="simulacion-potencia">REALIZAR SIMULACIÓN</button>
			</div>
		</div>
	</div>

	{{-- <div class="d-none">
		<div class="column">
			<h4 class="column-title">Simulación Potencia</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($arreglo_potencia_simulada as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								NUEVA<br> POTENCIA
							</td>
							@foreach($arreglo_potencia_simulada as $index=>$potencia)
								<td class="td-number">
									{{round($potencia["potencia"], 0)}}
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>	 --}}

	<div class="row row-2">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Potencia Contratada
							</th>
							<th>
								Excesos Potencia
							</th>
							<th>
								Término Potencia
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($data_analisis["dataFP"] as $idx=>$data)
							<tr>
								<td>P{{$idx}}</td>
								<td>{{number_format($data_analisis["dataFPC"][$idx],2,',','.')}} €</td>
								<td>{{number_format($data_analisis["dataFPE"][$idx],2,',','.')}} €</td>
								<td>{{number_format($data_analisis["dataFP"][$idx],2,',','.')}} €</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>
								TOTAL
							</th>
							<th>
								{{number_format($data_analisis["totalFC"],0,',','.')}} €
							</th>
							<th>
								{{number_format($data_analisis["totalFPE"],0,',','.')}} €
							</th>
							<th>
								{{number_format($data_analisis["totalFP"],0,',','.')}} €
							</th>	
						</tr>												
					</tfoot>
				</table>
			</div>
		</div>	
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Potencia Contratada
							</th>
							<th>
								Excesos Potencia
							</th>
							<th>
								Término Potencia
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($data_analisis["dataFP_simulated"] as $idx=>$data)
							<tr>
								<td>P{{$idx}}</td>
								<td>{{number_format($data_analisis["dataFPC_simulated"][$idx],2,',','.')}} €</td>
								<td>{{number_format($data_analisis["dataFPE_simulated"][$idx],2,',','.')}} €</td>
								<td>{{number_format($data_analisis["dataFP_simulated"][$idx],2,',','.')}} €</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>
								TOTAL
							</th>
							<th>
								{{number_format($data_analisis["totalFC_simulated"],0,',','.')}} €
							</th>
							<th>
								{{number_format($data_analisis["totalFPE_simulated"],0,',','.')}} €
							</th>
							<th>
								{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Potencia Contratada
							</th>
							<th>
								Excesos Potencia
							</th>
							<th>
								Término Potencia
							</th>
							<th>
								AHORRO
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>SITUACIÓN ACTUAL</td>
							<td>{{number_format($data_analisis["totalFC"],0,',','.')}} €</td>
							<td>{{number_format($data_analisis["totalFPE"],0,',','.')}} €</td>
							<td>{{number_format($data_analisis["totalFP"],0,',','.')}} €</td>
							<td rowspan="2">{{number_format($data_analisis["totalFPDifference"],0,',','.')}} €</td>
						</tr>
						<tr>
							<td>SIMULACIÓN</td>
							<td>{{number_format($data_analisis["totalFC_simulated"],0,',','.')}} €</td>
							<td>{{number_format($data_analisis["totalFPE_simulated"],0,',','.')}} €</td>
							<td>{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="d-none">
		<div class="col-md-6 export-pdf" data-pdforder="1">
			<h4 class="title-1 title-analisis">Situación Actual</h4>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
						@foreach($arreglo_potencia as $potencia)
							<th class="text-center" style="vertical-align: middle;">
								{{$potencia["periodo"]}}<br> (kW)
							</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center" style="vertical-align: middle;">
							POTENCIA<br> CONTRATADA
						</td>
						@foreach($arreglo_potencia as $potencia)
							<td class="text-center" style="vertical-align: middle;">
								{{number_format($potencia["potencia"],0,',','.')}}
							</td>
						@endforeach
					</tr>
					<tr style="background:#7F7F7F;">
						<td class="text-center" style="vertical-align: middle;font-weight:bold;color:white;">
							POTENCIA<br> MAXIMA REGISTRADA
						</td>
						@foreach($data_analisis["dataFP_max"] as $potencia)
							<td class="text-center" style="vertical-align: middle;font-weight:bold;color:white;">
								{{number_format($potencia,0,',','.')}}
							</td>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="3">
			<h4 class="title-1 title-analisis">Simulación Potencia</h4>
			<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
						@foreach($arreglo_potencia_simulada as $potencia)
							<th class="text-center" style="vertical-align: middle;">
								{{$potencia["periodo"]}}<br> (kW)
							</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center" style="vertical-align: middle;">
							NUEVA<br> POTENCIA
						</td>
						@foreach($arreglo_potencia_simulada as $index=>$potencia)
							<td class="text-center" style="vertical-align: middle;">
								{{round($potencia["potencia"], 0)}}
							</td>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="2"><br><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
					@foreach($data_analisis["dataFP"] as $idx=>$data)
						<tr class="text-center" style="vertical-align: middle;">
							<td>P{{$idx}}</td>
							<td>{{number_format($data_analisis["dataFPC"][$idx],2,',','.')}} €</td>
							<td>{{number_format($data_analisis["dataFPE"][$idx],2,',','.')}} €</td>
							<td>{{number_format($data_analisis["dataFP"][$idx],2,',','.')}} €</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th>
							TOTAL
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFC"],0,',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($data_analisis["totalFPE"],0,',','.')}} €
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFP"],0,',','.')}} €
						</th>	
					</tr>												
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="4"><br><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
					@foreach($data_analisis["dataFP_simulated"] as $idx=>$data)
						<tr class="text-center" style="vertical-align: middle;">
							<td>P{{$idx}}</td>
							<td>{{number_format($data_analisis["dataFPC_simulated"][$idx],2,',','.')}} €</td>
							<td>{{number_format($data_analisis["dataFPE_simulated"][$idx],2,',','.')}} €</td>
							<td>{{number_format($data_analisis["dataFP_simulated"][$idx],2,',','.')}} €</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th>
							TOTAL
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFC_simulated"],0,',','.')}} €
						</th>
						<th class="text-center">
							{{number_format($data_analisis["totalFPE_simulated"],0,',','.')}} €
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="5"><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFC"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFPE"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFP"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($data_analisis["totalFPDifference"],0,',','.')}} €</td>
					</tr>
					<tr>
						<td class="text-center" style="vertical-align: middle;">SIMULACIÓN</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFC_simulated"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFPE_simulated"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	@if(count($data_analisis["months"]) > 1)
		@foreach($data_analisis["months"] as $index=>$data_month)		
			<div class="row row-2">
				<div class="title">
					<h3>{{$data_month["name"]}}</h3>
				</div>
				<div class="column">
					<div class="table-container">
						<table class="table-responsive text-center">
							<thead>
								<tr class="row-header">
									<th></th>
									<th>
										Potencia Contratada {{10 + 4*($index) + 1}}
									</th>
									<th>
										Excesos Potencia {{10 + 4*($index) + 2}}
									</th>
									<th>
										Término Potencia {{10 + 4*($index) + 3}}
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data_month["dataFP"] as $idx=>$data)
									<tr>
										<td>P{{$idx}}</td>
										<td>{{number_format($data_month["dataFPC"][$idx],2,',','.')}} €</td>
										<td>{{number_format($data_month["dataFPE"][$idx],2,',','.')}} €</td>
										<td>{{number_format($data_month["dataFP"][$idx],2,',','.')}} €</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th>
										TOTAL
									</th>
									<th>
										{{number_format($data_month["totalFPC"],0,',','.')}} €
									</th>
									<th>
										{{number_format($data_month["totalFPE"],0,',','.')}} €
									</th>
									<th>
										{{number_format($data_month["totalFP"],0,',','.')}} €
									</th>
								</tr>			
							</tfoot>
						</table>
					</div>
				</div>	
				<div class="column">
					<div class="table-container">
						<table class="table-responsive text-center">
							<thead>
								<tr class="row-header">
									<th></th>
									<th>
										Potencia Contratada
									</th>
									<th>
										Excesos Potencia
									</th>
									<th>
										Término Potencia
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data_month["dataFP_simulated"] as $idx=>$data)
									<tr>
										<td>P{{$idx}}</td>
										<td>{{number_format($data_month["dataFPC_simulated"][$idx],2,',','.')}} €</td>
										<td>{{number_format($data_month["dataFPE_simulated"][$idx],2,',','.')}} €</td>
										<td>{{number_format($data_month["dataFP_simulated"][$idx],2,',','.')}} €</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th>
										TOTAL
									</th>
									<th>
										{{number_format($data_month["totalFPC_simulated"],0,',','.')}} €
									</th>
									<th>
										{{number_format($data_month["totalFPE_simulated"],0,',','.')}} €
									</th>
									<th>
										{{number_format($data_month["totalFP_simulated"],0,',','.')}} €
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="column">
					<div class="table-container">
						<table class="table-responsive text-center">
							<thead>
								<tr class="row-header">
									<th></th>
									<th>
										Potencia Contratada
									</th>
									<th>
										Excesos Potencia
									</th>
									<th>
										Término Potencia
									</th>
									<th>
										AHORRO
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>SITUACIÓN ACTUAL</td>
									<td>{{number_format($data_month["totalFPC"],0,',','.')}} €</td>
									<td>{{number_format($data_month["totalFPE"],0,',','.')}} €</td>
									<td>{{number_format($data_month["totalFP"],0,',','.')}} €</td>
									<td rowspan="2">{{number_format($data_month["totalFP_difference"],0,',','.')}} €</td>
								</tr>
								<tr>
									<td>SIMULACIÓN</td>
									<td>{{number_format($data_month["totalFPC_simulated"],0,',','.')}} €</td>
									<td>{{number_format($data_month["totalFPE_simulated"],0,',','.')}} €</td>
									<td>{{number_format($data_month["totalFP_simulated"],0,',','.')}} €</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>	
			
			<div class="d-none">
				<div class="col-md-12 export-pdf" style="margin-top:20px; margin-bottom:10px;  data-pdforder="{{10 + 4*($index)}}"">
					<h3>{{$data_month["name"]}}</h3>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 1}}">
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
								<th class="text-center" style="vertical-align: middle;">
									Potencia Contratada {{10 + 4*($index) + 1}}
								</th>
								<th class="text-center">
									Excesos Potencia {{10 + 4*($index) + 2}}
								</th>
								<th class="text-center" style="vertical-align: middle;">
									Término Potencia {{10 + 4*($index) + 3}}
								</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data_month["dataFP"] as $idx=>$data)
								<tr class="text-center" style="vertical-align: middle;">
									<td>P{{$idx}}</td>
									<td>{{number_format($data_month["dataFPC"][$idx],2,',','.')}} €</td>
									<td>{{number_format($data_month["dataFPE"][$idx],2,',','.')}} €</td>
									<td>{{number_format($data_month["dataFP"][$idx],2,',','.')}} €</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th>
									TOTAL
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFPC"],0,',','.')}} €
								</th>
								<th class="text-center">
									{{number_format($data_month["totalFPE"],0,',','.')}} €
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFP"],0,',','.')}} €
								</th>
							</tr>			
						</tfoot>
					</table>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 2}}">
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
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
							@foreach($data_month["dataFP_simulated"] as $idx=>$data)
								<tr class="text-center" style="vertical-align: middle;">
									<td>P{{$idx}}</td>
									<td>{{number_format($data_month["dataFPC_simulated"][$idx],2,',','.')}} €</td>
									<td>{{number_format($data_month["dataFPE_simulated"][$idx],2,',','.')}} €</td>
									<td>{{number_format($data_month["dataFP_simulated"][$idx],2,',','.')}} €</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th>
									TOTAL
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFPC_simulated"],0,',','.')}} €
								</th>
								<th class="text-center">
									{{number_format($data_month["totalFPE_simulated"],0,',','.')}} €
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFP_simulated"],0,',','.')}} €
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 3}}"><br><br>
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
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
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFPC"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFPE"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFP"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($data_month["totalFP_difference"],0,',','.')}} €</td>
							</tr>
							<tr>
								<td class="text-center" style="vertical-align: middle;">SIMULACIÓN</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFPC_simulated"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFPE_simulated"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFP_simulated"],0,',','.')}} €</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endforeach
	@endif
@else
	{{-- <div class="d-none">
		<div class="column">
			<h4 class="column-title">Simulación Potencia</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($arreglo_potencia_simulada as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								NUEVA<br> POTENCIA
							</td>
							@foreach($arreglo_potencia_simulada as $index=>$potencia)
								<td class="td-number">
									{{round($potencia["potencia"], 0)}}
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div> --}}

	<div class="row row-2">
		<div class="column">
			<h4 class="column-title">Situación Actual</h4>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th>
							</th>
							@foreach($arreglo_potencia as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								POTENCIA<br> CONTRATADA
							</td>
							@foreach($arreglo_potencia as $potencia)
								<td>
										{{number_format($potencia["potencia"],0,',','.')}}
								</td>
							@endforeach
						</tr>
						<tr class="row-highlight">
							<td>
								POTENCIA<br> MAXIMA REGISTRADA
							</td>
							@foreach($data_analisis["dataFP_max"] as $potencia)
								<td>
									{{number_format($potencia,0,',','.')}}
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="column">
			<h4 class="column-title">Simulación Potencia</h4>
			<form method="POST" class="d-none" id="simulacion-potencia" action="{{route('simulacion.potencia.save')}}">
				{{ csrf_field() }}
				<input type="hidden" name="user" value="{{$user->id}}" />
				<input type="hidden" name="count" value="{{$contador2->id}}" />
			</form>
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($arreglo_potencia_simulada as $potencia)
								<th>
									{{$potencia["periodo"]}}<br> (kW)
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								NUEVA<br> POTENCIA
							</td>
							@foreach($arreglo_potencia_simulada as $index=>$potencia)
								<td class="td-number">
									<input type="number" name="simulatedv[]" form="simulacion-potencia" value="{{round($potencia["potencia"], 0)}}" />
								</td>
							@endforeach
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-container reverse">
				<button type="submit" form="simulacion-potencia" class="btn">REALIZAR SIMULACIÓN</button>
			</div>
		</div>
	</div>


	<div class="row row-2">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Término Potencia
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($data_analisis["dataFP"] as $idx=>$data)
							<tr>
								<td>P{{$idx}}</td>
								<td>{{number_format($data_analisis["dataFP"][$idx],2,',','.')}} €</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>
								TOTAL
							</th>
							<th>
								{{number_format($data_analisis["totalFP"],0,',','.')}} €
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>	
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Término Potencia
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($data_analisis["dataFP_simulated"] as $idx=>$data)
							<tr>
								<td>P{{$idx}}</td>
								<td>{{number_format($data_analisis["dataFP_simulated"][$idx],2,',','.')}} €</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr class="row-header">
							<th>
								TOTAL
							</th>
							<th>
								{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>	
	</div>

	<div class="row">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								Término Potencia
							</th>
							<th>
								AHORRO
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>SITUACIÓN ACTUAL</td>
							<td>{{number_format($data_analisis["totalFP"],0,',','.')}} €</td>
							<td rowspan="2">{{number_format($data_analisis["totalFPDifference"],0,',','.')}} €</td>
						</tr>
						<tr>
							<td>SIMULACIÓN</td>
							<td>{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="d-none">
		<div class="col-md-6 export-pdf" data-pdforder="1">
			<h4 class="title-1 title-analisis">Situación Actual</h4>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th>
						</th>
						@foreach($arreglo_potencia as $potencia)
							<th class="text-center" style="vertical-align: middle;">
								{{$potencia["periodo"]}}<br> (kW)
							</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center" style="vertical-align: middle;">
							POTENCIA<br> CONTRATADA
						</td>
						@foreach($arreglo_potencia as $potencia)
							<td class="text-center" style="vertical-align: middle;">
								 {{number_format($potencia["potencia"],0,',','.')}}
							</td>
						@endforeach
					</tr>
					<tr style="background:#7F7F7F;">
						<td class="text-center" style="vertical-align: middle;font-weight:bold;color:white;">
							POTENCIA<br> MAXIMA REGISTRADA
						</td>
						@foreach($data_analisis["dataFP_max"] as $potencia)
							<td class="text-center" style="vertical-align: middle;font-weight:bold;color:white;">
								{{number_format($potencia,0,',','.')}}
							</td>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="3">
			<h4 class="title-1 title-analisis">Simulación Potencia</h4>
			<table class="tabla1 table-analisis-comparacion table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
						@foreach($arreglo_potencia_simulada as $potencia)
							<th class="text-center" style="vertical-align: middle;">
								{{$potencia["periodo"]}}<br> (kW)
							</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center" style="vertical-align: middle;">
							NUEVA<br> POTENCIA
						</td>
						@foreach($arreglo_potencia_simulada as $index=>$potencia)
							<td class="text-center" style="vertical-align: middle;">
								{{round($potencia["potencia"], 0)}}
							</td>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="2"><br><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
						<th class="text-center" style="vertical-align: middle;">
							Término Potencia
						</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data_analisis["dataFP"] as $idx=>$data)
						<tr class="text-center" style="vertical-align: middle;">
							<td>P{{$idx}}</td>
							<td>{{number_format($data_analisis["dataFP"][$idx],2,',','.')}} €</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th>
							TOTAL
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFP"],0,',','.')}} €
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="4"><br><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
						<th class="text-center" style="vertical-align: middle;">
							Término Potencia
						</th>
					</tr>
				</thead>
				<tbody>
					@foreach($data_analisis["dataFP_simulated"] as $idx=>$data)
						<tr class="text-center" style="vertical-align: middle;">
							<td>P{{$idx}}</td>
							<td>{{number_format($data_analisis["dataFP_simulated"][$idx],2,',','.')}} €</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th>
							TOTAL
						</th>
						<th class="text-center" style="vertical-align: middle;">
							{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="5"><br><br>
			<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
				<thead>
					<tr>
						<th></th>
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
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFP"],0,',','.')}} €</td>
						<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($data_analisis["totalFPDifference"],0,',','.')}} €</td>
					</tr>
					<tr>
						<td class="text-center" style="vertical-align: middle;">SIMULACIÓN</td>
						<td class="text-center" style="vertical-align: middle;">{{number_format($data_analisis["totalFP_simulated"],0,',','.')}} €</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	@if(count($data_analisis["months"]) > 1)
		@foreach($data_analisis["months"] as $index=>$data_month)
			<div class="row row-2">
				<div class="title">
					<h3>{{$data_month["name"]}}</h3>
				</div>
				<div class="column">
					<div class="table-container">
						<table class="table-responsive text-center">
							<thead>
								<tr class="row-header">
									<th></th>
									<th>
										Término Potencia
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data_month["dataFP"] as $idx=>$data)
									<tr>
										<td>P{{$idx}}</td>
										<td>{{number_format($data_month["dataFP"][$idx],2,',','.')}} €</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th>
										TOTAL
									</th>
									<th>
										{{number_format($data_month["totalFP"],0,',','.')}} €
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div class="column">
					<div class="table-container">
						<table class="table-responsive text-center">
							<thead>
								<tr class="row-header">
									<th></th>
									<th>
										Término Potencia
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($data_month["dataFP_simulated"] as $idx=>$data)
									<tr>
										<td>P{{$idx}}</td>
										<td>{{number_format($data_month["dataFP_simulated"][$idx],2,',','.')}} €</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th>
										TOTAL
									</th>
									<th>
										{{number_format($data_month["totalFP_simulated"],0,',','.')}} €
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="column">
					<div class="table-container">
						<div class="table-responsive">
							<table class="table-responsive text-center">
								<thead>
									<tr class="row-header">
										<th></th>
										<th>
											Término Potencia
										</th>
										<th>
											AHORRO
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>SITUACIÓN ACTUAL</td>
										<td>{{number_format($data_month["totalFP"],0,',','.')}} €</td>
										<td rowspan="2">{{number_format($data_month["totalFP_difference"],0,',','.')}} €</td>
									</tr>
									<tr>
										<td>SIMULACIÓN</td>
										<td>{{number_format($data_month["totalFP_simulated"],0,',','.')}} €</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="d-none">
				<div class="col-md-12 export-pdf" style="margin-top:20px; margin-bottom:10px;" data-pdforder="{{10 + 4*($index)}}">
					<h3>{{$data_month["name"]}}</h3>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 1}}">
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
								<th class="text-center" style="vertical-align: middle;">
									Término Potencia
								</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data_month["dataFP"] as $idx=>$data)
								<tr class="text-center" style="vertical-align: middle;">
									<td>P{{$idx}}</td>
									<td>{{number_format($data_month["dataFP"][$idx],2,',','.')}} €</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th>
									TOTAL
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFP"],0,',','.')}} €
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 2}}">
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
								<th class="text-center" style="vertical-align: middle;">
									Término Potencia
								</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data_month["dataFP_simulated"] as $idx=>$data)
								<tr class="text-center" style="vertical-align: middle;">
									<td>P{{$idx}}</td>
									<td>{{number_format($data_month["dataFP_simulated"][$idx],2,',','.')}} €</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th>
									TOTAL
								</th>
								<th class="text-center" style="vertical-align: middle;">
									{{number_format($data_month["totalFP_simulated"],0,',','.')}} €
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="col-md-6 export-pdf" data-pdforder="{{10 + 4*($index) + 3}}"><br><br>
					<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive">
						<thead>
							<tr>
								<th></th>
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
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFP"],0,',','.')}} €</td>
								<td class="text-center" style="vertical-align: middle;" rowspan="2">{{number_format($data_month["totalFP_difference"],0,',','.')}} €</td>
							</tr>
							<tr>
								<td class="text-center" style="vertical-align: middle;">SIMULACIÓN</td>
								<td class="text-center" style="vertical-align: middle;">{{number_format($data_month["totalFP_simulated"],0,',','.')}} €</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endforeach
	@endif
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
<script>
	const tabPanels = Array.from(document.querySelectorAll(".tab-panel"))

	tabPanels.forEach((tabPanel) => {
		const tabs = Array.from(tabPanel.querySelectorAll(".tab-link"))
		let activeTab = tabPanel.querySelector(".tab-link.active")
		let activeTabContent = tabPanel.querySelector(".tab-content.active")

		tabs.forEach((tab) => {
			tab.addEventListener("click", () => {
				if (tab !== activeTab) {
					let tabId = tab.getAttribute("data-tab-id")
					let tabContent = tabPanel.querySelector(`div[data-tab-id="${tabId}"]`)

					activeTab.classList.remove("active")
					activeTabContent.classList.remove("active")

					tab.classList.add("active")
					tabContent.classList.add("active")

					activeTab = tab
					activeTabContent = tabContent
				}
			})
		})
	})
</script>
<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
<script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/screenfull.js')}}"></script>
<script src="{{asset('js/scripts.js')}}"></script>
<script src="{{asset('js/bootstrap.min.js')}}"> </script>
<script src="{{asset('js/canvas.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
	var intervalo = @php echo json_encode($label_intervalo); @endphp;
	var namesMonth = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

	$(document).ready(function(){
		createPlots();
		$(".tab-link").click(renderTabView);

		const tabPanels = Array.from(document.querySelectorAll(".tab-panel"))
		tabPanels.forEach((tab) => {
			let firstTab = tab.querySelector('.tab-link')
			firstTab.click()
			console.log(firstTab);
		})
	});

	function renderTabView(event){
		// var href = $(this).attr("href");
		// var cnt = $(href);
		let tabId = $(this).attr("data-tab-id");
		cnt = $('div[data-tab-id=' + tabId +']')

		if(cnt.length > 0){
			chart = cnt.data("chart");
			if(chart != undefined) {
				var rendered = cnt.data("chart_rendered");
				if(!rendered) {
					window.setTimeout(function(){chart.render();chart.render();}, 600);
				}
			}
		}
	}

	function togglePlotsDataSeries(e) {
		if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible ){
			e.dataSeries.visible = false;
		} else {
			e.dataSeries.visible = true;
		}
		var cnt = $(e.chart.container).closest(".plot-tab");
		if(cnt.length > 0) {
			chart = cnt.data("chart");
			if(chart != undefined) {
				chart.render();
			}
		}
	}

	CanvasJS.addCultureInfo("es", {
		decimalSeparator: ",",// Observe ToolTip Number Format
		digitGroupSeparator: "."
	});

	function createPlots(){
		var plots = $(".plot-tab");

		for(var iK = 0; iK < plots.length; iK++) {
			var plot = $(plots[iK]);
			var cntPlot = plot.find(".plot-container");
			var labels = $.parseJSON(plot.find(".plot-labels").val());
			var seriesValues = plot.find(".serie-value");
			var seriesColors = plot.find(".serie-color");
			var seriesName = plot.find(".serie-name");
			var seriesAuxLabels = plot.find(".serie-aux_label");
			var seriesInterval = plot.find(".serie-interval");
			//var aux_interval = $(seriesInterval[0]).val();
			//aux_interval = ($.isNumeric(aux_interval))? parseInt(aux_interval):"";
			var labels2 = [];
			var aux_interval = null;
			var tick = 1;

			if(intervalo == "Hoy" || intervalo == "Ayer"){
				labels2 = labels;
			}else if(intervalo == "Semana Actual" || intervalo == "Semana Anterior"){
				aux_interval = 48;
				for(var l = 0; l < labels.length; l++){
					var h=0;
					labels2[l] = null;
					for(var p = 0; p < labels.length/96; p++){
						h = 48+96*p;
						if(l == h){
							labels2[l] = labels[l].substr(0, labels[l].length - 5);;
						}
					}
				}
			}else if(intervalo == "Mes Actual" || intervalo == "Mes Anterior"){
				aux_interval = 48;
				for(var l = 0; l < labels.length; l++){
					var h=0;
					labels2[l] = null;
					for(var p = 0; p < labels.length/96; p++){
						h = 48+96*p;
						if(l == h){
							labels2[l] = labels[l].substr(0, labels[l].length - 5);;
						}
					}
				}
			}else if(intervalo == "Ultimo Trimestre" || intervalo == "Trimestre Actual"){
				aux_interval = 15;
				tick = 0;
				for(var l = 0; l < labels.length; l++){
					var h=0;
					labels2[l] = null;
					for(var p = 0; p < 3; p++){
						h = parseInt(15+30*p);
						if(l == h){
							labels2[l] = new Date(labels[l]);
							h = parseInt(labels2[l].getMonth());
							labels2[l] = namesMonth[h];
						}
					}
				}
			}else if(intervalo == "Último Año" || intervalo == "Año Actual"){
				aux_interval = 15;
				tick = 0;
				for(var l = 0; l < labels.length; l++){
					var h=0;
					labels2[l] = null;
					for(var p = 0; p < 12; p++){
						h = parseInt(15+30*p);
						if(l == h){
							labels2[l] = new Date(labels[l]);
							h = parseInt(labels2[l].getMonth());
							labels2[l] = namesMonth[h];
						}
					}
				}
			}else{
				labels2 = labels;
			}

			var data = new Array();
			var dataPlot = new Array();
			for(var i = 0; i < seriesValues.length; i++) {
				var serieVal = $(seriesValues[i]).val();
				serieVal = $.parseJSON(serieVal);
				var seriedata = new Array();
				for(j = 0; j < serieVal.length; j++) {
					var d = {
						y : serieVal[j],
						x : j,
						z: labels[j],
						label: labels2[j]
					};
					seriedata.push(d);
				}
				data[i] = seriedata;

				if(i == 0) {
					var tooltip = $(seriesAuxLabels[i]).val() +"{z} <br>{name}: {y}  kW"
				} else {
					var tooltip = "{name}: {y}  kW";
				}

				var conf = {
					type: "stepLine",
					showInLegend: true,
					visible: true,
					bevelEnabled: true,
					markerSize: 0,
					name: $(seriesName[i]).val(),
					legendColor: $(seriesColors[i]).val(),
					lineColor: $(seriesColors[i]).val(),
					color: $(seriesColors[i]).val(),
					legendMarkerColor: $(seriesColors[i]).val(),
					toolTipContent: tooltip,
					dataPoints: data[i]
				};
				dataPlot.push(conf);
			}

			var titulo = plot.find(".plot-name").val();  
			var conta = "{{ $contador2->count_label }}";
			var date_to = "{{ $date_to }}";
			var date_from ="{{ $date_from }}";
			var chart = new CanvasJS.Chart(cntPlot.attr("id"), {
				animationEnabled: false,
				culture: "es",
				theme: "light2",
				title:{
					text: plot.find(".plot-name").val(),
					fontSize: 18,
					margin: 50,
					fontColor: "#004165"
				},
				exportFileName: titulo+"-"+conta+"-"+date_from+"-"+date_to,
				exportEnabled: true,
				axisX: {
					tickThickness: tick,
					valueFormatString: " ",
					title: 'Máxima Potencia Demandada: '+plot.find(".plot-max").val()+' kW',
					titleFontSize: 12,
					titleFontColor: "#004165",
					lineColor: "#004165",
					labelFontColor: "#004165",
					interval: aux_interval,
					tickColor: "#004165"
				},
				axisY: {
					suffix: " kW",
					titleFontColor: "#004165",
					lineColor: "#004165",
					labelFontColor: "#004165",
					tickColor: "#004165"
				},
				toolTip: {
					shared: "true"
				},
				legend:{
					cursor:"pointer",
					itemclick : togglePlotsDataSeries
				},
				data: dataPlot
			});
			plot.data("chart", chart);
			plot.data("chart_rendered", 0);
			// if(iK == 0){
			// 	chart.render();
			// 	plot.data("chart_rendered", 1);
			// }
		}
	}

	var empresa = "{{$user->name}}";
	var email = "{{$user->email}}";
	var date_from = "{{$date_from}}";
	var date_to = "{{$date_to}}";
	var conta = "{{$contador_label}}";
	//console.log(dataURL);


	$("#exportButton").click(function(){
		@if($tipo_tarifa == 1)
			var idxBreak = ",29,37,45,53,";
		@else
			var idxBreak = "";
		@endif
		var tokenInput = $("#form-pdf input[name='_token']")[0].outerHTML;
		$("#form-pdf").html("");
		$("#form-pdf").append(tokenInput);
		var header = $(".pdf-header")[0].outerHTML;
		var input = $("<input name='elements[]' type='hidden' value='"+btoa(unescape(encodeURIComponent(header)))+"' />");
		var type = $("<input name='type_elements[]' value='2' type='hidden' />");
		$("#form-pdf").append(input);
		$("#form-pdf").append(type);
		var objActive = $(".active.plot-tab .graph-1");
		console.log(objActive);
		var width = parseInt(objActive.width());
		var height = 350;

		var cntChart = $(".plot-tab");
		var handleCharts = [];
		var dataCharts = [];

		var idxElement = 1;

		for(var i = 0; i < cntChart.length; i++){
			var chart = $(cntChart[i]).data("chart");
			chart.options.width = width;
			chart.options.height = height;
			chart.render();
			handleCharts.push(chart);

			var canvas = $(cntChart[i]).find("canvas")[0];
			var data = canvas.toDataURL('image/jpeg', 1.0);
			dataCharts.push(data);
		}

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
		$("#form_navegation").submit();
	}
	function siguiente()
	{
		$('#before_navigation').val("1");
		$("#form_navegation").submit();
	}
	function volver()
	{
		$('#before_navigation').val("0");
	}
</script>
{{-- <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script> --}}
<script src="{{asset('js/skycons.js')}}"></script>
@endsection
