<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submeter 4.0</title> 
<link href="{{asset('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' />
<!-- <link href="{{asset('css/style.css')}}" rel='stylesheet' type='text/css' /> -->

</head>
<body>
	<header class="clearfix">
      <div id="logo">
        <img src="{{asset($logo)}}" style="width: 90px; height: 90px">
      </div>
      <div id="company">
        <h2 class="name">Empresa: {{$usuario->name}}</h2>
        @if(!is_null($usuario->_perfil))
        <div>Dirección: {{$usuario->_perfil->direccion}}</div>        
        @else
        	<div>Dirección: Sin dirección</div>
        @endif
        <div>Contador: {{$contador}}</div>
        <div>Email: {{$usuario->email}}</div>
        <div>
        	Intervalo del reporte: Desde {{$date_from}} hasta {{$date_to}}
        </div>
      </div>
    </header>
    <main>
    	<br>
	    <div class="container-fluid">			
			<div class="outter-wp">
				<div class="forms-main">
					<div class="row">							
							<div class="col-md-12">
								<?php echo($html); ?>	
							</div>
					</div>
					</div>
				</div>
			
			</div>
		</div>    	
    </main>
	
</body>
</html>