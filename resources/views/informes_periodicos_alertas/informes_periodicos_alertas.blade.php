@extends('Dashboard.layouts.global5')


@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 10])
@endsection

@section('content')

	@if (Session::has('message') && !is_array(Session::get('message')))
		<div id="message-success" class="alert alert-success">{{ Session::get('message') }}</div>
	@endif
	@if (Session::has('message-error') && !is_array(Session::get('message')))
		<div id="message-success" class="alert alert-danger">{{ Session::get('message-error') }}</div>
	@endif

	<div class="row row-2">
		<h2 class="title">Contadores</h2>
		{{-- INFORMES  --}}
		<div class="column">
			<form class="d-none" action="{{route('informes.programados',$id)}}" method="POST" id="form-counter-reports">
				{{ csrf_field() }}
				<input type="hidden" name="contador" value="{{$cont}}">
			</form>
			<div class="table-container">
				<table class="table-responsive text-center">
					<colgroup>
						<col class="shrink">
						<col class="shrink">
						<col class="shrink">
						<col>
					</colgroup>
					<thead>
						<tr class="row-header">
							<th>ON/OFF</th>
							<th>CSV</th>
							<th>Perioricidad</th>
							<th data-column-size="Destinatarios">Destinatarios</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>								
								<input type="checkbox" form="form-counter-reports" id="my_checkbox1" name="my_checkbox1" class="checkbox" @if(isset($informes_programados[0]) && $informes_programados[0]['check'] == 1) checked @endif/>								
								<label for="my_checkbox1" class="switch2"></label>
							</td>
							<td>
								<select class="select-csv" name="selectcheck1" form="form-counter-reports">
									@if(isset($informes_programados[0]) && $informes_programados[0]['selectcheck'] == 1)
										<option value="1" selected>SI</option>
										<option value="0">NO</option>
									@else
										<option value="1">SI</option>
										<option value="0" selected>NO</option>
									@endif
								</select>
							</td>
							<td>DIARIO</td>
							<td>								
								<textarea data-form-validation="email-list" name="destinatarios1" form="form-counter-reports" id="destinatarios1" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_programados[0]) && $informes_programados[0]['check'] == 1){{$informes_programados[0]['emails']}}@endif</textarea>								
							</td>
						</tr>
						<tr>
							<td>								
								<input type="checkbox" name="my_checkbox2" form="form-counter-reports" id="my_checkbox2" class="checkbox" @if(isset($informes_programados[1]) && $informes_programados[1]['check'] == 2) checked @endif/>									
								<label for="my_checkbox2" class="switch2"></label>
							</td>
							<td>
								<select class="select-csv" name="selectcheck2" form="form-counter-reports">
									@if(isset($informes_programados[1]) && $informes_programados[1]['selectcheck'] == 1)
										<option value="1" selected>SI</option>
										<option value="0">NO</option>
									@else
										<option value="1">SI</option>
										<option value="0" selected>NO</option>
									@endif
								</select>
							</td>
							<td>SEMANAL</td>
							<td>								
								<textarea data-form-validation="email-list" name="destinatarios2" id="destinatarios2" form="form-counter-reports" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_programados[1]) && $informes_programados[1]['check'] == 2){{$informes_programados[1]['emails']}}@endif</textarea>								
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox3" form="form-counter-reports" id="my_checkbox3" class="checkbox" @if(isset($informes_programados[2]) && $informes_programados[2]['check'] == 3) checked @endif/>
								<label for="my_checkbox3" class="switch2"></label>
							</td>
							<td>
								<select class="select-csv" name="selectcheck3" form="form-counter-reports">
									@if(isset($informes_programados[2]) && $informes_programados[2]['selectcheck'] == 1)
										<option value="1" selected>SI</option>
										<option value="0">NO</option>
									@else
										<option value="1">SI</option>
										<option value="0" selected>NO</option>
									@endif
								</select>
							</td>
							<td>MENSUAL</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios3" form="form-counter-reports" id="destinatarios3" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_programados[2]) && $informes_programados[2]['check'] == 3){{$informes_programados[2]['emails']}}@endif</textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox4" form="form-counter-reports" id="my_checkbox4" class="checkbox" @if(isset($informes_programados[3]) && $informes_programados[3]['check'] == 4) checked @endif/>
								<label for="my_checkbox4" class="switch2"></label>
							</td>
							<td>
								<select class="select-csv" name="selectcheck4" form="form-counter-reports">
									@if(isset($informes_programados[3]) && $informes_programados[3]['selectcheck'] == 1)
										<option value="1" selected>SI</option>
										<option value="0">NO</option>
									@else
										<option value="1">SI</option>
										<option value="0" selected>NO</option>
									@endif
								</select>
							</td>
							<td>TRIMESTRAL</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios4" form="form-counter-reports" id="destinatarios4" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_programados[3]) && $informes_programados[3]['check'] == 4){{$informes_programados[3]['emails']}}@endif</textarea>			
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox5" form="form-counter-reports" id="my_checkbox5" class="checkbox" @if(isset($informes_programados[4]) && $informes_programados[4]['check'] == 5) checked @endif/>
								<label for="my_checkbox5" class="switch2"></label>
							</td>
							<td>
								<select class="select-csv" name="selectcheck5" form="form-counter-reports">
									@if(isset($informes_programados[4]) && $informes_programados[4]['selectcheck'] == 1)
										<option value="1" selected>SI</option>
										<option value="0">NO</option>
									@else
										<option value="1">SI</option>
										<option value="0" selected>NO</option>
									@endif
								</select>
							</td>
							<td>ANUAL</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios5" form="form-counter-reports" id="destinatarios5" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_programados[4]) && $informes_programados[4]['check'] == 5){{$informes_programados[4]['emails']}}@endif</textarea>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-container center">
				<button class="btn" form="form-counter-reports">Programar Informes</button>
			</div>
		</div>
		{{-- ALERTAS --}}
		<div class="column">
			<form class="d-none" action="{{route('alertas.programadas',$id)}}" method="POST" id="form-alerts">
				{{ csrf_field() }}
				<input type="hidden" name="contador" value="{{$cont}}">
			</form>
			<div class="table-container">
				<table class="table-responsive text-center column-header">
					<colgroup>
						<col class="shrink">
						<col class="shrink">
						<col>
					</colgroup>
					<thead>
						<tr class="row-header">
							<th ></th>
							<th>Alerta</th>
							<th>Destinatarios</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							@if($tipo_count == 1)
								<td>POTENCIA CONTRATADA</td>
								<td>
									<h5 class="title-analisis">Pot. Demandada &ge;
										<select name="select1" form="form-alerts">
											@foreach($porcentajes_alerta as $key => $value)
												@if(isset($alertas_programadas[0]) && $alertas_programadas[0]['alert_value'] == $key)
													<option value="{{$key}}" selected="selected">{{$value}}</option>
												@else
													<option value="{{$key}}">{{$value}}</option>
												@endif
											@endforeach
										</select>
										&nbsp; Pot. Contratada
									</h5>
								</td>
							@elseif($tipo_count == 2)
								<td>GENERACI&Oacute;N ENERG&Iacute;A TOTAL</td>
								<td>
									<h5 class="title-analisis">Día actual
										<select name="select1_search_type" form="form-alerts">
											@foreach($search_types as $key => $value)
												@if(isset($alertas_programadas[0]) && $alertas_programadas[0]['search_type'] == $key)
													<option value="{{$key}}" selected="selected">{{$value}}</option>
												@else
													<option value="{{$key}}">{{$value}}</option>
												@endif
											@endforeach
										</select>
										<select name="select1" form="form-alerts">
											@foreach($porcentajes_alerta as $key => $value)
												@if(isset($alertas_programadas[0]) && $alertas_programadas[0]['alert_value'] == $key)
													<option value="{{$key}}" selected="selected">{{$value}}</option>
												@else
													<option value="{{$key}}">{{$value}}</option>
												@endif
											@endforeach
										</select>
										&nbsp; Día Anterior
									</h5>
								</td>
							@elseif($tipo_count == 3)
								<td>Consumo GN (Nm<sup>3</sup>)</td>
								<td>
									<h5 class="title-analisis">Día actual &ge;
										<select name="select1" form="form-alerts">
											@foreach($porcentajes_alerta as $key => $value)
												@if(isset($alertas_programadas[0]) && $alertas_programadas[0]['alert_value'] == $key)
													<option value="{{$key}}" selected="selected">{{$value}}</option>
												@else
													<option value="{{$key}}">{{$value}}</option>
												@endif
											@endforeach
										</select>
										&nbsp; Día Anterior
									</h5>
								</td>
							@endif
							<td>
								<textarea data-form-validation="email-list" name="destinatarios6" form="form-alerts" id="destinatarios6" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($alertas_programadas[0]) && $alertas_programadas[0]['alert_type'] == 1){{$alertas_programadas[0]['emails']}}@endif</textarea>
							</td>
						</tr>
						@if($tipo_count != 2)
							<tr>
								<td>
									@if($tipo_count < 3)
										ENERGÍA REACTIVA
									@else
										Qd Facturada (kWh/día)
									@endif
								</td>
								<td>
									@if(isset($alertas_programadas[1]) && $alertas_programadas[1]['alert_type'] == 2)
										@if(isset($alertas_programadas[1]) && $alertas_programadas[1]['alert_value'] == '1')
											<h5 class="title-analisis">	
												@if($tipo_count < 3)
													cos &empty; &le;
												@else
													Qd Máxima &ge;
												@endif
												<select name="select2" form="form-alerts">
													@if($tipo_count < 3)
														<option value="1">0,95</option>
														<option value="2">0,90</option>
														<option value="3">0,85</option>
													@else
														<option value="1">10%</option>
														<option value="2">15%</option>
														<option value="3">20%</option>
													@endif
												</select>
											</h5>
										@elseif(isset($alertas_programadas[1]) && $alertas_programadas[1]['alert_value'] == '2')
											<h5 class="title-analisis">
												@if($tipo_count < 3)
													cos &empty; &le;
												@else
													Qd Máxima &ge;
												@endif
												<select name="select2" form="form-alerts">
													@if($tipo_count < 3)
														<option value="2">0,90</option>
														<option value="1">0,95</option>
														<option value="3">0,85</option>
													@else
														<option value="2">15%</option>
														<option value="1">10%</option>
														<option value="3">20%</option>
													@endif
												</select>
											</h5>
										@elseif(isset($alertas_programadas[1]) && $alertas_programadas[1]['alert_value'] == '3')
											<h5 class="title-analisis">
												@if($tipo_count < 3)
													cos &empty; &le;
												@else
													Qd Máxima &ge;
												@endif
												<select name="select2" form="form-alerts">
													@if($tipo_count < 3)
														<option value="3">0,85</option>
														<option value="1">0,95</option>
														<option value="2">0,90</option>
													@else
														<option value="3">20%</option>
														<option value="1">10%</option>
														<option value="2">15%</option>
													@endif
												</select>
											</h5>
										@else
											<h5 class="title-analisis">
												@if($tipo_count < 3)
													cos &empty; &le;
												@else
													Qd Máxima &ge;
												@endif
												<select name="select2" form="form-alerts">
													@if($tipo_count < 3)
														<option value="1">0,95</option>
														<option value="2">0,90</option>
														<option value="3">0,85</option>
													@else
														<option value="1">10%</option>
														<option value="2">15%</option>
														<option value="3">20%</option>
													@endif
												</select>
											</h5>
										@endif
									@else
										@if($tipo_count < 3)
											<h5 class="title-analisis">
												@if($tipo_count < 3)
													cos &empty; &le;
												@else
													Qd Máxima &ge;
												@endif
												<select name="select2" form="form-alerts">
													<option value="1">0,95</option>
													<option value="2">0,90</option>
													<option value="3">0,85</option>
												</select>
											</h5>
										@else
											<h5 class="title-analisis">Qd Máxima &ge;
												<select name="select2" form="form-alerts">
													<option value="1">10%</option>
													<option value="2">15%</option>
													<option value="3">20%</option>
												</select>
											</h5>
										@endif
									@endif
								</td>
								<td>
									<textarea data-form-validation="email-list" name="destinatarios7" form="form-alerts" id="destinatarios7" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($alertas_programadas[1]) && $alertas_programadas[1]['alert_type'] == 2){{$alertas_programadas[1]['emails']}}@endif</textarea>
								</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>	
			<div class="btn-container center">
				<button class="btn" form="form-alerts">Programar Alertas</button>
			</div>
		</div>
	</div>

	{{-- ANALIZADORES --}}
	<div class="row">
		<h2 class="title">Analizadores</h2>
		<div class="column">
			<form class="d-none" action="{{route('informes.analizadores.programados',$id)}}" method="POST" id="form-analyzers">
				{{ csrf_field() }}
				<input type="hidden" name="contador" value="{{$cont}}">
			</form>
			<div class="table-container">
				<table class="table-responsive text-center">
					<colgroup>
						<col class="shrink">
						<col class="shrink">
						<col>
					</colgroup>
					<thead>
						<tr class="row-header">
							<th>ON/OFF</th>
							<th>Perioricidad</th>
							<th>Destinatarios</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox8" form="form-analyzers" id="my_checkbox8" class="checkbox" @if(isset($informes_analizadores_programados[0]) && $informes_analizadores_programados[0]['check'] == 1) checked @endif />
								<label for="my_checkbox8" class="switch2"></label>
							</td>
							<td>DIARIO</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios8" form="form-analyzers" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_analizadores_programados[0]) && $informes_analizadores_programados[0]['check'] == 1){{$informes_analizadores_programados[0]['emails']}}@endif</textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox10" form="form-analyzers" id="my_checkbox10" class="checkbox" @if(isset($informes_analizadores_programados[2]) && $informes_analizadores_programados[2]['check'] == 3) checked @endif />
								<label for="my_checkbox10" class="switch2"></label>
							</td>
							<td>MENSUAL</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios10" form="form-analyzers" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_analizadores_programados[2]) && $informes_analizadores_programados[2]['check'] == 3){{$informes_analizadores_programados[2]['emails']}}@endif</textarea>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="my_checkbox12" form="form-analyzers" id="my_checkbox12" class="checkbox" @if(isset($informes_analizadores_programados[4]) && $informes_analizadores_programados[4]['check'] == 5) checked 	@endif />
								<label for="my_checkbox12" class="switch2"></label>
							</td>
							<td>ANUAL</td>
							<td>
								<textarea data-form-validation="email-list" name="destinatarios12" form="form-analyzers" placeholder="mail1@example.com;mail2@example.com; (sin espacios)">@if(isset($informes_analizadores_programados[4]) && $informes_analizadores_programados[4]['check'] == 5){{$informes_analizadores_programados[4]['emails']}}@endif</textarea>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-container center">
				<button class="btn" form="form-analyzers">Programar Informes</button>
			</div>
		</div>
	</div>

	@include('informes_periodicos_alertas.nuevas_alertas', [
		"id"=>$id, 
		"contador_label"=>$contador_label, 
		"alertas_general"=>$alertas_general
	])
@endsection

@section('scripts')

<script src="{{asset('js/validateForms.js')}}"></script>
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
