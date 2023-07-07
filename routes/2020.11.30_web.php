<?php
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

Route::get('/home', 'HomeController@index')->name('home');
Route::get('administrar_usuarios/{id}/{id2}', ['as' => 'admin.users', 'uses' => 'UserController@AdministrarUsuarios']);

Route::get('empresa', 'EmpresasController@list')->name("enterprise.index");
Route::get('empresa/crear', 'EmpresasController@create')->name("enterprise.create");
Route::get('empresa/users/{enterprise_id}', 'EmpresasController@getUsers')->name("enterprise.users");
Route::get('empresa/{enterprise_id}', 'EmpresasController@edit')->name("enterprise.edit");
Route::post('empresa', 'EmpresasController@save')->name("enterprise.save");
Route::patch('empresa/{enterprise_id}', 'EmpresasController@update')->name("enterprise.update");
Route::delete('empresa/{enterprise_id}', 'EmpresasController@delete')->name("enterprise.delete");

Route::get('usuario', 'UserEnterpriseController@list')->name("user.index");
Route::get('usuario/{user_id}', 'UserEnterpriseController@show')->name("user.show");
Route::post('usuario', 'UserEnterpriseController@save')->name("user.save");
Route::patch('usuario/{user_id}', 'UserEnterpriseController@update')->name("user.update");
Route::delete('usuario/{user_id}', 'UserEnterpriseController@delete')->name("user.delete");

Route::get('contador', 'EnergyMeterController@list')->name("energymeter.index");
Route::get('contador/combo', 'EnergyMeterController@listCombo')->name("energymeter.indexcombo");
Route::get('contador/cambiar/{user_id}/{meter_id}', 'EnergyMeterController@changeCurrentMeter')->name("energymeter.change");
Route::get('contador/{meter_id}', 'EnergyMeterController@show')->name("energymeter.show");
Route::post('contador', 'EnergyMeterController@save')->name("energymeter.save");
Route::patch('contador/{meter_id}', 'EnergyMeterController@update')->name("energymeter.update");
Route::delete('contador/{meter_id}', 'EnergyMeterController@delete')->name("energymeter.delete");

Route::get('analizador', 'AnalyzerController@list')->name("analyzer.index");
Route::get('analizador/list', 'AnalyzerController@getAnalyzersList')->name("analyzer.list");
Route::get('analizador/{analyzer_id}', 'AnalyzerController@show')->name("analyzer.show");
Route::post('analizador', 'AnalyzerController@save')->name("analyzer.save");
Route::patch('analizador/{analyzer_id}', 'AnalyzerController@update')->name("analyzer.update");
Route::delete('analizador/{analyzer_id}', 'AnalyzerController@delete')->name("analyzer.delete");

Route::get('analyzergroup/list', 'AnalyzerGroupController@getAnalyzersGroupList')->name("analyzergroup.list");
Route::get('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@show')->name("analyzergroup.show");
Route::post('analyzergroup', 'AnalyzerGroupController@save')->name("analyzergroup.save");
Route::patch('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@update')->name("analyzergroup.update");
Route::delete('analyzergroup/{analyzer_group_id}', 'AnalyzerGroupController@delete')->name("analyzergroup.delete");


Route::post('eliminar_usuario',['as' => 'eliminar.user.list', 'uses' => 'UserController@EliminarUsuarioLista']);
Route::get('registrar-admin', ['as' => 'create.users', 'uses' => 'UserController@create']);
Route::get('registrar-cliente', ['as' => 'create.cliente', 'uses' => 'UserController@createClient']);
Route::post('registrar-admin', ['as' => 'store.users', 'uses' => 'UserController@store']);
Route::get('editar-user/{id}', 'UserController@edit')->name('edit.user');
Route::post('update-user/{id}', 'UserController@update')->name('update.user');

// MODULO BORRAR CONTADORES Y ANALIZADORES
Route::post('eliminar_contador',['as' => 'eliminar.contador.ajax', 'uses' => 'UserController@EliminarContador']);
Route::post('eliminar_analizador',['as' => 'eliminar.analizador.ajax', 'uses' => 'UserController@EliminarAnalizador']);

