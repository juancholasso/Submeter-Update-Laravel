@extends('Dashboard.layouts.global5')

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 7])
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
						<h5 style="text-align: center;">Seguimiento de Objetivos y Consumo<h5>
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
						<td>{{$current_count->count_label}}</td>
						<th class="text-left font-weight-bold ">CUPS</th>
						<td>{{$domicilio->CUPS}}</td>
					</tr>
					<tr>
						<th class="text-left font-weight-bold">Direccion del suministro</th>
						<td>{{$domicilio->suministro_del_domicilio}}</td>
						<th class="text-left font-weight-bold">Intervalo</th>
						<td>Desde {{$datesInfo[0]["date_from"]}} hasta {{$datesInfo[0]["date_to"]}} y desde {{$datesInfo[1]["date_from"]}} hasta {{$datesInfo[1]["date_to"]}}</td>
					</tr>
				</table><br><br>
			</div>
		</div>
	</div>

	<div class="row row-3 row-lg">	
		<div class="column text-center">
			<form method="POST" action="{{route('seguimiento.objetivos.change')}}">
				{{ csrf_field() }}
				<input type="hidden" name="date_from" value="{{$datesInfo[0]['date_from']}}"/>
				<input type="hidden" name="date_to" value="{{$datesInfo[0]['date_to']}}"/>
				<input type="hidden" name="interval_change" value=""/>
				<input type="hidden" name="type_date" value="0"/>
				<input type="hidden" name="user" value="{{$user->id}}"/>
				<input type="hidden" name="count" value="{{$current_count->id}}"/>
				<input type="hidden" name="period_type" value="{{$period_type}}"/>

				<div class="cnt-toggle btn-container">
					<button type="button" class="btn btn-primary btn-navigation" data-navigation="-1">Ant.</button>
					<button type="button" class="btn btn-link btn-toggle">
						<span class="fa fa-pencil"></span>
						{{$datesInfo[0]["label_period"]}}
					</button>
					<button type="button" class="btn btn-primary btn-navigation" data-navigation="1">Sig.</button>
				</div>

				<div class="cnt-toggle btn-container" style="display:none;">
					<input type="text" name="date_select" class="date-week" placeholder="Semana">
					<button type="button" class="btn btn-success btn-search-date"><span class="fa fa-check"></span></button>
					<button type="button" class="btn btn-danger btn-toggle"><span class="fa fa-times"></span></button>
				</div>
				<h5 class="column-title"><strong>Intervalo: </strong> Desde {{$datesInfo[0]["date_from"]}} hasta {{$datesInfo[0]["date_to"]}}</h5>				
			</form>
		</div>

		<div class="column text-center">	
			<form method="POST" action="{{route('seguimiento.objetivos.period')}}">
				{{ csrf_field() }}
				<input type="hidden" name="dates_from[]" value="{{$datesInfo[0]['date_from']}}"/>
				<input type="hidden" name="dates_to[]" value="{{$datesInfo[0]['date_to']}}"/>
				<input type="hidden" name="dates_from[]" value="{{$datesInfo[1]['date_from']}}"/>
				<input type="hidden" name="dates_to[]" value="{{$datesInfo[1]['date_to']}}"/>
				<input type="hidden" name="user" value="{{$user->id}}"/>
				<input type="hidden" name="count" value="{{$current_count->id}}"/>
				<div class="cnt-toggle btn-container">
					<button class="btn btn-primary btn-toggle" type="button"><span class="fa fa-pencil"></span> {{$label_interval}}</button>
				</div>
				<div class="cnt-toggle btn-container" style="display:none;">
					<select name="period_type">
						@foreach($periodTypes as $index=>$type)
							<option value="{{$index}}">{{$type}}</option>
						@endforeach
					</select>
					<div class="btn-container">
						<button type="submit" class="btn btn-success"><span class="fa fa-check"></span></button>
						<button type="button" class="btn btn-danger btn-toggle"><span class="fa fa-times"></span></button>
					</div>
				</div>
			</form>
		</div>

		<div class="column text-center">
			<form method="POST" action="{{route('seguimiento.objetivos.change')}}">
				{{ csrf_field() }}
				<input type="hidden" name="date_from" value="{{$datesInfo[1]['date_from']}}"/>
				<input type="hidden" name="date_to" value="{{$datesInfo[1]['date_to']}}"/>
				<input type="hidden" name="interval_change" value=""/>
				<input type="hidden" name="type_date" value="1"/>
				<input type="hidden" name="user" value="{{$user->id}}"/>
				<input type="hidden" name="count" value="{{$current_count->id}}"/>
				<input type="hidden" name="period_type" value="{{$period_type}}"/>
				<div class="cnt-toggle btn-container">
					<button type="button" class="btn btn-primary btn-navigation"  data-navigation="-1">Ant.</button>
					<button type="button" class="btn btn-link btn-toggle">
						<span class="fa fa-pencil"></span>
						{{$datesInfo[1]["label_period"]}}
					</button>
					<button type="button" class="btn btn-primary btn-navigation"  data-navigation="1">Sig.</button>
				</div>
				<div class="cnt-toggle btn-container" style="display:none;">
					<input type="text" name="date_select" class="date-week" placeholder="Semana">
					<div class="btn-group">
						<button type="button" class="btn btn-success btn-search-date"><span class="fa fa-check"></span></button>
						<button type="button" class="btn btn-danger btn-toggle"><span class="fa fa-times"></span></button>
					</div>
				</div>
				<h5 class="columnt-title"><strong>Intervalo: </strong> Desde {{$datesInfo[1]["date_from"]}} hasta {{$datesInfo[1]["date_to"]}}</h5>
			</form>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<div id="grafTotal" class="plot-tab graph shadow">
				<input type="hidden" class="plot-name" value='{{ $dataPlotting["total"]["name"] }}' />
				<input type="hidden" class="plot-labels" value='{!! $dataPlotting["total"]["labels"] !!}' />
				@foreach($dataPlotting["total"]["series"] as $serie)
					<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
					<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
					<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
					<input type="hidden" class="serie-aux_label" value='{{ $serie["aux_label"] }}' />
				@endforeach
				<div id="graphTotal" class="plot-container" style="height: 330px; width: 100%;"></div>
			</div>
			<div class="btn-container reverse"><button class="btn" id="exportButton"> Generar PDF</button></div>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<div class="table-container">			
				<table class="table-responsive text-center">
					<thead>
						<tr class="row-header">
							<th>Intervalo</th>
							<th>Consumo</th>
							<th>Línea Base (Objetivo)</th>
							<th>Diferencia LB</th>
						</tr>
					</thead>
					<tbody>
						@foreach($dataComparison["totalData"] as $index=>$data)
							<tr>
								<td>
									{{$dataComparison["dataLabelPeriods"][$index]}}
								</td>
								<td>
									{{number_format($dataComparison["totalData"][$index],0,',','.')}}
								</td>
								<td>
									{{number_format($dataComparison["totalBaseLine"][$index],0,',','.')}}
								</td>
								<td>
									{{number_format($dataComparison["totalDifferences"][$index],0,',','.')}}
								</td>
							</tr>
						@endforeach
						<tr class="row-highlight">
							<td>Variación</td>
							<td>{{number_format($dataComparison["totalVariation"],0,',','.')}}</td>
							<td>{{number_format($dataComparison["totalVariationBaseline"],0,',','.')}}</td>
							<td>{{number_format($dataComparison["totalVariationDifference"],0,',','.')}}</td>
						</tr>
					</tbody>
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
							<th>Intervalo</th>
							@foreach($dataComparison["data1"] as $index=>$data)
							<th>P{{$index + 1}}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{{$dataComparison["dataLabelPeriods"][0]}}
							</td>
							@foreach($dataComparison["data1"] as $index=>$data)
								<td>
									{{number_format($data,0,',','.')}}
								</td>
							@endforeach
						</tr>
						<tr>
							<td>
								{{$dataComparison["dataLabelPeriods"][1]}}
							</td>
							@foreach($dataComparison["data2"] as $index=>$data)
								<td>
									{{number_format($data,0,',','.')}}
								</td>
							@endforeach
						</tr>
						<tr class="row-highlight">
							<td>
								Variación
							</td>
							@foreach($dataComparison["dataVariation"] as $index=>$data)
								<td>
									{{number_format($data,0,',','.')}}
								</td>
							@endforeach
						</tr>
					</tbody>
					<tfoot>
						<tr class="row-header">
							<td>
								Objetivo
							</td>
							@foreach($dataComparison["dataBaseLine1"] as $index=>$data)
								<td>
									{{number_format($data,0,',','.')}}
								</td>
							@endforeach
						</tr>
					</tfoot>
				</table>		
			</div>
		</div>
	</div>

	<div class="d-none">
		<div class="export-pdf" data-pdforder="1" style="height:50px;"></div>
		<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive export-pdf" data-pdforder="2">
			<thead>
				<tr>
					<th class="text-center">Intervalo</th>
					<th class="text-center">Consumo</th>
					<th class="text-center">Línea Base (Objetivo)</th>
					<th class="text-center">Diferencia LB</th>
				</tr>
			</thead>
			<tbody>
				@foreach($dataComparison["totalData"] as $index=>$data)
					<tr>
						<td class="text-center" style="vertical-align: middle;">
							{{$dataComparison["dataLabelPeriods"][$index]}}
						</td>
						<td class="text-center" style="vertical-align: middle;">
							{{number_format($dataComparison["totalData"][$index],0,',','.')}}
						</td>
						<td class="text-center" style="vertical-align: middle;">
							{{number_format($dataComparison["totalBaseLine"][$index],0,',','.')}}
						</td>
						<td class="text-center" style="vertical-align: middle;">
							{{number_format($dataComparison["totalDifferences"][$index],0,',','.')}}
						</td>
					</tr>
				@endforeach
				<tr style="background-color:#AAA;">
					<td>Variación</td>
					<td>{{number_format($dataComparison["totalVariation"],0,',','.')}}</td>
					<td>{{number_format($dataComparison["totalVariationBaseline"],0,',','.')}}</td>
					<td>{{number_format($dataComparison["totalVariationDifference"],0,',','.')}}</td>
				</tr>
			</tbody>
		</table>
		<div class="export-pdf" data-pdforder="3" style="height:50px;"></div>
		<table class="table-analisis-comparacion tabla1 table table-bordered table-hover table-responsive export-pdf" data-pdforder="4">
			<thead>
				<tr>
					<th class="text-center">Intervalo</th>
					@foreach($dataComparison["data1"] as $index=>$data)
					<th class="text-center">P{{$index + 1}}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-center" style="vertical-align: middle;">
						{{$dataComparison["dataLabelPeriods"][0]}}
					</td>
					@foreach($dataComparison["data1"] as $index=>$data)
						<td class="text-center">
							{{number_format($data,0,',','.')}}
						</td>
					@endforeach
				</tr>
				<tr>
					<td class="text-center" style="vertical-align: middle;">
						{{$dataComparison["dataLabelPeriods"][1]}}
					</td>
					@foreach($dataComparison["data2"] as $index=>$data)
						<td class="text-center">
							{{number_format($data,0,',','.')}}
						</td>
					@endforeach
				</tr>
				<tr style="background-color:#AAA;">
					<td class="text-center" style="vertical-align: middle;">
						Variación
					</td>
					@foreach($dataComparison["dataVariation"] as $index=>$data)
						<td class="text-center">
							{{number_format($data,0,',','.')}}
						</td>
					@endforeach
				</tr>
			</tbody>
			<tfoot>
				<tr style="background-color:#004165;">
					<td class="text-center" style="vertical-align: middle; color:#FFF;font-weight:600;">
						Objetivo
					</td>
					@foreach($dataComparison["dataBaseLine1"] as $index=>$data)
						<td class="text-center" style=" color:#FFF; font-weight:600;">
							{{number_format($data,0,',','.')}}
						</td>
					@endforeach
				</tr>
			</tfoot>
		</table>
	</div>

	<form method="post" id="form-pdf" class="d-none" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$datesInfo[1]['date_from'],'date_to'=>$datesInfo[1]['date_to'],'contador_label'=>$current_count->count_label])}}">
		{{ csrf_field() }}
	</form>
