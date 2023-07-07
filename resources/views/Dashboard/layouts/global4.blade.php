<!DOCTYPE HTML>
<html>
	<head>
		<title>Submeter 4.0 | Home</title>

		<link rel="shortcut icon" type="image/x-icon" href="{{asset('images/Submeter_Favicon.ico')}}">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<!-- Lineas agregadas por el colorpicker-->
		<!-- Tener en cuenta la version del jquery para evitar problemas con la funcionalidad de colorpicker-->
		<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.6/css/bootstrap-colorpicker.css" rel="stylesheet"> -->
		<script src="https://code.jquery.com/jquery-2.2.2.min.js"></script>
		<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.6/js/bootstrap-colorpicker.js"></script> -->
		<!-- Lineas agregadas por el colorpicker-->
		<!-- <link href="{{asset('css/spectrum.css')}}" rel="stylesheet"> -->
		<link href="{{asset('js/colorpicker/css/bootstrap-colorpicker.css')}}" rel="stylesheet">
		<!-- <script src="{{asset('js/spectrum.js')}}" type="text/javascript"></script> -->
		<script src="{{asset('js/colorpicker/js/bootstrap-colorpicker.min.js')}}" type="text/javascript"></script>
		<!-- <script src="https://submet.es/js/bootstrap-colorpicker.min.js" type="text/javascript"></script> -->

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		
		<link href="{{asset('css/style.css?v=4.2.3')}}" rel='stylesheet' type='text/css' />
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
		<link href="{{asset('css/custom.css?v=4.1.3')}}" rel="stylesheet">        
		<link href="{{asset('css/bootstrap-tabs-x-bs4.min.css')}}" type="text/css" rel="stylesheet">

		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap2/bootstrap-switch.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.18/datatables.min.css"/>
 
		<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.18/datatables.min.js"></script>
		<!----------------------------Scripts for pdf--------------------------->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
		<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
		<!----------------------------Scripts for pdf--------------------------->        
		<!-- js necesario para las gráficas -->        
		@if(!isset($chartjsnew))
			<script src="{{asset('js/Chart.js')}}"></script>
		@else
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" type="text/javascript"></script>
			<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-piechart-outlabels" type="text/javascript"></script>
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
		@endif
	</head>
	<body>
		<div class="wrapper">
			<div class="sidebar">
				<nav class="navbar navbar-expand-lg navbar-side navbar-dark sticky-top flex-md-nowrap p-0">
					{{-- <a class="navbar-brand pt-3 mx-0" href="{!! route('home') !!}">
						@if(isset($ctrl) && $ctrl == 1)
							<h4>{!! $user->enterprise_name !!}</h4>
						@else
							<h4>{!! $user->enterprise_name !!}</h4>
						@endif
					</a> --}}
					<a class="navbar-brand pt-3 mx-0" style="padding: 0.2em 0 !important;" href="{!! route('home') !!}">
						<img src="{{asset('images/submeter_logo_white_big.png')}}" alt="submeter_logo" width="200px" style="margin: 0 auto" />
					</a>
					<button class="navbar-toggler mr-3 d-inline d-md-none" type="button" data-toggle="collapse" data-target="#side-menu" aria-controls="menuVertical" aria-expanded="true">
						<span class="navbar-toggler-icon"></span>
					</button>
				</nav>
				<div class="d-none d-md-inline">
					<div class="cnt-nav-submeter bg-submeter-1 scrollbar-submeter nav-menu">
						@include('Dashboard.includes.menu4',array('user_log' => $user))
					</div>    				
				</div>
				<div class="d-inline d-md-none">
					<div class="collapse bg-submeter-1 scrollbar-submeter nav-menu" id="side-menu">
						@include('Dashboard.includes.menu4',array('user_log' => $user))                        
					</div>
				</div>    						
			</div>
			<div class="page-content full-body">
				<div class="row bg-submeter-2">
					<div class="col-8 col-sm-6">
						<div class="py-3 pl-3 pl-sm-5">
							@if(isset($titulo))
								@if($titulo == 'Emisiones CO2')
									<h4>Emisiones CO<sub>2</sub></h4>
								@else
									<h4>{{$titulo}}</h4>
								@endif
							@endif
						</div>
					</div>
					<div class="col-4 col-sm-6">
						@include('Dashboard.includes.menu_horizontal4')
					</div>
				</div>				
				@yield('content')    			
			</div>   		
		</div> 
		@yield('scripts')
		<script src="{{asset('js/app.js')}}" type="text/javascript"></script>  
	</body>    
</html>