//MODULO ENERGÍA Y POTENCIA
Route::get('resumen_energia_potencia/{id}',['as' => 'resumen.energia.potencia', 'uses' => 'ResumenEnergiaController@ResumenEnergiaPotencia']);
Route::post('config_interval',['as' => 'config.interval', 'uses' => 'UserController@ConfigurarIntervalo']);
Route::post('navigation',['as' => 'config.navigation', 'uses' => 'UserController@ConfigurarIntervaloNavegacion']);
Route::post('subperiod',['as' => 'config.subperiodo', 'uses' => 'UserController@cargarSubPeriodo']);

//MODULO CONTADORES
Route::get('ver_panel_usuario/{id}/{ctrl}',['as' => 'ver.panel.user', 'uses' => 'UserController@VerPanelUser']);
Route::get('contadores_pdf/{id}',['as' => 'contadores.pdf', 'uses' => 'UserController@ContadoresPdf']);

// MÓDULO CONSUMO DE ENERGÍA
Route::get('consumo_energia/{id}',['as' => 'consumo.energia', 'uses' => 'ConsumoEnergiaController@ConsumoEnergia']);

//MODULO ANALISIS DE POTENCIA
Route::get('analisis_potencia/{id}',['as' => 'analisis.potencia', 'uses' => 'UserController@AnalisisPotencia']);
Route::get('analisis_potencia_optimizacion/{id}',['as' => 'analisis.potencia.optimizacion', 'uses' => 'AnalisisPotencia@Optimizacion']);
Route::post('analisis_potencia_optima',['as' => 'analisis.potencia.optima', 'uses' => 'UserController@OptimizarPotencia']);
Route::get('analisis_potencia_2/{id}',['as' => 'analisis.potencia2', 'uses' => 'AnalisisPotencia@AnalisisPotencia']);
Route::get('analisis_potencia_envio_email',['as' => 'analisis_potencia_envio_email', 'uses' => 'UserController@EnvioEmailOptimizacion']);


//MODULO SIMULACION POTENCIA
Route::get('simulacion_potencia/{id}',['as' => 'simulacion.potencia', 'uses' => 'SimulacionPotencia@SimulacionPotencia']);
Route::post('simulacion_potencia',['as' => 'simulacion.potencia.save', 'uses' => 'SimulacionPotencia@GuardarValoresSimulacion']);

//MODULO MERCADO ENERGETICO
Route::get('mercado_energetico/{id}',['as' => 'mercado.energetico', 'uses' => 'MercadoEnergetico@MercadoEnergetico']);

// MODULO COMPARARDOR DE OFERTAS
Route::get('comparador_ofertas/{id}',['as' => 'comparador.ofertas', 'uses' => 'UserController@ComparadorOfertas']);
Route::post('comparador_ofertas/{id}',['as' => 'calculo.comparador.ofertas', 'uses' => 'UserController@CalculoComparadorOfertas']);
Route::get('comparador_ofertas_pdf/{id}',['as' => 'comparador.ofertas.pdf', 'uses' => 'UserController@ComparadorOfertasPdf']);
Route::get('vista_comparador_ofertas/{id}',['as' => 'vista.comparador.ofertas', 'uses' => 'UserController@ExportarComparacionOfertas']);

// MODULO EMISIONES CO2
Route::get('emisiones_co2/{id}',['as' => 'emisiones.co2', 'uses' => 'EmisionesCO2Controller@EmisionesCO2']);

// MODULO SIMULACIÓN DE FACTURAS
Route::get('simulacion_facturas/{id}',['as' => 'simulacion.facturas', 'uses' => 'SimulacionFacturasController@SimulacionFactura']);
Route::get('grafica_analisis_potencia/{data}', 'AnalisisPotencia@graficaAnalisis');
Route::get('simulacion_facturas_pdf/{id}',['as' => 'simulacion.facturas.pdf', 'uses' => 'SimulacionFacturasController@SimulacionFacturaPdf']);

// MODULO DE INFORMES PERIÓDICOS Y ALERTAS
Route::get('informes_alertas/{id}',['as' => 'informes.periodicos.alertas', 'uses' => 'UserController@InformesPeriodicosAlertas']);
//Route::post('informes', ['as' => 'informes.programados', 'uses' => 'UserController@InformesProgramados']);
Route::post('informes/{id}', ['as' => 'informes.programados', 'uses' => 'UserController@InformesProgramados']);
Route::post('alertas/{id}', ['as' => 'alertas.programadas', 'uses' => 'UserController@AlertasProgramadas']);

