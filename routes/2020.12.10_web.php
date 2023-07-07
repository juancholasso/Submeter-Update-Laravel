<?php

use Illuminate\Support\Facades\Route;

Route::get('email', 'EmailController@sendEmail');
Route::get('/', ['as' => 'default.index', 'uses' => 'HomeController@index']);

Route::get('reinicio_password', ['as' => 'password.reinicio', 'uses' => 'HomeController@ReinicioPassword']);

Route::post('home', ['as' => 'home.index', 'uses' => 'HomeController@index']);

// Link para usuario registrado
Route::get('usuario-registrado', ['as' => 'registro.usuario', 'uses' => 'UsuarioRegistradoController@usuarioRegistrado']);
Route::post('save-registrado', ['as' => 'save.registro', 'uses' => 'UsuarioRegistradoController@saveRegistro']);
Route::get('solicitud_registro',['as' => 'solicitud.registro', 'uses' => 'UsuarioRegistradoController@solicitarRegistro']);
Route::post('send_solicitud',['as' => 'send.solicitud.registro', 'uses' => 'UsuarioRegistradoController@enviarSolicitudRegistro']);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('administrar_usuarios/{id}/{id2}', ['as' => 'admin.users', 'uses' => 'UserController@AdministrarUsuarios'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('empresa', 'EmpresasController@list')->name("enterprise.index")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('empresa/crear', 'EmpresasController@create')->name("enterprise.create")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('empresa/users/{enterprise_id}', 'EmpresasController@getUsers')->name("enterprise.users")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('empresa/{enterprise_id}', 'EmpresasController@edit')->name("enterprise.edit")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('empresa', 'EmpresasController@save')->name("enterprise.save")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::patch('empresa/{enterprise_id}', 'EmpresasController@update')->name("enterprise.update")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('empresa/{enterprise_id}', 'EmpresasController@delete')->name("enterprise.delete")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('usuario', 'UserEnterpriseController@list')->name("user.index")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('usuario/{user_id}', 'UserEnterpriseController@show')->name("user.show")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('usuario', 'UserEnterpriseController@save')->name("user.save")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::patch('usuario/{user_id}', 'UserEnterpriseController@update')->name("user.update")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('usuario/{user_id}', 'UserEnterpriseController@delete')->name("user.delete")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('contador', 'EnergyMeterController@list')->name("energymeter.index")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('contador/combo', 'EnergyMeterController@listCombo')->name("energymeter.indexcombo")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('contador/cambiar/{user_id}/{meter_id}', 'EnergyMeterController@changeCurrentMeter')->name("energymeter.change")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('contador/{meter_id}', 'EnergyMeterController@show')->name("energymeter.show")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('contador', 'EnergyMeterController@save')->name("energymeter.save")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::patch('contador/{meter_id}', 'EnergyMeterController@update')->name("energymeter.update")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('contador/{meter_id}', 'EnergyMeterController@delete')->name("energymeter.delete")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('analizador', 'AnalyzerController@list')->name("analyzer.index")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizador/combo', 'AnalyzerController@listComboAnalyzer')->name("analyzer.indexcombo")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizador/list', 'AnalyzerController@getAnalyzersList')->name("analyzer.list")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizador/{analyzer_id}', 'AnalyzerController@show')->name("analyzer.show")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('analizador', 'AnalyzerController@save')->name("analyzer.save")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::patch('analizador/{analyzer_id}', 'AnalyzerController@update')->name("analyzer.update")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('analizador/{analyzer_id}', 'AnalyzerController@delete')->name("analyzer.delete")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('analyzergroup/list', 'AnalyzerGroupController@getAnalyzersGroupList')->name("analyzergroup.list")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@show')->name("analyzergroup.show")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('analyzergroup', 'AnalyzerGroupController@save')->name("analyzergroup.save")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::patch('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@update')->name("analyzergroup.update")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@delete')->name("analyzergroup.delete")->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión


