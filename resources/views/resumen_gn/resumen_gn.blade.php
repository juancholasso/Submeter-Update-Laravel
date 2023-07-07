@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 1])
@endsection

@php
	$titulo = "Resumen GN";		
@endphp

@section('content')
	<form action="{{route('config.subperiodo')}}" id="form-subperiod" method="POST">
		{{ csrf_field() }}
		<input type="hidden" name="option_interval" value="9">
		<input type="hidden" name="label_intervalo" value="{{ $dataSubperiodo['label'] }}">
		<input type="hidden" name="date_from_personalice" value="">
		<input type="hidden" name="date_to_personalice" value="">
		<input type="hidden" name="before_navigation" value="1">
		<input type="hidden" name="dates_begin" value='{!! json_encode($dataSubperiodo["begin_periods"]) !!}' />
		<input type="hidden" name="dates_end" value='{!! json_encode($dataSubperiodo["end_periods"]) !!}' />				    		
	</form>
											
	<div class="row">
		<div class="column">
			<div id="plotGasGNK" class="graph shadow plot-tab">
				<input type="hidden" class="plot-labels" value='{!! $dataPlotting["gasGNk"]["labels"] !!}' />
				<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["gasGNk"]["time_label"] }}' />
				<input type="hidden" class="plot-total" value='{{ number_format($dataPlotting["gasGNk"]["total"],2,',','.') }}' />
				@foreach($dataPlotting["gasGNk"]["series"] as $serie)
					<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
				@endforeach
				<div id="gas_gnk" class="plot-container"></div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="column">
			<div id="plotGasGNW" class="graph shadow plot-tab">
				<input type="hidden" class="plot-labels" value='{!! $dataPlotting["gasGNw"]["labels"] !!}' />
				<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["gasGNw"]["time_label"] }}' />
				<input type="hidden" class="plot-total" value='{{ number_format($dataPlotting["gasGNw"]["total"],2,',','.') }}' />
				@foreach($dataPlotting["gasGNw"]["series"] as $serie)
					<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
				@endforeach
				<div id="gas_gnw" class="plot-container"></div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="column items-start col-50">
			<div class="table-container">
				<table class="table-responsive table-striped text-left">
					<colgroup>
						<col class="shrink">
						<col>
					</colgroup>
					<tbody>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">Tarifa</th>
							<td class="text-left">{{$tarifa}}</td>
						</tr>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">PCS</th>
							<td class="text-left">{{number_format($PCS,2,',','.')}} kWh/Nm³</td>
						</tr>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">Qd contratado</th>
							<td class="text-left">{{number_format($QD_contratado,2,',','.')}} kWh/día</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="column items-end col-50">
			<div class="table-container">
				<table class="table-responsive table-striped">
					<colgroup>
						<col class="shrink">
						<col>
					</colgroup>
					<tbody>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">Coste Término Fijo</th>
							<td class="text-left">{{number_format($coste_termino_fijo,0,',','.')}} €</td>
						</tr>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">Coste Término Variable</th>
							<td class="text-left">{{number_format($coste_termino_variable,0,',','.')}} €</td>
						</tr>
						<tr>
							<th scope="row" class="bg-primary color-fff text-left">Coste TOTAL</th>
							<td class="text-left">{{number_format(($coste_termino_variable+$coste_termino_fijo),0,',','.')}} €</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-container flex-row-reverse">
				<button type="button" class="btn" id="exportButton">Generar PDF</button>
			</div>
		</div>
	</div>	
@endsection

