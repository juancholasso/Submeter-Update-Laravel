@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('content')
	@if($user->tipo == 2)		
		<div class="row">
			<div class="column col-65">
				<div class="table-container">
					<table class="table-responsive table-striped">
						<thead>
							<tr class="row-header">
								<th>Contador</th>
								<th>Tarifa</th>
								<th>CUPS</th>
								<th>Nombre</th>
								<th>Dirección</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($contadoresGas as $contadorGas)
								<tr>
									<td class="text-center">{{$contadorGas["label"]}}</td>
									<td><strong>{{$contadorGas["domicilio"]->tarifa}}</strong></td>
									<td>{{$contadorGas["domicilio"]->cups}}</td>
									<td><strong>{{$contadorGas["nombre"]}}</strong></td>
									<td>{{$contadorGas["domicilio"]->suministro_del_domicilio}}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="column col-35">
				<div id="map" class="gmap-container"></div>
			</div>
		</div>

		<div class="row mt-content">
			<div class="flex flex-wrap m-auto mt-content">
				<div class="chart-container">
					<div id="pie-total"></div>
				</div>
				<div class="chart-container">
					<div id="pie-variable"></div>
				</div>
				<div class="chart-container">
					<div id="pie-fijo"></div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="column">
				<div class="wrapper-lg">
					<div class="table-container">
						<table class="table-responsive text-left">
							<thead>
								<tr class="row-header text-center">
									<th span="2">Concepto</th>
									<th>Importe</th>
								</tr>
							</thead>
							<tbody>
								<tr class="row-highlight">
									<td span="2">T&eacute;rmino Variable</td>
									<td>{{number_format($totalContadoresGas["variable"], 2, ',', '.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td>
										@foreach ($contadoresGas as $contadorGas)
											{{$contadorGas["label"]}}: {{number_format($contadorGas["variable"], 2, ',', '.')}} €<br/>
										@endforeach
									</td>
								</tr>
								<tr class="row-highlight">
									<td span="2">T&eacute;rmino Fijo</td>
									<td>{{number_format($totalContadoresGas["fijo"], 2, ',', '.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td>
										@foreach ($contadoresGas as $contadorGas)
											{{$contadorGas["label"]}}: {{number_format($contadorGas["fijo"], 2, ',', '.')}} €<br/>
										@endforeach
									</td>
								</tr>
								<tr class="row-highlight">
									<td span="2">I.E. HC</td>
									<td>{{number_format($totalContadoresGas["iehc"], 2, ',', '.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td>
										@foreach ($contadoresGas as $contadorGas)
											{{$contadorGas["label"]}}: {{number_format($contadorGas["iehc"], 2, ',', '.')}} €<br/>
										@endforeach
									</td>
								</tr>
								<tr class="row-highlight">
									<td span="2">Alquiler</td>
									<td>{{number_format($totalContadoresGas["alquiler"], 2, ',', '.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td>
										@foreach ($contadoresGas as $contadorGas)
											{{$contadorGas["label"]}}: {{number_format($contadorGas["alquiler"], 2, ',', '.')}} €<br/>
										@endforeach
									</td>
								</tr>
								<tr class="row-highlight">
									<td span="2">I.V.A. (21%)</td>
									<td>{{number_format($totalContadoresGas["iva"], 2, ',', '.')}} €</td>
								</tr>
								<tr>
									<td></td>
									<td>
										@foreach ($contadoresGas as $contadorGas)
											{{$contadorGas["label"]}}: {{number_format($contadorGas["iva"], 2, ',', '.')}} €<br/>
										@endforeach
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr class="row-header">
									<th span="2">TOTAL</th>
									<th>{{number_format($totalContadoresGas["total"], 2, ',', '.')}} €</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="btn-container flex-row-reverse">
						<a href="{{route('contadores.pdf', $user->id)}}" class="btn">Generar PDF</a>
					</div>
				</div>
			</div>
		</div>							
	@endif			
@endsection

@section('modals')
  @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')	
@include('Dashboard.includes.scripts_modal_interval')
@include('Dashboard.includes.script_intervalos')
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
	const contadoresGas = JSON.parse(`@php echo json_encode($contadoresGas); @endphp`)
	const chartTotalData 		= []
	const chartVariableData = []
	const chartFijoData 		= []
	
	contadoresGas.forEach((contadorGas)=>{
		chartTotalData.push(
			{ label: contadorGas.label, y: contadorGas.total }
		)
		chartVariableData.push(
			{ label: contadorGas.label, y: contadorGas.variable }
		)
		chartFijoData.push(
			{ label: contadorGas.label, y: contadorGas.fijo }
		)
	})

	window.onload = function() {
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
			]
		)

		const chartTotal 		= new CanvasJS.Chart("pie-total", {
			//	colorSet: "blueShades",
			height:350,
			animationEnabled: true,
			backgroundColor: "transparent",
			title: {
				text: "Coste Total",
				fontColor: "#004165",
				fontWeight: "bold",
				fontFamily: "Univers-45-Light",
				fontSize: "18"
			},
			data: [{
				type: "pie",
				radius: 100,
				startAngle: 270,
				toolTipContent: "{label} - #percent%",
				indexLabel: "{label} - #percent%",
				dataPoints: chartTotalData
			}]
		})
		const chartVariable = new CanvasJS.Chart("pie-variable", {
			//		colorSet: "blueShades",
			height:350,
			animationEnabled: true,
			backgroundColor: "transparent",
			title: {
				text: "Termino Variable",
				fontColor: "#004165",
				fontWeight: "bold",
				fontFamily: "Univers-45-Light",
				fontSize: "18"
			},
			data: [{
				type: "pie",
				radius: 100,
				startAngle: 270,
				toolTipContent: "{label} - #percent%",
				indexLabel: "{label} - #percent%",
				dataPoints: chartVariableData

			}]
		})
		const chartFijo 		= new CanvasJS.Chart("pie-fijo", {
			//		colorSet: "blueShades",
			height:350,
			animationEnabled: true,
			backgroundColor: "transparent",
			title: {
				text: "Termino Fijo",
				fontColor: "#004165",
				fontWeight: "bold",
				fontFamily: "Univers-45-Light",
				fontSize: "18"
			},
			data: [{
				type: "pie",
				radius: 100,
				startAngle: 270,
				toolTipContent: "{label} - #percent%",
				indexLabel: "{label} - #percent%",
				dataPoints: chartFijoData

			}]
		})

		chartTotal.render()
		chartVariable.render()
		chartFijo.render()
	}
</script>
<script>
	function initialize(){
		const mapContainer = document.querySelector("#map")
		const mapOptions = {
			mapTypeId: 'roadmap',
			gestureHandling: 'greedy'
		}
		const map = new google.maps.Map(mapContainer, mapOptions)
		const mapBounds = new google.maps.LatLngBounds()
		const markers = JSON.parse(`@php echo $markers; @endphp`)

		setMarkers(map, mapBounds, markers)
		map.setTilt(45)
		map.fitBounds(mapBounds)
	}

	function setMarkers(map, mapBounds, markers){
		let lastInfoWindow = null
		
		markers.forEach((marker) => {
			const position = new google.maps.LatLng(marker.lat, marker.lng)
			const mapMarker = new google.maps.Marker({
				position: position,
				map,
				// title: marker.name,
				label: marker.custom_label
			})
			const infoWindow = createInfoWindow(marker)
			const infoWindowHandler = () => {
				if(lastInfoWindow) lastInfoWindow.close()
				infoWindow.open(map, mapMarker)
				lastInfoWindow = infoWindow
			}

			mapBounds.extend(position)
			mapMarker.addListener("click", infoWindowHandler)
			mapMarker.addListener("mouseover", infoWindowHandler)
		})
	}

	function createInfoWindow(marker){
		const infoWindow = document.createElement('div')
		const strong = document.createElement('strong')
		const text = document.createElement('text')

		strong.innerText = marker.name
		infoWindow.appendChild(strong)
		infoWindow.appendChild(document.createElement('br'))
		text.innerText = marker.address
		infoWindow.appendChild(text)

		return new google.maps.InfoWindow({
			content: infoWindow
		})
	}
</script>
<script async defer src='{{$maps_url}}'></script>
@endsection