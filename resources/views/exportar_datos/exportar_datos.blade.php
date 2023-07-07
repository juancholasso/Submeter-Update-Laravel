@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 12])
@endsection


@section('content')
	<div class="row" role="tabpanel" id="Contador" aria-labelledby="Contador">
		<form class="d-none" name="form-export" id="form-export" method="POST" action="{{route('get.export')}}">
			{{ csrf_field() }}
			<input type="hidden" name="date_from" value="{{$date_from}}">
			<input type="hidden" name="date_to" value="{{$date_to}}">
			<input type="hidden" name="cont" value="{{$contador_label}}">
			<input type="hidden" name="id" value="{{$id}}">
		</form>
		<div class="column">
			<div class="wrapper-xs">
				<div class="table-container" style="margin-top: 4vh">
					<table class="table-responsive text-center column-header">
						<tbody>
							<tr>
								<td>Contador</td>
								<td>{{$contador_label}}</td>
							</tr>
							<tr>
								<td>Fecha Inicio</td>
								<td>
									<input type="text" id="datepicker" class="datepicker selectable_date text-center" name="date_from" value="{{$date_from}}" form="form-export">
								</td>
							</tr>
							<tr>
								<td>Fecha Final</td>
								<td>
									<input type="text" id="datepicker2" class="datepicker selectable_date text-center" name="date_to" value="{{$date_to}}" form="form-export">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="btn-container">
					<button type="button" id="export_data" class="btn btn-sm">Exportar PDF</button>
					<button type="button" class="btn btn-sm apply-filters-custom-interval">Aplicar Cambios</button>
					<button type="submit" class="btn btn-sm" form="form-export">Exportar CSV</button>
				</div>
			</div>
		</div>
	</div>

	<form id="custom-interval-form" action="{{route('config.interval')}}" method="POST">
		{{ csrf_field() }}
		<input type="hidden" id="custom-date-from" name="date_from_personalice">
		<input type="hidden" id="custom-date-to" name="date_to_personalice">
		<input type="hidden" name="user_id" value="{{$user->id}}">
		<input type="hidden" name="option_interval" value="9">
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
	{{-- <script src="{{asset('js/bootstrap.min.js')}}"> </script> --}}
	{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script> --}}
	<script src="{{asset('js/canvas.js')}}"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/2.3.2/jspdf.plugin.autotable.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
		function changeFunc() {
			var selectBox = document.getElementById("option_interval");
			var selectedValue = selectBox.options[selectBox.selectedIndex].value;
			if(selectedValue == 9) {
				return true;
				$('#div_datatimes').show();
				$('#datepicker').prop('required',true);
				$('#datepicker2').prop('required',true);
			} else {
				$('#div_datatimes').hide();
				$('#datepicker').val('');
				$('#datepicker2').val('');
				$('#datepicker').prop('required',false);
				$('#datepicker2').prop('required',false);
			}
		}

		$(".apply-filters-custom-interval").on( "click", function (event) {
			event.preventDefault();
			var selectedDateFrom = $("#datepicker").val(),
				selectedDateTo	 = $("#datepicker2").val();

			if ( ! selectedDateFrom || ! selectedDateTo ) {
				alert("Por favor configure una fecha válida");
				return;
			}

			$(this).prop("disabled", true);
			$("#custom-date-from").val(selectedDateFrom);
			$("#custom-date-to").val(selectedDateTo);
			$("#custom-interval-form").submit();
		} );
	</script>
	<script>
		$('#div_datatimes').hide();
		//$('#datepicker').val('');
		//$('#datepicker2').val('');
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
	<script> 
		$(function () {
			$('#supported').text('Supported/allowed: ' + !!screenfull.enabled);

			if (!screenfull.enabled) {
				return false;
			}

			$('#toggle').click(function () {
				screenfull.toggle($('#container')[0]);
			});

			$("[name='my-checkbox']").bootstrapSwitch();

		});

		$("#export_data").click(function(){
			var dattos = @php echo json_encode($datos_contador) @endphp;
			var name = @php echo json_encode($user->name) @endphp;
			var cont = @php echo json_encode($contador_label) @endphp;
			var tipo_count = @php echo json_encode($tipo_count) @endphp;
			var date_from = @php echo json_encode($date_from) @endphp;
			var date_to = @php echo json_encode($date_to) @endphp;
			var total1 = @php echo json_encode(number_format($total1,0,',','.')) @endphp;
			var total2 = @php echo json_encode(number_format($total2,0,',','.')) @endphp;
			var aux_c = @php echo json_encode($aux_cont_datos) @endphp;

			if(aux_c == 0){
				var total3 = @php echo json_encode(number_format($total3/1,2,',','.')) @endphp;
				var total4 = @php echo json_encode(number_format($total4/1,2,',','.')) @endphp;
				var total5 = @php echo json_encode(number_format($total5/1,2,',','.')) @endphp;
				var total6 = @php echo json_encode(number_format($total6/1,2,',','.')) @endphp;
			} else {
				if(tipo_count < 3) {
					var total3 = @php echo json_encode(number_format($total3,2,',','.')) @endphp;
					var total4 = @php echo json_encode(number_format($total4,2,',','.')) @endphp;
					var total5 = @php echo json_encode(number_format($total5,2,',','.')) @endphp;
					var total6 = @php echo json_encode(number_format($total6,2,',','.')) @endphp;
				} else {
					var total3 = @php echo json_encode(number_format($total3/$aux_cont_datos,2,',','.')) @endphp;
					var total4 = @php echo json_encode(number_format($total4/$aux_cont_datos,2,',','.')) @endphp;
					var total5 = @php echo json_encode(number_format($total5/$aux_cont_datos,2,',','.')) @endphp;
					var total6 = @php echo json_encode(number_format($total6/$aux_cont_datos,2,',','.')) @endphp;
				}
			}

			// console.log(cont);
			// console.log(dattos);
			var datapoins = [];
			for (var i = 0; i < dattos.length; i++) {
				// total1 += dattos[i]['EAct_imp'];
				// total2 += dattos[i]['EAct_exp'];
				// total3 += dattos[i]['EReac_Camp_imp'];
				// total4 += dattos[i]['EReac_cap_exp'];
				// total5 += dattos[i]['EReac_imp'];
				// total6 += dattos[i]['EReac_ind'];

				//     		var rows = [
				//     {"id": 1, "name": "Shaw", "country": "Tanzania", ...},
				//     {"id": 2, "name": "Nelson", "country": "Kazakhstan", ...},
				//     {"id": 3, "name": "Garcia", "country": "Madagascar", ...},
				//     ...
				// ];
				if(tipo_count < 3) {
					datapoins.push({"fecha": dattos[i]['date'], "tiempo": dattos[i]['time'], "EAct_Imp": dattos[i]['EAct_imp'], "EAct_Exp": dattos[i]['EAct_exp'], "EReac_imp": dattos[i]['EReac_imp'], "ERInd_Exp": dattos[i]['EReac_ind'], "ERCap_Exp": dattos[i]['EReac_cap_exp'], "ERCap_Imp": dattos[i]['EReac_Camp_imp']},)
				} else {
					datapoins.push({"fecha": dattos[i]['date'], "tiempo": dattos[i]['time'], "volumen_bruto": dattos[i]['volumen_bruto'], "volumen_neto": dattos[i]['volumen_neto'], "caudal_neto": dattos[i]['caudal_neto'], "caudal_bruto": dattos[i]['caudal_bruto'], "factor_correccion": dattos[i]['factor_correccion'], "presion": dattos[i]['presion']},)
				}
			};

			if(tipo_count < 3)
				datapoins.push({"fecha": 'Total', "tiempo": '', "EAct_Imp": total1, "EAct_Exp": total2, "EReac_imp": total3, "ERInd_Exp": total4, "ERCap_Exp": total5, "ERCap_Imp": total6},);
			else
				datapoins.push({"fecha": '', "tiempo": '', "volumen_bruto": 'Total', "volumen_neto": 'Total', "caudal_neto": 'Valor Medio', "caudal_bruto": 'Valor Medio', "factor_correccion": 'Valor Medio', "presion": 'Valor Medio'},);
			datapoins.push({"fecha": '', "tiempo": '', "volumen_bruto": total1, "volumen_neto": total2, "caudal_neto": total3, "caudal_bruto": total4, "factor_correccion": total5, "presion": total6},);
			// console.log(datapoins);
			var pdf = new jsPDF('l');
			pdf.text(20,20,"Empresa: "+name);
			pdf.text(20,30,"Contador:"+cont);
			pdf.text(20,40,"Intervalo: Desde "+date_from+" hasta "+date_to);

			// var columns = ["Fecha", "Tiempo", "EAct Imp(kWh)", "EAct exp(kWh)","ERInd_imp(kvarh)","ERInd_exp(kvarh)","ERCap_exp(kvarh)","ERCap_imp(kvarh)"];
			if(tipo_count < 3) {
				var columns = [
					{title: "Fecha", dataKey: "fecha"},
					{title: "Tiempo", dataKey: "tiempo"},
					{title: "EAct imp(kWh)", dataKey: "EAct_Imp"},
					{title: "EAct exp(kWh)", dataKey: "EAct_Exp"},
					{title: "ERInd imp(kvarh)", dataKey: "EReac_imp"},
					{title: "ERInd exp(kvarh)", dataKey: "ERInd_Exp"},
					{title: "ERCap exp(kvarh)", dataKey: "ERCap_Exp"},
					{title: "ERCap imp(kvarh)", dataKey: "ERCap_Imp"},
				];
			} else {
				var columns = [
					{title: "Fecha", dataKey: "fecha"},
					{title: "Tiempo", dataKey: "tiempo"},
					{title: "Volumen Bruto (m3)", dataKey: "volumen_bruto"},
					{title: "Volumen Neto (m3)", dataKey: "volumen_neto"},
					{title: "Caudal Neto (m3/s)", dataKey: "caudal_neto"},
					{title: "Caudal Bruto (m3/s)", dataKey: "caudal_bruto"},
					{title: "Factor Corrección", dataKey: "factor_correccion"},
					{title: "Presión (bar)", dataKey: "presion"},
				];
			}
			var data = [datapoins];

			pdf.autoTable(columns,datapoins,
					{ margin:{ top: 50 }}
			);

			pdf.save("Datos_"+cont+"_"+date_from+"_"+date_to+".pdf");
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
	{{-- <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script> --}}
	<script src="{{asset('js/skycons.js')}}"></script>
@endsection
