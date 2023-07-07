<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

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
	      			<img src="{{ $file_name_plot }}" style="width:250px; height:180px;"/>
	      		</div>
	      	</div>
	      </div>
	    </div>
		<br/>
		<main>
			<div class="col-md-12">
				<?php $j = 1; ?>
				@foreach($user->energy_meters as $_contador)
					<table class="table table-hover table-responsive table-bordered" style="margin: 0px auto;">
						<thead>
							<tr>
								<th  class="text-center" style="background-color: #004165;
	  color: white; width: 23%;">
									Concepto
								</th>
								<th  class="text-center" style="background-color: #004165;
	  color: white; width: 57%;">
									Cálculo
								</th>
								<th  class="text-center" style="background-color: #004165;
	  color: white;">
									Importe
								</th>
							</tr>
						</thead>
						<tbody>
							<tr class="text-left">
								<td style="color: #004165;">
									<b>Término Variable</b>
								</td>
								<td>
								</td>
								<?php $total1 = 0; ?>
								<td style="color: #004165;" class="text-center">
									@if(isset($consumo_GN_kWh[0]) && isset($precio_variable))
										<?php 
											$total1 += $consumo_GN_kWh[0]->consumo*$precio_variable->Precio;
										 ?>
									@endif
									@if(isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
										<?php 
											$total1 += $consumo_GN_kWh[0]->consumo*(-1)*$descuento_variable->Descuento;
										?>
									@endif
									<b>{{number_format($total1,2,',','.')}} €</b>
								</td>
							</tr>
							<?php 
								$i = 0;
							 ?>
							
								<tr class="text-left">
									<td></td>
									<td style="color: #004165; padding:0px;">
										<table class="table table-striped text-center" style="margin-bottom:0px;">
											<tr>
    										@if(isset($consumo_GN_kWh[0]) && isset($precio_variable))
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh 
    											</td>
    											<td style="padding:1px 8px 1px;"> 
    												{{number_format($precio_variable->Precio,5,',','.')}} €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo*$precio_variable->Precio,2,',','.')}} €
    											</td>
    										@elseif(isset($consumo_GN_kWh[0]) && !isset($precio_variable))
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0 €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0 €
    											</td>
    										@elseif(!isset($consumo_GN_kWh[0]) && isset($precio_variable))
    											<td style="padding:1px 8px 1px;">
    												0 kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{number_format($precio_variable->Precio,5,',','.')}} €/kWh 
    											</td> 
    											<td style="padding:1px 8px 1px;">
    												{{0*$precio_variable->Precio,2,',','.'}} €
    											</td>
    										@else
    											<td style="padding:1px 8px 1px;">
    												0 kWh 
    											<td style="padding:1px 8px 1px;">
    												0 €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												 0 €
    											</td>
    										@endif 
											</tr>
											<tr>
    										@if(isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{number_format($descuento_variable->Descuento*-1,5,',','.')}} €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo*$descuento_variable->Descuento*-1,2,',','.')}} €
    											</td>
    										@elseif(isset($consumo_GN_kWh[0]) && !isset($descuento_variable->Descuento))
    											<td style="padding:1px 8px 1px;">
    												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0,00000 €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0,00 €
    											</td>
    										@elseif(!isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
    											<td style="padding:1px 8px 1px;">
    												0 kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{number_format($descuento_variable->Precio,5,',','.')}} €/kWh
    											</td>
    											<td style="padding:1px 8px 1px;">
    												{{0*$precio_variable->Precio,2,',','.'}} €
    											</td>
    										@else
    											<td style="padding:1px 8px 1px;">
    												0 kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0,00 €/kWh 
    											</td>
    											<td style="padding:1px 8px 1px;">
    												0 €
    											</td>
    										@endif
    										<?php 
    											$i++;
    										 ?>
    										 </tr>
										</table>
									</td>
									<td></td>
								</tr>
																							
							<tr class="text-left">
								<td style="color: #004165;"><b>Término Fijo</b></td>
								<td>
									
								</td>
								<td style="color: #004165;" class="text-center">
									<?php 
										$total2 = 0;
									 ?>
									@if(isset($coste_termino_fijo))
										<?php 
											$total2 = $coste_termino_fijo->Precio;
										 ?>
									@endif
									<b> {{number_format($total2,2,',','.')}} €</b>
								</td>
							</tr>
							<?php 
								$i = 0;
							 ?>
							
								<tr class="text-left">
									<td></td>
									<td style="color: #004165; padding:0px;">
										<table class="table table-striped text-center" style="margin-bottom:0px;">
											<tr>
        										@if(isset($consumo_GN_kWh_diario[0]) && isset($precio_fijo->Precio))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($precio_fijo->Precio,5,',','.')}} €/kWh 
													</td>
													<td style="padding:1px 8px 1px;">
														{{number_format($coste_precio_fijo->Precio,2,',','.')}} €
													</td>
        										@elseif(isset($consumo_GN_kWh_diario[0]) && !isset($precio_fijo->Precio))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €/kWh 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €
        											</td>
        										@elseif(!isset($consumo_GN_kWh_diario[0]) && isset($precio_fijo->Precio))
        											<td style="padding:1px 8px 1px;">
        												0 kWh/día 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($precio_fijo->Precio,5,',','.')}} €/kWh 
													</td>
        											<td style="padding:1px 8px 1px;">
        												{{0*$precio_fijo->Precio,2,',','.'}} €
        											</td>
        										@else
        											<td style="padding:1px 8px 1px;">
        												0 kWh/día 
													</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €/kWh 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €
        											</td>
        										@endif
        									</tr>
        									<tr>
        										@if(isset($consumo_GN_kWh_diario[0]) && isset($descuento->Descuento))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												{{number_format($descuento->Descuento*-1,5,',','.')}} €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												{{number_format($coste_descuento_fijo->Descuento,2,',','.')}} €
        											</td>
        										@elseif(isset($consumo_GN_kWh_diario[0]) && !isset($descuento->Descuento))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh_diario[0]->consumo,0,',','.')}} kWh/día
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												0 €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												0 €
        											</td>
        										@elseif(!isset($consumo_GN_kWh_diario[0]) && isset($descuento->Descuento))
        											<td style="padding:1px 8px 1px;">
        												0 kWh/día 
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($descuento->Descuento,5,',','.')}} €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												{{number_format(0,2,',','.')}} €
        											</td>        											
        										@else
        											<td style="padding:1px 8px 1px;">
        												0 kWh/día
        											</td>
        											<td style="padding:1px 8px 1px;"> 
        												0,00000 €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €
        											</td>        											
        										@endif
    										</tr>
										</table>
									</td>
									<td></td>
								</tr>
							
							<tr class="text-left">
								<td style="color: #004165;"><b>I.E.HC</b></td>
								<td>
									
								</td>
								<td style="color: #004165;" class="text-center">
									<?php 
										$total3 = 0;
									 ?>
									@if(isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
										<?php 
											$total3 = $consumo_GN_kWh[0]->consumo*$I_E_HC->valor;
										 ?>
									@endif
									<b> {{number_format($total3,2,',','.')}} €</b>
								</td>
							</tr>
							<?php 
								$i = 0;
							 ?>
							
								<tr class="text-left">
									<td></td>
									<td style="color: #004165; padding:0px;">
										<table class="table table-striped text-center" style="margin-bottom:0px;">
											<tr>					
        										@if(isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($I_E_HC->valor,5,',','.')}} €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh[0]->consumo*$I_E_HC->valor,2,',','.')}} €
        											</td>        											
        										@elseif(!isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh[0]->consumo,0,',','.')}} kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format(0,5,',','.')}} €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($consumo_GN_kWh[0]->consumo*0,2,',','.')}} €
        											</td>
        										@elseif(isset($I_E_HC->valor) && !isset($consumo_GN_kWh[0]->consumo))
        											<td style="padding:1px 8px 1px;">
        												{{number_format(0,0,',','.')}} kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format($I_E_HC->valor,5,',','.')}} €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												{{number_format(0*$I_E_HC->valor,2,',','.')}} €
        											</td>        											
        										@else
        											<td style="padding:1px 8px 1px;">
        												0 kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €/kWh
        											</td>
        											<td style="padding:1px 8px 1px;">
        												0,00 €
        											</td>        											
        										@endif
											</tr>
										</table>
									</td>
									<td></td>
								</tr>																
							
							<tr class="text-left">
								<td style="color: #004165;">
									<b>Equipo de Medida</b>
								</td>
								<td>
									
								</td>
								<td style="color: #004165;" class="text-center">
									<?php 
										$total4 = 0;
									 ?>
									@if(isset($equipo_medida->valor))
										<?php 
											$total4 = $equipo_medida->valor*($diasDiferencia+1);
										 ?>
										<b>{{number_format($total4,2,',','.')}} €</b>
									@else
										<b>0,00 €</b>
									@endif
								</td>
							</tr>
							<tr class="text-left">
								<td style="color: #004165;">
									<b>I.V.A.</b>
								</td>
								<td>
									
								</td>
								<td style="color: #004165;" class="text-center">
									<?php 
										$total5 = ($total1+$total2+$total3+$total4)*0.21;
									 ?>
									<b>{{number_format(($total1+$total2+$total3+$total4)*0.21,2,',','.')}} €</b>
								</td>
							</tr>
							<tr class="text-center">
								<td>
									
								</td>
								<td style="color: #004165;" class="text-left">
									<table class="table text-center" style="margin-bottom:0px;">
										<tr>
											<td class="text-right" style="padding:1px 8px 1px; border-top:none; width:70%;">
												{{number_format(($total1+$total2+$total3+$total4),2,',','.')}} € 
											</td>
											<td style="padding:1px 8px 1px; border-top:none;">
												21 %
											</td>
										</tr>
									</table>
								</td>
								<td>
									
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th  class="text-center" style="background-color: #004165;
	  color: white;">
									
								</th>
								<th  class="text-right" style="background-color: #004165;
	  color: white;">
									<b>TOTAL</b>
								</th>
								<th  class="text-center" style="background-color: #004165;
	  color: white;">
									{{number_format($total1+$total2+$total3+$total4+$total5,0,',','.')}} €
								</th>
							</tr>
						</tfoot>
					</table>
					<?php $j++; ?>
					@break
				@endforeach
			</div>
		</main>
    </body>    
</html>
	