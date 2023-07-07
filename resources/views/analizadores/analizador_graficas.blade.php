@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	<div class="content counters">
		<ul class="btn-list">
			@foreach($analyzers_data as $i => $analyzer_meter)<li>
				<a class="btn @if($analyzer_meter['id'] == $analizador->id) active @php	$activeAnlz = $i; @endphp @endif" href="{{route('analizadores.graficas', [$user->id, $group_id, $analyzer_meter['id'] ])}}">
					<i class="fa fa-clock-o"></i><span>{{$analyzer_meter['name']}}</span>
				</a>
			</li>@endforeach
		</ul>
		<div class="dropdown">
			<button class="btn active dropdown__button" type="button">
				<i class="fa fa-clock-o"></i><span>{{$analyzers_data[$activeAnlz]['name']}}</span>
			</button>
			<ul class="dropdown__menu">
				@foreach($analyzers_data as $i => $analyzer_meter)
					@if ($i === $activeAnlz)
						@continue
					@endif
					<li class="dropdown__item">
						<a href="{{route('analizadores.graficas', [$user->id, $group_id, $analyzer_meter['id'] ])}}">
							<i class="fa fa-clock-o"></i><span>{{$analyzer_meter['name']}}</span>
						</a>
					</li>
				@endforeach			
			</ul>
		</div>
	</div>
@endsection

