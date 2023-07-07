<html>
	<head>
	<script
          src="https://code.jquery.com/jquery-3.2.1.min.js"
          integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
          crossorigin="anonymous"></script>
	<script type="text/javascript" src="{{asset('js/highcharts/highcharts6.js') }}"></script>  	
	<script type="text/javascript" src="{{asset('js/highcharts/highcharts3d6.js') }}"></script>
    
    
</head>

<body>	
    <div id="container" style="width:100%; height:500px;"></div>
</body>

<script type="text/javascript">
	var keySerie = '{!! $keySerie !!}';
	var dataSerie = '{!! $dataSerie !!}';
	var percentageSerie = '{!! $percentageSerie !!}';

	if (keySerie.length > 0) {
		keySerie = $.parseJSON(keySerie);
	} else {
		keySerie = [];
	}

	if (dataSerie.length > 0) {
		dataSerie = $.parseJSON(dataSerie);
	} else {
		dataSerie = [];
	}

	if (percentageSerie.length > 0) {
		percentageSerie = $.parseJSON(percentageSerie);
	} else {
		percentageSerie = [];
	}

	var serie = [];
	for(var i = 0; i < keySerie.length; i++)
	{
		serie.push({
            name: keySerie[i] ,
            y: dataSerie[i]
        });
	}
	
	var pieColors = (function () {
    var colors = [],
        base = Highcharts.getOptions().colors[0],
        i;

    for (i = 0; i < 10; i += 1) {
        // Start out with a darkened base color (negative brighten), and end
        // up with a much brighter color
        colors.push(Highcharts.Color(base).brighten((i - 3) / 7).get());
    }
    return colors;
}());
	
	Highcharts.chart('container', {
    chart: {
		plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie',

    },
    title: false,    
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            
			colors: pieColors,
            dataLabels: {
                enabled: true,
                format: '{point.name}: <b>{point.percentage:.2f}%</b>',
                style: {
					fontSize: '12px',
                    color: 'black',
					fontWeight: 'normal',
                }
            }
        },
        series: {
            animation: false
        }
    },
    series: [{
        name: 'Brands',
        colorByPoint: true,
        data: serie
    }]
});
    </script>
</html>