<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link href="{{asset('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' />
        <script src="{{asset('js/Chart.js')}}"></script>
    </head>
    <body>
    	<header class="clearfix">
	      <div class="row">
	      	<div class="col-md-3">
	      		<div id="logo">
			        <img src="{{asset($image)}}" style="width: 80px; height: 80px">
			    </div>
	      	</div>
	      	<div class="col-md-9">
		      <div id="company">
		        <h2 class="name">Empresa: {{$user->name}}</h2>
		        @if(!is_null($user->_perfil))
		        <div>Dirección: {{$user->_perfil->direccion}}</div>        
		        @else
		        	<div>Dirección: Sin dirección</div>
		        @endif
		        <div>Contador: {{$array_contadores}}</div>
		        <div>Email: {{$user->email}}</div>
		        <div>
		        	Intervalo del reporte: Desde {{$date_from}} hasta {{$date_to}}
		        </div>
		      </div>
	      	</div>
	      </div>
	    </header>
		<br>
		<main>
			<div class="col-md-12">
				<table class="table table-bordered table-striped table-hover table-condensed table-responsive tabla1 table-analisis-comparacion" id="tabla2" style="width: 70%; margin: 0px auto">
					<thead style="background-color: #004165;">										
						<tr>
							<th class="text-center" style="color: #fff;" width="30%">Concepto</th>
							<th class="text-center" style="color: #fff;">Cálculo</th>
							<th class="text-center" style="color: #fff;">Importe</th>	
						</tr>
					</thead>									
					<tbody>
						<tr>
							<td class="text-left">
								<label style="color: #004165;">Término Variable</label>
							</td>
							<td></td>
							<td class="text-center">
								<?php 
									$total1 = 0;
									if(isset($termino_variable))
									{
										foreach ($termino_variable as $key1) {
											$total1 = $total1 + $key1[0]->valor;
										}																		
									}
									$iva_gas = array(0);
								 ?>
								<label style="color: #004165;">{{number_format($total1,2,',','.')}} €</label>
							</td>
						</tr>
						<tr>
							<td style="margin-right: 25px;">
								
							</td>
							<?php $i = 0; $j = 0;?>
								<td style="color: #004165;">
									@if(isset($termino_variable))
										@foreach($termino_variable as $coste)
											G{{$i+1}}: {{number_format($coste[0]->valor,2,',','.')}} €</i><br><br>
										<?php 
											$iva_gas[$i] =  $coste[0]->valor;
											$i++; 
										?>
										@endforeach
									@else
										G{{$i+1}}: {{number_format($total1,2,',','.')}} €</i><br><br>
									@endif
								</td>
							<td></td>
						</tr>
						<tr >
							<td class="text-left">
								<label style="color: #004165;">Término Fijo</label>
							</td>
							<td></td>															
							<td class="text-center">
								<?php 
									$total2 = 0;
									if(isset($termino_fijo))
									{
										foreach ($termino_fijo as $key2) {
											$total2 += $key2[0]->valor;
										}																		
									}
								 ?>
								<label style="color: #004165;">{{number_format($total2,2,',','.')}} €</label>
							</td>
						</tr>
						<tr>
							<td style="margin-right: 25px;">
								
							</td>
							<?php $i = 0;?>
								<td style="color: #004165;">
									@if(isset($termino_fijo))
										@foreach($termino_fijo as $coste)
											G{{$i+1}}: {{number_format($coste[0]->valor,2,',','.')}} €</i><br><br>
										<?php
											$iva_gas[$i] += $coste[0]->valor;
											$i++;
										?>
										@endforeach
									@else
										G{{$i+1}}: {{number_format(0,2,',','.')}} €</i><br><br>
									@endif
								</td>
							<td></td>
						</tr>
						<tr>
							<td class="text-left">
								<label style="color: #004165;">I.E.HC</label>
							</td>
							<td></td>															
							<td class="text-center">
								<?php 
									$total3 = 0;
									if(isset($consumo_GN_kWh[0][0]->consumo) && isset($I_E_HC[0]->valor))
									{
										foreach ($consumo_GN_kWh[0] as $key2) {
											$total3 += $key2->consumo*$I_E_HC[0]->valor;
										}																		
									}
								 ?>
								<label style="color: #004165;">{{number_format($total3,2,',','.')}} €</label>
							</td>
						</tr>
						<tr>
							<td></td>
							<?php $i= 0; ?>
							<td style="color: #004165;">
								@if(isset($consumo_GN_kWh[0][0]->consumo) && isset($I_E_HC[0]->valor))
									@foreach($consumo_GN_kWh[0] as $consu)
										<?php 
											$aux = $consu->consumo*$I_E_HC[0]->valor;
										 ?>
										G{{$i+1}}: {{number_format($aux,2,',','.')}} €</i><br><br>
									<?php
										$iva_gas[$i] += $aux;
										$i++;
									?>
									@endforeach
								@else
									G{{$i+1}}: {{number_format(0,2,',','.')}} €</i><br><br>
								@endif
							</td>
							<td></td>																
						</tr>

						<tr>
							<td class="text-left">
								<?php 
									$total5 = 0;
									
									if(empty($equipo_medida))
									{
										$total5 = 0;
									}else
									{
										if(!empty($equipo_medida))
										{
											foreach ($equipo_medida[0] as $key5) {
												if(!empty($key5))
													$total5 = $total5 + ($key5->valor*($diasDiferencia+1));
												else
													$total5 = $total5 + 0;
											}
										}else{
											$total5 = 0;
										}
									}

								 ?>
								<label style="color: #004165;">Equipo de Medida</label>
							</td>
							<td></td>															
							<td class="text-center">
								<label style="color: #004165;">{{number_format($total5,2,',','.')}} €</label>
							</td>
						</tr>

						<tr>
							<td></td>
							<?php $i= 0; ?>
							<td style="color: #004165;">
								@if(empty($equipo_medida))
											G{{$i+1}}: 0 €</i><br><br>
								@else
									@foreach($equipo_medida[0] as $equi)
										@if(!empty($equi))
											G{{$i+1}}: {{number_format($equi->valor*($diasDiferencia+1),2,',','.')}} €</i><br><br>
											<?php
												$iva_gas[$i] += $equi->valor*($diasDiferencia+1);
											 ?>
										 @else
										 	G{{$i+1}}: 0,00 €</i><br><br>
										 @endif
										<?php $i++; ?>
									@endforeach
								@endif
							</td>
							<td></td>																
						</tr>

						<tr>
							<td class="text-left">
								<?php 
									$total_iva = 0;
								 ?>
								@foreach($iva_gas as $value)
									<?php 
										$total_iva += $value;
									 ?>
								@endforeach
								<label style="color: #004165;">I.V.A.</label>
							</td>
							<td></td>
							<td class="text-center">
								<label class="text-center" style="color: #004165;">{{number_format(($total_iva)*.21,2,',','.')}} €</label>
							</td>
						</tr>
						<tr>
							<td></td>
							<td style="color: #004165;">
								<?php $i = 0; ?>
								@foreach($iva_gas as $coste)
									G{{$i+1}}: {{number_format($coste*.21,2,',','.')}} €</i><br><br>
								<?php 																	
									$i++; 
								?>
								@endforeach
							</td>																
							<td></td>
						</tr>
					</tbody>

					<tfoot style="background-color: #004165;">										
						<tr>
							<th></th>
							<th class="float-right" style="color: #fff;">TOTAL</th>
							<th class="text-center" style="color: #fff;">{{number_format($total1+$total2+$total3+$total5+($total_iva*.21),0,',','.')}} €</th>	
						</tr>
					</tfoot>
				</table>
			</div>
		</main>
    </body>
</html>
	