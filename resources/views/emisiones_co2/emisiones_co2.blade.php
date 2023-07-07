@extends('Dashboard.layouts.global5')

@section('intervals')
	@include('Dashboard.includes.intervalos')
@endsection

@section('counters')
	@include('Dashboard.includes.contadores', ["menuId" => 11])
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
            <h5 style="text-align: center;">Emisiones CO2<h5>
          </div>
          <div class="col">
            <img class="float-right" width="60" height="60" src="{{asset('images/Logo_WEB_Submeter.png')}}">
          </div>
        </div>
      </div>
      <div>
        <table class="table table-bordered"  id="pdf_encabezado">
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
        </table><br/><br/>
      </div>
      {{--es la version antigua del codigo de arriba
        <p>
          Empresa: {{$user->name}}
          @if(isset($domicilio->suministro_del_domicilio))
            {{$domicilio->suministro_del_domicilio}}
          @else
            sin ubicación
          @endif
        </p>
        <p>Contador: {{$contador_label}}</p>
        <p>Email: {{$user->email}}</p>
        <p>Intervalo: Desde {{$date_from}} hasta {{$date_to}}</p>
      --}}
    </div>
  </div>

  @php $j = 1;@endphp
  @php $total_emisiones = 0; @endphp
  @php $total_emisiones_antes = 0; @endphp
  @php $months_T = array (1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'); @endphp
               
  {{-- @if(isset($domicilio->suministro_del_domicilio))
    <label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">{{$domicilio->suministro_del_domicilio}}</label></label>
  @else
    <label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">sin ubicación</label></label>
  @endif                
  <label class="title-ubicacion">Intervalo: <label class="title-ubicacion2"> Desde {{$date_from}} hasta {{$date_to}}</label></label> --}}
  
  @foreach($emisiones as $emi)
    @php $total_emisiones = $total_emisiones +  $emi->emisiones @endphp
  @endforeach

  @foreach($emisiones_antes as $emi_antes)
    @php $total_emisiones_antes = $total_emisiones_antes +  $emi_antes->emisiones @endphp
  @endforeach

  <div class="row">
    <div class="column">
      <div class="graph shadow plot-tab">
        @php $aux_cont = implode('_', explode(' ', $contador_label)) @endphp
        @include('Dashboard.Graficas.consumo_diario_energia',array('id_var' => 'Emisiones_'.$aux_cont))
      </div>
      <form id="export-csv" class="d-none" name="export-csv" action="{{route('export.csv.co2')}}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="user_id" value="{{$user->id}}">
        <input type="hidden" name="date_from" value="{{$date_from}}">
        <input type="hidden" name="date_to" value="{{$date_to}}">
      </form>     
      <div class="btn-container">
        <button class="btn" type="submit" form="export-csv"> Exportar datos (CSV)</button>
        <button class="btn" id="exportButton"> Generar PDF</button>
      </div>
    </div>
  </div>
      
  @php $titleAxisX = "Total Emisiones CO2: ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq" @endphp
  @if($label_intervalo != 'Personalizado')
    <div class="row">
      <div class="column">
        <div class="table-container">
          <table class="table-responsive table-striped column-header text-center">
            @if($label_intervalo=='Hoy' or $label_intervalo=='Ayer' )
              @php
                $titleAxisX = "$date_from Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq            $date_antes_from Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
              @endphp
              <thead>
                <tr class="row-header">
                  <th>Intervalo</th>
                  <th>{{$date_from}}</th>
                  <th>{{$date_antes_from}}</th>
                  <th>Variación</th>
                  <th>Var (%)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones - $total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              </tbody>
            @elseif($label_intervalo=='Semana Actual' or $label_intervalo=='Semana Anterior')
              @php
                $titleAxisX = "Semana ".date("W", strtotime($date_from))." Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq            Semana ".date("W", strtotime($date_antes_from))." Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
              @endphp
              <thead>
                <tr class="row-header">
                  <th>Intervalo</th>
                  <th>Semana: {{ date("W", strtotime($date_from))}}</th>
                  <th>Semana: {{date("W", strtotime($date_antes_from))}}</th>
                  <th>Variación</th>
                  <th>Var (%)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Lunes</td>
                  @php $emision_lunes = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Lunes')
                        @php $emision_lunes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_lunes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_lunes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_lunes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_lunes_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Lunes')
                        @php $emision_lunes_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_lunes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_lunes_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_lunes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_lunes-$emision_lunes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_lunes_antes!=0)
                    <td>{{number_format((($emision_lunes/$emision_lunes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Martes</td>
                  @php $emision_martes = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Martes')
                        @php $emision_martes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_martes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_martes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_martes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_martes_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Martes')
                          @php $emision_martes_antes=$emision->emisiones   @endphp
                          <td>{{number_format($emision_martes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_martes_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_martes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_martes-$emision_martes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_martes_antes!=0)
                    <td>{{number_format((($emision_martes/$emision_martes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Miércoles</td>
                  @php $emision_miercoles = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Miércoles')
                        @php $emision_miercoles=$emision->emisiones   @endphp
                        <td>{{number_format($emision_miercoles, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_miercoles!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_miercoles===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_miercoles_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Miércoles')
                        @php $emision_miercoles_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_miercoles_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_miercoles_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_miercoles_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_miercoles-$emision_miercoles_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_miercoles_antes!=0)
                    <td>{{number_format((($emision_miercoles/$emision_miercoles_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Jueves</td>
                  @php $emision_jueves = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Jueves')
                        @php $emision_jueves=$emision->emisiones   @endphp
                        <td>{{number_format($emision_jueves, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_jueves!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_jueves===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_jueves_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Jueves')
                        @php $emision_jueves_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_jueves_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_jueves_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_jueves_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_jueves-$emision_jueves_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_jueves_antes!=0)
                    <td>{{number_format((($emision_jueves/$emision_jueves_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Viernes</td>
                  @php $emision_viernes = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Viernes')
                        @php $emision_viernes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_viernes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_viernes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_viernes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_viernes_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Viernes')
                          @php $emision_viernes_antes=$emision->emisiones   @endphp
                          <td>{{number_format($emision_viernes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_viernes_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_viernes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_viernes-$emision_viernes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_viernes_antes!=0)
                    <td>{{number_format((($emision_viernes/$emision_viernes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Sabado</td>
                  @php $emision_sabado = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Sabado')
                        @php $emision_sabado=$emision->emisiones   @endphp
                        <td>{{number_format($emision_sabado, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_sabado!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_sabado===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_sabado_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Sabado')
                        @php $emision_sabado_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_sabado_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_sabado_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_sabado_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_sabado-$emision_sabado_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_sabado_antes!=0)
                    <td>{{number_format((($emision_sabado/$emision_sabado_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Domingo</td>
                  @php $emision_domingo = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Domingo')
                        @php $emision_domingo=$emision->emisiones   @endphp
                        <td>{{number_format($emision_domingo, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_domingo!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_domingo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_domingo_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Domingo')
                          @php $emision_domingo_antes=$emision->emisiones   @endphp
                          <td>{{number_format($emision_domingo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_domingo_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_domingo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_domingo-$emision_domingo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_domingo_antes!=0)
                    <td>{{number_format((($emision_domingo/$emision_domingo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format( $total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              </tbody>
            @elseif($label_intervalo=='Mes Actual' or $label_intervalo=='Mes Anterior')
              <thead>
                <tr class="row-header">
                  <th>Intervalo</th>
                  @php
                    $date_from_nombre = date("m", strtotime($date_from));
                    $date_from_nombre = $months_T[(int)$date_from_nombre];
                    $date_antes_from_nombre = date("m", strtotime($date_antes_from));
                    $date_antes_from_nombre = $months_T[(int)$date_antes_from_nombre];
                    $titleAxisX = "$date_from_nombre (".date("Y", strtotime($date_from)).") Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             $date_antes_from_nombre (".date("Y", strtotime($date_antes_from)).") Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                  @endphp
                  <th>{{$date_from_nombre}} ( {{date("Y", strtotime($date_from))}} )</th>
                  <th>{{ $date_antes_from_nombre}} ( {{date("Y", strtotime($date_antes_from))}} )</th>
                  <th>Variación</th>
                  <th>Var (%)</th>
                </tr>
              </thead>
              <tr>
                <td>Total</td>
                <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                <td>{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                @if($total_emisiones_antes==0)
                  <td> 100 %</td>
                @else
                  <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                @endif
              </tr>
            @elseif($label_intervalo=='Trimestre Actual' or $label_intervalo=='Ultimo Trimestre')
              @php
                $titleAxisX = ceil(date("m", strtotime($date_from))/3)."T (".date("Y", strtotime($date_from)).") Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             ".ceil(date("m", strtotime($date_antes_from))/3)."T (".date("Y", strtotime($date_antes_from)).") Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
              @endphp
              <thead>
                <tr class="row-header">
                  <th>Intervalo</th>
                  <th>{{ceil(date("m", strtotime($date_from))/3)}}T ( {{date("Y", strtotime($date_from))}} )</th>
                  <th> {{ceil(date("m", strtotime($date_antes_from))/3)}}T ( {{date("Y", strtotime($date_antes_from))}} )</th>
                  <th>Variación</th>
                  <th>Var (%)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              </tbody>
            @elseif($label_intervalo=='Año Actual' or $label_intervalo=='Último Año')
              @php
                $titleAxisX = "Año ".date("Y", strtotime($date_from))." Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             Año ".date("Y", strtotime($date_antes_from))." Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
              @endphp
              <thead>
                <tr class="row-header">
                  <th>Intervalo</th>
                  <th>{{ date("Y", strtotime($date_from))}}</th>
                  <th>{{date("Y", strtotime($date_antes_from))}}</th>
                  <th>Variación</th>
                  <th>Var (%)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Enero</td>
                  @php $emision_enero = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Enero('.date("Y", strtotime($date_from)).')')
                        @php $emision_enero=$emision->emisiones   @endphp
                        <td>{{number_format($emision_enero, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_enero!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_enero===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_enero_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Enero('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_enero_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_enero_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_enero_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_enero_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_enero-$emision_enero_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_enero_antes!=0)
                    <td>{{number_format((($emision_enero/$emision_enero_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Febrero</td>
                  @php $emision_febrero = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Febrero('.date("Y", strtotime($date_from)).')')
                        @php $emision_febrero=$emision->emisiones   @endphp
                        <td>{{number_format($emision_febrero, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_febrero!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_febrero===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_febrero_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Febrero('.date("Y", strtotime($date_antes_from)).')')
                          @php $emision_febrero_antes=$emision->emisiones   @endphp
                          <td>{{number_format($emision_febrero_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_febrero_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_febrero_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_febrero-$emision_febrero_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_febrero_antes!=0)
                    <td>{{number_format((($emision_febrero/$emision_febrero_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Marzo</td>
                  @php $emision_marzo = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Marzo('.date("Y", strtotime($date_from)).')')
                        @php $emision_marzo=$emision->emisiones   @endphp
                        <td>{{number_format($emision_marzo, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_marzo!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_marzo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_marzo_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Marzo('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_marzo_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_marzo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_marzo_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_marzo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_marzo-$emision_marzo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_marzo_antes!=0)
                    <td>{{number_format((($emision_marzo/$emision_marzo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Abril</td>
                  @php $emision_abril = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Abril('.date("Y", strtotime($date_from)).')')
                        @php $emision_abril=$emision->emisiones   @endphp
                        <td>{{number_format($emision_abril, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_abril!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_abril===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_abril_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Abril('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_abril_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_abril_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_abril_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_abril_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_abril-$emision_abril_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_abril_antes!=0)
                    <td>{{number_format((($emision_abril/$emision_abril_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Mayo</td>
                  @php $emision_mayo = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Mayo('.date("Y", strtotime($date_from)).')')
                        @php $emision_mayo=$emision->emisiones   @endphp
                        <td>{{number_format($emision_mayo, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_mayo!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_mayo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_mayo_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Mayo('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_mayo_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_mayo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_mayo_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_mayo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_mayo-$emision_mayo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_mayo_antes!=0)
                    <td>{{number_format((($emision_mayo/$emision_mayo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Junio</td>
                  @php $emision_junio = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Junio('.date("Y", strtotime($date_from)).')')
                        @php $emision_junio=$emision->emisiones   @endphp
                        <td>{{number_format($emision_junio, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_junio!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_junio===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_junio_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Junio('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_junio_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_junio_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_junio_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_junio_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_junio-$emision_junio_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_junio_antes!=0)
                    <td>{{number_format((($emision_junio/$emision_junio_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Julio</td>
                  @php $emision_julio = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Julio('.date("Y", strtotime($date_from)).')')
                        @php $emision_julio=$emision->emisiones   @endphp
                        <td>{{number_format($emision_julio, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_julio!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_julio===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_julio_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Julio('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_julio_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_julio_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_julio_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_julio_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_julio-$emision_julio_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_julio_antes!=0)
                    <td>{{number_format((($emision_julio/$emision_julio_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Agosto</td>
                  @php $emision_agosto = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Agosto('.date("Y", strtotime($date_from)).')')
                        @php $emision_agosto=$emision->emisiones   @endphp
                        <td>{{number_format($emision_agosto, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_agosto!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_agosto===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_agosto_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Agosto('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_agosto_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_agosto_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_agosto_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_agosto_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_agosto-$emision_agosto_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_agosto_antes!=0)
                    <td>{{number_format((($emision_agosto/$emision_agosto_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Septiembre</td>
                  @php $emision_septiembre = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Septiembre('.date("Y", strtotime($date_from)).')')
                        @php $emision_septiembre=$emision->emisiones   @endphp
                        <td>{{number_format($emision_septiembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_septiembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_septiembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_septiembre_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Septiembre('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_septiembre_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_septiembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_septiembre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_septiembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_septiembre-$emision_septiembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_septiembre_antes!=0)
                    <td>{{number_format((($emision_septiembre/$emision_septiembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Octubre</td>
                  @php $emision_octubre = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Octubre('.date("Y", strtotime($date_from)).')')
                        @php $emision_octubre=$emision->emisiones   @endphp
                        <td>{{number_format($emision_octubre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_octubre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_octubre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_octubre_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Octubre('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_octubre_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_octubre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_octubre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_octubre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_octubre-$emision_octubre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_octubre_antes!=0)
                    <td>{{number_format((($emision_octubre/$emision_octubre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Noviembre</td>
                  @php $emision_noviembre = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Noviembre('.date("Y", strtotime($date_from)).')')
                        @php $emision_noviembre=$emision->emisiones   @endphp
                        <td>{{number_format($emision_noviembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_noviembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_noviembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_noviembre_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Noviembre('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_noviembre_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_noviembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_noviembre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_noviembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_noviembre-$emision_noviembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_noviembre_antes!=0)
                    <td>{{number_format((($emision_noviembre/$emision_noviembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Diciembre</td>
                  @php $emision_diciembre = 0   @endphp
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Diciembre('.date("Y", strtotime($date_from)).')')
                        @php $emision_diciembre=$emision->emisiones   @endphp
                        <td>{{number_format($emision_diciembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_diciembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_diciembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  @php $emision_diciembre_antes = 0   @endphp
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Diciembre('.date("Y", strtotime($date_antes_from)).')')
                        @php $emision_diciembre_antes=$emision->emisiones   @endphp
                        <td>{{number_format($emision_diciembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_diciembre_antes!==0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_diciembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_diciembre-$emision_diciembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_diciembre_antes!=0)
                    <td>{{number_format((($emision_diciembre/$emision_diciembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td>Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              </tbody>
            @endif
          </table>
        </div>
      </div>
    </div>

    <div class="d-none">
      <div class="row export-pdf" data-pdforder="1">
        <?php $titleAxisX = "Total Emisiones CO2: ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq" ?>
        @if($label_intervalo != 'Personalizado')
          <div class="col-md-8 col-md-offset-2">
            <table class="table table-bordered table-striped table-light text-center text-nowrap">
              @if($label_intervalo=='Hoy' or $label_intervalo=='Ayer' )
                <?php
                  $titleAxisX = "$date_from Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq            $date_antes_from Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                ?>
                <thead class="bg-submeter-4">
                  <tr>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Intervalo</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{$date_from}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{$date_antes_from}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Variación</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Var (%)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="bg-submeter-1" style="font-weight: bold;color:white;">Total</td>
                    <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                    <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                    <td>{{number_format($total_emisiones - $total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                    @if($total_emisiones_antes==0)
                      <td> 100 %</td>
                    @else
                      <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                    @endif
                  </tr>
                </tbody>
              @elseif($label_intervalo=='Semana Actual' or $label_intervalo=='Semana Anterior')
                <?php
                  $titleAxisX = "Semana ".date("W", strtotime($date_from))." Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq            Semana ".date("W", strtotime($date_antes_from))." Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                ?>
                <thead class="bg-submeter-4">
                  <tr>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Intervalo</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Semana: {{ date("W", strtotime($date_from))}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Semana: {{date("W", strtotime($date_antes_from))}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Variación</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Var (%)</th>
                  </tr>
                </thead>
                <tr> {{--fallo--}}
                  <td style="font-weight: bold;">Lunes</td>
                  <?php $emision_lunes = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Lunes')
                        <?php $emision_lunes=$emision->emisiones   ?>
                        <td>{{number_format($emision_lunes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_lunes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_lunes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_lunes_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Lunes')
                        <?php $emision_lunes_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_lunes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_lunes_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_lunes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_lunes-$emision_lunes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_lunes_antes!=0)
                    <td>{{number_format((($emision_lunes/$emision_lunes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Martes</td>
                  <?php $emision_martes = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Martes')
                        <?php $emision_martes=$emision->emisiones   ?>
                        <td>{{number_format($emision_martes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_martes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_martes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_martes_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Martes')
                          <?php $emision_martes_antes=$emision->emisiones   ?>
                          <td>{{number_format($emision_martes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_martes_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_martes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_martes-$emision_martes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_martes_antes!=0)
                    <td>{{number_format((($emision_martes/$emision_martes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Miércoles</td>
                  <?php $emision_miercoles = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Miércoles')
                        <?php $emision_miercoles=$emision->emisiones   ?>
                        <td>{{number_format($emision_miercoles, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_miercoles!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_miercoles===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_miercoles_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Miércoles')
                        <?php $emision_miercoles_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_miercoles_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_miercoles_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_miercoles_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_miercoles-$emision_miercoles_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_miercoles_antes!=0)
                    <td>{{number_format((($emision_miercoles/$emision_miercoles_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Jueves</td>
                  <?php $emision_jueves = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Jueves')
                        <?php $emision_jueves=$emision->emisiones   ?>
                        <td>{{number_format($emision_jueves, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_jueves!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_jueves===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_jueves_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Jueves')
                        <?php $emision_jueves_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_jueves_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_jueves_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_jueves_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_jueves-$emision_jueves_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_jueves_antes!=0)
                    <td>{{number_format((($emision_jueves/$emision_jueves_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Viernes</td>
                  <?php $emision_viernes = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Viernes')
                        <?php $emision_viernes=$emision->emisiones   ?>
                        <td>{{number_format($emision_viernes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_viernes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_viernes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_viernes_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Viernes')
                          <?php $emision_viernes_antes=$emision->emisiones   ?>
                          <td>{{number_format($emision_viernes_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_viernes_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_viernes_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_viernes-$emision_viernes_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_viernes_antes!=0)
                    <td>{{number_format((($emision_viernes/$emision_viernes_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Sabado</td>
                  <?php $emision_sabado = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Sabado')
                        <?php $emision_sabado=$emision->emisiones   ?>
                        <td>{{number_format($emision_sabado, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_sabado!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_sabado===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_sabado_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Sabado')
                        <?php $emision_sabado_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_sabado_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_sabado_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_sabado_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_sabado-$emision_sabado_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_sabado_antes!=0)
                    <td>{{number_format((($emision_sabado/$emision_sabado_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Domingo</td>
                  <?php $emision_domingo = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Domingo')
                        <?php $emision_domingo=$emision->emisiones   ?>
                        <td>{{number_format($emision_domingo, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_domingo!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_domingo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_domingo_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Domingo')
                          <?php $emision_domingo_antes=$emision->emisiones   ?>
                          <td>{{number_format($emision_domingo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_domingo_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_domingo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_domingo-$emision_domingo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_domingo_antes!=0)
                    <td>{{number_format((($emision_domingo/$emision_domingo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">Total</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format( $total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td class="bg-submeter-1" style="font-weight: bold;color:white;"> 100 %</td>
                  @else
                    <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              @elseif($label_intervalo=='Mes Actual' or $label_intervalo=='Mes Anterior')
                <thead class="bg-submeter-4">
                  <tr>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Intervalo</th>
                    <?php
                      $date_from_nombre = date("m", strtotime($date_from));
                      $date_from_nombre = $months_T[(int)$date_from_nombre];
                      $date_antes_from_nombre = date("m", strtotime($date_antes_from));
                      $date_antes_from_nombre = $months_T[(int)$date_antes_from_nombre];
                      $titleAxisX = "$date_from_nombre (".date("Y", strtotime($date_from)).") Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             $date_antes_from_nombre (".date("Y", strtotime($date_antes_from)).") Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                    ?>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{$date_from_nombre}} ( {{date("Y", strtotime($date_from))}} )</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{ $date_antes_from_nombre}} ( {{date("Y", strtotime($date_antes_from))}} )</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Variación</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Var (%)</th>
                  </tr>
                </thead>
                <tr>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              @elseif($label_intervalo=='Trimestre Actual' or $label_intervalo=='Ultimo Trimestre')
                <?php
                  $titleAxisX = ceil(date("m", strtotime($date_from))/3)."T (".date("Y", strtotime($date_from)).") Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             ".ceil(date("m", strtotime($date_antes_from))/3)."T (".date("Y", strtotime($date_antes_from)).") Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                ?>
                <thead class="bg-submeter-4">
                  <tr>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Intervalo</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{ceil(date("m", strtotime($date_from))/3)}}T ( {{date("Y", strtotime($date_from))}} )</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;"> {{ceil(date("m", strtotime($date_antes_from))/3)}}T ( {{date("Y", strtotime($date_antes_from))}} )</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Variación</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Var (%)</th>
                  </tr>
                </thead>
                <tr>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">Total</td>
                  <td>{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td>{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td> 100 %</td>
                  @else
                    <td>{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              @elseif($label_intervalo=='Año Actual' or $label_intervalo=='Último Año')
                <?php
                  $titleAxisX = "Año ".date("Y", strtotime($date_from))." Total Emisiones CO2:  ".number_format($total_emisiones, 0, ',', '.')." kg CO2 eq             Año ".date("Y", strtotime($date_antes_from))." Total Emisiones CO2:  ".number_format($total_emisiones_antes, 0, ',', '.')." kg CO2 eq"
                ?>
                <thead class="bg-submeter-4">
                  <tr>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Intervalo</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{ date("Y", strtotime($date_from))}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">{{date("Y", strtotime($date_antes_from))}}</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Variación</th>
                    <th class="text-center text-white" style="vertical-align: middle;color:white;">Var (%)</th>
                  </tr>
                </thead>
                <tr>
                  <td style="font-weight: bold;">Enero</td>
                  <?php $emision_enero = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Enero('.date("Y", strtotime($date_from)).')')
                        <?php $emision_enero=$emision->emisiones   ?>
                        <td>{{number_format($emision_enero, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_enero!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_enero===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_enero_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Enero('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_enero_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_enero_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_enero_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_enero_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_enero-$emision_enero_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_enero_antes!=0)
                    <td>{{number_format((($emision_enero/$emision_enero_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Febrero</td>
                  <?php $emision_febrero = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Febrero('.date("Y", strtotime($date_from)).')')
                        <?php $emision_febrero=$emision->emisiones   ?>
                        <td>{{number_format($emision_febrero, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_febrero!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_febrero===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_febrero_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Febrero('.date("Y", strtotime($date_antes_from)).')')
                          <?php $emision_febrero_antes=$emision->emisiones   ?>
                          <td>{{number_format($emision_febrero_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                          @if ($emision_febrero_antes!=0)
                            @break
                          @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_febrero_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_febrero-$emision_febrero_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_febrero_antes!=0)
                    <td>{{number_format((($emision_febrero/$emision_febrero_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Marzo</td>
                  <?php $emision_marzo = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Marzo('.date("Y", strtotime($date_from)).')')
                        <?php $emision_marzo=$emision->emisiones   ?>
                        <td>{{number_format($emision_marzo, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_marzo!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_marzo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_marzo_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Marzo('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_marzo_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_marzo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_marzo_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_marzo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_marzo-$emision_marzo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_marzo_antes!=0)
                    <td>{{number_format((($emision_marzo/$emision_marzo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Abril</td>
                  <?php $emision_abril = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Abril('.date("Y", strtotime($date_from)).')')
                        <?php $emision_abril=$emision->emisiones   ?>
                        <td>{{number_format($emision_abril, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_abril!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_abril===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_abril_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Abril('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_abril_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_abril_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_abril_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_abril_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_abril-$emision_abril_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_abril_antes!=0)
                    <td>{{number_format((($emision_abril/$emision_abril_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Mayo</td>
                  <?php $emision_mayo = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Mayo('.date("Y", strtotime($date_from)).')')
                        <?php $emision_mayo=$emision->emisiones   ?>
                        <td>{{number_format($emision_mayo, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_mayo!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_mayo===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_mayo_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Mayo('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_mayo_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_mayo_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_mayo_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_mayo_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_mayo-$emision_mayo_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_mayo_antes!=0)
                    <td>{{number_format((($emision_mayo/$emision_mayo_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Junio</td>
                  <?php $emision_junio = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Junio('.date("Y", strtotime($date_from)).')')
                        <?php $emision_junio=$emision->emisiones   ?>
                        <td>{{number_format($emision_junio, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_junio!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_junio===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_junio_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Junio('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_junio_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_junio_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_junio_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_junio_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_junio-$emision_junio_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_junio_antes!=0)
                    <td>{{number_format((($emision_junio/$emision_junio_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Julio</td>
                  <?php $emision_julio = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Julio('.date("Y", strtotime($date_from)).')')
                        <?php $emision_julio=$emision->emisiones   ?>
                        <td>{{number_format($emision_julio, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_julio!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_julio===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_julio_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Julio('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_julio_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_julio_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_julio_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_julio_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_julio-$emision_julio_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_julio_antes!=0)
                    <td>{{number_format((($emision_julio/$emision_julio_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Agosto</td>
                  <?php $emision_agosto = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Agosto('.date("Y", strtotime($date_from)).')')
                        <?php $emision_agosto=$emision->emisiones   ?>
                        <td>{{number_format($emision_agosto, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_agosto!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_agosto===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_agosto_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Agosto('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_agosto_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_agosto_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_agosto_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_agosto_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_agosto-$emision_agosto_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_agosto_antes!=0)
                    <td>{{number_format((($emision_agosto/$emision_agosto_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Septiembre</td>
                  <?php $emision_septiembre = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Septiembre('.date("Y", strtotime($date_from)).')')
                        <?php $emision_septiembre=$emision->emisiones   ?>
                        <td>{{number_format($emision_septiembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_septiembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_septiembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_septiembre_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Septiembre('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_septiembre_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_septiembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_septiembre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_septiembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_septiembre-$emision_septiembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_septiembre_antes!=0)
                    <td>{{number_format((($emision_septiembre/$emision_septiembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Octubre</td>
                  <?php $emision_octubre = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Octubre('.date("Y", strtotime($date_from)).')')
                        <?php $emision_octubre=$emision->emisiones   ?>
                        <td>{{number_format($emision_octubre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_octubre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_octubre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_octubre_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Octubre('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_octubre_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_octubre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_octubre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_octubre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_octubre-$emision_octubre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_octubre_antes!=0)
                    <td>{{number_format((($emision_octubre/$emision_octubre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Noviembre</td>
                  <?php $emision_noviembre = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Noviembre('.date("Y", strtotime($date_from)).')')
                        <?php $emision_noviembre=$emision->emisiones   ?>
                        <td>{{number_format($emision_noviembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_noviembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_noviembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_noviembre_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Noviembre('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_noviembre_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_noviembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_noviembre_antes!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_noviembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_noviembre-$emision_noviembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_noviembre_antes!=0)
                    <td>{{number_format((($emision_noviembre/$emision_noviembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td style="font-weight: bold;">Diciembre</td>
                  <?php $emision_diciembre = 0   ?>
                  @if(isset($emisiones[0]))
                    @foreach($emisiones as $emision)
                      @if($emision->eje === 'Diciembre('.date("Y", strtotime($date_from)).')')
                        <?php $emision_diciembre=$emision->emisiones   ?>
                        <td>{{number_format($emision_diciembre, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_diciembre!=0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_diciembre===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <?php $emision_diciembre_antes = 0   ?>
                  @if(isset($emisiones_antes[0]))
                    @foreach($emisiones_antes as $emision)
                      @if($emision->eje === 'Diciembre('.date("Y", strtotime($date_antes_from)).')')
                        <?php $emision_diciembre_antes=$emision->emisiones   ?>
                        <td>{{number_format($emision_diciembre_antes, 2, ',', '.')}} kg CO2 eq.	</td>
                        @if ($emision_diciembre_antes!==0)
                          @break
                        @endif
                      @endif
                    @endforeach
                  @endif
                  @if ($emision_diciembre_antes===0)
                    <td>0,00 kg CO2 eq.</td>
                  @endif
                  <td>{{number_format($emision_diciembre-$emision_diciembre_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($emision_diciembre_antes!=0)
                    <td>{{number_format((($emision_diciembre/$emision_diciembre_antes)-1)*100, 2, ',', '.')}} %</td>
                  @else
                    <td> 100 %</td>
                  @endif
                </tr>
                <tr>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">Total</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format($total_emisiones, 2, ',', '.')}} kg CO2 eq.</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format($total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format($total_emisiones-$total_emisiones_antes, 2, ',', '.')}} kg CO2 eq.</td>
                  @if($total_emisiones_antes==0)
                    <td class="bg-submeter-1" style="font-weight: bold;color:white;"> 100 %</td>
                  @else
                    <td class="bg-submeter-1" style="font-weight: bold;color:white;">{{number_format((($total_emisiones/$total_emisiones_antes)-1)*100, 2, ',', '.')}} %</td>
                  @endif
                </tr>
              @endif
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif
                
  @php $j++; @endphp
          
  <form method="post" class="d-none" id="form-pdf" action="{{route('exportacion.pdf',['titulo'=>$titulo,'date_from'=>$date_from,'date_to'=>$date_to,'contador_label'=>$contador_label])}}">
    {{ csrf_field() }}
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
<script src="{{asset('js/bootstrap.min.js')}}"> </script>
<script src="{{asset('js/canvas.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
<!-- <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script> -->
<script src="{{asset('js/skycons.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@include('Dashboard.includes.scripts_emisiones_co2')
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
@endsection