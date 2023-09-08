@extends('Dashboard.layouts.global5')

@section('intervals')
    @include('Dashboard.includes.intervalos')
@endsection

@section('counters')
    @php $user_id = Auth::user()->id; @endphp
    @include('Dashboard.includes.contadores', [
        'menuId' => 14,
        'origin_link' => '/analizadores/grupos/' . $user_id,
    ])
@endsection

@section('content')
    @if (isset($current_group_data['name']) && count($current_group_data['analyzers_stats']) > 0)
        <div class="row">
            <div class="column">
                <div class="btn-container justify-start">
                    <button type="button" class="btn active">{{ $current_group_data['name'] }}
                        ({{ count($current_group_data['analyzers_stats']) }})</button>
                    @if (isset($current_group_data['analyzers_stats']))
                        @foreach ($current_group_data['analyzers_stats'] as $analyzer)
                            <input type="hidden" name="dataAName[]" value="{{ $analyzer['nombre'] }}" />
                            <input type="hidden" name="dataAColor[]" value="{{ $analyzer['color'] }}" />
                            <input type="hidden" name="dataActiva[]" value="{{ $analyzer['energia_activa'] }}" />
                            <input type="hidden" name="dataFActiva[]"
                                value="{{ $analyzer['nombre'] }}: {{ number_format($analyzer['energia_activa'], 0, ',', '.') }} KWh" />
                            <input type="hidden" name="dataPotencia[]"
                                value="{{ $analyzer['potencia_activa_promedio'] }}" />
                            <input type="hidden" name="dataFPotencia[]"
                                value="{{ $analyzer['nombre'] }}: {{ number_format($analyzer['potencia_activa_promedio'], 2, ',', '.') }} KW" />
                        @endforeach
                    @endif
                    @foreach ($data_groups as $group)
                        <input type="hidden" name="groupActiva[]" value="{{ $group['total_energia_activa'] }}" />
                        <input type="hidden" name="groupFActiva[]"
                            value="{{ $group['name'] }}: {{ number_format($group['total_energia_activa'], 0, ',', '.') }} KWh" />
                        <input type="hidden" name="groupReactiva[]" value="{{ $group['total_energia_reactiva'] }}" />
                        <input type="hidden" name="groupFReactiva[]"
                            value="{{ number_format($group['total_energia_reactiva'], 0, ',', '.') }} KVAh" />
                        <input type="hidden" name="groupPotencia[]"
                            value="{{ $group['total_potencia_activa_promedio'] }}" />
                        <input type="hidden" name="groupFPotencia[]"
                            value="{{ $group['name'] }}: {{ number_format($group['total_potencia_activa_promedio'], 2, ',', '.') }} KW" />
                        <input type="hidden" name="groupName[]" value="{{ $group['name'] }}" />
                        @if (count($group['analyzers_stats']) == 0)
                            @continue
                        @endif
                        @if ($group['group_id'] != $current_group_data['group_id'])
                            <a class="btn"
                                href="{{ route('analyzersgroupselected', ['id' => $user->id, 'group_id' => $group['group_id']]) }}"><span>{{ $group['name'] }}
                                    ({{ count($group['analyzers_stats']) }})</span></a>
                        @endif
                    @endforeach
                    @if (isset($data_groups) && is_array($data_groups) && count($data_groups) > 0)
                        <input type="hidden" name="totalGroupsEnergyF"
                            value="{{ number_format($groups_total_energy, 0, ',', '.') }} KWh" />
                        <input type="hidden" name="totalGroupsEnergy" value="{{ $groups_total_energy }}" />
                        @foreach ($data_groups as $group)
                            <div class="data-sankey">
                                <input type="hidden" name="eNameG[]"
                                    value="{{ $group['name'] }}: {{ number_format($group['total_energia_activa'], 0, ',', '.') }} KWh ({{ number_format($group['porcentaje_energia_activa'], 2, ',', '.') }} %)" />
                                <input type="hidden" name="eValueG[]" value="{{ $group['total_energia_activa'] }}" />
																<input type="hidden" name="eHeightG[]" value="{{ $group['Sankey_resolutions']?:750 }}" />
                                <input type="hidden" name="eRestOperationG[]"
                                    value="{{ isset($group['rest_operation']) ? $group['rest_operation'] : '0' }}" />
                                @if (is_array($group['analyzers_stats']) && count($group['analyzers_stats']) > 0)
                                    @foreach ($group['analyzers_stats'] as $analyzer)
                                        <input type="hidden" name="eNameA[]"
                                            value="{{ $analyzer['nombre'] }}: {{ number_format($analyzer['energia_activa'], 0, ',', '.') }} KWh  ({{ number_format($analyzer['porcentaje_energia_activa'], 2, ',', '.') }} %)" />
                                        <input type="hidden" name="eValueA[]" value="{{ $analyzer['energia_activa'] }}" />
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    @endif
                    @if (isset($meters_data))
                        <div class="meter-data-sankey">
                            @foreach ($meters_data as $meter)
                                <div>
                                    <input type="hidden" name="meterdata[]"
                                        value="{{ $meter['count_label'] }}^{{ $meter['energia_activa'] }}^{{ $meter['count_label'] }}: {{ number_format($meter['energia_activa'], 0, ',', '.') }} KWh  ({{ number_format($meter['porcentaje_energia_activa'], 2, ',', '.') }} %)^" />
                                    @php
                                        $v = '';
                                        foreach ($groups_data as $group) {
                                            if ($meter['group_id'] == $group['id']) {
                                                $v = $group['nombre'] . '^' . $group['energia_activa'] . '^' . $group['nombre'] . ': ' . number_format($group['energia_activa'], 0, ',', '.') . ' KWh  (' . number_format($group['porcentaje_energia_activa'], 2, ',', '.') . ' %)^';
                                                break;
                                            }
                                        }
                                    @endphp
                                    <input type="hidden" name="groupdata[]" value="{{ $v }}" />
                                    <div class="data-sankey-in-meter">
                                        @foreach ($data_groups as $group)
                                            @if (is_array($group['analyzers_stats']) && count($group['analyzers_stats']) > 0)
                                                @foreach ($group['analyzers_stats'] as $analyzer)
                                                    @if ($analyzer['meter_id'] == $meter['id'])
                                                        <input type="hidden" name="eNameA[]"
                                                            value="{{ $analyzer['nombre'] }}: {{ number_format($analyzer['energia_activa'], 0, ',', '.') }} KWh  ({{ number_format($analyzer['porcentaje_energia_activa'], 2, ',', '.') }} %)" />
                                                        <input type="hidden" name="eValueA[]"
                                                            value="{{ $analyzer['energia_activa'] }}" />
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (isset($current_group_data['analyzers_stats']))
            <div class="row">
                <div class="column">
                    <div class="btn-container justify-start">
                        @foreach ($current_group_data['analyzers_stats'] as $analyzer)
                            @if (!isset($analyzer['nombre']))
                                @continue
                            @endif
                            <div class="card card-anlz m-1">
                                <div class="card__header" style='background-color:{{ $analyzer['color'] }};'>
                                    <a class="card__title"
                                        href="{{ route('analizadores.graficas', ['user_id' => $user->id, 'group_id' => $current_group_data['group_id'], 'anlz_id' => $analyzer['id']]) }}">{{ $analyzer['nombre'] }}</a>
                                </div>
                                <div class="card__body">
                                    <p>Energía Activa: {{ number_format($analyzer['energia_activa'], 0, ',', '.') }} kWh</p>
                                    <p>Energía Reactiva: {{ number_format($analyzer['energia_reactiva'], 0, ',', '.') }} kVArh
                                    </p>
                                    <p>Pot. Activa Media:
                                        {{ number_format($analyzer['potencia_activa_promedio'], 2, ',', '.') }} KW</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="column">
                <div class="tab-panel" data-submeter-tab-panel>
                    <div class="tabs">
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-uni">Esquema Unifilar</a>
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-activa">Energía Activa</a>
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-potencia">Potencia Activa</a>
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-gactiva">Grupos E. Activa</a>
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-gpotencia">Grupos P. Activa</a>
                        <a class="tab-link" data-submeter-toggle="tab" href="#tab-sankey">Sankey</a>
                    </div>
                    <div class="wrapper-tab-content">
                        <div class="tab-content graph plot-tab" id="tab-uni">
                            @if (isset($current_group_data['file_image']))
                                <div class="d-flex">
                                    <img style="max-height: 95%; max-width: 95%; margin: auto;"
                                        alt="esquema unifilar analizadores"
                                        src="{{ asset($current_group_data['file_image']) }}" />
                                </div>
                            @endif
                        </div>
                        <div class="tab-content graph plot-tab" id="tab-activa">
                            <div class="plot-container" id="chart-activa" style="height: 600px;"></div>
                        </div>
                        <div class="tab-content graph plot-tab" id="tab-potencia">
                            <div class="plot-container" id="chart-potencia" style="height: 600px;"></div>
                        </div>
                        <div class="tab-content graph plot-tab" id="tab-gactiva">
                            <div class="plot-container" id="chart-gactiva" style="height: 600px;"></div>
                        </div>
                        <div class="tab-content graph plot-tab" id="tab-gpotencia">
                            <div class="plot-container" id="chart-gpotencia" style="height: 600px;"></div>
                        </div>
                        <div class="tab-content graph plot-tab" id="tab-sankey">
                            <div id="sankey_plot" class="grid place-items-center overflow-x-auto overflow-y-hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('modals')
    @include('Dashboard.modals.modal_intervals5')