Route::post('eliminar_usuario',['as' => 'eliminar.user.list', 'uses' => 'UserController@EliminarUsuarioLista'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('registrar-admin', ['as' => 'create.users', 'uses' => 'UserController@create'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('registrar-cliente', ['as' => 'create.cliente', 'uses' => 'UserController@createClient'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('registrar-admin', ['as' => 'store.users', 'uses' => 'UserController@store'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('editar-user/{id}', 'UserController@edit')->name('edit.user')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('update-user/{id}', 'UserController@update')->name('update.user')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO BORRAR CONTADORES Y ANALIZADORES
Route::post('eliminar_contador',['as' => 'eliminar.contador.ajax', 'uses' => 'UserController@EliminarContador'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('eliminar_analizador',['as' => 'eliminar.analizador.ajax', 'uses' => 'UserController@EliminarAnalizador'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//MODULO ENERGÍA Y POTENCIA
Route::get('resumen_energia_potencia/{id}',['as' => 'resumen.energia.potencia', 'uses' => 'ResumenEnergiaController@ResumenEnergiaPotencia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('config_interval',['as' => 'config.interval', 'uses' => 'UserController@ConfigurarIntervalo'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('navigation',['as' => 'config.navigation', 'uses' => 'UserController@ConfigurarIntervaloNavegacion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('subperiod',['as' => 'config.subperiodo', 'uses' => 'UserController@cargarSubPeriodo'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//MODULO CONTADORES
Route::get('ver_panel_usuario/{id}/{ctrl}',['as' => 'ver.panel.user', 'uses' => 'ResumenContadoresController@VerPanelUser'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('contadores_pdf/{id}',['as' => 'contadores.pdf', 'uses' => 'UserController@ContadoresPdf'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MÓDULO CONSUMO DE ENERGÍA
Route::get('consumo_energia/{id}',['as' => 'consumo.energia', 'uses' => 'ConsumoEnergiaController@ConsumoEnergia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_energia',['as' => 'export.csv.energia', 'uses' => 'ConsumoEnergiaController@exportCSVConsumoEnergia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_generacion',['as' => 'export.csv.generacion', 'uses' => 'ConsumoEnergiaController@exportCSVGeneracion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_balance',['as' => 'export.csv.balance', 'uses' => 'ConsumoEnergiaController@exportCSVBalance'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//MODULO ANALISIS DE POTENCIA
Route::get('analisis_potencia/{id}',['as' => 'analisis.potencia', 'uses' => 'UserController@AnalisisPotencia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analisis_potencia_optimizacion/{id}',['as' => 'analisis.potencia.optimizacion', 'uses' => 'AnalisisPotencia@Optimizacion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('analisis_potencia_optima',['as' => 'analisis.potencia.optima', 'uses' => 'UserController@OptimizarPotencia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analisis_potencia_2/{id}',['as' => 'analisis.potencia2', 'uses' => 'AnalisisPotencia@AnalisisPotencia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analisis_potencia_envio_email',['as' => 'analisis_potencia_envio_email', 'uses' => 'UserController@EnvioEmailOptimizacion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión


//MODULO SIMULACION POTENCIA
Route::get('simulacion_potencia/{id}',['as' => 'simulacion.potencia', 'uses' => 'SimulacionPotencia@SimulacionPotencia'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('simulacion_potencia',['as' => 'simulacion.potencia.save', 'uses' => 'SimulacionPotencia@GuardarValoresSimulacion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//MODULO MERCADO ENERGETICO
Route::get('mercado_energetico/{id}',['as' => 'mercado.energetico', 'uses' => 'MercadoEnergetico@MercadoEnergetico'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_mercado',['as' => 'export.csv.mercado', 'uses' => 'MercadoEnergetico@exportCSVMercado'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO COMPARARDOR DE OFERTAS
Route::get('comparador_ofertas/{id}',['as' => 'comparador.ofertas', 'uses' => 'UserController@ComparadorOfertas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('comparador_ofertas/{id}',['as' => 'calculo.comparador.ofertas', 'uses' => 'UserController@CalculoComparadorOfertas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('comparador_ofertas_pdf/{id}',['as' => 'comparador.ofertas.pdf', 'uses' => 'UserController@ComparadorOfertasPdf'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('vista_comparador_ofertas/{id}',['as' => 'vista.comparador.ofertas', 'uses' => 'UserController@ExportarComparacionOfertas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO EMISIONES CO2
Route::get('emisiones_co2/{id}',['as' => 'emisiones.co2', 'uses' => 'EmisionesCO2Controller@EmisionesCO2'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_co2',['as' => 'export.csv.co2', 'uses' => 'EmisionesCO2Controller@exportCSVCo2'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO SIMULACIÓN DE FACTURAS
Route::get('simulacion_facturas/{id}',['as' => 'simulacion.facturas', 'uses' => 'SimulacionFacturasController@SimulacionFactura'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('grafica_analisis_potencia/{data}', 'AnalisisPotencia@graficaAnalisis')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('simulacion_facturas_pdf/{id}',['as' => 'simulacion.facturas.pdf', 'uses' => 'SimulacionFacturasController@SimulacionFacturaPdf'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO DE INFORMES PERIÓDICOS Y ALERTAS
Route::get('informes_alertas/{id}',['as' => 'informes.periodicos.alertas', 'uses' => 'UserController@InformesPeriodicosAlertas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
//Route::post('informes', ['as' => 'informes.programados', 'uses' => 'UserController@InformesProgramados'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('informes/{id}', ['as' => 'informes.programados', 'uses' => 'UserController@InformesProgramados'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('alertas/{id}', ['as' => 'alertas.programadas', 'uses' => 'UserController@AlertasProgramadas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
//ruta nueva para nuevos informes
Route::post('informes_analizadores/{id}', ['as' => 'informes.analizadores.programados', 'uses' => 'UserController@AnalizadoresInformesProgramados'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::post('pdf/exportacion', ['as' => 'exportacion.pdf', 'uses'=>'PDFController@createPDFTemplate'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// MODULO EXPORTAR DATOS
Route::get('exportar_datos/{id}',['as' => 'exportar.datos', 'uses' => 'UserController@ExportarDatos'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar',['as' => 'get.export', 'uses' => 'UserController@GetExportar'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// URL de prueba
//Route::get('prueba', 'UserController@prueba')->name('prueba');
//Route::get('prueba2', 'UserController@prueba2')->name('prueba2');
//Route::get('prueba3', 'UserController@prueba3')->name('prueba3');

// Perfil de usuarios
Route::get('perfil', 'UserController@perfilForm')->name('perfil.form')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('perfil/{id}', 'UserController@storePerfil')->name('store.perfil')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

//Grupos
Route::get('group/{group}', 'GroupsController@getGroup')->name('group.get')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('groups', 'GroupsController@getGroups')->name('groups.get')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('groups', 'GroupsController@storeGroup')->name('groups.store')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete("groups", 'GroupsController@deleteGroup')->name('groups.delete')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

//MODULO AREA CLIENTE
Route::get('area-cliente/{id}', 'UserController@areaCliente')->name('area.cliente')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('area-cliente/{id}', 'UserController@storeAreaCliente')->name('store.area.cliente')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('delete-conditions', 'UserController@deleteConditions')->name('delete.conditions')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión

// ANALIZADORES SUBMETERING
Route::get('analizadores/grupos/{id}',['as' => 'analyzersgroup', 'uses' => 'AnalyzerController@showAnalyzers'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizadores/grupos/{id}/{group_id}',['as' => 'analyzersgroupselected', 'uses' => 'AnalyzerController@showAnalyzersSelected'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizadores/{id}',['as' => 'analizadores', 'uses' => 'UserController@Analizadores'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('analizadores_potencia_corrientes/{user_id}/{group_id}/{id}',['as' => 'analizadores.graficas', 'uses' => 'UserController@AnalizadoresGraficas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('exportar_csv_analizador',['as' => 'export.csv.analizador', 'uses' => 'UserController@exportCSVAnalizador'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('analizadores_informes_alertas/{id}/{user_id}',['as' => 'analizadores.informes.alertas', 'uses' => 'UserController@AnalizadoresInformesAlertasProgramados'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

Route::get('new_analizadores/{id}',['as' => 'new_analizadores', 'uses' => 'AnalyzerController@showAnalyzers'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

// PRODUCCIÓN SUBMETERING
//Route::get('produccion/databases',['as' => 'production.databases', 'uses' => 'ProductionController@getDatabases'])->middleware('auth'); //# @Leo W* obtener las BD disponibles en el host de un contador 
Route::get('produccion/counters/{enterprise}/',['as' => 'production.counters', 'uses' => 'ProductionController@getCounters'])->middleware('auth'); //# @Leo W* obtener los contadores asociados a una empresa
Route::get('produccion/connections/{counter}/',['as' => 'production.connection', 'uses' => 'ProductionController@getConnections'])->middleware('auth'); //# @Leo W* obtener las conexiones de contador
Route::get('produccion/tables/{counter}/{id}',['as' => 'production.database_counter', 'uses' => 'ProductionController@getCounterTables'])->middleware('auth'); //# @Leo W* obtener las BD de produccion habilitadas para un contador


Route::get('produccion/visualizar/{id}',['as' => 'production.data', 'uses' => 'ProductionController@showProductionData'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('produccion/create',['as' => 'production.create', 'uses' => 'ProductionController@create'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('produccion',['as' => 'production.list', 'uses' => 'ProductionController@list'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('produccion/database',['as' => 'production.database', 'uses' => 'ProductionController@readDataBase'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('produccion/edit/{production_id}',['as' => 'production.edit', 'uses' => 'ProductionController@show'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('produccion/{id}',['as' => 'produccion', 'uses' => 'UserController@Produccion'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('produccion/csv',['as' => 'production.csv', 'uses' => 'ProductionController@exportCSV'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('produccion/',['as' => 'production.save', 'uses' => 'ProductionController@saveProduction'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('produccion/{production_id}',['as' => 'production.update', 'uses' => 'ProductionController@updateProduction'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('connection/connectionMeters', 'NewConnectionsController@saveConnection')->name('production.ConnectionSave')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::delete('produccion/{id}',['as' => 'production.delete', 'uses' => 'ProductionController@removeProduction'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión


// IDENTIFICADORES
Route::get('identificadores/{id}',['as' => 'identificadores', 'uses' => 'UserController@Identificadores'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//SEGUIMIENTO DE CONSUMO
Route::get('seguimiento_objetivos/{id}',['as' => 'seguimiento.objetivos', 'uses' => 'SeguimientoObjetivosController@Seguimiento'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('seguimiento_objetivos/{id}/{counter_id}',['as' => 'seguimiento.objetivos.count', 'uses' => 'SeguimientoObjetivosController@SeguimientoCounter'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('seguimiento_objetivos',['as' => 'seguimiento.objetivos.change', 'uses' => 'SeguimientoObjetivosController@SeguimientoCambiarFechas'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::post('seguimiento_objetivos/period',['as' => 'seguimiento.objetivos.period', 'uses' => 'SeguimientoObjetivosController@SeguimientoCambiarPeriodo'])->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión //[Rogelio R - Workana] Se agrega la validación de la sesión

//PASSWORD RESET
Route::post("cambio_password", ['as'=>'cambio.password', 'uses'=>'LoginController@sendResetPassword']);
Route::post("reset_password", ['as'=>'reset.password.login', 'uses'=>'LoginController@resetPassword']);

//[Rogelio R - Workana] - Menú de Log de Accesos
Route::get('logaccesos/{id}', 'AccessLogController@getAccessLog')->name('logaccesos')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
Route::get('logaccesos-csv/{id}', 'AccessLogController@getAccessLogCsv')->name('logaccesos.export.csv')->middleware('auth'); //[Rogelio R - Workana] Se agrega la validación de la sesión