@section('content')
	@php
		$aux_cont = implode('_', explode(' ', $contador2->count_label));
	@endphp

	<div class="hidden">
		<div class="pdf-header">
			<div class="container" style="width:100%; display: inline-block">
				<div class="row">
					<div class="col"></div>
					<div class="col">
						<h5 style="text-align: center; color:#004165; font-weight: bold;">Informe Analizador:</h5>
						<h5 style="text-align: center; color:#004165; font-weight: bold; white-space: nowrap;">{{$analizador->label}}</h5>
					</div>
					<div class="col">
						<img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
					</div>
				</div>
			</div>
		</div>
		<div class="export-pdf" data-pdforder="1" style="margin-top:100px;margin-bottom:100px">
			<div class="col">
				<h5 style="text-align: center;color:#004165;font-weight: bold;">Consumo de Energia<h5>
				<br/>
			</div>
			<div class="panel-group " style="display: inline-block; width: 49%;">
				<div class="panel panel-primary">
					<div class="panel-heading text-center" style="background-color: {{$color_etiqueta}}">
						<a  style="color: #272822 !important; font-weight: bold;font-size: 11pt">Energía Total Activa</a>
					</div>
					<div class="panel-body text-center">
						{{number_format($analyzer_energy['energia_activa'],0,',','.')}} kWh<br/>
					</div>
				</div>
			</div>
			<div class="panel-group  " style="display: inline-block; width: 49%;">
				<div class="panel panel-primary">
					<div class="panel-heading text-center" style="background-color: {{$color_etiqueta}}">
						<a  style="color: #272822 !important; font-weight: bold; font-size: 11pt">Energía Total Reactiva</a>
					</div>
					<div class="panel-body text-center">
						{{number_format($analyzer_energy['energia_reactiva'],0,',','.')}} kVArh
					</div>
				</div>
			</div>
		</div>
		<div class="export-pdf" data-pdforder="2" >
			<h5 style="text-align: center;color:#004165;font-weight: bold;">Tasas de Distorsión Armonica<h5>
			<br/>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="3">
			<table class="table-responsive table-striped tabla1 table table-bordered " border="1" bordercolor="#004165" width="100%" style="text-align: center;">
				<thead>
					<tr>
						<th class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Distorsión Armónica</th>
						<th class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;"></th>
						<th class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">M&aacute;xima</th>
						<th class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">M&iacute;nima</th>
						<th class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">Media</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td rowspan="3" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center; border-width: 1px 1px 5px 1px;">THDU</td>
						<td style="font-weight: bold;vertical-align: middle;text-align: center;">F1</td>
						<td>{{number_format($parsedTableData["thdu1"]["max"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdu1"]["min"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdu1"]["avg"], 2, ',', '.')}} %</td>
					</tr>
					<tr>
						<td style="font-weight: bold;vertical-align: middle;text-align: center;">F2</td>
						<td>{{number_format($parsedTableData["thdu2"]["max"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdu2"]["min"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdu2"]["avg"], 2, ',', '.')}} %</td>
					</tr>
					<tr>
						<td style="font-weight: bold;vertical-align: middle;text-align: center; border-width: 1px 1px 5px 1px;">F3</td>
						<td style="border-width: 1px 1px 5px 1px;">{{number_format($parsedTableData["thdu3"]["max"], 2, ',', '.')}} %</td>
						<td style="border-width: 1px 1px 5px 1px;">{{number_format($parsedTableData["thdu3"]["min"], 2, ',', '.')}} %</td>
						<td style="border-width: 1px 1px 5px 1px;">{{number_format($parsedTableData["thdu3"]["avg"], 2, ',', '.')}} %</td>
					</tr>
					<tr>
						<td rowspan="3" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">THDI</td>
						<td style="font-weight: bold;vertical-align: middle;text-align: center;">F1</td>
						<td>{{number_format($parsedTableData["thdi1"]["max"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi1"]["min"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi1"]["avg"], 2, ',', '.')}} %</td>
					</tr>
					<tr>
						<td style="font-weight: bold;vertical-align: middle;text-align: center;">F2</td>
						<td>{{number_format($parsedTableData["thdi2"]["max"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi2"]["min"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi2"]["avg"], 2, ',', '.')}} %</td>
					</tr>
					<tr>
						<td style="font-weight: bold;vertical-align: middle;text-align: center;">F3</td>
						<td>{{number_format($parsedTableData["thdi3"]["max"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi3"]["min"], 2, ',', '.')}} %</td>
						<td>{{number_format($parsedTableData["thdi3"]["avg"], 2, ',', '.')}} %</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="export-pdf" data-pdforder="4" >
			<div style="margin-top:2000px;"></div>
				<h5 style="text-align: center;color:#004165;font-weight: bold;padding-top: 60px;">Resumen de Parametros Registrados<h5>
				<br/>
		</div>
		<div class="col-md-6 export-pdf" data-pdforder="5" style="margin-top: 15px; margin-bottom: 50px;">
			<table border="1" bordercolor="#004165" width="100%" style="text-align: center;">
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="5" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Potencia Activa</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr style="text-align: center;">
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa1"]["max"], 2, ',', '.')}} kW</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa1"]["min"], 2, ',', '.')}} kW</td>
					<td style="font-weight: bold; font-size: 15px;">{{number_format($parsedTableData["activa1"]["avg"], 2, ',', '.')}} kW</td>
				</tr>
				<tr style="text-align: center;">
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa2"]["max"], 2, ',', '.')}} kW</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa2"]["min"], 2, ',', '.')}} kW</td>
					<td style="font-weight: bold; font-size: 15px;">{{number_format($parsedTableData["activa2"]["avg"], 2, ',', '.')}} kW</td>
				</tr>
				<tr style="text-align: center;">
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa3"]["max"], 2, ',', '.')}} kW</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activa3"]["min"], 2, ',', '.')}} kW</td>
					<td style="font-weight: bold; font-size: 15px;">{{number_format($parsedTableData["activa3"]["avg"], 2, ',', '.')}} kW</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Trif&aacute;sico</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activaTotal"]["max"], 2, ',', '.')}} kW</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["activaTotal"]["min"], 2, ',', '.')}} kW</td>
					<td style="font-weight: bold; font-size: 15px;">{{number_format($parsedTableData["activaTotal"]["avg"], 2, ',', '.')}} kW</td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle; color:white;text-align: center;">
					<td rowspan="5" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Potencia Reactiva</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva1"]["max"], 2, ',', '.')}} kVAr</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva1"]["min"], 2, ',', '.')}} kVAr</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["reactiva1"]["avg"], 2, ',', '.')}} kVAr</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva2"]["max"], 2, ',', '.')}} kVAr</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva2"]["min"], 2, ',', '.')}} kVAr</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["reactiva2"]["avg"], 2, ',', '.')}} kVAr</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva3"]["max"], 2, ',', '.')}} kVAr</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactiva3"]["min"], 2, ',', '.')}} kVAr</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["reactiva3"]["avg"], 2, ',', '.')}} kVAr</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Trif&aacute;sico</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactivaTotal"]["max"], 2, ',', '.')}} kVAr</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["reactivaTotal"]["min"], 2, ',', '.')}} kVAr</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["reactivaTotal"]["avg"], 2, ',', '.')}} kVAr</td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="5" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Potencia Aparente</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente1"]["max"], 2, ',', '.')}} kVA</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente1"]["min"], 2, ',', '.')}} kVA</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["aparente1"]["avg"], 2, ',', '.')}} kVA</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente2"]["max"], 2, ',', '.')}} kVA</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente2"]["min"], 2, ',', '.')}} kVA</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["aparente2"]["avg"], 2, ',', '.')}} kVA</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente3"]["max"], 2, ',', '.')}} kVA</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparente3"]["min"], 2, ',', '.')}} kVA</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["aparente3"]["avg"], 2, ',', '.')}} kVA</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Trif&aacute;sico</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparenteTotal"]["max"], 2, ',', '.')}} kVA</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["aparenteTotal"]["min"], 2, ',', '.')}} kVA</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["aparenteTotal"]["avg"], 2, ',', '.')}} kVA</td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="4" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Tension</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension1"]["max"], 2, ',', '.')}} V</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension1"]["min"], 2, ',', '.')}} V</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["tension1"]["avg"], 2, ',', '.')}} V</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension2"]["max"], 2, ',', '.')}} V</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension2"]["min"], 2, ',', '.')}} V</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["tension2"]["avg"], 2, ',', '.')}} V</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension3"]["max"], 2, ',', '.')}} V</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["tension3"]["min"], 2, ',', '.')}} V</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["tension3"]["avg"], 2, ',', '.')}} V</td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="4" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Intensidad</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad1"]["max"], 2, ',', '.')}} A</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad1"]["min"], 2, ',', '.')}} A</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["intensidad1"]["avg"], 2, ',', '.')}} A</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad2"]["max"], 2, ',', '.')}} A</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad2"]["min"], 2, ',', '.')}} A</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["intensidad2"]["avg"], 2, ',', '.')}} A</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad3"]["max"], 2, ',', '.')}} A</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["intensidad3"]["min"], 2, ',', '.')}} A</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["intensidad3"]["avg"], 2, ',', '.')}} A</td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="5" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Factor de Potencia</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 1</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp1"]["max"], 2, ',', '.')}} </td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp1"]["min"], 2, ',', '.')}} </td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["fdp1"]["avg"], 2, ',', '.')}} </td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 2</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp2"]["max"], 2, ',', '.')}} </td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp2"]["min"], 2, ',', '.')}} </td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["fdp2"]["avg"], 2, ',', '.')}} </td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Fase 3</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp3"]["max"], 2, ',', '.')}} </td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdp3"]["min"], 2, ',', '.')}} </td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["fdp3"]["avg"], 2, ',', '.')}} </td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Trif&aacute;sico</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdpTotal"]["max"], 2, ',', '.')}} </td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["fdpTotal"]["min"], 2, ',', '.')}} </td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["fdpTotal"]["avg"], 2, ',', '.')}} </td>
				</tr>
				<tr class="bg-submeter-1" style="vertical-align: middle;color:white;text-align: center;">
					<td rowspan="3" class="bg-submeter-4" style="font-weight: bold;vertical-align: middle;color:white;text-align: center;">Otros</td>
					<td></td>
					<td>M&aacute;xima</td>
					<td>M&iacute;nima</td>
					<td>Media</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Frecuencia</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["freq"]["max"], 2, ',', '.')}} Hz</td>
					<td style="font-size: 15px; ">{{number_format($parsedTableData["freq"]["min"], 2, ',', '.')}} Hz</td>
					<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["freq"]["avg"], 2, ',', '.')}} Hz</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">cos Φ</td>
					@if(number_format($parsedTableData["cosphi"]["max"], 0, ',', '.') == 0)
						<td style="font-size: 15px; "> --- </td>
						<td style="font-size: 15px; "> --- </td>
						<td style="font-weight: bold;font-size: 15px;"> --- </td>
					@else
						<td style="font-size: 15px; ">{{number_format($parsedTableData["cosphi"]["max"], 2, ',', '.')}}</td>
						<td style="font-size: 15px; ">{{number_format($parsedTableData["cosphi"]["min"], 2, ',', '.')}}</td>
						<td style="font-weight: bold;font-size: 15px;">{{number_format($parsedTableData["cosphi"]["avg"], 2, ',', '.')}}</td>
					@endif
				</tr>
			</table>
		</div>
	</div>

	<div class="row justify-start">
		<div class="column">
			<div class="row justify-start flex-wrap-reverse items-center m-0">
				<div class="cards mr-content">
					<div class="card card-anlz shadow">
						<div class="card__header" style="background-color: {{$color_etiqueta}}">
							<div class="card__title">Energía Total Activa</div>
						</div>
						<div class="card__body">
							<p class="text-center w-100">{{number_format($analyzer_energy['energia_activa'],0,',','.')}} kWh<br/></p>
						</div>
					</div>
					<div class="card card-anlz shadow">
						<div class="card__header" style="background-color: {{$color_etiqueta}}">
							<div class="card__title">Energía Total Reactiva</div>
						</div>
						<div class="card__body">
							<p class="text-center w-100">{{number_format($analyzer_energy['energia_reactiva'],0,',','.')}} kVArh</p>
						</div>
					</div>
				</div>
				@if(!empty($table_data->last()->date))
					<div class="alert alert-info w-max m-auto">
						Ultimo Registro: {{$table_data->last()->date}} a las {{$table_data->last()->time}}
					</div>
				@endif
				<div class="btn-container reverse flex-grow-1 mb-4">
					<a href="{!! route('analyzersgroup',[$user->id]) !!}" class="btn btn-info flex-grow-0"><i class="fa fa-undo"></i></a>
				</div>
			</div>
		</div>
	</div>	

	<div class="row">
		<div class="column col-50">
			<div class="tab-panel flex-grow-1" data-submeter-tab-panel>
				<div class="tabs">
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart1" href="#tab-{{'Potencia_avg_'.$aux_cont}}">Pot. Trif&aacute;sica</a>
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart2" href="#tab-{{'Potencia_max_'.$aux_cont}}">Pot. Activa Fase</a>
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart3" href="#tab-{{'Potencia_Reac_'.$aux_cont}}">Pot. Reactiva Fase</a>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Potencia_avg_'.$aux_cont}}">
					<div class="plot-container" id="{{'Potencia_avg_'.$aux_cont}}"></div>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Potencia_max_'.$aux_cont}}">
					<div class="plot-container" id="{{'Potencia_max_'.$aux_cont}}"></div>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Potencia_Reac_'.$aux_cont}}">
					<div class="plot-container" id="{{'Potencia_Reac_'.$aux_cont}}"></div>
				</div>
			</div>
			<div class="tab-panel flex-grow-1" data-submeter-tab-panel>
				<div class="tabs">
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart4" href="#tab-{{'Corrientes_'.$aux_cont}}">Intensidades</a>
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart5" href="#tab-{{'FDP_'.$aux_cont}}">FDP</a>
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart6" href="#tab-{{'Cosfi_'.$aux_cont}}">Cos Φ</a>
					<a class="tab-link" data-submeter-toggle="tab" data-render-chart="chart7" href="#tab-{{'Frecuencia_'.$aux_cont}}">Frecuencia</a>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Corrientes_'.$aux_cont}}">
					<div class="plot-container" id="{{'Corrientes_'.$aux_cont}}"></div>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'FDP_'.$aux_cont}}">
					<div class="plot-container" id="{{'FDP_'.$aux_cont}}"></div>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Cosfi_'.$aux_cont}}">
					<div class="plot-container" id="{{'Cosfi_'.$aux_cont}}"></div>
				</div>
				<div class="tab-content graph plot-tab flex-grow-1" id="tab-{{'Frecuencia_'.$aux_cont}}">
					<div class="plot-container" id="{{'Frecuencia_'.$aux_cont}}"></div>
				</div>
			</div>
		</div>
		<div class="column col-50">
			<div class="table-container">
				<table class="table-responsive text-center">		
					<colgroup>
						<col class="shrink">
						<col class="shrink">
						<col>
						<col>
						<col>
					</colgroup>			
					<tbody>
						<tr class="row-highlight">
							<td rowspan="5" class="bg-primary color-fff">Potencia Activa</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["activa1"]["max"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa1"]["min"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa1"]["avg"], 2, ',', '.')}} kW</td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["activa2"]["max"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa2"]["min"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa2"]["avg"], 2, ',', '.')}} kW</td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["activa3"]["max"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa3"]["min"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activa3"]["avg"], 2, ',', '.')}} kW</td>
						</tr>
						<tr>
							<td>Trif&aacute;sico</td>
							<td>{{number_format($parsedTableData["activaTotal"]["max"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activaTotal"]["min"], 2, ',', '.')}} kW</td>
							<td>{{number_format($parsedTableData["activaTotal"]["avg"], 2, ',', '.')}} kW</td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="5" class="bg-primary color-fff">Potencia Reactiva</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["reactiva1"]["max"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva1"]["min"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva1"]["avg"], 2, ',', '.')}} kVAr</td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["reactiva2"]["max"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva2"]["min"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva2"]["avg"], 2, ',', '.')}} kVAr</td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["reactiva3"]["max"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva3"]["min"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactiva3"]["avg"], 2, ',', '.')}} kVAr</td>
						</tr>
						<tr>
							<td>Trif&aacute;sico</td>
							<td>{{number_format($parsedTableData["reactivaTotal"]["max"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactivaTotal"]["min"], 2, ',', '.')}} kVAr</td>
							<td>{{number_format($parsedTableData["reactivaTotal"]["avg"], 2, ',', '.')}} kVAr</td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="5" class="bg-primary color-fff">Potencia Aparente</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["aparente1"]["max"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente1"]["min"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente1"]["avg"], 2, ',', '.')}} kVA</td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["aparente2"]["max"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente2"]["min"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente2"]["avg"], 2, ',', '.')}} kVA</td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["aparente3"]["max"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente3"]["min"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparente3"]["avg"], 2, ',', '.')}} kVA</td>
						</tr>
						<tr>
							<td>Trif&aacute;sico</td>
							<td>{{number_format($parsedTableData["aparenteTotal"]["max"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparenteTotal"]["min"], 2, ',', '.')}} kVA</td>
							<td>{{number_format($parsedTableData["aparenteTotal"]["avg"], 2, ',', '.')}} kVA</td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="4" class="bg-primary color-fff">Tension</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["tension1"]["max"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension1"]["min"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension1"]["avg"], 2, ',', '.')}} V</td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["tension2"]["max"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension2"]["min"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension2"]["avg"], 2, ',', '.')}} V</td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["tension3"]["max"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension3"]["min"], 2, ',', '.')}} V</td>
							<td>{{number_format($parsedTableData["tension3"]["avg"], 2, ',', '.')}} V</td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="4" class="bg-primary color-fff">Intensidad</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["intensidad1"]["max"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad1"]["min"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad1"]["avg"], 2, ',', '.')}} A</td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["intensidad2"]["max"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad2"]["min"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad2"]["avg"], 2, ',', '.')}} A</td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["intensidad3"]["max"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad3"]["min"], 2, ',', '.')}} A</td>
							<td>{{number_format($parsedTableData["intensidad3"]["avg"], 2, ',', '.')}} A</td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="5" class="bg-primary color-fff">Factor de Potencia</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Fase 1</td>
							<td>{{number_format($parsedTableData["fdp1"]["max"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp1"]["min"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp1"]["avg"], 2, ',', '.')}} </td>
						</tr>
						<tr>
							<td>Fase 2</td>
							<td>{{number_format($parsedTableData["fdp2"]["max"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp2"]["min"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp2"]["avg"], 2, ',', '.')}} </td>
						</tr>
						<tr>
							<td>Fase 3</td>
							<td>{{number_format($parsedTableData["fdp3"]["max"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp3"]["min"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdp3"]["avg"], 2, ',', '.')}} </td>
						</tr>
						<tr>
							<td>Trif&aacute;sico</td>
							<td>{{number_format($parsedTableData["fdpTotal"]["max"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdpTotal"]["min"], 2, ',', '.')}} </td>
							<td>{{number_format($parsedTableData["fdpTotal"]["avg"], 2, ',', '.')}} </td>
						</tr>
						<tr class="row-highlight">
							<td rowspan="3" class="bg-primary color-fff">Otros</td>
							<td></td>
							<td>M&aacute;xima</td>
							<td>M&iacute;nima</td>
							<td>Media</td>
						</tr>
						<tr>
							<td>Frecuencia</td>
							<td>{{number_format($parsedTableData["freq"]["max"], 2, ',', '.')}} Hz</td>
							<td>{{number_format($parsedTableData["freq"]["min"], 2, ',', '.')}} Hz</td>
							<td>{{number_format($parsedTableData["freq"]["avg"], 2, ',', '.')}} Hz</td>
						</tr>
						<tr>
							<td>cos Φ</td>
							@if(number_format($parsedTableData["cosphi"]["max"], 0, ',', '.') == 0)
								<td> --- </td>
								<td> --- </td>
								<td> --- </td>
							@else
								<td>{{number_format($parsedTableData["cosphi"]["max"], 2, ',', '.')}}</td>
								<td>{{number_format($parsedTableData["cosphi"]["min"], 2, ',', '.')}}</td>
								<td>{{number_format($parsedTableData["cosphi"]["avg"], 2, ',', '.')}}</td>
							@endif
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="column col-50">
			<div class="table-container">
				<table class="table-responsive table-striped text-center">
					<colgroup>
						<col class="shrink">
						<col class="shrink">
						<col>
						<col>
						<col>
					</colgroup>
					<thead>
						<tr>
							<th class="bg-primary color-fff">Distorsión Armónica</th>
							<th class="bg-secondary color-fff"></th>
							<th class="bg-secondary color-fff">M&aacute;xima</th>
							<th class="bg-secondary color-fff">M&iacute;nima</th>
							<th class="bg-secondary color-fff">Media</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="bg-primary color-fff" rowspan="3">THDU</td>
							<td>F1</td>
							<td>{{number_format($parsedTableData["thdu1"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu1"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu1"]["avg"], 2, ',', '.')}} %</td>
						</tr>
						<tr>
							<td>F2</td>
							<td>{{number_format($parsedTableData["thdu2"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu2"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu2"]["avg"], 2, ',', '.')}} %</td>
						</tr>
						<tr>
							<td>F3</td>
							<td>{{number_format($parsedTableData["thdu3"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu3"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdu3"]["avg"], 2, ',', '.')}} %</td>
						</tr>
						<tr>
							<td class="bg-primary color-fff" rowspan="3">THDI</td>
							<td>F1</td>
							<td>{{number_format($parsedTableData["thdi1"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi1"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi1"]["avg"], 2, ',', '.')}} %</td>
						</tr>
						<tr>
							<td>F2</td>
							<td>{{number_format($parsedTableData["thdi2"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi2"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi2"]["avg"], 2, ',', '.')}} %</td>
						</tr>
						<tr>
							<td>F3</td>
							<td>{{number_format($parsedTableData["thdi3"]["max"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi3"]["min"], 2, ',', '.')}} %</td>
							<td>{{number_format($parsedTableData["thdi3"]["avg"], 2, ',', '.')}} %</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="column col-50">
			<form class="form card shadow" action="{{route('analizadores.informes.alertas',[$user->id, $analizador->id])}}" method="POST">
				{{ csrf_field() }}

				<div class="card__header">
					<div class="card__title">Programación de informes y alertas</div>
					<button type="submit" class="btn btn-info"><i class="fa fa-save"></i><span>Aplicar</span></button>
				</div>

				<div class="card__body">
					<div class="row">
						<div class="column col-50 m-0">
							<div class="form-group m-0">
								<input class="form-control checkbox" type="checkbox" name="my_checkbox1" id="my_checkbox1" @if($informes_programados == 1) checked @endif>
								<label class="switch2" for="my_checkbox1"></label>
								<label class="form-label w-auto" for="my_checkbox1">Informes</label>
							</div>
						</div>
						<div class="column col-50 m-0">
							<div class="form-group m-0">
								<input class="form-control checkbox" type="checkbox" name="my_checkbox2" id="my_checkbox2" @if($alertas_programados == 1) checked @endif>
								<label class="switch2" for="my_checkbox2"></label>
								<label class="form-label w-auto" for="my_checkbox2">Alertas</label>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="btn-container end">
				<button class="btn" id="exportButton">Generar <br>PDF</button>
				<button class="btn" id="export_data"> Exportar <br>Datos (PDF)</button>
				<form id="export-csv" name="export-csv" action="{{route('export.csv.analizador')}}" method="POST">
					{{ csrf_field() }}
					<input type="hidden" name="analizador_id" value="{{$analizador->id}}">
					<input type="hidden" name="date_from" value="{{$date_from}}">
					<input type="hidden" name="date_to" value="{{$date_to}}">
					<button class="btn" type="submit"> Exportar <br>Datos (CSV)</button>
				</form>
			</div>
		</div>
	</div>

	<form class="d-none" method="post" id="form-pdf" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$analizador->label])}}">
		{{ csrf_field() }}
	</form>
@endsection

@section('modals')
  @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')
<script>
	const tabPanelList =  document.querySelectorAll("[data-submeter-tab-panel]")
	tabPanelList.forEach((tabPanel) => {
		const tabList = tabPanel.querySelectorAll('[data-submeter-toggle="tab"]')		
		if (isNull(tabList)) return

		let activeTab = tabList[0]
		let activeTabContent = getTarget(activeTab)
		activeTab.classList.add("active")
		activeTabContent.classList.add("active")

		tabList.forEach((tab) => {
			tab.addEventListener("click", (e) => {
				e.preventDefault()
				if (e.target === activeTab){
					return
				}
				
				activeTab.classList.remove("active")
				activeTab = tab
				activeTab.classList.add("active")
				
				activeTabContent.classList.remove("active")
				activeTabContent = getTarget(activeTab)
				activeTabContent.classList.add("active")
			})
		})
	})

	function getTarget(element){
		let targetId

		if (element.tagName === "BUTTON"){
			targetId = element.dataset.target
		} else if (element.tagName === "A"){
			targetId = element.getAttribute("href")
		} 

		if (!isNull(targetId)){
			const target = document.querySelector(targetId)
			return target
		}

		return
	}

	function isNull(variable){
		return variable === undefined || variable === null
	}
</script>
@include('Dashboard.includes.scripts_modal_interval')
@include('Dashboard.includes.script_intervalos')
<script src="{{asset('js/canvas.js')}}"></script>
@include('analizadores.scripts.script_graficas_analizadores')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/2.3.2/jspdf.plugin.autotable.js"></script>
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
<script>
	$("#export_data").click(function(){
		var dattos2 = @php echo json_encode($datos_analizador_corriente)@endphp;
		var dattos1 = @php echo json_encode($datos_analizador_potencia)@endphp;
		var name = @php echo json_encode($user->name)@endphp;
		var cont = @php echo json_encode($contador_label)@endphp;
		var date_from = @php echo json_encode($date_from)@endphp;
		var date_to = @php echo json_encode($date_to)@endphp;
		var analizador = @php echo json_encode($analizador->label) @endphp;
		// console.log(cont);
		// console.log(dattos);
		var datapoins = [];
		var datapoins2 = [];
		for (var i = 0; i < dattos1.length; i++) {
			datapoins.push({"fecha": dattos1[i]['date'], "tiempo": dattos1[i]['time'], "Potencia_Activa_L1": dattos1[i]['potencia_activa_L1'], "Potencia_Activa_L2": dattos1[i]['potencia_activa_L2'], "Potencia_Activa_L3": dattos1[i]['potencia_activa_L3']},)
		};

		for (var i = 0; i < dattos2.length; i++) {

			datapoins2.push({"fecha": dattos2[i]['date'], "tiempo": dattos2[i]['time'], "Corriente_L1": dattos2[i]['corriente_L1'], "Corriente_L2": dattos2[i]['corriente_L2'], "Corriente_L3": dattos2[i]['corriente_L3']},)
		};

		// console.log(datapoins);
		var pdf = new jsPDF('l');
		pdf.setFontSize(11);
		pdf.text(15,15,"Empresa: "+name);
		pdf.text(15,23,"Contador:"+cont);
		pdf.text(15,31,"Analizador: "+analizador);
		pdf.text(15,39,"Intervalo: Desde "+date_from+" hasta "+date_to);

		var pdf2 = new jsPDF('l');
		pdf2.setFontSize(11);
		pdf2.text(15,15,"Empresa: "+name);
		pdf2.text(15,23,"Contador:"+cont);
		pdf2.text(15,31,"Analizador: "+analizador);
		pdf2.text(15,39,"Intervalo: Desde "+date_from+" hasta "+date_to);

		// var columns = ["Fecha", "Tiempo", "EAct Imp(kWh)", "EAct exp(kWh)","ERInd_imp(kVArh)","ERInd_exp(kVArh)","ERCap_exp(kVArh)","ERCap_imp(kVArh)"];
		var columns = [
				{title: "Fecha", dataKey: "fecha"},
				{title: "Tiempo", dataKey: "tiempo"},
				{title: "Potencia Activa L1 (kW)", dataKey: "Potencia_Activa_L1"},
				{title: "Potencia Activa L2 (kW)", dataKey: "Potencia_Activa_L2"},
				{title: "Potencia Activa L3 (kW)", dataKey: "Potencia_Activa_L3"},
		];
		var columns2 = [
				{title: "Fecha", dataKey: "fecha"},
				{title: "Tiempo", dataKey: "tiempo"},
				{title: "Corriente L1 (A)", dataKey: "Corriente_L1"},
				{title: "Corriente L2 (A)", dataKey: "Corriente_L2"},
				{title: "Corriente L3 (A)", dataKey: "Corriente_L3"},
		];
		var data = [datapoins];
		var data2 = [datapoins2];

		pdf.autoTable(columns,datapoins,
		{ margin:{ top: 50 }}
		);

		pdf2.autoTable(columns2,datapoins2,
		{ margin:{ top: 50 }}
		);

		pdf.save("Datos_Potencia_"+analizador+".pdf");
		pdf2.save("Datos_Corriente_"+analizador+".pdf");
	});
</script>
@endsection