@endsection
@section('scripts')
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
<script type="text/javascript">

	$(document).ready(function(){
		createPlots();
		var inputsWeek = $(".date-week");
		for(var i = 0; i < inputsWeek.length; i++){
			var inputW = $(inputsWeek[i]);
			var form = inputW.closest("form");
			var date_from = form.find("input[name='date_from']").val();
			inputW.datepicker({
				showWeek: true,
				firstDay: 1,
				defaultDate: date_from,
				dateFormat: "yy-mm-dd"
			});
		}

		$(".btn-toggle").click(toggleSearchWeek);
		$(".btn-navigation").click(navigateWeeks);
		$(".btn-search-date").click(searchWeek);
	});

	function isValidDate(dateString) {
		var regEx = /^\d{4}-\d{2}-\d{2}$/;
		if(!dateString.match(regEx)) return false;  // Invalid format
		var d = new Date(dateString);
		if(Number.isNaN(d.getTime())) return false; // Invalid date
		return d.toISOString().slice(0,10) === dateString;
	}

	function toggleSearchWeek() {
		var btn = $(this);
		var form = btn.closest("form");
		form.find(".cnt-toggle").toggle();
		form.find("input[name='date_select']").val("");
	}

	function navigateWeeks(){
		var btn = $(this);
		var form = btn.closest("form");
		var dataNavigation = btn.data("navigation");
		form.find("input[name='interval_change']").val(dataNavigation);
		form.submit();
	}

	function searchWeek(){
		var btn = $(this);
		var form = btn.closest("form");
		var search = form.find("input[name='date_select']").val();
		if(search.length > 0 && isValidDate(search))
			form.submit();
		return false;
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
			var aux_interval = $(seriesInterval[0]).val();
			aux_interval = ($.isNumeric(aux_interval))? parseInt(aux_interval):"";
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
					var tooltip = $(seriesAuxLabels[i]).val() +"{label} <br>{name}: {y}  kWh"
				}
				else {
					var tooltip = "{name}: {y}  kWh";
				}
				var conf = {
					type: "spline",
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
			var conta = "{{ $current_count->count_label }}";
			var date_to = "{{ $datesInfo[1]['date_to'] }}";
			var date_from ="{{ $datesInfo[1]['date_from'] }}";
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
					suffix: " kWh",
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
@endsection