@section('modals')
  @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')	
	@include('Dashboard.includes.scripts_modal_interval')
	@include('Dashboard.includes.script_intervalos')
	<script src="{{asset('js/bootstrap.min.js')}}"> </script>
	<script src="{{asset('js/canvas.js')}}"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
		$(document).ready(function(){
			renderGnkPlot();
			renderGnwPlot();
		});

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

		function clickDataSeries(e){
				var idx = e.dataPointIndex;
			var datesBegin = $("#form-subperiod [name='dates_begin']");
			var datesEnd = $("#form-subperiod [name='dates_end']");
			if(datesBegin.length > 0 && datesEnd.length > 0) {
				datesBegin = datesBegin.val();
				datesBegin = $.parseJSON(datesBegin);
				datesEnd = datesEnd.val();
				datesEnd = $.parseJSON(datesEnd);

				if(datesBegin !== undefined && datesEnd != undefined){
					if(datesBegin.length > 0 && datesEnd.length > 0 && datesBegin.length > idx && datesEnd.length > idx){
						$("#form-subperiod [name='date_from_personalice']").val(datesBegin[idx]);
						$("#form-subperiod [name='date_to_personalice']").val(datesEnd[idx]);
						$("#form-subperiod").submit();
					}
				}
			}
			return false;
		}

		function renderGnkPlot() {
			var cnt = $("#gas_gnk");
			if(cnt.length == 0) {
				return false;
			}

			var cntData = $("#plotGasGNK");
			var dataLabels = cntData.find(".plot-labels").val();
			dataLabels = $.parseJSON(dataLabels);
			var aux_interval = cntData.find(".plot-time-label").val();
			var totalData = cntData.find(".plot-total").val();    		
		
			var seriesValues = cntData.find(".serie-value");
			var data = new Array();
			
			for(var i = 0; i <  seriesValues.length; i++) {
				var serieVal = $(seriesValues[i]).val();
				serieVal = $.parseJSON(serieVal);
				var seriedata = new Array();
				for(j = 0; j < dataLabels.length; j++) {
					var d = {
						y : serieVal[j],
						x : j,
						label: dataLabels[j],
						color: "#004165",
						click: clickDataSeries
					};
					seriedata.push(d);
				}
				data[i] = seriedata;
			}

			CanvasJS.addCultureInfo("es", {      
				decimalSeparator: ",",
				digitGroupSeparator: "."
			});

			var titulo = "{{$titulo}}";  
			var conta = "{{ $contador2->count_label }}";
			var date_to = "{{ $date_to }}";
			var date_from ="{{ $date_from }}";
			var chart = new CanvasJS.Chart("gas_gnk", {
				theme: "light2",
				culture: "es",
				title:{
					text: "Consumo GN (Nm3)",
					fontSize: 18,				
					margin: 50,
					fontColor: "#004165"
				},
				exportFileName: "ConsumoGN(Nm3)-"+conta+"-"+date_from+"-"+date_to,
				exportEnabled: true,
				axisY: {
					suffix: " Nm3",
					titleFontColor: "#004165",
					lineColor: "#004165",
					labelFontColor: "#004165",
					tickColor: "#004165"
				},
				axisX: {
					title: "CONSUMO GN "+ totalData + " Nm3",
					titleFontColor: "#004165",
					titleFontSize: 12,
					lineColor: "#004165",
					labelFontColor: "#004165",
					tickColor: "#004165",
					interval: 1
				},
				toolTip: {
					shared: "true"
				},
				legend:{
					cursor:"pointer",
					itemclick : togglePlotsDataSeries
				},
				data: [{
					type: "spline",
					cursor: "zoom-in",
					visible: true,
					showInLegend: true,
					toolTipContent: "{name}: {y} Nm3",
					name: "Consumo Nm3",
					legendMarkerColor: "#B9C9D0",
					lineColor: "#B9C9D0",
					color: "#B9C9D0",
					dataPoints: data[0]
				}]
			});

			chart.render();
			cntData.data("chart", chart);
		}

		function renderGnwPlot() {
			var cnt = $("#gas_gnw");
			if(cnt.length == 0) {
				return false;
			}

			var cntData = $("#plotGasGNW");
			var dataLabels = cntData.find(".plot-labels").val();
			dataLabels = $.parseJSON(dataLabels);
			var aux_interval = cntData.find(".plot-time-label").val();
			var totalData = cntData.find(".plot-total").val();    		
		
			var seriesValues = cntData.find(".serie-value");
			var data = new Array();
			
			for(var i = 0; i <  seriesValues.length; i++) {
				var serieVal = $(seriesValues[i]).val();
				serieVal = $.parseJSON(serieVal);
				var seriedata = new Array();
				for(j = 0; j < dataLabels.length; j++) {
					var d = {
							y : serieVal[j],
							x : j,
							label: dataLabels[j],
						color: "#004165",
							click: clickDataSeries
					};
					seriedata.push(d);
				}
				data[i] = seriedata;
			}

			CanvasJS.addCultureInfo("es", {      
				decimalSeparator: ",",
				digitGroupSeparator: "."
			});
			
			var titulo = "{{$titulo}}";  
			var conta = "{{ $contador2->count_label }}";
			var date_to = "{{ $date_to }}";
			var date_from ="{{ $date_from }}";
			var chart = new CanvasJS.Chart("gas_gnw", {
				theme: "light2",
				culture: "es",
				title:{
					text: "Consumo GN (kWh)",
					fontSize: 18,				
					margin: 50,
					fontColor: "#004165"
				},
				exportFileName: "ConsumoGN(kWh)-"+conta+"-"+date_from+"-"+date_to,
				exportEnabled: true,
				axisY: {
					suffix: " kWh",
					titleFontColor: "#004165",
					lineColor: "#004165",
					labelFontColor: "#004165",
					tickColor: "#004165"
				},
				axisX: {
					title: "CONSUMO GN "+ totalData + " kWh",
					interval: 1,
					titleFontColor: "#004165",
					titleFontSize: 12,
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
				data: [{
					type: "column",
					cursor: "zoom-in",
					visible: true,
					showInLegend: true,
					toolTipContent: "{name}: {y} kWh",
					name: "Consumo kWh",
					legendMarkerColor: "#004165",
					lineColor: "#004165",
					color: "#004165",
					dataPoints: data[0]
				}]
			});
			chart.render();
			cntData.data("chart", chart);
		}

		var email = "{{ $user->email }}";
		var date_from ="{{ $date_from }}";
		var date_to = "{{ $date_to }}";
		var conta = "{{ $contador2->count_label }}";
		var empresa = "Empresa: {{$user->name}}";
		var ubicacion = "Ubicación: {{$direccion}}";
		var titulo = "{{$titulo}}";   	

		$("#exportButton").click(function(){
			var canvas1 = $("#gas_gnk .canvasjs-chart-canvas").get(0);
			var canvas2 = $("#gas_gnw .canvasjs-chart-canvas").get(0);
			
			var pdf = new jsPDF("l", "mm", "a4");
			pdf.setTextColor(51, 51, 51);
			pdf.text(20, 20, empresa);
			pdf.setFontSize(11);
			pdf.text(20, 30, ubicacion);
			pdf.text(20, 37, 'Contador: '+conta);
			pdf.text(20, 44, 'Email: '+email);
			pdf.text(20, 51, 'Intervalo: Desde '+date_from+' hasta '+date_to);
			var width = pdf.internal.pageSize.width;    
			var height = pdf.internal.pageSize.height;		
			pdf.addImage(canvas1.toDataURL(), 'JPEG', 20, 60, 250, 0);
			pdf.addImage(canvas2.toDataURL(), 'JPEG', 20, 135, 250, 0); 
			pdf.save(titulo+"_"+conta+"_"+date_from+"_"+date_to+".pdf");
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
		});

		$( function() {
			$( "#datepicker2" ).datepicker({
				dateFormat:'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
			});
		});
	</script>
@endsection