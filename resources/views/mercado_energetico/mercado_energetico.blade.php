@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 6])
@endsection

@section('content')
	<div class="d-none" >
		<div class="pdf-header">
			<div class="container" style="width:100%; display: inline-block">
				<div class="row">
					<div class="col">
						<img class="float-left" width="60" height="60" src="{{asset($dir_image_count)}}">
					</div>
					<div class="col">
						<h5 style="text-align: center;">Mercado Energetico<h5>
					</div>
					<div class="col">
					<img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
					</div>
				</div>
			</div>
			<div>
				<table class="table table-bordered" id="pdf_encabezado" >
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
				</table>
				<br>
				<br>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<div class="plot-tab graph shadow">
				<input type="hidden" class="plot-name" value='{{ $dataPlotting["total"]["name"] }}' />
				<input type="hidden" class="plot-labels" value='{!! $dataPlotting["total"]["labels"] !!}' />
				@foreach($dataPlotting["total"]["series"] as $serie)
					<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
					<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
					<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
					<input type="hidden" class="serie-aux_label" value='{{ $serie["aux_label"] }}' />
					<input type="hidden" class="serie-type" value='{{ $serie["type"] }}' />
				@endforeach
				<div id="graphTotal" class="plot-container" style="height: 330px; width: 100%;"></div>
			</div>
			<form id="export-csv" class="d-none" name="export-csv" action="{{route('export.csv.mercado')}}" method="POST">
				{{ csrf_field() }}
				<input type="hidden" name="date_from" value="{{$date_from}}">
				<input type="hidden" name="date_to" value="{{$date_to}}">
				<input type="hidden" name="contador_label" value="{{$contador_label}}">
				<input type="hidden" name="labelPeriodos" value="{{serialize($dataPlotting["total"]["labelPeriodos"])}}">
				<input type="hidden" name="dataOMIE" value="{{serialize($dataPlotting["total"]["dataOMIE"])}}">
				<input type="hidden" name="dataREE" value="{{serialize($dataPlotting["total"]["dataREE"])}}">
				<input type="hidden" name="dataCliente" value="{{serialize($dataPlotting["total"]["dataCliente"])}}">
			</form>
			<div class="btn-container">
				<!--//<button class="btn" type="submit" form="export-csv"> Exportar datos (CSV)</button>-->
				<button class="btn" id="exportButton"> Generar PDF</button>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<div class="card shadow">
				<div class="card__header">
					<div class="card__title">
						<button class="btn btn-info" data-submeter-toggle="collapse" data-target="#datosPeriodo">
							<i class="fa fa-plus"></i> Datos del Periodo
						</button>
					</div>
				</div>
				<div id="datosPeriodo" class="card__body d-none">
					<div class="table-container">
						<table class="table-responsive table-striped text-center">
							<thead>
								<tr class="row-header">
									<th>Periodo</th>
									<th>OMIE</th>
									<th>REE</th>
									<th>Cliente</th>
								</tr>
							</thead>
							<tbody>
								@foreach($dataPlotting["total"]["labelPeriodos"] as $index=>$periodo)
									<tr>
										<td>
											@if($label_intervalo == "Ayer" || $label_intervalo == "Hoy")
												{{sprintf("%02d", (int)$periodo-1).":00 - ".sprintf("%02d", (int)$periodo).":00"}}
											@else
												{{$periodo}}
											@endif
										</td>											
										<td>
											{{number_format($dataPlotting["total"]["dataOMIE"][$index] ,2,',','.')}} €/MWh
										</td>
										<td>
											{{number_format($dataPlotting["total"]["dataREE"][$index] ,2,',','.')}} €/MWh
										</td>
										<td>
											{{number_format($dataPlotting["total"]["dataCliente"][$index] ,2,',','.')}} €/MWh
										</td>
									</tr>
								@endforeach
								<tr class="row-highlight">
									<td>Precio Medio</td>
									@if(count(array_filter($dataPlotting["total"]["dataOMIE"])) == 0)
										<td> 0 €/MWh </td>
									@else
										<td>{{number_format(array_sum($dataPlotting["total"]["dataOMIE"])/count(array_filter($dataPlotting["total"]["dataOMIE"])),2,',','.')}} €/MWh</td>
									@endif
	
									@if(count(array_filter($dataPlotting["total"]["dataREE"])) == 0)
										<td> 0 €/MWh </td>
									@else
										<td>{{number_format(array_sum($dataPlotting["total"]["dataREE"])/count(array_filter($dataPlotting["total"]["dataREE"])),2,',','.')}} €/MWh</td>
									@endif
	
									@if(count(array_filter($dataPlotting["total"]["dataCliente"])) == 0)
										<td> 0 €/MWh </td>
									@else
										<td>{{number_format(array_sum($dataPlotting["total"]["dataCliente"])/count(array_filter($dataPlotting["total"]["dataCliente"])),2,',','.')}} €/MWh</td>
									@endif
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive table-striped text-center vertical-table">
					<thead>
						<tr class="row-header">
							<th></th>
							@foreach($vector_potencia as $potencia)
								<th>
									{{$potencia}}
								</th>
							@endforeach
							<th>
								Precio Medio
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>OMIE</td>
							@foreach($dataMercado["dataOmie"] as $coste)
								<td>
									{{number_format($coste ,2,',','.')}} €/MWh
								</td>
							@endforeach
							@if(count(array_filter($dataPlotting["total"]["dataOMIE"])) == 0)
								<td> 0 €/MWh </td>
							@else
								<td>{{number_format(array_sum($dataPlotting["total"]["dataOMIE"])/count(array_filter($dataPlotting["total"]["dataOMIE"])),2,',','.')}} €/MWh</td>
							@endif
						</tr>
						<tr>
							<td>ESIOS</td>
							@foreach($dataMercado["dataRee"] as $coste)
								<td>
									{{number_format($coste ,2,',','.')}} €/MWh
								</td>
							@endforeach
							@if(count(array_filter($dataPlotting["total"]["dataREE"])) == 0)
								<td> 0 €/MWh </td>
							@else
								<td>{{number_format(array_sum($dataPlotting["total"]["dataREE"])/count(array_filter($dataPlotting["total"]["dataREE"])),2,',','.')}} €/MWh</td>
							@endif
						</tr>
						<tr class="row-highlight">
							<td>Cliente</td>
							@foreach($dataMercado["dataCliente"] as $coste)
								<td>
									{{number_format($coste ,2,',','.')}} €/MWh
								</td>
							@endforeach
							@if(count(array_filter($dataPlotting["total"]["dataCliente"])) == 0)
								<td> 0 €/MWh </td>
							@else
								<td>{{number_format(array_sum($dataPlotting["total"]["dataCliente"])/count(array_filter($dataPlotting["total"]["dataCliente"])),2,',','.')}} €/MWh</td>
							@endif
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="row d-none">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive table-striped text-center">
					<thead>
						<tr class="row-header">
							<th></th>
							<th>
								OMIE
							</th>
							<th>
								ESIOS
							</th>
							<th>
								Cliente
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($vector_potencia as $index=>$potencia)
							<tr>
								<td>{{$potencia}}</td>
								<td>
									{{number_format($dataMercado["dataOmie"][$index] ,2,',','.')}} €/MWh
								</td>
								<td>
									{{number_format($dataMercado["dataRee"][$index] ,2,',','.')}} €/MWh
								</td>
								<td>
									{{number_format($dataMercado["dataCliente"][$index] ,2,',','.')}} €/MWh
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="row row-3 row-md">
		<div class="title">
			<h3>Análisis de coste según tipo de contrato</h3>
		</div>
		<div class="column">
			<div class="card--euro card-market shadow">
				<div class="card--euro__icon color-fff bg-success shadow">
					<i class="fa fa-euro-sign"></i>
				</div>
				<div class="card__header">
					<div class="card__title">Coste por energía consumida a precio indexado</div>
				</div>
				<div class="card__body">
					{{number_format($dataMercado["totalOmie"] ,2,',','.')}} €
				</div>
			</div>
		</div>
		<div class="column">
			<div class="card--euro card-market shadow">
				<div class="card--euro__icon color-fff bg-warning shadow">
					<i class="fa fa-euro-sign"></i>
				</div>
				<div class="card__header">
					<div class="card__title">Coste por energía consumida a precio fijo</div>
				</div>
				<div class="card__body">
					{{number_format($dataMercado["totalActiva"] ,2,',','.')}} €
				</div>
			</div>
		</div>
		<div class="column">
			<div class="card--euro card-market shadow">
				<div class="card--euro__icon color-fff bg-danger shadow">
					<i class="fa fa-euro-sign"></i>
				</div>
				<div class="card__header">
					<div class="card__title">Diferencia</div>
				</div>
				<div class="card__body">
					{{number_format($dataMercado["diferenciaEnergia"] ,2,',','.')}} €
				</div>
			</div>
		</div>
	</div>		

	<div class="d-none">
		<table class="table table-bordered table-striped table-light  export-pdf" data-pdforder="1">
			<thead class="bg-submeter-4">
					<tr>
						<th class="text-center text-white" style="vertical-align: middle;">
							Periodo
						</th>
						<th class="text-center text-white" style="vertical-align: middle;">
							OMIE
						</th>
						<th class="text-center text-white" style="vertical-align: middle;">
							REE
						</th>
						<th class="text-center text-white" style="vertical-align: middle;">
							Cliente
						</th>
					</tr>
			</thead>
			<tbody>
					@foreach($dataPlotting["total"]["labelPeriodos"] as $index=>$periodo)
					<tr>
						@if($label_intervalo == "Ayer" || $label_intervalo == "Hoy")
						<td class="font-weight-bold">{{sprintf("%02d", (int)$periodo-1).":00 - ".sprintf("%02d", (int)$periodo).":00"}}</td>
					@else
						<td class="font-weight-bold">{{$periodo}}</td>
					@endif
						<td class="text-center">
						{{number_format($dataPlotting["total"]["dataOMIE"][$index] ,2,',','.')}} €/MWh
					</td>
					<td class="text-center">
						{{number_format($dataPlotting["total"]["dataREE"][$index] ,2,',','.')}} €/MWh
					</td>
					<td class="text-center">
						{{number_format($dataPlotting["total"]["dataCliente"][$index] ,2,',','.')}} €/MWh
					</td>
					</tr>
					@endforeach
					<tr>
							<td class="font-weight-bold bg-submeter-1 text-white">Precio Medio</td>
							@if(count(array_filter($dataPlotting["total"]["dataOMIE"])) == 0)
								<td class="text-center bg-submeter-1 text-white font-weight-bold"> 0 €/MWh </td>
							@else
								<td class="text-center bg-submeter-1 text-white font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataOMIE"])/count(array_filter($dataPlotting["total"]["dataOMIE"])),2,',','.')}} €/MWh</td>
							@endif
							@if(count(array_filter($dataPlotting["total"]["dataREE"])) == 0)
								<td class="text-center bg-submeter-1 text-white font-weight-bold"> 0 €/MWh </td>
							@else
								<td class="text-center bg-submeter-1 text-white font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataREE"])/count(array_filter($dataPlotting["total"]["dataREE"])),2,',','.')}} €/MWh</td>
							@endif
							@if(count(array_filter($dataPlotting["total"]["dataCliente"])) == 0)
								<td class="text-center bg-submeter-1 text-white font-weight-bold"> 0 €/MWh </td>
							@else
								<td class="text-center bg-submeter-1 text-white font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataCliente"])/count(array_filter($dataPlotting["total"]["dataCliente"])),2,',','.')}} €/MWh</td>
							@endif
					</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-striped table-light  export-pdf" data-pdforder="3">
			<thead class="bg-submeter-4">
					<tr>
						<th>

						</th>
						@foreach($vector_potencia as $potencia)
							<th class="text-center text-white" style="vertical-align: middle;">
								{{$potencia}}
							</th>
						@endforeach
						<th class="text-center text-white" style="vertical-align: middle;">
							Precio Medio
						</th>
					</tr>
			</thead>
			<tbody>
				<tr>
					<td class="font-weight-bold">OMIE</td>
					@foreach($dataMercado["dataOmie"] as $coste)
						<td class="text-center">
							{{number_format($coste ,2,',','.')}} €/MWh
						</td>
					@endforeach
					@if(count(array_filter($dataPlotting["total"]["dataOMIE"])) == 0)
						<td class="text-center font-weight-bold"> 0 €/MWh </td>
					@else
						<td class="text-center font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataOMIE"])/count(array_filter($dataPlotting["total"]["dataOMIE"])),2,',','.')}} €/MWh</td>
					@endif
				</tr>
				<tr>
					<td class="font-weight-bold">ESIOS</td>
					@foreach($dataMercado["dataRee"] as $coste)
						<td class="text-center">
							{{number_format($coste ,2,',','.')}} €/MWh
						</td>
					@endforeach
					@if(count(array_filter($dataPlotting["total"]["dataREE"])) == 0)
						<td class="text-center font-weight-bold"> 0 €/MWh </td>
					@else
						<td class="text-center font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataREE"])/count(array_filter($dataPlotting["total"]["dataREE"])),2,',','.')}} €/MWh</td>
					@endif
				</tr>
				<tr>
					<td class="font-weight-bold bg-submeter-1 text-white">Cliente</td>
					@foreach($dataMercado["dataCliente"] as $coste)
						<td class="text-center bg-submeter-1 text-white">
							{{number_format($coste ,2,',','.')}} €/MWh
						</td>
					@endforeach
					@if(count(array_filter($dataPlotting["total"]["dataCliente"])) == 0)
						<td class="text-center bg-submeter-1 text-white font-weight-bold"> 0 €/MWh </td>
					@else
						<td class="text-center bg-submeter-1 text-white font-weight-bold">{{number_format(array_sum($dataPlotting["total"]["dataCliente"])/count(array_filter($dataPlotting["total"]["dataCliente"])),2,',','.')}} €/MWh</td>
					@endif
				</tr>
			</tbody>
		</table>
		<div class="export-pdf" data-pdforder="2" style="height:20px;"></div>
		<div class="export-pdf" data-pdforder="4">
			<div style="height:20px"></div>
			<table class="table table-bordered">
				<thead class="bg-submeter-4">
					<tr>
						<th class="text-white text-center " style="width: 33%;">Coste por energía consumida a precio indexado</th>
						<th class="text-white text-center " style="width: 33%;">Coste por energía consumida a precio fijo</th>
						<th class="text-white text-center " style="width: 33%;">Diferencia</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center">{{number_format($dataMercado["totalOmie"] ,2,',','.')}} €</td>
						<td class="text-center">{{number_format($dataMercado["totalActiva"] ,2,',','.')}} €</td>
						<td class="text-center">{{number_format($dataMercado["diferenciaEnergia"] ,2,',','.')}} €</td>
					</tr>
				</tbody>
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
@include('Dashboard.includes.script_intervalos')
@include('Dashboard.includes.scripts_modal_interval')
<script type="text/javascript">
	// const btnList = document.querySelectorAll(".btn.toggle")
	// btnList.forEach((btn) => {
	// 	btn.addEventListener("click", () => {
	// 		const targetId = btn.dataset.target
	// 		const target = document.querySelector(targetId)
	// 		// target.attributes.toggle("d-none")
	// 		target.hidden = !target.hidden
	// 	})
	// })
