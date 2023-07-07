@extends('Dashboard.layouts.global')

@section('content')
	<div id="wrapper">        
        <div id="page-wrapper" class="gray-bg dashbard-1">
       		<div class="content-main">	
       			@if($user->tipo == 2)       				
					<div class="banner col-md-12">							
					    <div class="col-md-6 text-center">
							<button id = "button_interval" type="button" class="btn btn-lg btn-primary float-left" data-toggle="modal" data-target="#myModal" style="margin-right: 2em;"><i class="fa fa-plus"></i>  Intervalos de Períodos ({{$label_intervalo}})</button>
						</div>

					    <div class="col-md-6 text-right" style="margin-top: 5px">
							@if($label_intervalo != 'Personalizado')
								<form id="form_navegation" action="{{route('config.navigation')}}" method="POST">
									{{ csrf_field() }}
									<input type="hidden" name="option_interval" value="9">
									<input type="hidden" name="label_intervalo" value="{{$label_intervalo}}">
									<input type="hidden" name="date_from_personalice" value="{{$date_from}}">
									<input type="hidden" name="date_to_personalice" value="{{$date_to}}">
									<input type="hidden" name="before_navigation" id="before_navigation" value="0">
						    		<button type="submit" class="btn  btn-primary btn-arrow-left" onclick="anterior()">Ant.</button>
									@if(isset(Session::get('_flash')['current_date']))
										<button type="button" class="btn  btn-link">{{Session::get('_flash')['current_date']}}</button>
									@else
						    			<button type="button" class="btn  btn-link">{{$label_intervalo}}</button>
						    		@endif
						    		<button type="submit" class="btn  btn-primary btn-arrow-right" onclick="siguiente()">Sig.</button>
								</form>
							@endif
						</div>    	
					    @if(isset($ctrl) && $ctrl == 1)					    		
	  	     					<a href="{!! route('admin.users',[2, $id]) !!}" class="btn btn-info btn-lg float-right"><i class="fa fa-undo"></i></a>
	  	     			@endif
					</div>
					<div class="content-mid">					
						<div class="grid_3 col-md-12">	  	     				
	  	     				<div class="but_list">
	  	       					<div class="bs-example bs-example-tabs" role="tabpanel" data-example-id="togglable-tabs">
				   					<ul id="myTab" class="nav nav-tabs" role="tablist">
				   						@foreach($user->energy_meters as $i => $contador)
        		   							@if(App\Http\Controllers\GroupsController::checkContadorMenu($user->id, 14, $contador->id))
            		   							@if($contador->id == $user->current_count->meter_id)
                                					<li role="presentation" class="active">
                  			  							<a href="{{route('energymeter.change', [$user->id, $contador->id])}}" id="home-tab" style="font-size: 14pt"><i class="fa fa-clock-o"></i>{{$contador->count_label}}</a>
                  			  						</li>
              			  						@else
              			  							<li role="presentation">
                  			  							<a href="{{route('energymeter.change', [$user->id, $contador->id])}}" id="home-tab" style="font-size: 14pt"><i class="fa fa-clock-o"></i>{{$contador->count_label}}</a>
                  			  						</li>
              			  						@endif
              			  					@endif
                        				@endforeach
	  								</ul>
	  		  						<div id="myTabContent" class="tab-content">
	  		  							@if(count($analizadores) > 0)
      		  								<div role="tabpanel" class="tab-pane fade in active col-md-12" id="Contador" aria-labelledby="Contador">
        	  		  							@if(isset($domicilio->suministro_del_domicilio))
          		  									<label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">{{$domicilio->suministro_del_domicilio}}</label></label>
          		  								@else
          		  									<label class="title-ubicacion">Ubicación: <label class="title-ubicacion2">sin ubicación</label></label>
          		  								@endif
          		  								<br>
          		  								<label class="title-ubicacion">Intervalo: <label class="title-ubicacion2"> Desde {{$date_from}} hasta {{$date_to}}</label></label>		  		  							
          		  								<br>    	  		  								
          		  								<div class="col-md-12">
        	  		  								@foreach($analizadores as $index => $analizador)
          												<div class="panel-group col-md-3">
    													  	<div class="panel panel-primary">
    													    	<div class="panel-heading text-center" style="background-color: {{$analizador->color_etiqueta}}">
    													    		<a href="{{route('analizadores.graficas', [$user->id, $analizador->id])}}" style="color: #272822 !important; font-weight: bold;">{{$analizador->label}} </a>
    													    	</div>
    													    	@if(count($total_energias) > $index)
        													    	<div class="panel-body">
        													    		Energía Total Activa: {{number_format($total_energias[$index]->energia_activa,0,',','.')}} kWh<br>
        													    		Energía Total Reactiva: {{number_format($total_energias[$index]->energia_reactiva,0,',','.')}} kVArh
        													    	</div>
    													    	@endif
    													  	</div>
    													</div>		  		  							
        		  		  							@endforeach						  		  							
          		  								</div>
      		  								</div>
  		  								@endif  									
		  		  							

		  		  						@if(count($analizadores) == 0)
	  		  								<div class="col-md-12">
	  		  									<div class="alert alert-info text-center" role="alert" style="margin-top: 200px; margin-bottom: 300px">
												  <strong>Actualmente no tiene equipos monitorizados en este espacio</strong>
												</div>
	  		  								</div>
	  		  							@else
	  		  								<div class="col-md-12">
	  		  									<img src="{{asset($esquema_electrico)}}" class="img-responsive">
	  		  								</div>
	  		  							@endif
		  		  							
  						</div>
	       						</div>
	       					</div>
	      				</div>
					</div>
					<div class="clearfix"> </div>
				@else
					<div class="banner col-md-12">
						<br><br>
					</div>

					<div class="content-mid">
						<div class="grid_3 col-md-12">				    	
					    	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
					    </div>
					</div>
					<div class="clearfix"></div>
				@endif
			</div>
			<div class="content-bottom">				
          		@include('Dashboard.modals.modal_intervalos');
			</div>
			<div class="copy">
	            <p> &copy; 2020 Submeter 4.0. Todos los derechos reservados</p>
			</div>
		</div>
		<div class="clearfix"> </div>
	</div>
@endsection

@section('scripts')
	<script src="{{asset('js/jquery.min.js')}}"> </script>
	<script src="{{asset('js/jquery.metisMenu.js')}}"></script>
    <script src="{{asset('js/jquery.slimscroll.min.js')}}"></script>        
    <script src="{{asset('js/custom.js')}}"></script>
    <script src="{{asset('js/screenfull.js')}}"></script>
	<script src="{{asset('js/scripts.js')}}"></script>
    <script src="{{asset('js/jquery.nicescroll.js')}}"></script>	
	<script src="{{asset('js/bootstrap.min.js')}}"> </script>
    <script src="{{asset('js/pie-chart.js')}}" type="text/javascript"></script>
    <script src="{{asset('js/skycons.js')}}"></script>
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