Route::post('pdf/exportacion', ['as' => 'exportacion.pdf', 'uses'=>'PDFController@createPDFTemplate']);

// MODULO EXPORTAR DATOS
Route::get('exportar_datos/{id}',['as' => 'exportar.datos', 'uses' => 'UserController@ExportarDatos']);
Route::post('exportar',['as' => 'get.export', 'uses' => 'UserController@GetExportar']);

// URL de prueba
//Route::get('prueba', 'UserController@prueba')->name('prueba');
//Route::get('prueba2', 'UserController@prueba2')->name('prueba2');
//Route::get('prueba3', 'UserController@prueba3')->name('prueba3');

// Perfil de usuarios
Route::get('perfil', 'UserController@perfilForm')->name('perfil.form');
Route::post('perfil/{id}', 'UserController@storePerfil')->name('store.perfil');

//Grupos
Route::get('group/{group}', 'GroupsController@getGroup')->name('group.get');
Route::get('groups', 'GroupsController@getGroups')->name('groups.get');
Route::post('groups', 'GroupsController@storeGroup')->name('groups.store');
Route::delete("groups", 'GroupsController@deleteGroup')->name('groups.delete');

//MODULO AREA CLIENTE
Route::get('area-cliente/{id}', 'UserController@areaCliente')->name('area.cliente');
Route::post('area-cliente/{id}', 'UserController@storeAreaCliente')->name('store.area.cliente');
Route::post('delete-conditions', 'UserController@deleteConditions')->name('delete.conditions');

// ANALIZADORES SUBMETERING
Route::get('analizadores/grupos/{id}',['as' => 'analyzersgroup', 'uses' => 'AnalyzerController@showAnalyzers']);
Route::get('analizadores/grupos/{id}/{group_id}',['as' => 'analyzersgroupselected', 'uses' => 'AnalyzerController@showAnalyzersSelected']);
Route::get('analizadores/{id}',['as' => 'analizadores', 'uses' => 'UserController@Analizadores']);
Route::get('analizadores_potencia_corrientes/{user_id}/{group_id}/{id}',['as' => 'analizadores.graficas', 'uses' => 'UserController@AnalizadoresGraficas']);
Route::post('exportar_csv_analizador',['as' => 'export.csv.analizador', 'uses' => 'UserController@exportCSVAnalizador']);

Route::get('new_analizadores/{id}',['as' => 'new_analizadores', 'uses' => 'AnalyzerController@showAnalyzers']);

// PRODUCCIÓN SUBMETERING
Route::get('produccion/{id}',['as' => 'produccion', 'uses' => 'UserController@Produccion']);

// IDENTIFICADORES
Route::get('identificadores/{id}',['as' => 'identificadores', 'uses' => 'UserController@Identificadores']);

//SEGUIMIENTO DE CONSUMO
Route::get('seguimiento_objetivos/{id}',['as' => 'seguimiento.objetivos', 'uses' => 'SeguimientoObjetivosController@Seguimiento']);
Route::get('seguimiento_objetivos/{id}/{counter_id}',['as' => 'seguimiento.objetivos.count', 'uses' => 'SeguimientoObjetivosController@SeguimientoCounter']);
Route::post('seguimiento_objetivos',['as' => 'seguimiento.objetivos.change', 'uses' => 'SeguimientoObjetivosController@SeguimientoCambiarFechas']);
Route::post('seguimiento_objetivos/period',['as' => 'seguimiento.objetivos.period', 'uses' => 'SeguimientoObjetivosController@SeguimientoCambiarPeriodo']);

//PASSWORD RESET
Route::post("cambio_password", ['as'=>'cambio.password', 'uses'=>'LoginController@sendResetPassword']);
Route::post("reset_password", ['as'=>'reset.password.login', 'uses'=>'LoginController@resetPassword']);

Route::get('/test', function () {
    $exitCode = Artisan::call('alerta_potencia:consumo');
    
    //
});