@endsection

@section('scripts')
    @include('Dashboard.includes.scripts_modal_interval')
    @include('Dashboard.includes.script_intervalos')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        $('#div_datatimes').hide();
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#datepicker').prop('required', false);
        $('#datepicker2').prop('required', false);

        $(function() {
            $("#datepicker").datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
            });
        });

        $(function() {
            $("#datepicker2").datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
            });
        });

        function changeFunc() {
            var selectBox = document.getElementById("option_interval");
            var selectedValue = selectBox.options[selectBox.selectedIndex].value;
            if (selectedValue == 9) {
                $('#div_datatimes').show();
                $('#datepicker').prop('required', true);
                $('#datepicker2').prop('required', true);
            } else {
                $('#div_datatimes').hide();
                $('#datepicker').val('');
                $('#datepicker2').val('');
                $('#datepicker').prop('required', false);
                $('#datepicker2').prop('required', false);
            }
        }
    </script>
    <script>
        const tabPanelList = document.querySelectorAll("[data-submeter-tab-panel]")
        tabPanelList.forEach((tabPanel) => {
            const tabList = tabPanel.querySelectorAll('[data-submeter-toggle="tab"]')
            if (isNull(tabList)) return

            let activeTab = tabList[0]
            let activeTabContent = getTarget(activeTab)
            activeTab.classList.add("active")
            activeTabContent.classList.add("active")

            tabList.forEach((tab) => {
                tab.addEventListener("click", (e) => {
                    e.preventDefault()
                    if (e.target === activeTab) {
                        return
                    }

                    activeTab.classList.remove("active")
                    activeTab = tab
                    activeTab.classList.add("active")

                    activeTabContent.classList.remove("active")
                    activeTabContent = getTarget(activeTab)
                    activeTabContent.classList.add("active")
                })
            })
        })

        function getTarget(element) {
            let targetId

            if (element.tagName === "BUTTON") {
                targetId = element.dataset.target
            } else if (element.tagName === "A") {
                targetId = element.getAttribute("href")
            }

            if (!isNull(targetId)) {
                const target = document.querySelector(targetId)
                return target
            }

            return
        }

        function isNull(variable) {
            return variable === undefined || variable === null
        }
    </script>
    <script src="{{ asset('js/canvas.js') }}"></script>
    <script>
        var new_data_group_js = "<?php echo isset($new_data_group) ? addslashes(json_encode($new_data_group)) : '[]'; ?>";
        var current_data_group = "<?php echo isset($current_group_data) ? addslashes(json_encode($current_group_data)) : '{}'; ?>";
        var randomScalingFactor = function() {
            return Math.round(Math.random() * 100);
        };

        $(document).ready(function() {
            createPlotActiva();
            createPlotGActiva();
            google.charts.load('current', {
                'packages': ['sankey']
            });
            google.charts.setOnLoadCallback(drawSankey);

            $('[data-submeter-toggle="tab"]').click(function() {
                var cnt = $(this).attr("href");
                cnt = $(cnt);
                cnt = cnt.find(".plot-container");
                if (cnt.length > 0) {
                    var chart = cnt.data("chart");
                    if (chart) {
                        if (typeof(chart.render) == "function") {
                            window.setTimeout(function() {
                                chart.render();
                            }, 300)
                        }
                    }
                }
                return true;
            });
        });

        CanvasJS.addCultureInfo("es", {
            decimalSeparator: ",",
            digitGroupSeparator: "."
        })

        function calcDataPlot() {
            var plotRows = {};
            if (typeof new_data_group_js !== "undefined" && typeof current_data_group !== "undefined") {
                if (new_data_group_js !== "" && current_data_group !== "") {
                    var jsonRows = JSON.parse(new_data_group_js);
                    var jsonGroup = JSON.parse(current_data_group);
                    var pointsA = [],
                        pointsP = [],
                        colors = [];
                    //Level #1
                    jsonRows.map((a, i) => {
                        if (jsonGroup.group_id !== a.group_id) return;
                        //Level #2
                        if (!a.analyzers_stats) return;
                        a.analyzers_stats.map((b, ii) => {
                            if (b.id && b.nombre) {
                                colors.push(b.color);
                                pointsA.push({
                                    y: b.energia_activa,
                                    indexLabel: `${b.nombre}: ${numberFormat(b.energia_activa,0)} KWh`
                                });
                                pointsP.push({
                                    y: b.potencia_activa_promedio,
                                    indexLabel: `${b.nombre}: ${numberFormat(b.potencia_activa_promedio)} KW`
                                });
                            }

                            //Level #3
                            if (!b.analyzers_stats) return;
                            b.analyzers_stats.map((c, iii) => {
                                if (c.id && c.nombre) {
                                    colors.push(c.color);
                                    pointsA.push({
                                        y: c.energia_activa,
                                        indexLabel: `${c.nombre}: ${numberFormat(c.energia_activa,0)} KWh`
                                    });
                                    pointsP.push({
                                        y: c.potencia_activa_promedio,
                                        indexLabel: `${c.nombre}: ${numberFormat(c.potencia_activa_promedio)} KW`
                                    });
                                }

                                //Level #4
                                if (!c.analyzers_stats) return;
                                c.analyzers_stats.map((d, iv) => {
                                    if (d.id && d.nombre) {
                                        colors.push(d.color);
                                        pointsA.push({
                                            y: d.energia_activa,
                                            indexLabel: `${d.nombre}: ${numberFormat(d.energia_activa,0)} KWh`
                                        });
                                        pointsP.push({
                                            y: d.potencia_activa_promedio,
                                            indexLabel: `${d.nombre}: ${numberFormat(d.potencia_activa_promedio)} KW`
                                        });
                                    }

                                    //Level #5
                                    if (!d.analyzers_stats) return;
                                    d.analyzers_stats.map((e, v) => {
                                        if (e.id && e.nombre) {
                                            colors.push(e.color);
                                            pointsA.push({
                                                y: e.energia_activa,
                                                indexLabel: `${e.nombre}: ${numberFormat(e.energia_activa,0)} KWh`
                                            });
                                            pointsP.push({
                                                y: e.potencia_activa_promedio,
                                                indexLabel: `${e.nombre}: ${numberFormat(e.potencia_activa_promedio)} KW`
                                            });
                                        }

                                        //Level #6
                                        e.analyzers_stats.map((f, vi) => {
                                            if (f.id && f.nombre) {
                                                colors.push(f.color);
                                                pointsA.push({
                                                    y: f.energia_activa,
                                                    indexLabel: `${f.nombre}: ${numberFormat(f.energia_activa,0)} KWh`
                                                });
                                                pointsP.push({
                                                    y: f.potencia_activa_promedio,
                                                    indexLabel: `${f.nombre}: ${numberFormat(f.potencia_activa_promedio)} KW`
                                                });
                                            }
                                        });
                                    });
                                });
                            });
                        });
                    });
                    plotRows.pointsA = pointsA;
                    plotRows.pointsP = pointsP;
                    plotRows.colors = colors;
                }
            }
            return plotRows;
        }

        function createPlotActiva() {
            var activa = $("input[name^='dataActiva']");
            var factiva = $("input[name^='dataFActiva[]']");
            var potencia = $("input[name^='dataPotencia']");
            var fpotencia = $("input[name^='dataFPotencia']");
            var names = $("input[name^='dataAName']");
            var colors = $("input[name^='dataAColor']");
            var dataPointsA = [];
            var dataPointsP = [];
            var dataC = [];
            for (var i = 0; i < names.length; i++) {
                dataC.push($(colors[i]).val());
                dataPointsA.push({
                    y: parseFloat($(activa[i]).val()),
                    indexLabel: $(factiva[i]).val()
                })
                dataPointsP.push({
                    y: parseFloat($(potencia[i]).val()),
                    indexLabel: $(fpotencia[i]).val()
                })
            }
            //var dataPlot = calcDataPlot();
            //if(dataPlot.pointsA) dataPointsA = dataPlot.pointsA;
            //if(dataPlot.pointsP) dataPointsP = dataPlot.pointsP;
            //if(dataPlot.colors) dataC = dataPlot.colors;

            CanvasJS.addColorSet("analyzers", dataC);

            var titulo = "{{ $titulo }}";
            var conta = "{{ $contador2->count_label }}";
            var date_to = "{{ $date_to }}";
            var date_from = "{{ $date_from }}";
            var chart = new CanvasJS.Chart("chart-activa", {
                animationEnabled: true,
                culture: "es",
                theme: "light2",
                height: 600,
                colorSet: "analyzers",
                title: {
                    text: "Energía Activa por Grupos",
                    fontSize: 18,
                    margin: 50,
                    fontColor: "#004165"
                },
                exportFileName: "EnergíaActivaporGrupos-" + conta + "-" + date_from + "-" + date_to,
                exportEnabled: true,
                axisX: {
                    titleFontSize: 12,
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                axisY: {
                    title: '\u20AC' + "/MWh",
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                toolTip: {
                    shared: "true"
                },
                data: [{
                    type: "pie",
                    showInLegend: false,
                    legendText: "{indexLabel}",
                    dataPoints: dataPointsA
                }]
            });
            //chart.render();
            $("#chart-activa").data("chart", chart);

            var titulo = "{{ $titulo }}";
            var conta = "{{ $contador2->count_label }}";
            var date_to = "{{ $date_to }}";
            var date_from = "{{ $date_from }}";
            var chartp = new CanvasJS.Chart("chart-potencia", {
                animationEnabled: true,
                culture: "es",
                theme: "light2",
                height: 600,
                colorSet: "analyzers",
                title: {
                    text: "Potencia Media",
                    fontSize: 18,
                    margin: 50,
                    fontColor: "#004165"
                },
                exportFileName: "PotenciaMedia-" + conta + "-" + date_from + "-" + date_to,
                exportEnabled: true,
                axisX: {
                    titleFontSize: 12,
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                axisY: {
                    title: '\u20AC' + "/MWh",
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                toolTip: {
                    shared: "true"
                },
                data: [{
                    type: "pie",
                    showInLegend: false,
                    legendText: "{indexLabel}",
                    dataPoints: dataPointsP
                }]
            });
            //chartp.render();
            $("#chart-potencia").data("chart", chartp);
        }

        function createPlotGActiva() {
            var activa = $("input[name^='groupActiva']");
            var factiva = $("input[name^='groupFActiva[]']");
            var potencia = $("input[name^='groupPotencia']");
            var fpotencia = $("input[name^='groupFPotencia']");
            var names = $("input[name^='groupName']");
            var dataPointsGA = [];
            var dataPointsGP = [];
            for (var i = 0; i < names.length; i++) {
                dataPointsGA.push({
                    y: parseFloat($(activa[i]).val()),
                    indexLabel: $(factiva[i]).val()
                })
                dataPointsGP.push({
                    y: parseFloat($(potencia[i]).val()),
                    indexLabel: $(fpotencia[i]).val()
                })
            }

            var titulo = "{{ $titulo }}";
            var conta = "{{ $contador2->count_label }}";
            var date_to = "{{ $date_to }}";
            var date_from = "{{ $date_from }}";
            var chart = new CanvasJS.Chart("chart-gactiva", {
                animationEnabled: true,
                culture: "es",
                theme: "light2",
                height: 600,
                title: {
                    text: "Energía Activa",
                    fontSize: 18,
                    margin: 50,
                    fontColor: "#004165"
                },
                exportFileName: "EnergíaActiva-" + conta + "-" + date_from + "-" + date_to,
                exportEnabled: true,
                axisX: {
                    titleFontSize: 12,
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                axisY: {
                    title: '\u20AC' + "/MWh",
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                toolTip: {
                    shared: "true"
                },
                data: [{
                    type: "pie",
                    showInLegend: false,
                    legendText: "{indexLabel}",
                    dataPoints: dataPointsGA
                }]
            });
            //chart.render();
            $("#chart-gactiva").data("chart", chart);

            var titulo = "{{ $titulo }}";
            var conta = "{{ $contador2->count_label }}";
            var date_to = "{{ $date_to }}";
            var date_from = "{{ $date_from }}";
            var chartp = new CanvasJS.Chart("chart-gpotencia", {
                animationEnabled: true,
                culture: "es",
                theme: "light2",
                height: 600,
                title: {
                    text: "Potencia Media por Grupos",
                    fontSize: 18,
                    margin: 50,
                    fontColor: "#004165"
                },
                exportFileName: "PotenciaMediaporGrupos-" + conta + "-" + date_from + "-" + date_to,
                exportEnabled: true,
                axisX: {
                    titleFontSize: 12,
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                axisY: {
                    title: '\u20AC' + "/MWh",
                    titleFontColor: "#004165",
                    lineColor: "#004165",
                    labelFontColor: "#004165",
                    tickColor: "#004165"
                },
                toolTip: {
                    shared: "true"
                },
                data: [{
                    type: "pie",
                    showInLegend: false,
                    legendText: "{indexLabel}",
                    dataPoints: dataPointsGP
                }]
            });
            //chartp.render();
            $("#chart-gpotencia").data("chart", chartp);
        }

        function numberFormat(number = 0, decPlaces = 2, decSep = ",", thouSep = ".") {
            var sign = number < 0 ? "-" : "";
            var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
            var stringNum = i;
            if (i.length > 3) {
                stringNum = "";
                var aThousend = 0;
                for (var n = (i.length - 1); n >= 0; n--) {
                    aThousend++;
                    stringNum = (aThousend === 3 && n > 0 ? thouSep : "") + i[n] + stringNum;
                    if (aThousend === 3) aThousend = 0;
                }
            }
            /*
            var j = (j = i.length) > 3 ? j % 3 : 0;
            return sign +
            	(j ? i.substr(0, j) + thouSep : "") +
            	i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
            	(decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
            */
            return sign + stringNum + (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
        }

        function calcTotalAnalyzers() {
            var sankeyRows = [];
            if (typeof new_data_group_js !== "undefined") {
                if (new_data_group_js !== "") {
                    var jsonRows = JSON.parse(new_data_group_js);
                    var totalEnergy = 0;
                    jsonRows.map((n) => {
                        totalEnergy += n.total_energia_activa;
                    });
                    var totalNode = `Energía Total: ${numberFormat(totalEnergy, 0)} KWh`;
                    let currentLvl = 0;
                    //Level #1
                    jsonRows.map((a, i) => {
                        currentLvl = 1;
                        const restOperation1 = a.rest_operation && a.rest_operation === 1 ? true : false;
                        const nameNodeL1 =
                            `${a.name}: ${numberFormat(a.total_energia_activa, 0)} KWh (${numberFormat(a.porcentaje_energia_activa)} %)`;
                        sankeyRows.push([
                            totalNode,
                            nameNodeL1,
                            a.total_energia_activa + 1,
                            `Level ${currentLvl}`,
                            nameNodeL1
                        ]);
                        //Level #2
                        if (!a.analyzers_stats) return;
                        let subtotalNodeL1 = 0;
                        a.analyzers_stats.map(r1 => subtotalNodeL1 += (typeof r1.total_energia_activa !==
                            "undefined" ? r1.total_energia_activa : r1.energia_activa));
                        //a.analyzers_stats.map(r1 => subtotalNodeL1 += (typeof r1.energia_activa!=="undefined"?r1.energia_activa:0));
                        const restNodeL1 = (typeof a.total_energia_activa !== "undefined" ? a.total_energia_activa :
                            a.energia_activa) - subtotalNodeL1;
                        //const restNodeL1 = (typeof a.total_energia_activa!=="undefined"?a.total_energia_activa:0)-subtotalNodeL1;
                        if (restOperation1 && restNodeL1 > 0) {
                            sankeyRows.push([
                                nameNodeL1,
                                `Resto: ${numberFormat(restNodeL1,0)} KWh (${numberFormat(restNodeL1*100/totalEnergy)} %)`,
                                restNodeL1 + 1,
                                `Level ${currentLvl+1}`,
                                `Resto: ${numberFormat(restNodeL1,0)} KWh (${numberFormat(restNodeL1*100/totalEnergy)} %)`
                            ]);
                        }
                        a.analyzers_stats.map((b, ii) => {
                            currentLvl = 2;
                            const restOperation2 = typeof b.rest_operation === "undefined" ?
                                restOperation1 : (b.rest_operation && b.rest_operation === 1 ? true :
                                false);
                            const nameNodeL2 =
                                `${b.name?b.name:b.nombre}: ${numberFormat(typeof b.total_energia_activa!=="undefined"?b.total_energia_activa:b.energia_activa, 0)} KWh (${numberFormat(b.porcentaje_energia_activa)} %)`;
                            sankeyRows.push([
                                nameNodeL1,
                                nameNodeL2,
                                (typeof b.total_energia_activa !== "undefined" ? b
                                    .total_energia_activa : b.energia_activa) + 1,
                                `Level ${currentLvl}`,
                                nameNodeL2
                            ]);
                            //Level #3
                            if (!b.analyzers_stats) return;
                            let subtotalNodeL2 = 0;
                            b.analyzers_stats.map(r1 => subtotalNodeL2 += (typeof r1
                                .total_energia_activa !== "undefined" ? r1.total_energia_activa : r1
                                .energia_activa));
                            //b.analyzers_stats.map(r1 => subtotalNodeL2 += (typeof r1.energia_activa!=="undefined"?r1.energia_activa:0));
                            const restNodeL2 = (typeof b.total_energia_activa !== "undefined" ? b
                                .total_energia_activa : b.energia_activa) - subtotalNodeL2;
                            //const restNodeL2 = (typeof b.total_energia_activa!=="undefined"?b.total_energia_activa:0)-subtotalNodeL2;
                            if (restOperation2 && restNodeL2 > 0) {
                                sankeyRows.push([
                                    nameNodeL2,
                                    `Resto: ${numberFormat(restNodeL2,0)} KWh (${numberFormat(restNodeL2*100/totalEnergy)} %)`,
                                    restNodeL2 + 1,
                                    `Level ${currentLvl+1}`,
                                    `Resto: ${numberFormat(restNodeL2,0)} KWh (${numberFormat(restNodeL2*100/totalEnergy)} %)`
                                ]);
                            }
                            b.analyzers_stats.map((c, iii) => {
                                currentLvl = 3;
                                const restOperation3 = typeof c.rest_operation === "undefined" ?
                                    restOperation2 : (c.rest_operation && c.rest_operation === 1 ?
                                        true : false);
                                const nameNodeL3 =
                                    `${c.name?c.name:c.nombre}: ${numberFormat(typeof c.total_energia_activa!=="undefined"?c.total_energia_activa:c.energia_activa, 0)} KWh (${numberFormat(c.porcentaje_energia_activa)} %)`;
                                sankeyRows.push([
                                    nameNodeL2,
                                    nameNodeL3,
                                    (typeof c.total_energia_activa !== "undefined" ? c
                                        .total_energia_activa : c.energia_activa) + 1,
                                    `Level ${currentLvl}`,
                                    nameNodeL3
                                ]);
                                //Level #4
                                if (!c.analyzers_stats) return;
                                let subtotalNodeL3 = 0;
                                c.analyzers_stats.map(r1 => subtotalNodeL3 += (typeof r1
                                    .total_energia_activa !== "undefined" ? r1
                                    .total_energia_activa : r1.energia_activa));
                                //c.analyzers_stats.map(r1 => subtotalNodeL3 += (typeof r1.energia_activa!=="undefined"?r1.energia_activa:0));
                                const restNodeL3 = (typeof c.total_energia_activa !== "undefined" ?
                                    c.total_energia_activa : c.energia_activa) - subtotalNodeL3;
                                //const restNodeL3 = (typeof c.total_energia_activa!=="undefined"?c.total_energia_activa:0)-subtotalNodeL3;
                                if (restOperation3 && restNodeL3 > 0) {
                                    sankeyRows.push([
                                        nameNodeL3,
                                        `Resto: ${numberFormat(restNodeL3,0)} KWh (${numberFormat(restNodeL3*100/totalEnergy)} %)`,
                                        restNodeL3 + 1,
                                        `Level ${currentLvl+1}`,
                                        `Resto: ${numberFormat(restNodeL3,0)} KWh (${numberFormat(restNodeL3*100/totalEnergy)} %)`
                                    ]);
                                }
                                c.analyzers_stats.map((d, iv) => {
                                    currentLvl = 4;
                                    const restOperation4 = typeof d.rest_operation ===
                                        "undefined" ? restOperation3 : (d.rest_operation &&
                                            d.rest_operation === 1 ? true : false);
                                    const nameNodeL4 =
                                        `${d.name?d.name:d.nombre}: ${numberFormat(typeof d.total_energia_activa!=="undefined"?d.total_energia_activa:d.energia_activa,0)} KWh (${numberFormat(d.porcentaje_energia_activa)} %)`;
                                    sankeyRows.push([
                                        nameNodeL3,
                                        nameNodeL4,
                                        (typeof d.total_energia_activa !==
                                            "undefined" ? d.total_energia_activa : d
                                            .energia_activa) + 1,
                                        `Level ${currentLvl}`,
                                        nameNodeL4
                                    ]);
                                    //Level #5
                                    if (!d.analyzers_stats) return;
                                    let subtotalNodeL4 = 0;
                                    d.analyzers_stats.map(r1 => subtotalNodeL4 += (typeof r1
                                        .total_energia_activa !== "undefined" ? r1
                                        .total_energia_activa : r1.energia_activa));
                                    //d.analyzers_stats.map(r1 => subtotalNodeL4 += (typeof r1.energia_activa!=="undefined"?r1.energia_activa:0));
                                    const restNodeL4 = (typeof d.total_energia_activa !==
                                        "undefined" ? d.total_energia_activa : d
                                        .energia_activa) - subtotalNodeL4;
                                    //const restNodeL4 = (typeof d.total_energia_activa!=="undefined"?d.total_energia_activa:0)-subtotalNodeL4;
                                    if (restOperation4 && restNodeL4 > 0) {
                                        sankeyRows.push([
                                            nameNodeL4,
                                            `Resto: ${numberFormat(restNodeL4,0)} KWh (${numberFormat(restNodeL4*100/totalEnergy)} %)`,
                                            restNodeL4 + 1,
                                            `Level ${currentLvl+1}`,
                                            `Resto: ${numberFormat(restNodeL4,0)} KWh (${numberFormat(restNodeL4*100/totalEnergy)} %)`
                                        ]);
                                    }
                                    d.analyzers_stats.map((e, v) => {
                                        currentLvl = 5;
                                        const restOperation5 = typeof e
                                            .rest_operation === "undefined" ?
                                            restOperation4 : (e.rest_operation && e
                                                .rest_operation === 1 ? true : false
                                                );
                                        const nameNodeL5 =
                                            `${e.name?e.name:e.nombre}: ${numberFormat(typeof e.total_energia_activa!=="undefined"?e.total_energia_activa:e.energia_activa,0)} KWh (${numberFormat(e.porcentaje_energia_activa)} %)`;
                                        sankeyRows.push([
                                            nameNodeL4,
                                            nameNodeL5,
                                            (typeof e
                                                .total_energia_activa !==
                                                "undefined" ? e
                                                .total_energia_activa : e
                                                .energia_activa) + 1,
                                            `Level ${currentLvl}`,
                                            nameNodeL5
                                        ]);
                                        //Level #6
                                        if (!e.analyzers_stats) return;
                                        let subtotalNodeL5 = 0;
                                        e.analyzers_stats.map(r1 =>
                                            subtotalNodeL5 += (typeof r1
                                                .total_energia_activa !==
                                                "undefined" ? r1
                                                .total_energia_activa : r1
                                                .energia_activa));
                                        //e.analyzers_stats.map(r1 => subtotalNodeL5 += (typeof r1.energia_activa!=="undefined"?r1.energia_activa:0));
                                        const restNodeL5 = (typeof e
                                            .total_energia_activa !==
                                            "undefined" ? e
                                            .total_energia_activa : e
                                            .energia_activa) - subtotalNodeL5;
                                        //const restNodeL5 = (typeof e.total_energia_activa!=="undefined"?e.total_energia_activa:0)-subtotalNodeL5;
                                        if (restOperation5 && restNodeL5 > 0) {
                                            sankeyRows.push([
                                                nameNodeL5,
                                                `Resto: ${numberFormat(restNodeL5,0)} KWh (${numberFormat(restNodeL5*100/totalEnergy)} %)`,
                                                restNodeL5 + 1,
                                                `Level ${currentLvl+1}`,
                                                `Resto: ${numberFormat(restNodeL5,0)} KWh (${numberFormat(restNodeL5*100/totalEnergy)} %)`
                                            ]);
                                        }
                                        e.analyzers_stats.map((f, vi) => {
                                            currentLvl = 6;
                                            const restOperation6 = typeof f
                                                .rest_operation ===
                                                "undefined" ?
                                                restOperation5 : (f
                                                    .rest_operation && f
                                                    .rest_operation === 1 ?
                                                    true : false);
                                            const nameNodeL6 =
                                                `${f.name?f.name:f.nombre}: ${numberFormat(typeof f.total_energia_activa!=="undefined"?f.total_energia_activa:f.energia_activa,0)} KWh (${numberFormat(f.porcentaje_energia_activa)} %)`;
                                            sankeyRows.push([
                                                nameNodeL5,
                                                nameNodeL6,
                                                (typeof f
                                                    .total_energia_activa !==
                                                    "undefined" ? f
                                                    .total_energia_activa :
                                                    f.energia_activa
                                                    ) + 1,
                                                `Level ${currentLvl}`,
                                                nameNodeL6
                                            ]);
                                        });
                                    });
                                });
                            });
                        });
                    });
                }
            }
            return sankeyRows;
        }

        function drawSankey() {
            var cntSankey = $(".data-sankey");

            if (cntSankey.length <= 0) return

            var totalF = $("input[name='totalGroupsEnergyF']");
            var total = $("input[name='totalGroupsEnergy']");
            var totalNode = "Energía Total: " + totalF.val();
            var totalWidth = parseFloat(total.val());
            var rows = [];
            var meterSankey = $(".meter-data-sankey").find("input[name^='meterdata']");
            for (var i = 0; i < meterSankey.length; i++) {
                var val = $(meterSankey[i]).val().split('^');

                rows.push([totalNode, val[2], parseFloat(val[1]) + 1, 'Contador', val[2]]);

                var parentNode = val[2];
                var groups = $(meterSankey[i]).parent().find("input[name^='groupdata']");
                if ($(groups).val() != '') {
                    var gval = $(groups).val().split('^');

                    rows.push([parentNode, gval[2], parseFloat(gval[1]) + 1, 'Patito 3', gval[2]]);

                    parentNode = gval[2];
                }
                var aNames = $(meterSankey[i]).parent().find(".data-sankey-in-meter input[name^='eNameA']");
                var aValues = $(meterSankey[i]).parent().find(".data-sankey-in-meter input[name^='eValueA']");
                for (var j = 0; j < aNames.length; j++) {
                    var aName = $(aNames[j]);
                    var aValue = $(aValues[j]);
                    var aNodeValue = parseFloat(aValue.val());
                    var aNodeName = aName.val();

                    rows.push([parentNode, aNodeName, aNodeValue + 1, 'Patito 3', aNodeName]);
                }
            }

            for (var i = 0; i < cntSankey.length; i++) {
                var cnt = $(cntSankey[i]);
                var name = cnt.find("input[name^='eNameG']");
                var nodeName = name.val();
                var wdata = cnt.find("input[name^='eValueG']");
                var nodeWidth = parseFloat(wdata.val());

                var kindNodeName = "example-Gas-" + nodeName;
                //rows.push([totalNode, kindNodeName, nodeWidth+1, 'Patito Patito' ,kindNodeName]);
                //rows.push([kindNodeName, nodeName, nodeWidth+1, 'Patito Patito' ,nodeName]);
                rows.push([totalNode, nodeName, nodeWidth + 1, 'Patito Patito', nodeName]);

                var aNames = cnt.find("input[name^='eNameA']");
                var aValues = cnt.find("input[name^='eValueA']");
                for (var j = 0; j < aNames.length; j++) {
                    var aName = $(aNames[j]);
                    var aValue = $(aValues[j]);
                    var aNodeValue = parseFloat(aValue.val());
                    var aNodeName = aName.val();
                    rows.push([nodeName, aNodeName, aNodeValue + 1, 'Patito 3', aNodeName]);
                }
            }

						//Se toma la altura del ultimo grupo
						var cnt = $(cntSankey[cntSankey.length -1 ]);
						var heightSankey = cnt.find("input[name^='eHeightG']").val();
                        console.log(heightSankey);
						//heightSankey = 1000

            var composeRows = calcTotalAnalyzers();
            console.log("rows: ", rows);
            console.log("composeRows: ", composeRows);
            if (rows.length <= 0) return

            const sankeyData = new google.visualization.DataTable()
            sankeyData.addColumn('string', 'From')
            sankeyData.addColumn('string', 'To')
            sankeyData.addColumn('number', '')
            sankeyData.addColumn({
                type: 'string',
                role: 'annotation'
            });
            sankeyData.addColumn({
                'type': 'string',
                'role': 'tooltip',
                'p': {
                    'html': true
                }
            })
            sankeyData.addRows(composeRows)

            const sankeyContainer = document.querySelector("#sankey_plot")
            const chart = new google.visualization.Sankey(sankeyContainer)
            const sankeyResizeObserver = new ResizeObserver((entry) => {
                if (entry[0].contentRect.width === 0) return
								
                const sankeyWidth = entry[0].contentRect.width * 0.9 > 540 ? entry[0].contentRect.width * 0.9 : 540
                const sankeyHeight = entry[0].contentRect.height > heightSankey ? entry[0].contentRect.height : heightSankey
                const sankeyOptions = {
                    width: sankeyWidth,
                    height: sankeyHeight,
                    sankey: {
                        node: {
                            nodePadding: 25,
                            // label: {
                            // 	fontSize: 12
                            // }
                        }
                    }
                }
                chart.draw(sankeyData, sankeyOptions);
                //chart.nodeWidth(100);
            })
            sankeyResizeObserver.observe(sankeyContainer)
        }
    </script>
@endsection