</script>
<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
<script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/screenfull.js')}}"></script>
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
	});
</script>
<script type="text/javascript">	
	$(document).ready(function(){
		createPlots();
		window.setTimeout(renderPlots, 1000);
		$(window).focus(function(){
			renderPlots();
		});
	});
	
	function renderPlots(){
		var plots = $(".plot-tab");
		for (var i = 0; i < plots.length; i++) {
			var plot = $(plots[i]);
			var chart = plot.data("chart");
			if(chart !== undefined){
				chart.render();
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
			var seriesType = plot.find(".serie-type");

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
							label: labels[j]
					};
					seriedata.push(d);
				}
				data[i] = seriedata;

				if(i == 0) {
					var tooltip = $(seriesAuxLabels[i]).val() +"{label} <br>{name}: {y} "+'\u20AC'+ '/MWh';
				}
				else {
					var tooltip = "{name}: {y} "+'\u20AC'+ '/MWh';
				}

				var conf = {
				cursor: "zoom-in",
				type: $(seriesType[i]).val(),
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
					titleFontSize: 12,
					titleFontColor: "#004165",
					lineColor: "#004165",
					labelFontColor: "#004165",
					interval: 1,
					tickColor: "#004165"
					},
				axisY: {
					title: '\u20AC' + "/MWh",
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
			if(iK == 0){
				chart.render();
				plot.data("chart_rendered", 1);
			}
		}
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
<script type="text/javascript">
	function anterior()
	{
		$('#before_navigation').val("-1");
		$("#form_navigation").submit();
	}
	function siguiente()
	{
		$('#before_navigation').val("1");
		$("#form_navigation").submit();
	}
	function volver()
	{
		$('#before_navigation').val("0");
	}
</script>
@endsection
