<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link href="{{ public_path('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' media="all" />
        <style type="text/css">
            @page { margin-top: 10px; margin-bottom:0px; }
            body { margin-top: 10px; margin-bottom:0px; }
        </style>
    </head>
    <body>
    	<div class="clearfix">
	      <div class="row">
	      	<div class="col-md-6" style="width:50%; float:left;">
    	      	<div class="row">
        	      	<div class="col-md-3">
        	      		<div id="logo">
        			        <img src="{{ asset($image) }}" style="width: 90px; height: 90px">
        			    </div>
        	      	</div>
        	      	<div class="col-md-5">
        		      <div id="company">		      	
        		        <h3 class="name">Empresa: {{$user->name}}</h3>
        		        @if($domicilio != '')
        		        <div>Dirección: {{$domicilio}}</div>        
        		        @else
        		        	<div>Dirección: Sin dirección</div>
        		        @endif
        		        <div>Contador: {{$contador_label}}</div>
        		        <div>
        		        	Intervalo del reporte: Desde {{$date_from}} hasta {{$date_to}}
        		        </div>
        		      </div>
        	      	</div>
    	      	</div>
	      	</div>
	      	<div class="col-md-6" style="width:50%; float:left; margin-top:10px;">
	      		<div id="chart-div">
	      			<img src="{{ $file_name_plot }}" style="width:350px; height:180px;"/>
	      		</div>
	      	</div>
	      </div>
	    </div>
		<br>
		<main>
			<div class="col-md-12">
				
				<table class="table table-hover table-responsive table-bordered" style="margin: 0px auto; page-break-inside: avoid;">
					<thead>
						<tr>
							<th style="text-align: center; font-size: 12pt; background-color: #004165;
  color: white; width: 150px">
								Concepto
							</th>
							<th style="text-align: center; font-size: 12pt; background-color: #004165;
  color: white;" >
								Cálculo
							</th>
							<th style="text-align: center; font-size: 12pt; background-color: #004165; width: 110px;
  color: white;">
								Importe
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2">
								<label style="color: #004165;">Término Energía</label>
							</td>																				
							<td style="text-align: center !important;color: #004165;">
								 <b>{{number_format($total1+$total_,2,',','.')}} €</b><i class="fa fa-eur"></i>
							</td>
						</tr>
						<tr class="text-left">
							<td style="color: #004165;">
								<label style="color: #004165; margin-left: 10%; font-weight: 100;">Energía Activa</label>
							</td>
							<td style="color: #004165; padding:0px;">
								
								<?php 
									$i = 0;
								 ?>
								<table class="table table-striped" style="margin-bottom:0px;">
								@foreach($E_Activa as $it)										
									@if(isset($precio_energia[$i%6]))
										<tr>	
											<td style="padding:1px 8px 1px;">
												P{{$i+1}}
											</td>
											<td style="padding:1px 8px 1px;">
												{{number_format($it->Activa*1,'0',',','.')}} kWh
											</td>
											<td style="padding:1px 8px 1px;">
												{{number_format($it->Activa*$precio_energia[$i%6]->precio,'2',',','.')}} €
											</td>
										</tr>
										<!-- P{{$i+1}}:  * {{number_format($precio_energia[$i%6]->precio,'5',',','.')}} €/kWh= {{number_format($it->Activa*$precio_energia[$i%6]->precio,'2',',','.')}} €<br> -->	
									@else
										<tr>	
											<td style="padding:1px 8px 1px;">
												P{{($i%6)+1}}
											</td>
											<td style="padding:1px 8px 1px;">
												{{number_format($it->Activa*1,'0',',','.')}} kWh
											</td>
											<td style="padding:1px 8px 1px;">
												{{number_format(0,'5',',','.')}} €/kWh
											</td>
											<td style="padding:1px 8px 1px;">
												{{number_format($it->Activa*0,'2',',','.')}} €
											</td>
										</tr>
										<!-- P{{($i%6)+1}}: {{number_format($it->Activa*1,'0',',','.')}} kWh * {{number_format(0,'5',',','.')}} €/kWh= {{number_format($it->Activa*0,'2',',','.')}} €<br> -->
									@endif
									<?php 
										$i++;
									 ?>
										
								@endforeach	
								</table>								
							</td>								
							<td style="color: #004165; text-align: center !important;" class="text-center">
								{{number_format($total1,'2',',','.')}} €
							</td>
						</tr>
						<tr class="text-left">
							<td style="color: #004165;">
								<label style="color: #004165; margin-left: 10%; font-weight: 100;">Energía Reactiva</label>
							</td>
							<td style="color: #004165; padding:0px;">
								<table class="table table-striped text-center" style="margin-bottom:0px;">
									<tr>
										<th class="text-center" style="padding:1px 8px 1px;">
											Periodo
										</th>
										<th class="text-center" style="padding:1px 8px 1px; border-right: 1px solid #ddd;">
											Coste
										</th>
										<th class="text-center" style="padding:1px 8px 1px;">
											Periodo
										</th>
										<th class="text-center" style="padding:1px 8px 1px;">
											Coste
										</th>
									</tr>
									<?php 
										$i = 0;
									 ?>
									@foreach($coste_reactiva[0] as $it)
										@if(($i + 1) % 2 == 1)
											<tr>
										@endif    																
										@if(isset($precio_energia[$i%6]))					
											<td style="padding:1px 8px 1px;">
												P{{$i+1}}
											</td>
											@if(($i + 1) % 2 == 0)
											<td  style="padding:1px 8px 1px;">
											@else
											<td  style="padding:1px 8px 1px; border-right: 1px solid #ddd;">
											@endif
												{{number_format($it*1,'2',',','.')}} €
											</td>		
										@else
											<td  style="padding:1px 8px 1px;"> 
												P{{($i%6)+1}} 
											</td>
											@if(($i + 1) % 2 == 0)
											<td  style="padding:1px 8px 1px;">
											@else
											<td  style="padding:1px 8px 1px; border-right: 1px solid #ddd;">
											@endif
												{{number_format($it*1,'2',',','.')}} €
											</td>
										@endif
										@if(($i + 1) % 2 == 0)
											</tr>
										@endif
										<?php 
											$i++;
										 ?>											
									@endforeach
								</table>
							</td>								
							<td style="color: #004165; text-align: center !important;" class="text-center">{{number_format($total_*1,'2',',','.')}} €</td>
						</tr>
						<tr class="text-left">
							<td colspan="2">
								<label style="color: #004165;">Término Potencia</label>
							</td>																					
							<td class="text-center" style="color: #004165;">
								<b>{{number_format($data_analisis["totalFP"],2,',','.')}} €</b>									
							</td>
						</tr>

						<tr class="text-left">
							@if($tipo_tarifa == 1)
								<td style="color: #004165;">
									<label style="color: #004165; margin-left: 10%; font-weight: 100;">Potencia Contratada</label>
								</td>
							@else
								<td style="color: #004165;">
								</td>
							@endif
							<td style="color: #004165;  padding:0px;">
								<table class="table table-striped text-center" style="margin-bottom:0px;">
									@foreach($data_calculos["vector_potencia"] as $index => $potencia)
										<tr>
    										@if($tipo_tarifa == 1)
												<td style="padding:1px 8px 1px;">
													P{{$index + 1}}
												</td>
												<td style="padding:1px 8px 1px;">
													{{number_format($potencia,'0',',','.')}} kW
												</td>
												<td style="padding:1px 8px 1px;">
													{{number_format($data_analisis["costoDias"][$index],'5',',','.')}} €/kW
												</td>
												<td style="padding:1px 8px 1px;">
													{{number_format($data_analisis['dataFPC'][$index + 1],'2',',','.')}} €
												</td>
    										@else
    											<td style="padding:1px 8px 1px;">
													P{{$index + 1}}
												</td>
												<td style="padding:1px 8px 1px;">
													{{number_format($potencia,'0',',','.')}} kW
												</td>
												<td style="padding:1px 8px 1px;">
													(Max registrada {{number_format($data_analisis["maxPotencias"][$index],'0',',','.')}} kW)
												</td>
												<td style="padding:1px 8px 1px;">
													{{number_format($data_analisis['dataFP'][$index + 1],'2',',','.')}} €
												</td>
    											 
    										@endif
										 </tr>										
									@endforeach
								</table>
							</td>
							
							<td style="color: #004165; text-align: center !important;" class="text-center">
								@if($tipo_tarifa == 1)
									{{number_format($data_analisis["totalFC"],'2',',','.')}} €
								@else
									{{number_format($data_analisis["totalFP"],'2',',','.')}} €
								@endif
							</td>
						</tr>
						@if($tipo_tarifa == 1)
							<tr class="text-left" style="margin-top: 150px">
								<td style="color: #004165;">
									<label style="color: #004165; margin-left: 10%; font-weight: 100;">Excesos de Potencia</label>
								</td>
								<td style="color: #004165; padding:0px;">
									<table class="table table-striped text-center" style="margin-bottom:0px;">
										<tr>
											<th class="text-center" style="padding:1px 8px 1px;">
												Periodo
											</th>
											<th class="text-center" style="padding:1px 8px 1px; border-right: 1px solid #ddd;">
												Coste
											</th>
											<th class="text-center" style="padding:1px 8px 1px;">
												Periodo
											</th>
											<th class="text-center" style="padding:1px 8px 1px;">
												Coste
											</th>
										</tr>    										
										@if($tipo_tarifa == 1)
											@foreach($data_analisis["dataFPE"] as $index=>$exceso)
												@if(($index) % 2 == 1)
        											<tr>
        										@endif    																
        										<td style="padding:1px 8px 1px;">
    												P{{$index}}
    											</td>
    											@if(($index) % 2 == 0)
    											<td  style="padding:1px 8px 1px;">
    											@else
    											<td  style="padding:1px 8px 1px; border-right: 1px solid #ddd;">
    											@endif
    												{{number_format($exceso,'2',',','.')}} €
    											</td>
        										@if(($index) % 2 == 0)
        											</tr>
        										@endif            																		
											@endforeach
										@endif
									</table>
								</td>
								
								<td style="color: #004165; text-align: center !important;" class="text-center">
									{{number_format($data_analisis["totalFPE"],'2',',','.')}} €
								</td>
							</tr>
						@endif
						
						<tr class="text-left">
							<td style="color: #004165;">
								<b>I.E.E.</b>
							</td>
							<td style="color: #004165; padding:8px;">
								<table class="table text-center" style="margin-bottom:0px;">
									<tr>
										<td class="text-right" style="padding:1px 8px 1px; border-top:none; width:60%;">
											{{number_format($sumatoria,'2',',','.')}} €
										</td>
										<td style="padding:1px 8px 1px; border-top:none;">
											5.11269632 %
										</td>
									</tr>
								</table>
								<!-- {{number_format($sumatoria,'2',',','.')}} € * 5.11269632 % -->
							</td>
							<td style="color: #004165; text-align: center !important;" class="text-center">
								<b>{{number_format($impuesto,'2',',','.')}} €</b>
							</td>
						</tr>
						
						<tr class="text-left">
							<td style="color: #004165;" colspan="2">
								<b>Equipo de Medida</b>
							</td>								
							<td style="color: #004165; text-align: center !important;" class="text-center">
								@if(empty($equipo))
									<b>{{number_format(0,'2',',','.')}} €</b>
								@else
									<b>{{number_format($equipo[0]->valor*($diasDiferencia+1),'2',',','.')}} €</b>
								@endif
							</td>
						</tr>
						<tr class="text-left">
							<td style="color: #004165;">
								<b>I.V.A.</b>
							</td>
							<td style="color: #004165;" class="text-left">
								<table class="table text-center" style="margin-bottom:0px;">
									<tr>
										@if(empty($equipo))
										<td class="text-right" style="padding:1px 8px 1px; border-top:none; width:70%;">
											{{number_format(($sumatoria + $impuesto),'2',',','.')}} €
										</td>
										<td style="padding:1px 8px 1px; border-top:none;">
											21 %
										</td>
										@else
										<td class="text-right" style="padding:1px 8px 1px; border-top:none; width:60%;">
											{{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1))),'2',',','.')}} €
										</td>
										<td style="padding:1px 8px 1px; border-top:none;">
											21 %
										</td>
										@endif
									</tr>
								</table>
							</td>								
							<td style="color: #004165; text-align: center !important;" class="text-center">
								<b>{{number_format($IVA,'2',',','.')}} €</b>
							</td>
						</tr>							
					</tbody>
					<tfoot>
						<tr>
							<th style="text-align: center; font-size: 12pt; background-color: #004165;
  color: white;">
								
							</th>
							<th style="text-align: center; font-size: 12pt; background-color: #004165;
  color: white;">
								<b>TOTAL</b>
							</th>
							<th style="text-align: center; font-size: 12pt; background-color: #004165;
  color: white;">
								@if(empty($equipo))
									{{number_format(($sumatoria + $impuesto + $IVA),'2',',','.')}} €
								@else
									{{number_format(($sumatoria + $impuesto + ($equipo[0]->valor*($diasDiferencia+1)) + $IVA),'2',',','.')}} €
								@endif
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</main>
    </body>    
</html>
	