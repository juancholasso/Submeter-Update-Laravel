@extends('layouts.newapp')

@section('content')
	<h2 class="section-title">Iniciar sesi&oacute;n</h2>
	@if(session()->has('locked'))
		<div class="alert alert-danger">
			<h3 class="alert-title">@lang('auth.account_locked_title')</h3>
			<p>@lang('auth.account_locked_explain')</p>
			<hr>
			<p>@lang('auth.account_locked_solution')</p>
		</div>
	@endif
	@if (Session::has('message-error'))
		<div class="alert alert-danger">{{ Session::get('message-error') }}</div>
	@endif
	<form id="myForm" action="{{ route('login') }}" method="POST">
		{{ csrf_field() }}
		<input id="email" class="input" type="email" name="email" value="{{ old('email') }}" placeholder="* Email" required autofocus>
		@if ($errors->has('email'))
			<span class="form-error">{{ $errors->first('email') }}</span>
		@endif
		<input id="password" class="input" type="password" name="password" placeholder="* Contraseña" required>
		@if ($errors->has('password'))
			<span class="form-error">{{ $errors->first('password') }}</span>
		@endif
		<input type="hidden" name="lat" id="lat">
		<input type="hidden" name="lon" id="lon">
		<input type="hidden" name="address" id="address">
    <input type="hidden" name="timezoneoffset" id="timezoneoffset">
		<a class="styled-link" href="{{ route('password.request') }}">He olvidado mi contrase&ntilde;a</a>
		<input class="btn" type="submit" value="Iniciar sesi&oacute;n">
	</form>
  <span>¿No tienes cuenta?</span>
  <a class="btn" href="{{ route('solicitud.registro') }}">Solicitar registro</a>
@endsection

@section('scripts')
	{{-- <script src="{{asset('js/jquery.nicescroll.js')}}"></script> --}}
	{{-- <script src="{{asset('js/scripts.js')}}"></script> --}}
	<script>
		var lat = document.getElementById("lat");
		var lon = document.getElementById("lon");
		var address = document.getElementById("address");
	
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(showPosition, showError);
		}
		else { 
			console.log("Geolocation is not supported by this browser.");
		}
		
		/*Si acepta compartir su ubicación se sobreescriben los valores, de lo contrario se quedan los primeros*/
		function showPosition(position) {
			lat.value = position.coords.latitude;
			lon.value = position.coords.longitude;
	
			//Create query for the API.
			var latitude = "latitude=" + position.coords.latitude;
			var longitude = "&longitude=" + position.coords.longitude;
			var query = latitude + longitude + "&localityLanguage=en";
	
			const Http = new XMLHttpRequest();
	
			var bigdatacloud_api = "https://api.bigdatacloud.net/data/reverse-geocode-client?";
	
			bigdatacloud_api += query;
	
			Http.open("GET", bigdatacloud_api);
			Http.send();
	
			Http.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var myObj = JSON.parse(this.responseText);
					console.log(myObj);
					address.value= myObj.locality + ", " + myObj.city + ", " + myObj.principalSubdivision + ", " + myObj.countryName;
				}
			};
		}
	
		function showError(error) {
			switch(error.code) {
				case error.PERMISSION_DENIED:
				case error.POSITION_UNAVAILABLE:
				case error.TIMEOUT:
					/*Obtiene una coordenadas por IP en base al ISP*/
					$.ajax({
						url: "https://extreme-ip-lookup.com/json/?callback=callback",
						jsonpCallback: "callback",
						dataType: "jsonp",
						success: function(location) {
							$('#address').val(location.city + ', ' + location.region + ', ' + location.country);
							$('#lat').val(location.lat);
							$('#lon').val(location.lon);
						}
					});
				break;
				case error.UNKNOWN_ERROR:
					lat.value = 0;
					lon.value = 0;
					address.value="Error al obtener la geolocalización"
				break;
			}
		}
	</script>
	<script>
    var d = new Date();
    document.getElementById("timezoneoffset").value = d.getTimezoneOffset();
	</script>
@endsection
