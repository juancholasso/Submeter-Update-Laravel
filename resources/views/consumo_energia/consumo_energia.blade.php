@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 3])
@endsection

@section('content')
	{{-- Gráfico 1 --}}
	<form action="{{route('config.subperiodo')}}" id="form-subperiod" method="POST">
		{{ csrf_field() }}
		<input type="hidden" name="option_interval" value="9">
		<input type="hidden" name="label_intervalo" value="{{ $dataSubperiodo['label'] }}">
		<input type="hidden" name="date_from_personalice" value="">
		<input type="hidden" name="date_to_personalice" value="">
		<input type="hidden" name="before_navigation" value="1">
		<input type="hidden" name="dates_begin" value='{!! json_encode($dataSubperiodo["begin_periods"]) !!}' />
		<input type="hidden" name="dates_end" value='{!! json_encode($dataSubperiodo["end_periods"]) !!}' />
		<input type="hidden" name="user_id" value="{{$id}}">
	</form>

	<div class="d-none" >
		<div class="pdf-header">
			<div class="container" style="width:100%; display: inline-block">
				<div class="row">
					<div class="col">
						<img class="float-left" width="60" height="60" src="{{asset($dir_image_count)}}">
					</div>
					<div class="col">
						<h5 style="text-align: center;">Informe Consumo Energía<h5>
					</div>
					<div class="col">
					<img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
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
				</table>
				<br>
				<br>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="column">
			<div class="plot-tab graph shadow">
				@if($contador2->tipo == 1)
					<input type="hidden" class="plot-name" value='{{ $dataPlotting["activa"]["name"] }}' />
					<input type="hidden" class="plot-labels" value='{!! $dataPlotting["activa"]["labels"] !!}' />
					<input type="hidden" class="plot-index-label" value='{!! $dataPlotting["activa"]["index_label"] !!}' />
					<input type="hidden" class="plot-suffix" value='{{ $dataPlotting["activa"]["suffix"] }}' />
					<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["activa"]["time_label"] }}' />
					@foreach($dataPlotting["activa"]["series"] as $serie)
						<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
						<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
						<input type="hidden" class="serie-suffix" value='{!! $serie["suffix"] !!}' />
						<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
						<input type="hidden" class="serie-aux-label" value='{{ $serie["aux_label"] }}' />
						<input type="hidden" class="serie-total" value='{{ number_format($serie["total"],0,',','.') }}' />
					@endforeach
				@else
					<input type="hidden" class="plot-name" value='{{ $dataPlotting["activareactiva"]["name"] }}' />
					<input type="hidden" class="plot-labels" value='{!! $dataPlotting["activareactiva"]["labels"] !!}' />
					<input type="hidden" class="plot-index-label" value='{!! $dataPlotting["activareactiva"]["index_label"] !!}' />
					<input type="hidden" class="plot-suffix" value='{{ $dataPlotting["activareactiva"]["suffix"] }}' />
					<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["activareactiva"]["time_label"] }}' />
					@foreach($dataPlotting["activareactiva"]["series"] as $serie)
						<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
						<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
						<input type="hidden" class="serie-suffix" value='{!! $serie["suffix"] !!}' />
						<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
						<input type="hidden" class="serie-aux-label" value='{{ $serie["aux_label"] }}' />
						<input type="hidden" class="serie-total" value='{{ number_format($serie["total"],0,',','.') }}' />
					@endforeach
				@endif
				<div id="plot_consumo" class="plot-container"></div>
			</div>
		</div>
	</div>

	{{-- Gráfico 2 --}}
	<div class="row">
		<div class="column">
			<div class="plot-tab graph shadow">
				@if($contador2->tipo == 1)
					<input type="hidden" class="plot-name" value='{{ $dataPlotting["reactiva"]["name"] }}' />
					<input type="hidden" class="plot-labels" value='{!! $dataPlotting["reactiva"]["labels"] !!}' />
					<input type="hidden" class="plot-index-label" value='{!! $dataPlotting["reactiva"]["index_label"] !!}' />
					<input type="hidden" class="plot-suffix" value='{{ $dataPlotting["reactiva"]["suffix"] }}' />
					<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["reactiva"]["time_label"] }}' />
					@foreach($dataPlotting["reactiva"]["series"] as $serie)
						<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
						<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
						<input type="hidden" class="serie-suffix" value='{!! $serie["suffix"] !!}' />
						<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
						<input type="hidden" class="serie-aux-label" value='{{ $serie["aux_label"] }}' />
						<input type="hidden" class="serie-total" value='{{ number_format($serie["total"],0,',','.') }}' />
					@endforeach
				@else
					<input type="hidden" class="plot-name" value='{{ $dataPlotting["generacion"]["name"] }}' />
					<input type="hidden" class="plot-labels" value='{!! $dataPlotting["generacion"]["labels"] !!}' />
					<input type="hidden" class="plot-index-label" value='{!! $dataPlotting["generacion"]["index_label"] !!}' />
					<input type="hidden" class="plot-suffix" value='{{ $dataPlotting["generacion"]["suffix"] }}' />
					<input type="hidden" class="plot-time-label" value='{{ $dataPlotting["generacion"]["time_label"] }}' />
					@foreach($dataPlotting["generacion"]["series"] as $serie)
						<input type="hidden" class="serie-name" value='{{ $serie["name"] }}' />
						<input type="hidden" class="serie-color" value='{!! $serie["color"] !!}' />
						<input type="hidden" class="serie-suffix" value='{!! $serie["suffix"] !!}' />
						<input type="hidden" class="serie-value" value='{!! $serie["values"] !!}' />
						<input type="hidden" class="serie-aux-label" value='{{ $serie["aux_label"] }}' />
						<input type="hidden" class="serie-total" value='{{ number_format($serie["total"],0,',','.') }}' />
					@endforeach
				@endif
				<div id="plot_aux" class="plot-container"></div>
			</div>
			<div class="btn-container">
				<form id="export-csv-1" class="d-none" name="export-csv" action="{{route('export.csv.energia')}}" method="POST">
					{{ csrf_field() }}
					<input type="hidden" name="user_id" value="{{$user->id}}">
					<input type="hidden" name="date_from" value="{{$date_from}}">
					<input type="hidden" name="date_to" value="{{$date_to}}">
				</form>
				
				@if($contador2->tipo == 2)
					<form id="export-csv-2" class="d-none" name="export-csv" action="{{route('export.csv.generacion')}}" method="POST">
						{{ csrf_field() }}
						<input type="hidden" name="user_id" value="{{$user->id}}">
						<input type="hidden" name="date_from" value="{{$date_from}}">
						<input type="hidden" name="date_to" value="{{$date_to}}">
					</form>
					<form id="export-csv-3" class="d-none" name="export-csv" action="{{route('export.csv.balance')}}" method="POST">
						{{ csrf_field() }}
						<input type="hidden" name="user_id" value="{{$user->id}}">
						<input type="hidden" name="date_from" value="{{$date_from}}">
						<input type="hidden" name="date_to" value="{{$date_to}}">
					</form>
				@endif
				<div class="btn-container">
					<button class="btn" type="submit" form="export-csv-1"> Exportar datos <br> Consumo (CSV)</button>
					@if($contador2->tipo == 2)
						<button class="btn" type="submit" form="export-csv-2"> Exportar datos <br> Generación (CSV)</button>
						<button class="btn" type="submit" form="export-csv-3"> Exportar datos <br> Balance (CSV)</button>
					@endif
				</div>
				<form id="form-pdf" class="d-none" method="post" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$contador_label])}}">
        	{{ csrf_field() }}
        </form>
				<button class="btn" id="exportButton"> Generar <br> Informe PDF</button>
			</div>
		</div>
	</div>
		
	{{-- Tabla Consumo --}}
	<div class="row">
		<div class="column">
			<div class="table-container">
				<table class="table-responsive table-striped table-consumption text-center">
					<caption>Consumo</caption>
					<thead>
						<tr>
							<th>{{ $dataConsumo["aux_label"] }}</th>
							@foreach($data_calculos["vector_potencia"] as $index => $value)
								<th>P{{ $index + 1}}</th>
							@endforeach
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
							<tr>
								<th>
									<span>
										{{$nombre}}
									</span>
								</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td>
										@if($dataConsumo["EActiva"][$keyRow][$idxPotencia] > 0)
											<span>
												{{ number_format($dataConsumo["EActiva"][$keyRow][$idxPotencia],0,',','.') }} kWh
											</span> <br/>
											<span>
												{{ number_format($dataConsumo["EActivaPorc"][$keyRow][$idxPotencia],2,',','.') }} %
											</span>
										@else
											<span></span> <br/>
											<span></span>
										@endif
									</td>
								@endforeach
								<td>
									<span>
										{{ number_format($dataConsumo["EActivaTotales"][$keyRow],0,',','.') }} kWh
									</span>
								</td>
							</tr>
						@endforeach
						<tr class="row-highlight">
							<th>Total</th>
							@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
								<td>
									<span>
										{{ number_format($dataConsumo["EActivaPeriodo"][$idxPotencia],0,',','.') }} kWh
									</span> <br/>
									<span>
										{{ number_format($dataConsumo["EActivaPeriodoPorc"][$idxPotencia],2,',','.') }} %
									</span>
								</td>
							@endforeach
							<td>
								<span>
									{{ number_format($dataConsumo["totalActiva"],0,',','.') }} kWh
								</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="d-none">
		<div class="card mt-4 export-pdf" data-pdforder="1">
			<div class="card-header bg-submeter-4">
				<h5 class="mb-0 text-white font-weight-bold"> Consumo</h5>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped-submeter table-bordered table-responsive-xl table-primary table-hover bg-white">
					<thead>
						<tr>
							<th scope="col" class="text-center bg-submeter-potencia-total py-2">{{ $dataConsumo["aux_label"] }}</th>
							@foreach($data_calculos["vector_potencia"] as $index => $value)
								<th scope="col" class="text-center bg-submeter-potencia-{{$index + 1}} text-white py-2">P{{ $index + 1}}</th>
							@endforeach
							<th class="text-center bg-submeter-potencia-total py-2">Total</th>
						</tr>
					</thead>
					<tbody>
						@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
							<tr>
								<th scope="row" class="text-center py-2 align-middle">
									<span class="text-dark text-nowrap">
										{{$nombre}}
									</span>
								</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td class="text-center py-2">
										@if($dataConsumo["EActiva"][$keyRow][$idxPotencia] > 0)
												<span class="text-dark text-nowrap">
													{{ number_format($dataConsumo["EActiva"][$keyRow][$idxPotencia],0,',','.') }} kWh
												</span> <br/>
												<span class="text-dark text-nowrap">
													{{ number_format($dataConsumo["EActivaPorc"][$keyRow][$idxPotencia],2,',','.') }} %
												</span>
											@else
												<span class="text-dark text-nowrap"></span> <br/>
												<span class="text-dark text-nowrap"></span>
										@endif
									</td>
								@endforeach
								<td class="text-center py-2 align-middle">
									<span class="text-dark text-nowrap">
										{{ number_format($dataConsumo["EActivaTotales"][$keyRow],0,',','.') }} kWh
									</span>
								</td>
							</tr>
						@endforeach
						<tr class="bg-white">
							<th class="text-center align-middle">Total</th>
							@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
								<td class="text-center">
									<span class="text-dark text-nowrap font-weight-bold">
										{{ number_format($dataConsumo["EActivaPeriodo"][$idxPotencia],0,',','.') }} kWh
									</span> <br/>
									<span class="text-dark text-nowrap font-weight-bold">
										{{ number_format($dataConsumo["EActivaPeriodoPorc"][$idxPotencia],2,',','.') }} %
									</span>
								</td>
							@endforeach
							<td class="text-center align-middle">
								<span class="text-dark text-nowrap font-weight-bold">
									{{ number_format($dataConsumo["totalActiva"],0,',','.') }} kWh
								</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	@if($contador2->tipo == 2)
		{{-- Tabla Generación --}}
		<div class="row">
			<div class="column">
				<div class="table-container">
					<table class="table-responsive table-striped table-consumption text-center">
						<caption> Generación </caption>
						<thead>
							<tr>
								<th>{{ $dataConsumo["aux_label"] }}</th>
									@foreach($data_calculos["vector_potencia"] as $index => $value)
										<th>P{{ $index + 1}}</th>
									@endforeach
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
								<tr>
									<th>
										<span>
											{{$nombre}}
										</span>
									</th>
									@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
										<td>
											@if($dataConsumo["EGeneracion"][$keyRow][$idxPotencia] > 0)
												<span>
													{{ number_format($dataConsumo["EGeneracion"][$keyRow][$idxPotencia],0,',','.') }} kWh
												</span> <br/>
												<span>
													{{ number_format($dataConsumo["EGeneracionPorc"][$keyRow][$idxPotencia],2,',','.') }} %
												</span>
											@else
												<span></span> <br/>
												<span></span>
											@endif
										</td>
									@endforeach
									<td>
										<span>
											{{ number_format($dataConsumo["EGeneracionTotales"][$keyRow],0,',','.') }} kWh
										</span>
									</td>
								</tr>
							@endforeach
							<tr class="row-highlight">
								<th>Total</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td>
										<span>
											{{ number_format($dataConsumo["EGeneracionPeriodo"][$idxPotencia],0,',','.') }} kWh
										</span> <br/>
										<span>
											{{ number_format($dataConsumo["EGeneracionPeriodoPorc"][$idxPotencia],2,',','.') }} %
										</span>
									</td>
								@endforeach
								<td>
									<span>
										{{ number_format($dataConsumo["totalGeneracion"],0,',','.') }} kWh
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="d-none">
			<div class="card mt-4 export-pdf" data-pdforder="2">
				<div class="card-header bg-submeter-4">
					<h5 class="mb-0 text-white font-weight-bold"> Generación</h5>
				</div>
				<div class="card-body p-0">
					<table class="table table-striped-submeter table-bordered table-responsive-xl table-primary table-hover bg-white">
						<thead>
									<tr>
										<th scope="col" class="text-center bg-submeter-potencia-total py-2">{{ $dataConsumo["aux_label"] }}</th>
											@foreach($data_calculos["vector_potencia"] as $index => $value)
												<th scope="col" class="text-center bg-submeter-potencia-{{$index + 1}} text-white py-2">P{{ $index + 1}}</th>
											@endforeach
											<th class="text-center bg-submeter-potencia-total py-2">Total</th>
									</tr>
							</thead>
						<tbody>
							@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
								<tr>
									<th scope="row" class="text-center py-2 align-middle">
										<span class="text-dark text-nowrap">
											{{$nombre}}
										</span>
									</th>
									@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
										<td class="text-center py-2">
											@if($dataConsumo["EGeneracion"][$keyRow][$idxPotencia] > 0)
													<span class="text-dark text-nowrap">
														{{ number_format($dataConsumo["EGeneracion"][$keyRow][$idxPotencia],0,',','.') }} kWh
													</span> <br/>
													<span class="text-dark text-nowrap">
														{{ number_format($dataConsumo["EGeneracionPorc"][$keyRow][$idxPotencia],2,',','.') }} %
													</span>
												@else
													<span class="text-dark text-nowrap"></span> <br/>
													<span class="text-dark text-nowrap"></span>
											@endif
										</td>
									@endforeach
									<td class="text-center py-2 align-middle">
										<span class="text-dark text-nowrap">
											{{ number_format($dataConsumo["EGeneracionTotales"][$keyRow],0,',','.') }} kWh
										</span>
									</td>
								</tr>
							@endforeach
							<tr class="bg-white">
								<th class="text-center align-middle">Total</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td class="text-center">
										<span class="text-dark text-nowrap font-weight-bold">
											{{ number_format($dataConsumo["EGeneracionPeriodo"][$idxPotencia],0,',','.') }} kWh
										</span> <br/>
										<span class="text-dark text-nowrap font-weight-bold">
											{{ number_format($dataConsumo["EGeneracionPeriodoPorc"][$idxPotencia],2,',','.') }} %
										</span>
									</td>
								@endforeach
								<td class="text-center align-middle">
									<span class="text-dark text-nowrap font-weight-bold">
										{{ number_format($dataConsumo["totalGeneracion"],0,',','.') }} kWh
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		{{-- Tabla balance --}}
		<div class="row">
			<div class="column">
				<div class="table-container">
					<table class="table-responsive table-striped table-consumption text-center">
						<caption>Balance</caption>
						<thead>
							<tr>
								<th>{{ $dataConsumo["aux_label"] }}</th>
								@foreach($data_calculos["vector_potencia"] as $index => $value)
									<th>P{{ $index + 1}}</th>
								@endforeach
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
								<tr>
									<th>
										<span>
											{{$nombre}}
										</span>
									</th>
									@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
										<td>
											@if($dataConsumo["EBalance"][$keyRow][$idxPotencia] > 0)
												<span>
													{{ number_format($dataConsumo["EBalance"][$keyRow][$idxPotencia],0,',','.') }} kWh
												</span>
											@endif
										</td>
									@endforeach
									<td>
										<span>
											{{ number_format($dataConsumo["EBalanceTotales"][$keyRow],0,',','.') }} kWh
										</span>
									</td>
								</tr>
							@endforeach
							<tr class="row-highlight">
								<th>Total</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td>
										<span>
											{{ number_format($dataConsumo["EBalancePeriodo"][$idxPotencia],0,',','.') }} kWh
										</span>
									</td>
								@endforeach
								<td>
									<span>
										{{ number_format($dataConsumo["totalBalance"],0,',','.') }} kWh
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="d-none">
			<div class="card export-pdf mt-4" data-pdforder="3">
				<div class="card-header bg-submeter-4">
					<h5 class="mb-0 text-white font-weight-bold"> Balance </h5>
				</div>
				<div>
					<table class="table table-striped-submeter table-bordered table-responsive-xl table-primary table-hover bg-white">
						<thead>
							<tr>
								<th scope="col" class="text-center bg-submeter-potencia-total py-2">{{ $dataConsumo["aux_label"] }}</th>
									@foreach($data_calculos["vector_potencia"] as $index => $value)
										<th scope="col" class="text-center bg-submeter-potencia-{{$index + 1}} text-white py-2">P{{ $index + 1}}</th>
									@endforeach
								<th class="text-center bg-submeter-potencia-total py-2">Total</th>
							</tr>
						</thead>
						<tbody>
							@foreach($dataConsumo["displayValues"] as $keyRow => $nombre)
								<tr>
											<th scope="row" class="text-center py-2 align-middle">
												<span class="text-dark text-nowrap">
													{{$nombre}}
												</span>
											</th>
												@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
													<td class="text-center py-2">
														@if($dataConsumo["EBalance"][$keyRow][$idxPotencia] > 0)
																<span class="text-dark text-nowrap">
																	{{ number_format($dataConsumo["EBalance"][$keyRow][$idxPotencia],0,',','.') }} kWh
																</span>
														@endif
													</td>
												@endforeach
												<td class="text-center py-2 align-middle">
													<span class="text-dark text-nowrap">
														{{ number_format($dataConsumo["EBalanceTotales"][$keyRow],0,',','.') }} kWh
													</span>
												</td>
										</tr>
							@endforeach
							<tr class="bg-white">
								<th class="text-center align-middle">Total</th>
								@foreach($data_calculos["vector_potencia"] as $idxPotencia => $value)
									<td class="text-center">
										<span class="text-dark text-nowrap font-weight-bold">
											{{ number_format($dataConsumo["EBalancePeriodo"][$idxPotencia],0,',','.') }} kWh
										</span>
									</td>
								@endforeach
								<td class="text-center align-middle">
								<span class="text-dark text-nowrap font-weight-bold">
									{{ number_format($dataConsumo["totalBalance"],0,',','.') }} kWh
								</span>
							</td>
							</tr>
						</tbody>
					</table>
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
	<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
	<script src="{{asset('js/jquery.slimscroll.min.js')}}"></script>
	<script src="{{asset('js/custom.js')}}"></script>
	<script src="{{asset('js/screenfull.js')}}"></script>
	{{-- <script src="{{asset('js/jquery.nicescroll.js')}}"></script> --}}
	<script src="{{asset('js/scripts.js')}}"></script>
	<script src="{{asset('js/bootstrap.min.js')}}"> </script>
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

	<script type="text/javascript">
	<!--
    	$(document).ready(function(){
    		createPlots();
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

    	CanvasJS.addCultureInfo("es", {
          decimalSeparator: ",",// Observe ToolTip Number Format
          digitGroupSeparator: "."
        });

    	function createPlots(){
    		var plots = $(".plot-tab");

    		for(var iK = 0; iK < plots.length; iK++) {
    			var plot = $(plots[iK]);
    			var plot_suffix = plot.find(".plot-suffix").val();
    			var plot_time_label = plot.find(".plot-time-label").val();
    			var plot_index_label = plot.find(".plot-index-label").val();
    			var cntPlot = plot.find(".plot-container");
    			var labels = $.parseJSON(plot.find(".plot-labels").val());

    			var seriesValues = plot.find(".serie-value");
    			var seriesColors = plot.find(".serie-color");
					var seriesSuffix = plot.find(".serie-suffix");
    			var seriesName = plot.find(".serie-name");
    			var seriesAuxLabels = plot.find(".serie-aux-label");
    			var seriesTotal = plot.find(".serie-total");

    			var data = new Array();
    			var dataPlot = new Array();
    			var plotTotalData = "";

    			for(var i = 0; i < seriesValues.length; i++) {
    				plotTotalData += $(seriesAuxLabels[i]).val() + ": " + $(seriesTotal[i]).val() + " "  + $(seriesSuffix[i]).val();
    				var serieVal = $(seriesValues[i]).val();
    				serieVal = $.parseJSON(serieVal);
    				var seriedata = new Array();
    				for(j = 0; j < serieVal.length; j++) {
    					var d = {
    							y : serieVal[j],
    							x : j,
    							label: labels[j],
        						click: clickDataSeries
    					};
    					seriedata.push(d);
    				}
    				data[i] = seriedata;

    				if(i == 0) {
    					var tooltip = plot_time_label +": {label} <br> {name}: {y}  " + $(seriesSuffix[i]).val();
    				}
    				else {
    					var tooltip = "{name}: {y}  " + $(seriesSuffix[i]).val();
    				}

    				var conf = {
    						cursor: "zoom-in",
    						type: "column",
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
    						dataPoints: data[i],
    						indexLabelPlacement: "outside",
    						indexLabel: plot_index_label,
    						indexLabelFontColor: "#004165",
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
    					title: plotTotalData,
    					titleFontSize: 12,
    					titleFontColor: "#004165",
    					lineColor: "#004165",
    					labelFontColor: "#004165",
    					tickColor: "#004165"
    					},
    				axisY: {
    					suffix: " " + plot_suffix,
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
    			chart.render();
    		}
    	}

    	function clickDataSeries(e){
				var idx = e.dataPointIndex;
    		var datesBegin = $("#form-subperiod [name='dates_begin']");
    		var datesEnd = $("#form-subperiod [name='dates_end']");
				console.log(idx);
				console.log(datesBegin);
				console.log(datesEnd);
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

    	$("#exportButton").click(function(){

    		var idxBreak = "";

    		var tokenInput = $("#form-pdf input[name='_token']")[0].outerHTML;
    		$("#form-pdf").html("");
    		$("#form-pdf").append(tokenInput);

				var header = $(".pdf-header")[0].outerHTML;
				console.log(header)

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
	-->
	</script>
	<script src="{{asset('js/skycons.js')}}"></script>
@endsection
