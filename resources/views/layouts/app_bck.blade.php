<!DOCTYPE HTML>
<html>
    <head>
        <title>Submeter 4.0 | Login</title>
        <link rel="shortcut icon" type="image/x-icon" href="{{asset('images/Submeter_Favicon.ico')}}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />        
        <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
        <link href="{{asset('css/bootstrap.min.css')}}" rel='stylesheet' type='text/css' />
        <!-- Custom Theme files -->
        <link href="{{asset('css/style.css?version=4.04')}}" rel='stylesheet' type='text/css' />
        <link href="{{asset('css/font-awesome.css')}}" rel="stylesheet"> 
        <script src="{{asset('js/jquery.min.js')}}"> </script>
        <script src="{{asset('js/bootstrap.min.js')}}"> </script>
        <link href="{{asset('plugins/gldatepicker/styles/glDatePicker.default.css')}}" rel="stylesheet"> 

    </head>
    <body  class="font_login"> 
        @yield('content')

        @yield('scripts')
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    </body>
    <footer>
        <div class="copy-right">
            <p style="color: #E6E6E6"> &copy; 2020 Submeter 4.0. Todos los derechos reservados</p>     
        </div>
    </footer>
</html>

{{--<div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>


                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')
    </div>--}}