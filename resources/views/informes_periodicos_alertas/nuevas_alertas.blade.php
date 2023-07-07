@php
$frecuencia_meses = [
	["label" => "En", "value" => 0],
	["label" => "Feb", "value" => 1], 
	["label" => "Ma", "value" => 2], 
	["label" => "Ab", "value" => 3], 
	["label" => "May", "value" => 4], 
	["label" => "Jun", "value" => 5], 
	["label" => "Jul", "value" => 6], 
	["label" => "Ag", "value" => 7], 
	["label" => "Sep", "value" => 8], 
	["label" => "Oc", "value" => 9], 
	["label" => "Nov", "value" => 10], 
	["label" => "Dic", "value" => 11]
];
$frecuencia_dias = [
	["label" => "L", "value" => 0], 
	["label" => "M", "value" => 1], 
	["label" => "Mi", "value" => 2], 
	["label" => "J", "value" => 3], 
	["label" => "V", "value" => 4], 
	["label" => "S", "value" => 5], 
	["label" => "D", "value" => 6]
];
$avisos = [
	["Todos", "Todos"], 
	["1", "1"], 
	["2", "2"], 
	["3", "3"], 
	["4", "4"], 
	["5", "5"], 
	["6", "6"], 
	["7", "7"], 
	["8", "8"], 
	["9", "9"], 
	["10", "10"]
];
@endphp
<div class="row row-2">
	<h2 class="title">Programación Alertas</h2>
	{{-- INFORMES  --}}
	<div class="column">
		<form class="d-none" action="{{route('informes.periodicos.nuevas_alertas',$id)}}" method="POST" id="form-nuevas-alertas">
			{{ csrf_field() }}
		</form>
		<div class="table-container table-with-multi-select">
			<table class="table-responsive text-center" id="tblAlertas">
				<colgroup>
					<col class="shrink">
					<col class="shrink">
					<col class="shrink">
					<col class="shrink">
					<col>
				</colgroup>
				<thead>
					<tr class="row-header">
						<th>ON/OFF</th>
						<th>BBDD</th>
						<th>Frecuencia</th>
						<th>Avisos</th>
						<th>Nombre de la Alerta</th>
						<th data-column-size="Destinatarios">Destinatarios</th>
						<th>Gestión Alertas</th>
					</tr>
				</thead>
				<tbody>
					@if(count($alertas_general)>0)
						@foreach($alertas_general as $item => $alerta)
							@php
							$bdJson = !empty($alerta->conexion) ? json_decode($alerta->conexion) : [];
							$checkedHtml = '';
							if($alerta->activado){
								$checkedHtml = 'checked="checked"';
							}
							@endphp
							<tr data-id="{{$alerta->id}}">
								<td>
									<input id="cl_ckbx_{{$item}}" {{$checkedHtml}} name="activado[{{$alerta->id}}]" type="checkbox" form="form-counterlist-reports" class="checkbox" />
									<label for="cl_ckbx_{{$item}}" class="switch2"></label>
								</td>
								<td class="col-input">
									<input type="text" form="form-counterlist-reports" value="{{$bdJson->conection_name}}" disabled/>
									<input type="hidden" name="conexion[{{$alerta->id}}]" value="{{$alerta->conexion}}"/>
								</td>
								<td class="select-block">
									<div class="multiple-virtual-select multiple-virtual-select-1" id="frecuencia_mes_{{$alerta->id}}" name="frecuencia_mes[{{$alerta->id}}]" data-value="{{$alerta->frecuencia_mes}}"></div>
									<div class="multiple-virtual-select multiple-virtual-select-2" id="frecuencia_dia_{{$alerta->id}}" name="frecuencia_dia[{{$alerta->id}}]" data-value="{{$alerta->frecuencia_dia}}"></div>
								</td>
								<td>
									<select class="select-csv" form="form-counterlist-reports" name="avisos[{{$alerta->id}}]">
										@foreach($avisos as $aviso)
											@php
											$selectedHtml = '';
											if($aviso[0] === $alerta->avisos){
												$selectedHtml = 'selected="selected"';
											}
											@endphp
										<option value="{{$aviso[0]}}" {{$selectedHtml}}>{{$aviso[1]}}</option>
										@endforeach
									</select>
								</td>
								<td>
									<textarea form="form-counterlist-reports" name="nombre_alerta[{{$alerta->id}}]">{{$alerta->nombre_alerta}}</textarea>
								</td>
								<td>
									<textarea form="form-counterlist-reports" name="destinatarios[{{$alerta->id}}]" data-form-validation="email-list">{{$alerta->destinatarios}}</textarea>
								</td>
								<td>
									<button class="btn" name="btnGuardarAlerta">Guardar</button>
									<button class="btn btn-primary" name="btnEditarAlerta">Editar</button>
									<button class="btn btn-danger" name="btnBorrarAlerta">Borrar</button>
									<form class="d-none" action="{{route('informes.periodicos.actualizar_alertas',$alerta->id)}}" method="POST">
										{{ csrf_field() }}
									</form>
								</td>
							</tr>
						@endforeach
					@else
						<tr class="tr-empty">
							<td colspan="7">No se han guardado alertas aún</td>
						</tr>
					@endif
				</tbody>
				<tfoot>
					<tr>
						<td>
							<button class="btn btn-primary btn-box-modal" type="button" id="btnModalNuevo">
								<i class="fas fa-plus" aria-hidden="true"></i>
							</button>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<div class="modal fade" id="miAlerta" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fa fa-plus"></i> Selector de campo para la alerta</h5>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<form novalidate="" class="">
					<div class="form-group">
						<label class="form-label">Empresa</label>
						<div class="form-group-child">
							<select placeholder="Seleccione la empresa" name="modal_empresa" id="modal_empresa" class="form-control">
								<option value="">Seleccione la empresa</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">Contador</label>
						<div class="form-group-child">
							<select placeholder="Seleccione el contador" name="modal_contador" id="modal_contador" class="form-control">
								<option value="">Seleccione el contador</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">BBDD</label>
						<div class="form-group-child">
							<select placeholder="Seleccione la connección base de datos" name="modal_conexion" id="modal_conexion" class="form-control">
								<option value="">Seleccione la connección base de datos</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">Tabla</label>
						<div class="form-group-child">
							<select placeholder="Seleccione la tabla" name="modal_table" id="modal_table" class="form-control">
								<option value="">Seleccione la tabla</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">Campo</label>
						<div class="form-group-child">
							<select placeholder="Seleccione el campo" name="modal_field" id="modal_field" class="form-control">
								<option value="">Seleccione el campo</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">Condición</label>
						<div class="form-group-child">
							<select name="modal_condicion" id="modal_condicion" class="form-control">
								<option value="0">≥</option>
								<option value="1">≤</option>
								<option value="2">=</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label">Consigna de la alerta</label>
						<div class="form-group-child">
							<input type="number" class="form-control" name="modal_limite" id="modal_limite"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-close" data-dismiss="modal">Cerrar</button>
				<button type="button" class="btn btn-primary" id="btnInsertarNuevaLinea">Aceptar</button>
			</div>
		</div>
	</div>
</div>
<style>
	/*!
	* Virtual Select v1.0.16
	* https://sa-si-dev.github.io/virtual-select
	* Licensed under MIT (https://github.com/sa-si-dev/virtual-select/blob/master/LICENSE)
	*/
	@-webkit-keyframes vscomp-animation-spin{to{-webkit-transform:rotateZ(360deg);transform:rotateZ(360deg)}}@keyframes vscomp-animation-spin{to{-webkit-transform:rotateZ(360deg);transform:rotateZ(360deg)}}body.vscomp-popup-active{overflow:hidden}.vscomp-ele{display:inline-block;width:100%;max-width:250px}.vscomp-wrapper{display:inline-flex;position:relative;width:100%;font-family:sans-serif;font-size:14px;color:#333;text-align:left;flex-wrap:wrap}.vscomp-wrapper *,.vscomp-wrapper *::before,.vscomp-wrapper *::after{box-sizing:border-box}.vscomp-wrapper:focus{outline:none}.vscomp-dropbox-wrapper{position:absolute;top:0;left:0}.vscomp-toggle-button{display:flex;position:relative;align-items:center;width:100%;padding:7px 30px 7px 10px;border:1px solid #ddd;background-color:#fff;cursor:pointer}.vscomp-value{max-width:100%;height:20px;line-height:20px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.vscomp-arrow{position:absolute;display:flex;top:0;right:0;width:30px;height:100%;align-items:center;justify-content:center}.vscomp-arrow::after{content:'';-webkit-transform:rotate(45deg);transform:rotate(45deg);border:1px solid transparent;border-right-color:#111;border-bottom-color:#111;width:8px;height:8px;margin-top:-6px}.vscomp-clear-icon{position:relative;width:12px;height:12px}.vscomp-clear-icon::before,.vscomp-clear-icon::after{content:'';position:absolute;top:0;left:5px;width:2px;height:12px;background-color:#999}.vscomp-clear-icon::before{-webkit-transform:rotate(45deg);transform:rotate(45deg)}.vscomp-clear-icon::after{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.vscomp-clear-icon:hover::before,.vscomp-clear-icon:hover::after{background:#333}.vscomp-clear-button{position:absolute;display:none;top:50%;right:30px;width:24px;height:24px;justify-content:center;align-items:center;border-radius:50%;margin-top:-12px}.vscomp-clear-button:hover{background:#ccc}.vscomp-clear-button:hover .vscomp-clear-icon::before,.vscomp-clear-button:hover .vscomp-clear-icon::after{background-color:#333}.vscomp-dropbox-close-button{display:none;align-items:center;justify-content:center;position:absolute;left:50%;margin-left:-20px;bottom:-48px;width:40px;height:40px;background-color:#fff;border-radius:50%;cursor:pointer}.vscomp-value-tag.more-value-count{white-space:nowrap}.vscomp-dropbox-container{width:100%;z-index:2}.vscomp-dropbox{width:100%;background-color:#fff}.vscomp-options-container{position:relative;max-height:210px;overflow:auto}.vscomp-option{display:flex;flex-wrap:wrap;position:relative;align-items:center;padding:0 15px;height:40px;align-items:center;cursor:pointer}.vscomp-option.selected{background-color:#eee}.vscomp-option.focused{background-color:#ccc}.vscomp-option.disabled{opacity:0.5;cursor:default}.vscomp-option.group-title{opacity:0.6;cursor:default}.vscomp-option.group-option{padding-left:30px}.vscomp-new-option-icon{position:absolute;top:0;right:0;width:30px;height:30px}.vscomp-new-option-icon::before{content:'';position:absolute;top:0;right:0;border:15px solid #512da8;border-left-color:transparent;border-bottom-color:transparent}.vscomp-new-option-icon::after{content:'+';display:flex;align-items:center;justify-content:center;position:absolute;top:0;right:1px;font-size:18px;color:#fff;width:15px;height:15px}.vscomp-option-text{text-overflow:ellipsis;white-space:nowrap;overflow:hidden;width:100%}.vscomp-option-description{text-overflow:ellipsis;white-space:nowrap;overflow:hidden;width:100%;line-height:15px;color:#666;font-size:13px}.vscomp-search-container{display:flex;align-items:center;position:relative;height:40px;padding:0 5px 0 15px;border-bottom:1px solid #ddd}.vscomp-search-input{border:none;width:calc(100% - 30px);height:38px;padding:10px 0;font-size:15px;background-color:transparent;color:inherit}.vscomp-search-input:focus{outline:none}.vscomp-search-clear{display:flex;align-items:center;justify-content:center;width:30px;height:30px;font-size:25px;color:#999;cursor:pointer;user-select:none;visibility:hidden}.vscomp-search-clear:hover{color:inherit}.vscomp-no-options,.vscomp-no-search-results{display:none;justify-content:center;align-items:center;padding:20px 10px}.vscomp-options-loader{display:none;text-align:center;padding:20px 0}.vscomp-options-loader::before{content:'';display:inline-block;height:40px;width:40px;opacity:0.7;border-radius:50%;background-color:#fff;box-shadow:-4px -5px 3px -3px rgba(0,0,0,0.3);-webkit-animation:vscomp-animation-spin 0.8s infinite linear;animation:vscomp-animation-spin 0.8s infinite linear}.vscomp-ele[disabled]{cursor:not-allowed;user-select:none}.vscomp-ele[disabled] .vscomp-wrapper{pointer-events:none;opacity:0.7}.vscomp-wrapper .checkbox-icon{display:inline-flex;position:relative;width:15px;height:15px;margin-right:10px}.vscomp-wrapper .checkbox-icon::after{content:'';display:inline-block;width:100%;height:100%;border:2px solid #888;-webkit-transition-duration:.2s;transition-duration:.2s}.vscomp-wrapper .checkbox-icon.checked::after{width:50%;border-color:#512da8;border-left-color:transparent;border-top-color:transparent;-webkit-transform:rotate(45deg) translate(1px, -4px);transform:rotate(45deg) translate(1px, -4px)}.vscomp-wrapper.show-as-popup .vscomp-dropbox-container{display:flex;justify-content:center;align-items:center;position:fixed;top:0;left:0;width:100vw;height:100vh;overflow:auto;opacity:1;background-color:rgba(0,0,0,0.5)}.vscomp-wrapper.show-as-popup .vscomp-dropbox{position:relative;width:80%;max-height:calc(80% - 48px);max-width:500px;margin-top:-24px}.vscomp-wrapper.show-as-popup .vscomp-dropbox-close-button{display:flex}.vscomp-wrapper.has-select-all .vscomp-toggle-all-button{display:flex;align-items:center;cursor:pointer}.vscomp-wrapper.has-select-all .vscomp-search-input,.vscomp-wrapper.has-select-all .vscomp-toggle-all-label{width:calc(100% - 55px)}.vscomp-wrapper.has-select-all .vscomp-toggle-all-label{display:none}.vscomp-wrapper:not(.has-search-input) .vscomp-toggle-all-button{width:100%}.vscomp-wrapper:not(.has-search-input) .vscomp-toggle-all-label{display:inline-block}.vscomp-wrapper.multiple .vscomp-option .vscomp-option-text{width:calc(100% - 25px)}.vscomp-wrapper.multiple .vscomp-option .vscomp-option-description{padding-left:25px}.vscomp-wrapper.multiple .vscomp-option.selected .checkbox-icon::after{width:50%;border-color:#512da8;border-left-color:transparent;border-top-color:transparent;-webkit-transform:rotate(45deg) translate(1px, -4px);transform:rotate(45deg) translate(1px, -4px)}.vscomp-wrapper.focused .vscomp-toggle-button,.vscomp-wrapper:focus .vscomp-toggle-button{box-shadow:0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2)}.vscomp-wrapper.closed .vscomp-dropbox-container,.vscomp-wrapper.closed.vscomp-dropbox-wrapper{display:none}.vscomp-wrapper:not(.has-value) .vscomp-value{opacity:0.5}.vscomp-wrapper.has-clear-button.has-value .vscomp-clear-button{display:flex}.vscomp-wrapper.has-clear-button .vscomp-toggle-button{padding-right:54px}.vscomp-wrapper.has-no-options .vscomp-options-container,.vscomp-wrapper.has-no-search-results .vscomp-options-container{display:none}.vscomp-wrapper.has-no-options .vscomp-no-options{display:flex}.vscomp-wrapper.has-no-search-results .vscomp-no-search-results{display:flex}.vscomp-wrapper.has-search-value .vscomp-search-clear{visibility:visible}.vscomp-wrapper.has-no-options .vscomp-toggle-all-button{opacity:0.5;pointer-events:none}.vscomp-wrapper.keep-always-open .vscomp-toggle-button{padding-right:24px}.vscomp-wrapper.keep-always-open .vscomp-clear-button{right:5px}.vscomp-wrapper.keep-always-open .vscomp-arrow{display:none}.vscomp-wrapper.keep-always-open .vscomp-dropbox-container{position:relative;z-index:1}.vscomp-wrapper.keep-always-open .vscomp-dropbox{-webkit-transition-duration:0s;transition-duration:0s;box-shadow:none;border:1px solid #ddd}.vscomp-wrapper.keep-always-open.focused,.vscomp-wrapper.keep-always-open:focus,.vscomp-wrapper.keep-always-open:hover{box-shadow:0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2)}.vscomp-wrapper.server-searching .vscomp-options-list{display:none}.vscomp-wrapper.server-searching .vscomp-options-loader{display:block}.vscomp-wrapper.show-value-as-tags .vscomp-toggle-button{padding:4px 22px 0 10px}.vscomp-wrapper.show-value-as-tags .vscomp-value{display:flex;flex-wrap:wrap;height:auto;min-height:28px;overflow:auto;white-space:normal;text-overflow:unset}.vscomp-wrapper.show-value-as-tags .vscomp-value-tag{text-overflow:ellipsis;white-space:nowrap;overflow:hidden;display:inline-flex;align-items:center;max-width:100%;border:1px solid #ddd;margin:0 4px 4px 0;padding:2px 3px 2px 8px;font-size:12px;line-height:16px;border-radius:20px}.vscomp-wrapper.show-value-as-tags .vscomp-value-tag.more-value-count{padding-right:8px}.vscomp-wrapper.show-value-as-tags .vscomp-value-tag-content{text-overflow:ellipsis;white-space:nowrap;overflow:hidden;width:calc(100% - 20px)}.vscomp-wrapper.show-value-as-tags .vscomp-value-tag-clear-button{display:flex;align-items:center;justify-content:center;width:20px;height:20px}.vscomp-wrapper.show-value-as-tags .vscomp-value-tag-clear-button .vscomp-clear-icon{-webkit-transform:scale(0.8);transform:scale(0.8)}.vscomp-wrapper.show-value-as-tags .vscomp-arrow{height:34px}.vscomp-wrapper.show-value-as-tags .vscomp-clear-button{margin-top:0;top:5px}.vscomp-wrapper.show-value-as-tags.has-value .vscomp-arrow{display:none}.vscomp-wrapper.show-value-as-tags.has-value .vscomp-clear-button{right:2px}.vscomp-wrapper.show-value-as-tags:not(.has-value) .vscomp-toggle-button{padding-bottom:2px}.vscomp-wrapper.show-value-as-tags:not(.has-value) .vscomp-value{align-items:center;padding-bottom:3px}

	/*!
	* Popover v1.0.6
	* https://sa-si-dev.github.io/popover
	* Licensed under MIT (https://github.com/sa-si-dev/popover/blob/master/LICENSE)
	*/
	.pop-comp-wrapper{display:none;position:absolute;top:0;left:0;opacity:0;color:#000;background-color:#fff;box-shadow:0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2);text-align:left;flex-wrap:wrap;z-index:1}.pop-comp-arrow{position:absolute;z-index:1;width:16px;height:16px;overflow:hidden}.pop-comp-arrow::before{content:'';position:absolute;top:8px;left:8px;width:16px;height:16px;background-color:#fff;box-shadow:0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2);-webkit-transform-origin:left top;transform-origin:left top;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.pop-comp-content{position:relative;z-index:2}.pop-comp-wrapper.position-bottom .pop-comp-arrow{margin-left:-8px;left:0;top:-15px}.pop-comp-wrapper.position-bottom .pop-comp-arrow::before{box-shadow:0px 0px 2px 0 rgba(0,0,0,0.14)}.pop-comp-wrapper.position-top .pop-comp-arrow{margin-left:-8px;left:0;bottom:-15px}.pop-comp-wrapper.position-right .pop-comp-arrow{margin-top:-8px;top:0;left:-15px}.pop-comp-wrapper.position-left .pop-comp-arrow{margin-top:-8px;top:0;right:-15px}.pop-comp-disable-events{pointer-events:none}

	/* Custom */
	.col-input{
		min-width: 180px;
	}
	.select-block select, 
	.select-block .multiple-virtual-select{
		display: block;
		width: 100%;
		min-width: 150px;
	}
	.select-block select:first-child, 
	.select-block .multiple-virtual-select:first-child{
		margin-bottom: 10px;
	}
	.select-block .multiple-virtual-select .vscomp-toggle-button{
		padding-top: 3px;
		padding-bottom: 3px;
	}
	.btn-box-modal{
		display: flex;
		height: 2.75rem;
		width: 2.75rem;
		border: 1px solid hsl(0, 0%, 42.5%);
		border-radius: var(--border-rad);
		cursor: pointer;
	}
	.btn-box-modal i{
		line-height: 1.75rem;
	}
	.modal-open .modal#miAlerta{
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 1050;
		display: none;
		overflow: hidden;
		outline: 0;
		width: 100%;
		height: 100%;
		max-width: 100%;
		transform: unset;
		background-color: transparent;
	}
	.fade#miAlerta {
		opacity: 0;
		transition: opacity .15s linear;
	}
	#miAlerta.fade.show {
		opacity: 1;
	}
	#miAlerta.modal{
		z-index: 1;
	}
	.modal-open #miAlerta.modal {
		overflow-x: hidden;
		overflow-y: auto;
	}
	#miAlerta .modal-dialog {
		position: relative;
		width: auto;
		margin: .5rem auto;
		pointer-events: none;
	}
	#miAlerta.modal.fade .modal-dialog {
		transition: -webkit-transform .3s ease-out;
		transition: transform .3s ease-out;
		transition: transform .3s ease-out,-webkit-transform .3s ease-out;
		-webkit-transform: translate(0,-25%);
		transform: translate(0,-25%);
	}
	#miAlerta.modal.fade.show .modal-dialog {
		-webkit-transform: translate(0,0);
		transform: translate(0,0);
	}
	#miAlerta .modal-content {
		position: relative;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
		-ms-flex-direction: column;
		flex-direction: column;
		width: 100%;
		pointer-events: auto;
		background-color: #fff;
		background-clip: padding-box;
		border: 1px solid rgba(0,0,0,.2);
		border-radius: .3rem;
		outline: 0;
		z-index: 99;
	}
	#miAlerta.fade .modal-content {
		box-shadow: 10px 10px 32px 16px rgb(0 0 0 / 75%);
	}
	#miAlerta .modal-header {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-align: start;
		-ms-flex-align: start;
		align-items: flex-start;
		-webkit-box-pack: justify;
		-ms-flex-pack: justify;
		justify-content: space-between;
		padding: 1rem;
		border-bottom: 1px solid #e9ecef;
		border-top-left-radius: .3rem;
		border-top-right-radius: .3rem;
	}
	#miAlerta .modal-body {
		position: relative;
		-webkit-box-flex: 1;
		-ms-flex: 1 1 auto;
		flex: 1 1 auto;
		padding: 1rem;
	}
	#miAlerta .modal-footer {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-align: center;
		-ms-flex-align: center;
		align-items: center;
		-webkit-box-pack: end;
		-ms-flex-pack: end;
		justify-content: flex-end;
		padding: 1rem;
		border-top: 1px solid #e9ecef;
	}
	.modal-backdrop {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 1040;
		background-color: #000;
	}
	.modal-backdrop.fade{
		opacity: 0;
	}
	.modal-backdrop.show{
		opacity: .5;
	}
	#miAlerta .close {
		float: right;
		font-size: 1.5rem;
		font-weight: 700;
		line-height: 1;
		color: #000;
		text-shadow: 0 1px 0 #fff;
		opacity: .5;
	}
	#miAlerta button.close {
		padding: 0;
		background-color: transparent;
		border: 0;
		-webkit-appearance: none;
	}
	#miAlerta .modal-header .close {
		padding: 1rem;
		margin: -1rem -1rem -1rem auto;
	}
	#miAlerta .sr-only {
		border: 0;
		clip: rect(0,0,0,0);
		height: 1px;
		margin: -1px;
		overflow: hidden;
		padding: 0;
		position: absolute;
		width: 1px;
	}
	#miAlerta .modal-title {
		margin-bottom: 0;
		line-height: 1.5;
	}
	#miAlerta .form-group select{
		margin-left: 0;
    	max-width: 100%;
	}
	#miAlerta h5.modal-title{
		font-size: 1.25rem;
		line-height: 1.2;
		font-weight: 100;
		margin-top: 0;
	}
	#miAlerta .close:focus, 
	#miAlerta .close:hover {
		color: #000;
		text-decoration: none;
		opacity: .75;
		cursor: pointer;
	}
	#miAlerta .form-group {
		margin-bottom: 1rem;
	}
	#miAlerta .form-group label {
		display: inline-block;
		margin-bottom: .5rem;
	}
	#miAlerta .modal-footer>:not(:last-child) {
		margin-right: .25rem;
	}
	#miAlerta .modal-footer>:not(:first-child) {
		margin-left: .25rem;
	}
	.form-control:hover, .form-control:focus{
		color: inherit;
		background-color: transparent;
		border-color: inherit;
	}
	#btnInsertarNuevaLinea[disabled="disabled"]{
		background-color: gray !important;
	}
	.table-container.table-with-multi-select .table-responsive{
		overflow-y: initial;
	}
	.modal-body .form-label + .form-control{
		margin-left: 0;
	}
	.form-group .form-group-child{
		display: inline-block;
    	flex-grow: 1;
	}
	.form-group .form-group-child select, 
	.form-group .form-group-child input{
		width: 100%;
	}

	@media (min-width: 1430px){
		.table-container.table-with-multi-select{
			overflow: initial;
		}
	}
	@media (min-width: 576px){
		#miAlerta .modal-sm {
			max-width: 300px;
		}
		#miAlerta .modal-md {
			max-width: 550px;
		}
	}
</style>
<script>
	/*!
	* Virtual Select v1.0.16
	* https://sa-si-dev.github.io/virtual-select
	* Licensed under MIT (https://github.com/sa-si-dev/virtual-select/blob/master/LICENSE)
	*/
	!function(){"use strict";function e(e){return function(e){if(Array.isArray(e))return t(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,o){if(e){if("string"==typeof e)return t(e,o);var i=Object.prototype.toString.call(e).slice(8,-1);return"Object"===i&&e.constructor&&(i=e.constructor.name),"Map"===i||"Set"===i?Array.from(e):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?t(e,o):void 0}}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function t(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,i=new Array(t);o<t;o++)i[o]=e[o];return i}function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var s=function(){function t(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t)}var s,n,a;return s=t,a=[{key:"getString",value:function(e){return e||0===e?e.toString():""}},{key:"convertToBoolean",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];return e=!0===e||"true"===e||!1!==e&&"false"!==e&&t}},{key:"isEmpty",value:function(e){var t=!1;return e?Array.isArray(e)?0===e.length&&(t=!0):"object"===o(e)&&0===Object.keys(e).length&&(t=!0):t=!0,t}},{key:"isNotEmpty",value:function(e){return!this.isEmpty(e)}},{key:"removeItemFromArray",value:function(t,o,i){if(!Array.isArray(t)||!t.length||!o)return t;i&&(t=e(t));var s=t.indexOf(o);return-1!==s&&t.splice(s,1),t}},{key:"removeArrayEmpty",value:function(e){return Array.isArray(e)&&e.length?e.filter((function(e){return!!e})):[]}}],(n=null)&&i(s.prototype,n),a&&i(s,a),t}();function n(e){return function(e){if(Array.isArray(e))return a(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(e){if("string"==typeof e)return a(e,t);var o=Object.prototype.toString.call(e).slice(8,-1);return"Object"===o&&e.constructor&&(o=e.constructor.name),"Map"===o||"Set"===o?Array.from(e):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?a(e,t):void 0}}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,i=new Array(t);o<t;o++)i[o]=e[o];return i}function r(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var l=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}var t,o,i;return t=e,i=[{key:"addClass",value:function(t,o){t&&(o=o.split(" "),e.getElements(t).forEach((function(e){var t;(t=e.classList).add.apply(t,n(o))})))}},{key:"removeClass",value:function(t,o){t&&(o=o.split(" "),e.getElements(t).forEach((function(e){var t;(t=e.classList).remove.apply(t,n(o))})))}},{key:"toggleClass",value:function(t,o,i){var s;if(t)return void 0!==i&&(i=Boolean(i)),e.getElements(t).forEach((function(e){s=e.classList.toggle(o,i)})),s}},{key:"hasClass",value:function(e,t){return!!e&&e.classList.contains(t)}},{key:"hasEllipsis",value:function(e){return!!e&&e.scrollWidth>e.offsetWidth}},{key:"getData",value:function(e,t,o){if(e){var i=e?e.dataset[t]:"";return"number"===o?i=parseFloat(i)||0:"true"===i?i=!0:"false"===i&&(i=!1),i}}},{key:"setData",value:function(e,t,o){e&&(e.dataset[t]=o)}},{key:"setAttr",value:function(e,t,o){e&&e.setAttribute(t,o)}},{key:"setStyle",value:function(e,t,o){e&&(e.style[t]=o)}},{key:"getElements",value:function(e){if(e)return void 0===e.forEach&&(e=[e]),e}},{key:"addEvent",value:function(t,o,i){t&&(o=s.removeArrayEmpty(o.split(" "))).forEach((function(o){(t=e.getElements(t)).forEach((function(e){e.addEventListener(o,i)}))}))}},{key:"dispatchEvent",value:function(t,o){t&&(t=e.getElements(t),setTimeout((function(){t.forEach((function(e){e.dispatchEvent(new Event(o,{bubbles:!0}))}))}),0))}},{key:"getStyleText",value:function(e,t){var o="";for(var i in e)o+="".concat(i,": ").concat(e[i],";");return o&&!t&&(o='style="'.concat(o,'"')),o}},{key:"getAttributesText",value:function(e){var t="";if(!e)return t;for(var o in e){var i=e[o];void 0!==i&&(t+=" ".concat(o,'="').concat(i,'" '))}return t}}],(o=null)&&r(t.prototype,o),i&&r(t,i),e}();function p(e,t,o){return t in e?Object.defineProperty(e,t,{value:o,enumerable:!0,configurable:!0,writable:!0}):e[t]=o,e}function h(e){return function(e){if(Array.isArray(e))return u(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||c(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function c(e,t){if(e){if("string"==typeof e)return u(e,t);var o=Object.prototype.toString.call(e).slice(8,-1);return"Object"===o&&e.constructor&&(o=e.constructor.name),"Map"===o||"Set"===o?Array.from(e):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?u(e,t):void 0}}function u(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,i=new Array(t);o<t;o++)i[o]=e[o];return i}function d(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var v={13:"onEnterPress",38:"onUpArrowPress",40:"onDownArrowPress"},f=function(){function e(t){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e);try{this.setProps(t),this.setDisabledOptions(t.disabledOptions),this.setOptions(t.options),this.render()}catch(e){console.warn("Couldn't initiate Virtual Select"),console.error(e)}}var t,o,i;return t=e,i=[{key:"init",value:function(t){var o=t.ele;if(o){var i=!1;if("string"!=typeof o||(o=document.querySelector(o))){void 0===o.length&&(o=[o],i=!0);var s=[];return o.forEach((function(o){t.ele=o,s.push(new e(t))})),i?s[0]:s}}}},{key:"resetForm",value:function(e){var t=e.target.closest("form");t&&t.querySelectorAll(".vscomp-ele-wrapper").forEach((function(e){e.parentElement.virtualSelect.reset()}))}},{key:"reset",value:function(){this.virtualSelect.reset()}},{key:"setValueMethod",value:function(e,t){this.virtualSelect.setValueMethod(e,t)}},{key:"setOptionsMethod",value:function(e,t){this.virtualSelect.setOptionsMethod(e,t)}},{key:"setDisabledOptionsMethod",value:function(e){this.virtualSelect.setDisabledOptionsMethod(e)}},{key:"toggleSelectAll",value:function(e){this.virtualSelect.toggleAllOptions(e)}},{key:"isAllSelected",value:function(){return this.virtualSelect.isAllSelected}},{key:"addOptionMethod",value:function(e){this.virtualSelect.addOption(e,!0)}},{key:"getNewValueMethod",value:function(){return this.virtualSelect.getNewValue()}},{key:"getDisplayValueMethod",value:function(){return this.virtualSelect.getDisplayValue()}},{key:"getSelectedOptionsMethod",value:function(){return this.virtualSelect.getSelectedOptions()}},{key:"openMethod",value:function(){return this.virtualSelect.openDropbox()}},{key:"closeMethod",value:function(){return this.virtualSelect.closeDropbox()}},{key:"destroyMethod",value:function(){return this.virtualSelect.destroy()}},{key:"onResizeMethod",value:function(){document.querySelectorAll(".vscomp-ele-wrapper").forEach((function(e){e.parentElement.virtualSelect.onResize()}))}}],(o=[{key:"render",value:function(){if(this.$ele){var e="vscomp-wrapper",t=this.getTooltipAttrText("",!this.multiple,!0),o=this.getTooltipAttrText(this.clearButtonText);this.additionalClasses&&(e+=" "+this.additionalClasses),this.multiple&&(e+=" multiple",this.disableSelectAll||(e+=" has-select-all")),this.hideClearButton||(e+=" has-clear-button"),this.keepAlwaysOpen?e+=" keep-always-open":e+=" closed",this.showAsPopup&&(e+=" show-as-popup"),this.hasSearch&&(e+=" has-search-input"),this.showValueAsTags&&(e+=" show-value-as-tags");var i='<div class="vscomp-ele-wrapper '.concat(e,'" tabindex="0">\n        <input type="hidden" name="').concat(this.name,'" class="vscomp-hidden-input">\n\n        <div class="vscomp-toggle-button">\n          <div class="vscomp-value" ').concat(t,">\n            ").concat(this.placeholder,'\n          </div>\n\n          <div class="vscomp-arrow"></div>\n\n          <div class="vscomp-clear-button toggle-button-child" ').concat(o,'>\n            <i class="vscomp-clear-icon"></i>\n          </div>\n        </div>\n\n        ').concat(this.renderDropbox({wrapperClasses:e}),"\n      </div>");this.$ele.innerHTML=i,this.$body=document.querySelector("body"),this.$wrapper=this.$ele.querySelector(".vscomp-wrapper"),this.hasDropboxWrapper?(this.$allWrappers=[this.$wrapper,this.$dropboxWrapper],this.$dropboxContainer=this.$dropboxWrapper.querySelector(".vscomp-dropbox-container"),l.addClass(this.$dropboxContainer,"pop-comp-wrapper")):(this.$allWrappers=[this.$wrapper],this.$dropboxContainer=this.$wrapper.querySelector(".vscomp-dropbox-container")),this.$toggleButton=this.$ele.querySelector(".vscomp-toggle-button"),this.$clearButton=this.$ele.querySelector(".vscomp-clear-button"),this.$valueText=this.$ele.querySelector(".vscomp-value"),this.$hiddenInput=this.$ele.querySelector(".vscomp-hidden-input"),this.$dropboxCloseButton=this.$dropboxContainer.querySelector(".vscomp-dropbox-close-button"),this.$search=this.$dropboxContainer.querySelector(".vscomp-search-wrapper"),this.$optionsContainer=this.$dropboxContainer.querySelector(".vscomp-options-container"),this.$optionsList=this.$dropboxContainer.querySelector(".vscomp-options-list"),this.$options=this.$dropboxContainer.querySelector(".vscomp-options"),this.$noOptions=this.$dropboxContainer.querySelector(".vscomp-no-options"),this.$noSearchResults=this.$dropboxContainer.querySelector(".vscomp-no-search-results"),this.afterRenderWrapper()}}},{key:"renderDropbox",value:function(e){var t=e.wrapperClasses,o="self"!==this.dropboxWrapper?document.querySelector(this.dropboxWrapper):null,i={"z-index":this.zIndex};this.showAsPopup||this.dropboxWidth&&(i.width=this.dropboxWidth);var s='<div class="vscomp-dropbox-container" '.concat(l.getStyleText(i),'>\n        <div class="vscomp-dropbox">\n          <div class="vscomp-search-wrapper"></div>\n\n          <div class="vscomp-options-container">\n            <div class="vscomp-options-loader"></div>\n\n            <div class="vscomp-options-list">\n              <div class="vscomp-options"></div>\n            </div>\n          </div>\n\n          <div class="vscomp-no-options">').concat(this.noOptionsText,'</div>\n          <div class="vscomp-no-search-results">').concat(this.noSearchResultsText,'</div>\n\n          <span class="vscomp-dropbox-close-button"><i class="vscomp-clear-icon"></i></span>\n        </div>\n      </div>');if(o){var n=document.createElement("div");return this.$dropboxWrapper=n,this.hasDropboxWrapper=!0,n.innerHTML=s,o.appendChild(n),l.addClass(n,"vscomp-dropbox-wrapper ".concat(t)),""}return this.hasDropboxWrapper=!1,s}},{key:"renderOptions",value:function(){var e,t=this,o="",i=this.getVisibleOptions(),s="",n="",a=!(!this.markSearchResults||!this.searchValue),r=this.labelRenderer,p="function"==typeof r,h=l.getStyleText({height:this.optionHeight+"px"});if(a&&(e=new RegExp("(".concat(this.searchValue,")"),"gi")),this.multiple&&(s='<span class="checkbox-icon"></span>'),this.allowNewOption){var c=this.getTooltipAttrText("New Option");n='<span class="vscomp-new-option-icon" '.concat(c,"></span>")}i.forEach((function(i){var l,c="vscomp-option",u=t.getTooltipAttrText("",!0),d=s,v="",f="";i.isFocused&&(c+=" focused"),i.isDisabled&&(c+=" disabled"),i.isGroupTitle?(c+=" group-title",d=""):i.isSelected&&(c+=" selected"),i.isGroupOption&&(c+=" group-option"),l=p?r(i):i.label,i.description&&(f='<div class="vscomp-option-description" '.concat(u,">").concat(i.description,"</div>")),i.isCurrentNew?(c+=" current-new",v+=n):a&&!i.isGroupTitle&&(l=l.replace(e,"<mark>$1</mark>")),o+='<div class="'.concat(c,'" data-value="').concat(i.value,'" data-index="').concat(i.index,'" data-visible-index="').concat(i.visibleIndex,'" ').concat(h,">\n          ").concat(d,'\n          <span class="vscomp-option-text" ').concat(u,">\n            ").concat(l,"\n          </span>\n          ").concat(f,"\n          ").concat(v,"\n        </div>")})),this.$options.innerHTML=o;var u=!this.options.length&&!this.hasServerSearch,d=!u&&!i.length;(!this.allowNewOption||this.hasServerSearch||this.showOptionsOnlyOnSearch)&&l.toggleClass(this.$allWrappers,"has-no-search-results",d),l.toggleClass(this.$allWrappers,"has-no-options",u),this.setOptionsPosition(),this.setOptionsTooltip()}},{key:"renderSearch",value:function(){if(this.hasSearchContainer){var e="",t="";this.multiple&&!this.disableSelectAll&&(e='<span class="vscomp-toggle-all-button">\n          <span class="checkbox-icon vscomp-toggle-all-checkbox"></span>\n          <span class="vscomp-toggle-all-label">'.concat(this.selectAllText,"</span>\n        </span>")),this.hasSearch&&(t='<input type="text" class="vscomp-search-input" placeholder="'.concat(this.searchPlaceholderText,'">\n      <span class="vscomp-search-clear">&times;</span>'));var o='<div class="vscomp-search-container">\n        '.concat(e,"\n        ").concat(t,"\n      </div>");this.$search.innerHTML=o,this.$searchInput=this.$dropboxContainer.querySelector(".vscomp-search-input"),this.$searchClear=this.$dropboxContainer.querySelector(".vscomp-search-clear"),this.$toggleAllButton=this.$dropboxContainer.querySelector(".vscomp-toggle-all-button"),this.$toggleAllCheckbox=this.$dropboxContainer.querySelector(".vscomp-toggle-all-checkbox"),this.addEvent(this.$searchInput,"keyup change","onSearch"),this.addEvent(this.$searchClear,"click","onSearchClear"),this.addEvent(this.$toggleAllButton,"click","onToggleAllOptions")}}},{key:"addEvents",value:function(){this.addEvent(document,"click","onDocumentClick"),this.addEvent(this.$allWrappers,"keydown","onKeyDown"),this.addEvent(this.$toggleButton,"click","onToggleButtonClick"),this.addEvent(this.$clearButton,"click","onClearButtonClick"),this.addEvent(this.$dropboxContainer,"click","onDropboxContainerClick"),this.addEvent(this.$dropboxCloseButton,"click","onDropboxCloseButtonClick"),this.addEvent(this.$optionsContainer,"scroll","onOptionsScroll"),this.addEvent(this.$options,"click","onOptionsClick"),this.addEvent(this.$options,"mouseover","onOptionsMouseOver"),this.addEvent(this.$options,"touchmove","onOptionsTouchMove"),this.addMutationObserver()}},{key:"addEvent",value:function(e,t,o){var i=this;e&&(t=s.removeArrayEmpty(t.split(" "))).forEach((function(t){var s="".concat(o,"-").concat(t),n=i.events[s];n||(n=i[o].bind(i),i.events[s]=n),l.addEvent(e,t,n)}))}},{key:"onDocumentClick",value:function(e){var t=e.target.closest(".vscomp-wrapper");t!==this.$wrapper&&t!==this.$dropboxWrapper&&this.isOpened()&&this.closeDropbox()}},{key:"onKeyDown",value:function(e){var t=e.which||e.keyCode,o=v[t];o&&this[o](e)}},{key:"onEnterPress",value:function(){this.isOpened()?this.selectFocusedOption():this.openDropbox()}},{key:"onDownArrowPress",value:function(e){e.preventDefault(),this.isOpened()?this.focusOption("next"):this.openDropbox()}},{key:"onUpArrowPress",value:function(e){e.preventDefault(),this.isOpened()?this.focusOption("previous"):this.openDropbox()}},{key:"onToggleButtonClick",value:function(e){var t=e.target;t.closest(".vscomp-value-tag-clear-button")?this.removeValue(t.closest(".vscomp-value-tag")):t.closest(".toggle-button-child")||this.toggleDropbox()}},{key:"onClearButtonClick",value:function(){this.reset()}},{key:"onOptionsScroll",value:function(){this.setVisibleOptions()}},{key:"onOptionsClick",value:function(e){this.selectOption(e.target.closest(".vscomp-option:not(.disabled):not(.group-title)"))}},{key:"onDropboxContainerClick",value:function(e){e.target.closest(".vscomp-dropbox")||this.closeDropbox()}},{key:"onDropboxCloseButtonClick",value:function(){this.closeDropbox()}},{key:"onOptionsMouseOver",value:function(e){var t=e.target.closest(".vscomp-option:not(.disabled):not(.group-title)");t&&this.isOpened()&&this.focusOption(null,t)}},{key:"onOptionsTouchMove",value:function(){this.removeOptionFocus()}},{key:"onSearch",value:function(e){e.stopPropagation(),this.setSearchValue(e.target.value,!0)}},{key:"onSearchClear",value:function(){this.setSearchValue(""),this.focusSearchInput()}},{key:"onToggleAllOptions",value:function(){this.toggleAllOptions()}},{key:"onResize",value:function(){this.setOptionsContainerHeight(!0)}},{key:"addMutationObserver",value:function(){var e=this;if(this.hasDropboxWrapper){var t=this.$ele;this.mutationObserver=new MutationObserver((function(o){o.some((function(o){var i=h(o.removedNodes).some((function(e){if(e===t||e.contains(t))return!0}));return i&&e.destroy(),i}))})),this.mutationObserver.observe(document.querySelector("body"),{childList:!0,subtree:!0})}}},{key:"beforeValueSet",value:function(e){this.toggleAllOptionsClass(!e&&void 0)}},{key:"beforeSelectNewValue",value:function(){var e=this,t=this.getNewOption(),o=t.index;this.newValues.push(t.value),this.setOptionProp(o,"isCurrentNew",!1),this.setOptionProp(o,"isNew",!0),setTimeout((function(){e.setSearchValue(""),e.focusSearchInput()}),0)}},{key:"afterRenderWrapper",value:function(){l.setAttr(this.$ele,"name",this.name),l.addClass(this.$ele,"vscomp-ele"),this.renderSearch(),this.setOptionsHeight(),this.setVisibleOptions(),this.setOptionsContainerHeight(),this.addEvents(),this.setMethods(),this.keepAlwaysOpen||this.showAsPopup||this.initDropboxPopover(),this.initialSelectedValue?this.setValueMethod(this.initialSelectedValue,this.silentInitialValueSet):this.autoSelectFirstOption&&this.visibleOptions.length&&this.setValueMethod(this.visibleOptions[0].value,this.silentInitialValueSet),this.showOptionsOnlyOnSearch&&this.setSearchValue("",!1,!0)}},{key:"afterSetOptionsContainerHeight",value:function(e){e&&this.showAsPopup&&this.setVisibleOptions()}},{key:"afterSetSearchValue",value:function(){this.hasServerSearch?this.serverSearch():this.setVisibleOptionsCount(),this.selectAllOnlyVisible&&this.toggleAllOptionsClass()}},{key:"afterSetVisibleOptionsCount",value:function(){this.scrollToTop(),this.setOptionsHeight(),this.setVisibleOptions()}},{key:"afterValueSet",value:function(){this.scrollToTop(),this.setSearchValue(""),this.renderOptions()}},{key:"afterSetOptions",value:function(e){e&&this.setSelectedProp(),this.setOptionsHeight(),this.setVisibleOptions(),this.showOptionsOnlyOnSearch&&this.setSearchValue("",!1,!0),e||this.reset()}},{key:"setProps",value:function(e){e=this.setDefaultProps(e),this.setPropsFromElementAttr(e);var t=s.convertToBoolean;this.$ele=e.ele,this.dropboxWrapper=e.dropboxWrapper,this.valueKey=e.valueKey,this.labelKey=e.labelKey,this.descriptionKey=e.descriptionKey,this.aliasKey=e.aliasKey,this.optionHeightText=e.optionHeight,this.optionHeight=parseFloat(this.optionHeightText),this.multiple=t(e.multiple),this.hasSearch=t(e.search),this.hideClearButton=t(e.hideClearButton),this.autoSelectFirstOption=t(e.autoSelectFirstOption),this.hasOptionDescription=t(e.hasOptionDescription),this.silentInitialValueSet=t(e.silentInitialValueSet),this.allowNewOption=t(e.allowNewOption),this.markSearchResults=t(e.markSearchResults),this.showSelectedOptionsFirst=t(e.showSelectedOptionsFirst),this.disableSelectAll=t(e.disableSelectAll),this.keepAlwaysOpen=t(e.keepAlwaysOpen),this.showDropboxAsPopup=t(e.showDropboxAsPopup),this.hideValueTooltipOnSelectAll=t(e.hideValueTooltipOnSelectAll),this.showOptionsOnlyOnSearch=t(e.showOptionsOnlyOnSearch),this.selectAllOnlyVisible=t(e.selectAllOnlyVisible),this.alwaysShowSelectedOptionsCount=t(e.alwaysShowSelectedOptionsCount),this.disableAllOptionsSelectedText=t(e.disableAllOptionsSelectedText),this.showValueAsTags=t(e.showValueAsTags),this.noOptionsText=e.noOptionsText,this.noSearchResultsText=e.noSearchResultsText,this.selectAllText=e.selectAllText,this.searchPlaceholderText=e.searchPlaceholderText,this.optionsSelectedText=e.optionsSelectedText,this.optionSelectedText=e.optionSelectedText,this.allOptionsSelectedText=e.allOptionsSelectedText,this.clearButtonText=e.clearButtonText,this.moreText=e.moreText,this.placeholder=e.placeholder,this.position=e.position,this.dropboxWidth=e.dropboxWidth,this.tooltipFontSize=e.tooltipFontSize,this.tooltipAlignment=e.tooltipAlignment,this.tooltipMaxWidth=e.tooltipMaxWidth,this.noOfDisplayValues=parseInt(e.noOfDisplayValues),this.zIndex=parseInt(e.zIndex),this.maxValues=parseInt(e.maxValues),this.name=e.name,this.additionalClasses=e.additionalClasses,this.popupDropboxBreakpoint=e.popupDropboxBreakpoint,this.onServerSearch=e.onServerSearch,this.labelRenderer=e.labelRenderer,this.initialSelectedValue=0===e.selectedValue?"0":e.selectedValue,this.selectedValues=[],this.selectedOptions=[],this.newValues=[],this.events={},this.tooltipEnterDelay=200,this.searchValue="",this.searchValueOriginal="",this.isAllSelected=!1,(void 0===e.search&&this.multiple||this.allowNewOption||this.showOptionsOnlyOnSearch)&&(this.hasSearch=!0),this.hasServerSearch="function"==typeof this.onServerSearch,(this.maxValues||this.hasServerSearch||this.showOptionsOnlyOnSearch)&&(this.disableSelectAll=!0),this.keepAlwaysOpen&&(this.dropboxWrapper="self"),this.showAsPopup=this.showDropboxAsPopup&&!this.keepAlwaysOpen&&window.innerWidth<=parseFloat(this.popupDropboxBreakpoint),this.hasSearchContainer=this.hasSearch||this.multiple&&!this.disableSelectAll,this.optionsCount=this.getOptionsCount(e.optionsCount),this.halfOptionsCount=Math.ceil(this.optionsCount/2),this.optionsHeight=this.getOptionsHeight()}},{key:"setDefaultProps",value:function(e){var t={dropboxWrapper:"self",valueKey:"value",labelKey:"label",descriptionKey:"description",aliasKey:"alias",optionsCount:5,noOfDisplayValues:50,optionHeight:"40px",multiple:!1,hideClearButton:!1,autoSelectFirstOption:!1,hasOptionDescription:!1,silentInitialValueSet:!1,disableSelectAll:!1,noOptionsText:"No options found",noSearchResultsText:"No results found",selectAllText:"Select All",searchPlaceholderText:"Search...",clearButtonText:"Clear",moreText:"more...",optionsSelectedText:"options selected",optionSelectedText:"option selected",allOptionsSelectedText:"All",placeholder:"Select",position:"auto",zIndex:e.keepAlwaysOpen?1:2,allowNewOption:!1,markSearchResults:!1,tooltipFontSize:"14px",tooltipAlignment:"center",tooltipMaxWidth:"300px",showSelectedOptionsFirst:!1,name:"",additionalClasses:"",keepAlwaysOpen:!1,maxValues:0,showDropboxAsPopup:!0,popupDropboxBreakpoint:"576px",hideValueTooltipOnSelectAll:!0,showOptionsOnlyOnSearch:!1,selectAllOnlyVisible:!1,alwaysShowSelectedOptionsCount:!1,disableAllOptionsSelectedText:!1,showValueAsTags:!1};return e.hasOptionDescription&&(t.optionsCount=4,t.optionHeight="50px"),Object.assign(t,e)}},{key:"setPropsFromElementAttr",value:function(e){var t=e.ele,o={multiple:"multiple",placeholder:"placeholder",name:"name","data-dropbox-wrapper":"dropboxWrapper","data-value-key":"valueKey","data-label-key":"labelKey","data-description-key":"descriptionKey","data-alias-key":"aliasKey","data-search":"search","data-hide-clear-button":"hideClearButton","data-auto-select-first-option":"autoSelectFirstOption","data-has-option-description":"hasOptionDescription","data-options-count":"optionsCount","data-option-height":"optionHeight","data-position":"position","data-no-options-text":"noOptionsText","data-no-search-results-text":"noSearchResultsText","data-select-all-text":"selectAllText","data-search-placeholder-text":"searchPlaceholderText","data-options-selected-text":"optionsSelectedText","data-option-selected-text":"optionSelectedText","data-all-options-selected-text":"allOptionsSelectedText","data-clear-button-text":"clearButtonText","data-more-text":"moreText","data-silent-initial-value-set":"silentInitialValueSet","data-dropbox-width":"dropboxWidth","data-z-index":"zIndex","data-no-of-display-values":"noOfDisplayValues","data-allow-new-option":"allowNewOption","data-mark-search-results":"markSearchResults","data-tooltip-font-size":"tooltipFontSize","data-tooltip-alignment":"tooltipAlignment","data-tooltip-max-width":"tooltipMaxWidth","data-show-selected-options-first":"showSelectedOptionsFirst","data-disable-select-all":"disableSelectAll","data-keep-always-open":"keepAlwaysOpen","data-max-values":"maxValues","data-additional-classes":"additionalClasses","data-show-dropbox-as-popup":"showDropboxAsPopup","data-popup-dropbox-breakpoint":"popupDropboxBreakpoint","data-hide-value-tooltip-on-select-all":"hideValueTooltipOnSelectAll","data-show-options-only-on-search":"showOptionsOnlyOnSearch","data-select-all-only-visible":"selectAllOnlyVisible","data-always-show-selected-options-count":"alwaysShowSelectedOptionsCount","data-disable-all-options-selected-text":"disableAllOptionsSelectedText","data-show-value-as-tags":"showValueAsTags"};for(var i in o){var s=t.getAttribute(i);"multiple"!==i||""!==s&&"true"!==s||(s=!0),s&&(e[o[i]]=s)}}},{key:"setMethods",value:function(){var t=this.$ele;t.virtualSelect=this,t.value=this.multiple?[]:"",t.reset=e.reset,t.setValue=e.setValueMethod,t.setOptions=e.setOptionsMethod,t.setDisabledOptions=e.setDisabledOptionsMethod,t.toggleSelectAll=e.toggleSelectAll,t.isAllSelected=e.isAllSelected,t.addOption=e.addOptionMethod,t.getNewValue=e.getNewValueMethod,t.getDisplayValue=e.getDisplayValueMethod,t.getSelectedOptions=e.getSelectedOptionsMethod,t.open=e.openMethod,t.close=e.closeMethod,t.destroy=e.destroyMethod,this.hasDropboxWrapper&&(this.$dropboxWrapper.virtualSelect=this)}},{key:"setValueMethod",value:function(e,t){Array.isArray(e)||(e=[e]),e=e.map((function(e){return e||0==e?e.toString():""}));var o=[];this.allowNewOption&&e&&this.setNewOptionsFromValue(e);var i={};e.forEach((function(e){i[e]=!0})),this.options.forEach((function(e){!0!==i[e.value]||e.isDisabled||e.isGroupTitle?e.isSelected=!1:(e.isSelected=!0,o.push(e.value))})),this.multiple||(o=o[0]),this.beforeValueSet(),this.setValue(o,!t),this.afterValueSet()}},{key:"setOptionsMethod",value:function(e,t){this.setOptions(e),this.afterSetOptions(t)}},{key:"setDisabledOptionsMethod",value:function(e){this.setDisabledOptions(e,!0),this.setValueMethod(null),this.setVisibleOptions()}},{key:"setDisabledOptions",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];e=e.map((function(e){return e.toString()})),this.disabledOptions=e;var o={};e.forEach((function(e){o[e]=!0})),t&&e.length&&this.options.forEach((function(e){return e.isDisabled=!0===o[e.value],e}))}},{key:"setOptions",value:function(e){e||(e=[]);var t=[],o=this.disabledOptions.length,i=this.valueKey,n=this.labelKey,a=this.descriptionKey,r=this.aliasKey,l=this.hasOptionDescription,p=s.getString,h=s.convertToBoolean,c=this.getAlias,u=0,d=!1,v={};this.disabledOptions.forEach((function(e){v[e]=!0})),e.forEach((function e(s){var f=p(s[i]),y=s.options,m=!!y,g={index:u,value:f,label:p(s[n]),alias:c(s[r]),isVisible:h(s.isVisible,!0),isNew:s.isNew||!1,isGroupTitle:m};if(o&&(g.isDisabled=!0===v[f]),s.isGroupOption&&(g.isGroupOption=!0,g.groupIndex=s.groupIndex),l&&(g.description=p(s[a])),t.push(g),u++,m){var b=g.index;d=!0,y.forEach((function(t){t.isGroupOption=!0,t.groupIndex=b,e(t)}))}})),this.options=t,this.visibleOptionsCount=t.length,this.lastOptionIndex=this.options.length-1,this.newValues=[],this.hasOptionGroup=d,this.setSortedOptions()}},{key:"setServerOptions",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];this.setOptionsMethod(t,!0);var o=this.selectedOptions,i=this.options,s=!1;if(o.length){var n={};s=!0,i.forEach((function(e){n[e.value]=!0})),o.forEach((function(e){!1===n[e.value]&&(e.isVisible=!1,i.push(e))})),this.setOptionsMethod(i,!0)}if(this.allowNewOption&&this.searchValue){var a=i.some((function(t){return t.label.toLowerCase()===e.searchValue}));a||(s=!0,this.setNewOption())}s&&(this.setVisibleOptionsCount(),this.multiple&&this.toggleAllOptionsClass(),this.setValueText()),l.removeClass(this.$allWrappers,"server-searching")}},{key:"setSelectedOptions",value:function(){var e={};this.selectedValues.forEach((function(t){e[t]=!0})),this.selectedOptions=this.options.filter((function(t){return!0===e[t.value]}))}},{key:"setSortedOptions",value:function(){var e=h(this.options);this.showSelectedOptionsFirst&&this.selectedValues.length&&(e=this.hasOptionGroup?this.sortOptionsGroup(e):this.sortOptions(e)),this.sortedOptions=e}},{key:"setVisibleOptions",value:function(){var e=h(this.sortedOptions),t=2*this.optionsCount,o=this.getVisibleStartIndex(),i=this.getNewOption(),s=o+t-1,n=0;i&&(i.visibleIndex=n,n++),e=e.filter((function(e){var t=!1;return e.isVisible&&!e.isCurrentNew&&(t=n>=o&&n<=s,e.visibleIndex=n,n++),t})),i&&(e=[i].concat(h(e))),this.visibleOptions=e,this.renderOptions()}},{key:"setOptionsPosition",value:function(e){void 0===e&&(e=this.getVisibleStartIndex());var t=e*this.optionHeight;this.$options.style.transform="translate3d(0, ".concat(t,"px, 0)"),l.setData(this.$options,"top",t)}},{key:"setOptionsTooltip",value:function(){var e=this,t=this.getVisibleOptions(),o=this.hasOptionDescription;t.forEach((function(t){var i=e.$dropboxContainer.querySelector('.vscomp-option[data-index="'.concat(t.index,'"]'));l.setData(i.querySelector(".vscomp-option-text"),"tooltip",t.label),o&&l.setData(i.querySelector(".vscomp-option-description"),"tooltip",t.description)}))}},{key:"setValue",value:function(e,t){e?Array.isArray(e)?this.selectedValues=h(e):this.selectedValues=[e]:this.selectedValues=[];var o=this.multiple?this.selectedValues:this.selectedValues[0]||"";this.$ele.value=o,this.$hiddenInput.value=o,this.isMaxValuesSelected=!!(this.maxValues&&this.maxValues<=this.selectedValues.length),this.setValueText(),l.toggleClass(this.$allWrappers,"has-value",s.isNotEmpty(this.selectedValues)),l.toggleClass(this.$allWrappers,"max-value-selected",this.isMaxValuesSelected),t&&l.dispatchEvent(this.$ele,"change")}},{key:"setValueText",value:function(){var e=[],t=[],o=this.selectedValues,i=o.length,s=this.noOfDisplayValues,n=this.showValueAsTags,a=this.$valueText,r=0,p=this.isAllSelected&&!this.hasServerSearch&&!this.disableAllOptionsSelectedText&&!n;if(p&&this.hideValueTooltipOnSelectAll)a.innerHTML="".concat(this.allOptionsSelectedText," (").concat(i,")");else{var h={};o.forEach((function(e){h[e]=!0}));var u,d=function(e,t){var o="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!o){if(Array.isArray(e)||(o=c(e))||t&&e&&"number"==typeof e.length){o&&(e=o);var i=0,s=function(){};return{s:s,n:function(){return i>=e.length?{done:!0}:{done:!1,value:e[i++]}},e:function(e){throw e},f:s}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var n,a=!0,r=!1;return{s:function(){o=o.call(e)},n:function(){var e=o.next();return a=e.done,e},e:function(e){r=!0,n=e},f:function(){try{a||null==o.return||o.return()}finally{if(r)throw n}}}}(this.options);try{for(d.s();!(u=d.n()).done;){var v=u.value;if(!v.isCurrentNew){if(r>50)break;var f=v.value;if(!0===h[f]){var y=v.label;if(e.push(y),++r<=s)if(n){var m='<span class="vscomp-value-tag" data-value="'.concat(f,'">\n                  <span class="vscomp-value-tag-content">').concat(y,'</span>\n                  <span class="vscomp-value-tag-clear-button">\n                    <i class="vscomp-clear-icon"></i>\n                  </span>\n                </span>');t.push(m)}else t.push(y)}}}}catch(e){d.e(e)}finally{d.f()}var g=i-s;g>0&&t.push('<span class="vscomp-value-tag more-value-count">+ '.concat(g," ").concat(this.moreText,"</span>"));var b=e.join(", ");if(""===b)a.innerHTML=this.placeholder;else if(a.innerHTML=b,this.multiple){var O=this.maxValues;if(l.hasEllipsis(a)||O||this.alwaysShowSelectedOptionsCount||n){var S='<span class="vscomp-selected-value-count">'.concat(i,"</span>");if(O&&(S+=' / <span class="vscomp-max-value-count">'.concat(O,"</span>")),p)a.innerHTML="".concat(this.allOptionsSelectedText," (").concat(i,")");else if(n)a.innerHTML=t.join("");else{var w=1===i?this.optionSelectedText:this.optionsSelectedText;a.innerHTML="".concat(S," ").concat(w)}}else t=[]}}n||l.setData(a,"tooltip",t.join(", "))}},{key:"setSearchValue",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],o=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(e!==this.searchValueOriginal||o){t||(this.$searchInput.value=e);var i=e.replace(/\\/g,"").toLowerCase().trim();this.searchValue=i,this.searchValueOriginal=e,l.toggleClass(this.$allWrappers,"has-search-value",e),this.afterSetSearchValue()}}},{key:"setVisibleOptionsCount",value:function(){var e,t=0,o=!1,i=this.searchValue,s=this.showOptionsOnlyOnSearch,n=this.isOptionVisible.bind(this);this.hasOptionGroup&&(e=this.getVisibleOptionGroupsMapping(i)),this.options.forEach((function(a){var r;a.isCurrentNew||(s&&!i?(a.isVisible=!1,r={isVisible:!1,hasExactOption:!1}):r=n(a,i,o,e),r.isVisible&&t++,o||(o=r.hasExactOption))})),this.allowNewOption&&(i&&!o?(this.setNewOption(),t++):this.removeNewOption()),this.visibleOptionsCount=t,this.afterSetVisibleOptionsCount()}},{key:"setOptionProp",value:function(e,t,o){this.options[e]&&(this.options[e][t]=o)}},{key:"setOptionsHeight",value:function(){this.$optionsList.style.height=this.optionHeight*this.visibleOptionsCount+"px"}},{key:"setOptionsContainerHeight",value:function(e){var t;e?this.showAsPopup&&(this.optionsCount=this.getOptionsCount(),t=this.getOptionsHeight(),this.optionsHeight=t):(t=this.optionsHeight,this.keepAlwaysOpen&&(l.setStyle(this.$noOptions,"height",t),l.setStyle(this.$noSearchResults,"height",t))),l.setStyle(this.$optionsContainer,"max-height",t),this.afterSetOptionsContainerHeight(e)}},{key:"setNewOption",value:function(e){var t=e||this.searchValueOriginal.trim();if(t){var o=this.getNewOption();if(o){var i=o.index;this.setOptionProp(i,"value",t),this.setOptionProp(i,"label",t)}else{var s={value:t,label:t};e?(s.isNew=!0,this.newValues.push(t)):s.isCurrentNew=!0,this.addOption(s)}}}},{key:"setSelectedProp",value:function(){var e={};this.selectedValues.forEach((function(t){e[t]=!0})),this.options.forEach((function(t){!0===e[t.value]&&(t.isSelected=!0)}))}},{key:"setNewOptionsFromValue",value:function(e){if(e){var t=this.setNewOption.bind(this),o={};this.options.forEach((function(e){o[e.value]=!0})),e.forEach((function(e){e&&!0!==o[e]&&t(e)}))}}},{key:"setDropboxWrapperWidth",value:function(){if(!this.showAsPopup){var e=this.dropboxWidth||"".concat(this.$wrapper.offsetWidth,"px");l.setStyle(this.$dropboxContainer,"max-width",e)}}},{key:"getVisibleOptions",value:function(){return this.visibleOptions||[]}},{key:"getValue",value:function(){return this.multiple?this.selectedValues:this.selectedValues[0]}},{key:"getFirstVisibleOptionIndex",value:function(){return Math.ceil(this.$optionsContainer.scrollTop/this.optionHeight)}},{key:"getVisibleStartIndex",value:function(){var e=this.getFirstVisibleOptionIndex()-this.halfOptionsCount;return e<0&&(e=0),e}},{key:"getTooltipAttrText",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],o=arguments.length>2&&void 0!==arguments[2]&&arguments[2],i={"data-tooltip":e||"","data-tooltip-enter-delay":this.tooltipEnterDelay,"data-tooltip-z-index":this.zIndex,"data-tooltip-font-size":this.tooltipFontSize,"data-tooltip-alignment":this.tooltipAlignment,"data-tooltip-max-width":this.tooltipMaxWidth,"data-tooltip-ellipsis-only":t,"data-tooltip-allow-html":o};return l.getAttributesText(i)}},{key:"getOptionObj",value:function(e){if(e){var t=s.getString;return{index:e.index,value:t(e.value),label:t(e.label),description:t(e.description),alias:this.getAlias(e.alias),isCurrentNew:e.isCurrentNew||!1,isNew:e.isNew||!1,isVisible:!0}}}},{key:"getNewOption",value:function(){var e=this.options[this.lastOptionIndex];if(e&&e.isCurrentNew)return e}},{key:"getOptionIndex",value:function(e){var t;return e&&this.options.some((function(o){if(o.value==e)return t=o.index,!0})),t}},{key:"getNewValue",value:function(){var e={};this.newValues.forEach((function(t){e[t]=!0}));var t=this.selectedValues.filter((function(t){return!0===e[t]}));return this.multiple?t:t[0]}},{key:"getAlias",value:function(e){return e=e?(e=Array.isArray(e)?e.join(","):e.toString().trim()).toLowerCase():""}},{key:"getDisplayValue",value:function(){var e=[],t={};return this.selectedValues.forEach((function(e){t[e]=!0})),this.options.forEach((function(o){!0===t[o.value]&&e.push(o.label)})),this.multiple?e:e[0]||""}},{key:"getSelectedOptions",value:function(){var e=this.valueKey,t=this.labelKey,o=[],i={};return this.selectedValues.forEach((function(e){i[e]=!0})),this.options.forEach((function(s){if(!0===i[s.value]){var n,a=(p(n={},e,s.value),p(n,t,s.label),n);s.isNew&&(a.isNew=!0),o.push(a)}})),this.multiple?o:o[0]}},{key:"getVisibleOptionGroupsMapping",value:function(e){var t=this.options,o={},i=this.isOptionVisible;return(t=this.structureOptionGroup(t)).forEach((function(t){o[t.index]=t.options.some((function(t){return i(t,e).isVisible}))})),o}},{key:"getOptionsCount",value:function(e){if(this.showAsPopup){var t=80*window.innerHeight/100-48;this.hasSearchContainer&&(t-=40),e=Math.floor(t/this.optionHeight)}else e=parseInt(e);return e}},{key:"getOptionsHeight",value:function(){return this.optionsCount*this.optionHeight+"px"}},{key:"getSibling",value:function(e,t){var o="next"===t?"nextElementSibling":"previousElementSibling";do{e&&(e=e[o])}while(l.hasClass(e,"disabled")||l.hasClass(e,"group-title"));return e}},{key:"initDropboxPopover",value:function(){var e={ele:this.$ele,target:this.$dropboxContainer,position:this.position,zIndex:this.zIndex,margin:4,transitionDistance:30,hideArrowIcon:!0,disableManualAction:!0,disableUpdatePosition:!this.hasDropboxWrapper,afterShow:this.afterShowPopper.bind(this),afterHide:this.afterHidePopper.bind(this)};this.dropboxPopover=new PopoverComponent(e)}},{key:"openDropbox",value:function(e){this.isSilentOpen=e,e||l.dispatchEvent(this.$ele,"beforeOpen"),this.setDropboxWrapperWidth(),l.removeClass(this.$allWrappers,"closed"),this.dropboxPopover?this.dropboxPopover.show():this.afterShowPopper()}},{key:"afterShowPopper",value:function(){var e=this.isSilentOpen;this.isSilentOpen=!1,e||(this.moveSelectedOptionsFirst(),l.addClass(this.$allWrappers,"focused"),this.showAsPopup?(l.addClass(this.$body,"vscomp-popup-active"),this.isPopupActive=!0):this.focusSearchInput(),l.dispatchEvent(this.$ele,"afterOpen"))}},{key:"closeDropbox",value:function(e){this.isSilentClose=e,this.keepAlwaysOpen?this.removeOptionFocus():(e||l.dispatchEvent(this.$ele,"beforeClose"),this.dropboxPopover?this.dropboxPopover.hide():this.afterHidePopper())}},{key:"afterHidePopper",value:function(){var e=this.isSilentClose;this.isSilentClose=!1,l.removeClass(this.$allWrappers,"focused"),this.removeOptionFocus(),e||this.isPopupActive&&(l.removeClass(this.$body,"vscomp-popup-active"),this.isPopupActive=!1),l.addClass(this.$allWrappers,"closed"),e||l.dispatchEvent(this.$ele,"afterClose")}},{key:"moveSelectedOptionsFirst",value:function(){this.showSelectedOptionsFirst&&(this.setSortedOptions(),this.$optionsContainer.scrollTop&&this.selectedValues.length?this.scrollToTop():this.setVisibleOptions())}},{key:"toggleDropbox",value:function(){this.isOpened()?this.closeDropbox():this.openDropbox()}},{key:"isOpened",value:function(){return!l.hasClass(this.$wrapper,"closed")}},{key:"focusSearchInput",value:function(){var e=this.$searchInput;e&&e.focus()}},{key:"focusOption",value:function(e,t){var o,i=this.$dropboxContainer.querySelector(".vscomp-option.focused");if(t)o=t;else if(i)o=this.getSibling(i,e);else{var s=this.getFirstVisibleOptionIndex();o=this.$dropboxContainer.querySelector('.vscomp-option[data-visible-index="'.concat(s,'"]')),(l.hasClass(o,"disabled")||l.hasClass(o,"group-title"))&&(o=this.getSibling(o,"next"))}o&&o!==i&&(i&&l.removeClass(i,"focused"),l.addClass(o,"focused"),this.toggleFocusedProp(l.getData(o,"index"),!0),this.moveFocusedOptionToView(o))}},{key:"moveFocusedOptionToView",value:function(e){if(e||(e=this.$dropboxContainer.querySelector(".vscomp-option.focused")),e){var t,o=this.$optionsContainer.getBoundingClientRect(),i=e.getBoundingClientRect(),s=o.top,n=o.bottom,a=o.height,r=i.top,p=i.bottom,h=i.height,c=e.offsetTop,u=l.getData(this.$options,"top","number");s>r?t=c+u:n<p&&(t=c-a+h+u),void 0!==t&&(this.$optionsContainer.scrollTop=t)}}},{key:"removeOptionFocus",value:function(){var e=this.$dropboxContainer.querySelector(".vscomp-option.focused");e&&(l.removeClass(e,"focused"),this.toggleFocusedProp(null))}},{key:"selectOption",value:function(e){if(e){var t=!l.hasClass(e,"selected");if(t){if(this.multiple&&this.isMaxValuesSelected)return}else if(!this.multiple)return void this.closeDropbox();var o=this.selectedValues,i=l.getData(e,"value"),n=l.getData(e,"index");if(this.toggleSelectedProp(n,t),t){if(this.multiple)o.push(i),this.toggleAllOptionsClass();else{o.length&&this.toggleSelectedProp(this.getOptionIndex(o[0]),!1),o=[i];var a=this.$dropboxContainer.querySelector(".vscomp-option.selected");a&&l.toggleClass(a,"selected",!1),this.closeDropbox()}l.toggleClass(e,"selected")}else this.multiple&&(l.toggleClass(e,"selected"),s.removeItemFromArray(o,i),this.toggleAllOptionsClass(!1));l.hasClass(e,"current-new")&&this.beforeSelectNewValue(),this.setValue(o,!0)}}},{key:"selectFocusedOption",value:function(){this.selectOption(this.$dropboxContainer.querySelector(".vscomp-option.focused"))}},{key:"toggleAllOptions",value:function(e){if(this.multiple&&!this.disableSelectAll){"boolean"!=typeof e&&(e=!l.hasClass(this.$toggleAllCheckbox,"checked"));var t=[],o=this.selectAllOnlyVisible;this.options.forEach((function(i){i.isDisabled||i.isCurrentNew||i.isGroupTitle||(!e||o&&!i.isVisible?i.isSelected=!1:(i.isSelected=!0,t.push(i.value)))})),this.toggleAllOptionsClass(e),this.setValue(t,!0),this.renderOptions()}}},{key:"toggleAllOptionsClass",value:function(e){if(this.multiple){var t="boolean"==typeof e;t||(e=this.isAllOptionsSelected()),l.toggleClass(this.$toggleAllCheckbox,"checked",e),this.selectAllOnlyVisible&&t?this.isAllSelected=this.isAllOptionsSelected():this.isAllSelected=e}}},{key:"isAllOptionsSelected",value:function(){var e=!1;return this.options.length&&(e=!this.options.some((function(e){return!e.isSelected&&!e.isDisabled&&!e.isGroupTitle}))),e}},{key:"toggleFocusedProp",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];this.focusedOptionIndex&&this.setOptionProp(this.focusedOptionIndex,"isFocused",!1),this.setOptionProp(e,"isFocused",t),this.focusedOptionIndex=e}},{key:"toggleSelectedProp",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];this.setOptionProp(e,"isSelected",t)}},{key:"scrollToTop",value:function(){var e=!this.isOpened();e&&this.openDropbox(!0),this.$optionsContainer.scrollTop>0&&(this.$optionsContainer.scrollTop=0),e&&this.closeDropbox(!0)}},{key:"reset",value:function(){this.options.forEach((function(e){e.isSelected=!1})),this.beforeValueSet(!0),this.setValue(null,!0),this.afterValueSet(),l.dispatchEvent(this.$ele,"reset")}},{key:"addOption",value:function(e,t){if(e){this.lastOptionIndex++,e.index=this.lastOptionIndex;var o=this.getOptionObj(e);this.options.push(o),this.sortedOptions.push(o),t&&(this.visibleOptionsCount++,this.afterSetOptions())}}},{key:"removeOption",value:function(e){(e||0==e)&&(this.options.splice(e,1),this.lastOptionIndex--)}},{key:"removeNewOption",value:function(){var e=this.getNewOption();e&&this.removeOption(e.index)}},{key:"sortOptions",value:function(e){return e.sort((function(e,t){return e.isSelected||t.isSelected?e.isSelected&&(!t.isSelected||e.index<t.index)?-1:1:0}))}},{key:"sortOptionsGroup",value:function(e){var t=this.sortOptions;return(e=this.structureOptionGroup(e)).forEach((function(e){var o=e.options;e.isSelected=o.some((function(e){return e.isSelected})),e.isSelected&&t(o)})),t(e),this.destructureOptionGroup(e)}},{key:"isOptionVisible",value:function(e,t,o,i){var s=e.label.toLowerCase(),n=e.description,a=e.alias,r=-1!==s.indexOf(t);return e.isGroupTitle&&(r=i[e.index]),a&&!r&&(r=-1!==a.indexOf(t)),n&&!r&&(r=-1!==n.toLowerCase().indexOf(t)),e.isVisible=r,o||(o=s===t),{isVisible:r,hasExactOption:o}}},{key:"structureOptionGroup",value:function(e){var t=[],o={};return e.forEach((function(e){if(e.isGroupTitle){var i=[];e.options=i,o[e.index]=i,t.push(e)}})),e.forEach((function(e){e.isGroupOption&&o[e.groupIndex].push(e)})),t}},{key:"destructureOptionGroup",value:function(e){var t=[];return e.forEach((function(e){t.push(e),t=t.concat(e.options)})),t}},{key:"serverSearch",value:function(){l.removeClass(this.$allWrappers,"has-no-search-results"),l.addClass(this.$allWrappers,"server-searching"),this.setSelectedOptions(),this.onServerSearch(this.searchValue,this)}},{key:"removeValue",value:function(e){var t=this.selectedValues,o=l.getData(e,"value");s.removeItemFromArray(t,o),this.setValueMethod(t)}},{key:"destroy",value:function(){var e=this.$ele;e.virtualSelect=void 0,e.value=void 0,e.innerHTML="",this.hasDropboxWrapper&&(this.$dropboxWrapper.remove(),this.mutationObserver.disconnect()),l.removeClass(e,"vscomp-ele")}}])&&d(t.prototype,o),i&&d(t,i),e}();document.addEventListener("reset",f.resetForm),window.addEventListener("resize",f.onResizeMethod),window.VirtualSelect=f}(),function(){"use strict";function e(e){return function(e){if(Array.isArray(e))return t(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,o){if(e){if("string"==typeof e)return t(e,o);var i=Object.prototype.toString.call(e).slice(8,-1);return"Object"===i&&e.constructor&&(i=e.constructor.name),"Map"===i||"Set"===i?Array.from(e):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?t(e,o):void 0}}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function t(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,i=new Array(t);o<t;o++)i[o]=e[o];return i}var o=function(){function t(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t)}var o;return(o=[{key:"addClass",value:function(o,i){o&&(i=i.split(" "),t.getElements(o).forEach((function(t){var o;(o=t.classList).add.apply(o,e(i))})))}},{key:"removeClass",value:function(o,i){o&&(i=i.split(" "),t.getElements(o).forEach((function(t){var o;(o=t.classList).remove.apply(o,e(i))})))}},{key:"getElements",value:function(e){if(e)return void 0===e.forEach&&(e=[e]),e}},{key:"getMoreVisibleSides",value:function(e){if(!e)return{};var t=e.getBoundingClientRect(),o=window.innerWidth,i=window.innerHeight,s=t.left,n=t.top;return{horizontal:s>o-s-t.width?"left":"right",vertical:n>i-n-t.height?"top":"bottom"}}},{key:"getAbsoluteCoords",value:function(e){if(e){var t=e.getBoundingClientRect(),o=window.pageXOffset,i=window.pageYOffset;return{width:t.width,height:t.height,top:t.top+i,right:t.right+o,bottom:t.bottom+i,left:t.left+o}}}},{key:"getCoords",value:function(e){return e?e.getBoundingClientRect():{}}},{key:"getData",value:function(e,t,o){if(e){var i=e?e.dataset[t]:"";return"number"===o?i=parseFloat(i)||0:"true"===i?i=!0:"false"===i&&(i=!1),i}}},{key:"setData",value:function(e,t,o){e&&(e.dataset[t]=o)}},{key:"setStyle",value:function(e,t,o){e&&(e.style[t]=o)}},{key:"show",value:function(e){var o=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"block";t.setStyle(e,"display",o)}},{key:"hide",value:function(e){t.setStyle(e,"display","none")}},{key:"getHideableParent",value:function(e){for(var t,o=e.parentElement;o;){var i=getComputedStyle(o).overflow;if(-1!==i.indexOf("scroll")||-1!==i.indexOf("auto")){t=o;break}o=o.parentElement}return t}}])&&function(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}(t,o),t}(),i=["top","bottom","left","right"].map((function(e){return"position-".concat(e)})),s={top:"rotate(180deg)",left:"rotate(90deg)",right:"rotate(-90deg)"},n=function(){function e(t){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e);try{this.setProps(t),this.init()}catch(e){console.warn("Couldn't initiate popper"),console.error(e)}}var t;return(t=[{key:"init",value:function(){var e=this.$popperEle;e&&this.$triggerEle&&(o.setStyle(e,"zIndex",this.zIndex),this.setPosition())}},{key:"setProps",value:function(e){var t=(e=this.setDefaultProps(e)).position?e.position.toLowerCase():"auto";if(this.$popperEle=e.$popperEle,this.$triggerEle=e.$triggerEle,this.$arrowEle=e.$arrowEle,this.margin=parseFloat(e.margin),this.offset=parseFloat(e.offset),this.enterDelay=parseFloat(e.enterDelay),this.exitDelay=parseFloat(e.exitDelay),this.showDuration=parseFloat(e.showDuration),this.hideDuration=parseFloat(e.hideDuration),this.transitionDistance=parseFloat(e.transitionDistance),this.zIndex=parseFloat(e.zIndex),this.afterShowCallback=e.afterShow,this.afterHideCallback=e.afterHide,this.hasArrow=!!this.$arrowEle,-1!==t.indexOf(" ")){var o=t.split(" ");this.position=o[0],this.secondaryPosition=o[1]}else this.position=t}},{key:"setDefaultProps",value:function(e){return Object.assign({position:"auto",margin:8,offset:5,enterDelay:0,exitDelay:0,showDuration:300,hideDuration:200,transitionDistance:10,zIndex:1},e)}},{key:"setPosition",value:function(){o.show(this.$popperEle,"inline-flex");var e,t,n,a=window.innerWidth,r=window.innerHeight,l=o.getAbsoluteCoords(this.$popperEle),p=o.getAbsoluteCoords(this.$triggerEle),h=l.width,c=l.height,u=l.top,d=l.right,v=l.bottom,f=l.left,y=p.width,m=p.height,g=p.top,b=p.right,O=p.bottom,S=p.left,w=g-u,x=S-f,k=x,C=w,E=this.position,$=this.secondaryPosition,A=y/2-h/2,D=m/2-c/2,T=this.margin,V=this.transitionDistance,P=window.scrollY-u,M=r+P,I=window.scrollX-f,H=a+I,F=this.offset;F&&(P+=F,M-=F,I+=F,H-=F),"auto"===E&&(E=o.getMoreVisibleSides(this.$triggerEle).vertical);var W={top:{top:C-c-T,left:k+A},bottom:{top:C+m+T,left:k+A},right:{top:C+D,left:k+y+T},left:{top:C+D,left:k-h-T}},N=W[E];if(C=N.top,k=N.left,$&&("top"===$?C=w:"bottom"===$?C=w+m-c:"left"===$?k=x:"right"===$&&(k=x+y-h)),k<I?"left"===E?n="right":k=I+f>b?b-f:I:k+h>H&&("right"===E?n="left":k=H+f<S?S-d:H-h),C<P?"top"===E?n="bottom":C=P+u>O?O-u:P:C+c>M&&("bottom"===E?n="top":C=M+u<g?g-v:M-c),n){var L=W[n];"top"===(E=n)||"bottom"===E?C=L.top:"left"!==E&&"right"!==E||(k=L.left)}"top"===E?(e=C+V,t=k):"right"===E?(e=C,t=k-V):"left"===E?(e=C,t=k+V):(e=C-V,t=k);var B="translate3d(".concat(t,"px, ").concat(e,"px, 0)");if(o.setStyle(this.$popperEle,"transform",B),o.setData(this.$popperEle,"fromLeft",t),o.setData(this.$popperEle,"fromTop",e),o.setData(this.$popperEle,"top",C),o.setData(this.$popperEle,"left",k),o.removeClass(this.$popperEle,i.join(" ")),o.addClass(this.$popperEle,"position-".concat(E)),this.hasArrow){var j=0,q=0,R=k+f,z=C+u,K=this.$arrowEle.offsetWidth/2,G=s[E]||"";"top"===E||"bottom"===E?(j=y/2+S-R)<K?j=K:j>h-K&&(j=h-K):"left"!==E&&"right"!==E||((q=m/2+g-z)<K?q=K:q>c-K&&(q=c-K)),o.setStyle(this.$arrowEle,"transform","translate3d(".concat(j,"px, ").concat(q,"px, 0) ").concat(G))}o.hide(this.$popperEle)}},{key:"resetPosition",value:function(){o.setStyle(this.$popperEle,"transform","none"),this.setPosition()}},{key:"show",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},i=t.resetPosition,s=t.data;clearTimeout(this.exitDelayTimeout),clearTimeout(this.hideDurationTimeout),i&&this.resetPosition(),this.enterDelayTimeout=setTimeout((function(){var t=o.getData(e.$popperEle,"left"),i=o.getData(e.$popperEle,"top"),n="translate3d(".concat(t,"px, ").concat(i,"px, 0)"),a=e.showDuration;o.show(e.$popperEle,"inline-flex"),o.getCoords(e.$popperEle),o.setStyle(e.$popperEle,"transitionDuration",a+"ms"),o.setStyle(e.$popperEle,"transform",n),o.setStyle(e.$popperEle,"opacity",1),e.showDurationTimeout=setTimeout((function(){"function"==typeof e.afterShowCallback&&e.afterShowCallback(s)}),a)}),this.enterDelay)}},{key:"hide",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},i=t.data;clearTimeout(this.enterDelayTimeout),clearTimeout(this.showDurationTimeout),this.exitDelayTimeout=setTimeout((function(){if(e.$popperEle){var t=o.getData(e.$popperEle,"fromLeft"),s=o.getData(e.$popperEle,"fromTop"),n="translate3d(".concat(t,"px, ").concat(s,"px, 0)"),a=e.hideDuration;o.setStyle(e.$popperEle,"transitionDuration",a+"ms"),o.setStyle(e.$popperEle,"transform",n),o.setStyle(e.$popperEle,"opacity",0),e.hideDurationTimeout=setTimeout((function(){o.hide(e.$popperEle),"function"==typeof e.afterHideCallback&&e.afterHideCallback(i)}),a)}}),this.exitDelay)}},{key:"updatePosition",value:function(){o.setStyle(this.$popperEle,"transitionDuration","0ms"),this.resetPosition();var e=o.getData(this.$popperEle,"left"),t=o.getData(this.$popperEle,"top");o.show(this.$popperEle,"inline-flex"),o.setStyle(this.$popperEle,"transform","translate3d(".concat(e,"px, ").concat(t,"px, 0)"))}}])&&function(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}(e.prototype,t),e}();window.PopperComponent=n}(),function(){"use strict";function e(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var t=function(){function t(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t)}var o,i;return o=t,(i=[{key:"convertToBoolean",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];return!0===e||"true"===e||!1!==e&&"false"!==e&&t}},{key:"removeArrayEmpty",value:function(e){return Array.isArray(e)&&e.length?e.filter((function(e){return!!e})):[]}},{key:"throttle",value:function(e,t){var o,i=0;return function(){for(var s=arguments.length,n=new Array(s),a=0;a<s;a++)n[a]=arguments[a];var r=(new Date).getTime(),l=t-(r-i);clearTimeout(o),l<=0?(i=r,e.apply(void 0,n)):o=setTimeout((function(){e.apply(void 0,n)}),l)}}}])&&e(o,i),t}();function o(e){return function(e){if(Array.isArray(e))return i(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(e){if("string"==typeof e)return i(e,t);var o=Object.prototype.toString.call(e).slice(8,-1);return"Object"===o&&e.constructor&&(o=e.constructor.name),"Map"===o||"Set"===o?Array.from(e):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?i(e,t):void 0}}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,i=new Array(t);o<t;o++)i[o]=e[o];return i}function s(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var n=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}var i,n;return i=e,(n=[{key:"addClass",value:function(t,i){t&&(i=i.split(" "),e.getElements(t).forEach((function(e){var t;(t=e.classList).add.apply(t,o(i))})))}},{key:"removeClass",value:function(t,i){t&&(i=i.split(" "),e.getElements(t).forEach((function(e){var t;(t=e.classList).remove.apply(t,o(i))})))}},{key:"hasClass",value:function(e,t){return!!e&&e.classList.contains(t)}},{key:"getElement",value:function(e){return e&&("string"==typeof e?e=document.querySelector(e):void 0!==e.length&&(e=e[0])),e||null}},{key:"getElements",value:function(e){if(e)return void 0===e.forEach&&(e=[e]),e}},{key:"addEvent",value:function(t,o,i){e.addOrRemoveEvent(t,o,i,"add")}},{key:"removeEvent",value:function(t,o,i){e.addOrRemoveEvent(t,o,i,"remove")}},{key:"addOrRemoveEvent",value:function(o,i,s,n){o&&(i=t.removeArrayEmpty(i.split(" "))).forEach((function(t){(o=e.getElements(o)).forEach((function(e){"add"===n?e.addEventListener(t,s):e.removeEventListener(t,s)}))}))}},{key:"getScrollableParents",value:function(e){if(!e)return[];for(var t=[window],o=e.parentElement;o;){var i=getComputedStyle(o).overflow;-1===i.indexOf("scroll")&&-1===i.indexOf("auto")||t.push(o),o=o.parentElement}return t}}])&&s(i,n),e}();function a(e,t){for(var o=0;o<t.length;o++){var i=t[o];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var r={27:"onEscPress"},l=function(){function e(t){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e);try{this.setProps(t),this.init()}catch(e){console.warn("Couldn't initiate Popover component"),console.error(e)}}var o,i,s;return o=e,s=[{key:"init",value:function(t){var o=t.ele;if(o){var i=!1;if("string"==typeof o){if(!(o=document.querySelectorAll(o)))return;1===o.length&&(i=!0)}void 0===o.length&&(o=[o],i=!0);var s=[];return o.forEach((function(o){t.ele=o,e.destory(o),s.push(new e(t))})),i?s[0]:s}}},{key:"destory",value:function(e){if(e){var t=e.popComp;t&&t.destory()}}},{key:"showMethod",value:function(){return this.popComp.show()}},{key:"hideMethod",value:function(){return this.popComp.hide()}}],(i=[{key:"init",value:function(){this.$popover&&(this.setElementProps(),this.renderArrow(),this.initPopper(),this.addEvents())}},{key:"getEvents",value:function(){var e=[{$ele:document,event:"click",method:"onDocumentClick"},{$ele:document,event:"keydown",method:"onDocumentKeyDown"}];return this.disableManualAction||(e.push({$ele:this.$ele,event:"click",method:"onTriggerEleClick"}),this.showOnHover&&(e.push({$ele:this.$ele,event:"mouseenter",method:"onTriggerEleMouseEnter"}),e.push({$ele:this.$ele,event:"mouseleave",method:"onTriggerEleMouseLeave"}))),e}},{key:"addOrRemoveEvents",value:function(e){var t=this;this.getEvents().forEach((function(o){t.addOrRemoveEvent({action:e,$ele:o.$ele,events:o.event,method:o.method})}))}},{key:"addEvents",value:function(){this.addOrRemoveEvents("add")}},{key:"removeEvents",value:function(){this.addOrRemoveEvents("remove"),this.removeScrollEventListeners()}},{key:"addOrRemoveEvent",value:function(e){var o=this,i=e.action,s=e.$ele,a=e.events,r=e.method,l=e.throttle;s&&(a=t.removeArrayEmpty(a.split(" "))).forEach((function(e){var a="".concat(r,"-").concat(e),p=o.events[a];p||(p=o[r].bind(o),l&&(p=t.throttle(p,l)),o.events[a]=p),"add"===i?n.addEvent(s,e,p):n.removeEvent(s,e,p)}))}},{key:"addScrollEventListeners",value:function(){this.$scrollableElems=n.getScrollableParents(this.$ele),this.addOrRemoveEvent({action:"add",$ele:this.$scrollableElems,events:"scroll",method:"onAnyParentScroll",throttle:100})}},{key:"removeScrollEventListeners",value:function(){this.$scrollableElems&&(this.addOrRemoveEvent({action:"remove",$ele:this.$scrollableElems,events:"scroll",method:"onAnyParentScroll"}),this.$scrollableElems=null)}},{key:"onAnyParentScroll",value:function(){this.popper.updatePosition()}},{key:"onDocumentClick",value:function(e){var t=e.target,o=t.closest(".pop-comp-ele"),i=t.closest(".pop-comp-wrapper");this.hideOnOuterClick&&o!==this.$ele&&i!==this.$popover&&this.hide()}},{key:"onDocumentKeyDown",value:function(e){var t=e.which||e.keyCode,o=r[t];o&&this[o](e)}},{key:"onEscPress",value:function(){this.hideOnOuterClick&&this.hide()}},{key:"onTriggerEleClick",value:function(){this.toggle()}},{key:"onTriggerEleMouseEnter",value:function(){this.show()}},{key:"onTriggerEleMouseLeave",value:function(){this.hide()}},{key:"setProps",value:function(e){e=this.setDefaultProps(e),this.setPropsFromElementAttr(e);var o=t.convertToBoolean;this.$ele=e.ele,this.target=e.target,this.position=e.position,this.margin=parseFloat(e.margin),this.offset=parseFloat(e.offset),this.enterDelay=parseFloat(e.enterDelay),this.exitDelay=parseFloat(e.exitDelay),this.showDuration=parseFloat(e.showDuration),this.hideDuration=parseFloat(e.hideDuration),this.transitionDistance=parseFloat(e.transitionDistance),this.zIndex=parseFloat(e.zIndex),this.hideOnOuterClick=o(e.hideOnOuterClick),this.showOnHover=o(e.showOnHover),this.hideArrowIcon=o(e.hideArrowIcon),this.disableManualAction=o(e.disableManualAction),this.disableUpdatePosition=o(e.disableUpdatePosition),this.beforeShowCallback=e.beforeShow,this.afterShowCallback=e.afterShow,this.beforeHideCallback=e.beforeHide,this.afterHideCallback=e.afterHide,this.events={},this.$popover=n.getElement(this.target)}},{key:"setDefaultProps",value:function(e){return Object.assign({position:"auto",margin:8,offset:5,enterDelay:0,exitDelay:0,showDuration:300,hideDuration:200,transitionDistance:10,zIndex:1,hideOnOuterClick:!0,showOnHover:!1,hideArrowIcon:!1,disableManualAction:!1,disableUpdatePosition:!1},e)}},{key:"setPropsFromElementAttr",value:function(e){var t=e.ele,o={"data-popover-target":"target","data-popover-position":"position","data-popover-margin":"margin","data-popover-offset":"offset","data-popover-enter-delay":"enterDelay","data-popover-exit-delay":"exitDelay","data-popover-show-duration":"showDuration","data-popover-hide-duration":"hideDuration","data-popover-transition-distance":"transitionDistance","data-popover-z-index":"zIndex","data-popover-hide-on-outer-click":"hideOnOuterClick","data-popover-show-on-hover":"showOnHover","data-popover-hide-arrow-icon":"hideArrowIcon","data-popover-disable-manual-action":"disableManualAction","data-popover-disable-update-position":"disableUpdatePosition"};for(var i in o){var s=t.getAttribute(i);s&&(e[o[i]]=s)}}},{key:"setElementProps",value:function(){var t=this.$ele;t.popComp=this,t.show=e.showMethod,t.hide=e.hideMethod,n.addClass(this.$ele,"pop-comp-ele"),n.addClass(this.$popover,"pop-comp-wrapper")}},{key:"getOtherTriggerPopComp",value:function(){var e,t=this.$popover.popComp;return t&&t.$ele!==this.$ele&&(e=t),e}},{key:"initPopper",value:function(){var e={$popperEle:this.$popover,$triggerEle:this.$ele,$arrowEle:this.$arrowEle,position:this.position,margin:this.margin,offset:this.offset,enterDelay:this.enterDelay,exitDelay:this.exitDelay,showDuration:this.showDuration,hideDuration:this.hideDuration,transitionDistance:this.transitionDistance,zIndex:this.zIndex,afterShow:this.afterShow.bind(this),afterHide:this.afterHide.bind(this)};this.popper=new PopperComponent(e)}},{key:"beforeShow",value:function(){"function"==typeof this.beforeShowCallback&&this.beforeShowCallback(this)}},{key:"beforeHide",value:function(){"function"==typeof this.beforeHideCallback&&this.beforeHideCallback(this)}},{key:"show",value:function(){this.isShown()||(this.isShownForOtherTrigger()?this.showAfterOtherHide():(n.addClass(this.$popover,"pop-comp-disable-events"),this.$popover.popComp=this,this.beforeShow(),this.popper.show({resetPosition:!0}),n.addClass(this.$ele,"pop-comp-active")))}},{key:"hide",value:function(){this.isShown()&&(this.beforeHide(),this.popper.hide(),this.removeScrollEventListeners())}},{key:"toggle",value:function(e){void 0===e&&(e=!this.isShown()),e?this.show():this.hide()}},{key:"isShown",value:function(){return n.hasClass(this.$ele,"pop-comp-active")}},{key:"isShownForOtherTrigger",value:function(){var e=this.getOtherTriggerPopComp();return!!e&&e.isShown()}},{key:"showAfterOtherHide",value:function(){var e=this,t=this.getOtherTriggerPopComp();if(t){var o=t.exitDelay+t.hideDuration+100;setTimeout((function(){e.show()}),o)}}},{key:"afterShow",value:function(){var e=this;this.showOnHover?setTimeout((function(){n.removeClass(e.$popover,"pop-comp-disable-events")}),2e3):n.removeClass(this.$popover,"pop-comp-disable-events"),this.disableUpdatePosition||this.addScrollEventListeners(),"function"==typeof this.afterShowCallback&&this.afterShowCallback(this)}},{key:"afterHide",value:function(){n.removeClass(this.$ele,"pop-comp-active"),"function"==typeof this.afterHideCallback&&this.afterHideCallback(this)}},{key:"renderArrow",value:function(){if(!this.hideArrowIcon){var e=this.$popover.querySelector(".pop-comp-arrow");e||(this.$popover.insertAdjacentHTML("afterbegin",'<i class="pop-comp-arrow"></i>'),e=this.$popover.querySelector(".pop-comp-arrow")),this.$arrowEle=e}}},{key:"destory",value:function(){this.removeEvents()}}])&&a(o.prototype,i),s&&a(o,s),e}();window.PopoverComponent=l}();

	var frecuencia_meses = <?php echo json_encode($frecuencia_meses); ?>;
	var frecuencia_dias = <?php echo json_encode($frecuencia_dias); ?>;
	var avisos = <?php echo json_encode($avisos); ?>;
	var alerta_user_id = <?php echo $id; ?>;
	var contador_label = "<?php echo $contador_label; ?>";
	var modal_cache = null;
	function openModalAlerta(modal = {}){
		$("body").addClass("modal-open");
		$("body").attr("style", "overflow: hidden; padding-right: 23px;");
		$(".fade.modal-backdrop.show").remove();
		//$("#miAlerta").find(".modal-title").html(`<i class="fa fa-plus"></i> ${modal && modal.alerta_id?'Actualizar':'Insertar'} campo de base de datos`);
		$("#miAlerta").before(`<div class="fade modal-backdrop show" aria-hidden="true"></div>`);
		$("#miAlerta").addClass("show");
		$("#miAlerta").show();
		$("#miAlerta").find("#btnInsertarNuevaLinea").attr("disabled", "disabled");
		$("#modal_empresa").html(`<option value="">Seleccione la empresa</option>`);
		$("#modal_contador").html(`<option value="">Seleccione el contador</option>`);
		$("#modal_conexion").html(`<option value="">Seleccione la connección base de datos</option>`);
		$("#modal_table").html(`<option value="">Seleccione la tabla</option>`);
		$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		$("#modal_condicion").val(0);
		$("#modal_limite").val('');
		$.getJSON("/api/enterprices", function(data){
			if(data && data.length > 0){
				data.map(item => {
					$("#modal_empresa").append(`
						<option value="${item.id}">${item.name}</option>
					`);
				});
				if(modal && modal.alerta_id){
					$("#modal_empresa").val(modal.conexion_data.enterprise_id);
				}else
					$("#miAlerta").find("#btnInsertarNuevaLinea").removeAttr("disabled");
			}
		});
		modal_cache = null;
		if(modal && modal.alerta_id){
			modal_cache = modal;
			$.getJSON(`/api/enterprices/${modal_cache.conexion_data.enterprise_id}/meters`, function(data){
				if(data && data.length > 0){
					data.map(item => {
						$("#modal_contador").append(`
							<option value="${item.id}">${item.name}</option>
						`);
					});
					$("#modal_contador").val(modal_cache.conexion_data.meter_id);
				}
			});
			$.getJSON(`/produccion/connections/${modal_cache.conexion_data.meter_id}`, function(data){
				if(data && data.length > 0){
					let _tables = [];
					data.map(item => {
						if(item.id === modal_cache.conexion_data.conection_id)
							_tables = item.tables;
						$("#modal_conexion").append(`
							<option value="${item.id}" data-tables='${JSON.stringify(item.tables)}'>${item.name}</option>
						`);
					});
					$("#modal_conexion").val(modal_cache.conexion_data.conection_id);
					let _fields = [];
					_tables.map( item => {
						if(item.name === modal_cache.conexion_data.table_name)
							_fields = item.fields;
						$("#modal_table").append(`
							<option value="${item.name}" data-fields='${JSON.stringify(item.fields)}'>${item.name}</option>
						`);
					});
					$("#modal_table").val(modal_cache.conexion_data.table_name);
					_fields.map( item => {
						$("#modal_field").append(`
							<option value="${item.name}">${item.name}</option>
						`);
					});
					$("#modal_field").val(modal_cache.conexion_data.field_name);
					$("#modal_condicion").val(modal_cache.conexion_data.condition);
					$("#modal_limite").val(modal_cache.conexion_data.limit);
					$("#miAlerta").find("#btnInsertarNuevaLinea").removeAttr("disabled");
				}
			});
		}
	}
	function closeModalAlerta(){
		$("body").removeClass("modal-open");
		$("body").removeAttr("style");
		$(".fade.modal-backdrop.show").remove();
		$("#miAlerta").removeClass("show");
		$("#miAlerta").hide();
	}
	function customIsValidEmailList(emailList) {
		const regex = /^(([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)(\s*;\s*|\s*$))*$/
		return regex.test(emailList.toLowerCase())
	}
	$(document).ready(function(){
		const virtual_selects = {
			options: [frecuencia_meses, frecuencia_dias],
			placesholder: ['Seleccione los meses', 'Seleccine los días'],
			allSelectionText: ['Todos los meses', 'Todos los días'],
			classes: ['.multiple-virtual-select-1', '.multiple-virtual-select-2']
		};
		virtual_selects.classes.map( (sel, index) => {
			if($(sel).length){
				$(sel).each(function(a, b){
					const select_id = `#${$(b).attr('id')}`;
					const select_value = $(b).attr('data-value');
					VirtualSelect.init({
						ele: select_id,
						selectAllText: virtual_selects.allSelectionText[index], 
						placeholder: virtual_selects.placesholder[index],
						options: virtual_selects.options[index], 
						multiple: true, 
						search: false
					});
					if(select_value && select_value !== ''){
						document.querySelector(select_id).setValue(select_value.split(','));
					}
				});
			}
		});
	});
	$(document).on("click", "#btnModalNuevo", function(e){
		e.preventDefault();
		openModalAlerta();
	});
	$(document).on("click", "#miAlerta [data-dismiss=\"modal\"], #miAlerta", function(e){
		e.preventDefault();
		if(!$(e.target).hasClass("close") && !$(e.target).hasClass("modal") && !$(e.target).hasClass("btn-close")) return;
		closeModalAlerta();
	});
	$(document).on("change", "#modal_empresa", function(e){
		e.preventDefault();
		const empresa_id = $(e.target).val();
		if(empresa_id === ''){
			$("#modal_contador").html(`<option value="">Seleccione el contador</option>`);
			$("#modal_conexion").html(`<option value="">Seleccione la connección base de datos</option>`);
			$("#modal_table").html(`<option value="">Seleccione la tabla</option>`);
			$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		}else{
			$.getJSON(`/api/enterprices/${empresa_id}/meters`, function(data){
				if(data && data.length > 0){
					data.map(item => {
						$("#modal_contador").append(`
							<option value="${item.id}">${item.name}</option>
						`);
					});
				}
			});
		}
	});
	$(document).on("change", "#modal_contador", function(e){
		e.preventDefault();
		const contador_id = $(e.target).val();
		if(contador_id === ''){
			$("#modal_conexion").html(`<option value="">Seleccione la connección base de datos</option>`);
			$("#modal_table").html(`<option value="">Seleccione la tabla</option>`);
			$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		}else{
			/*
			$.getJSON(`/produccion/connections/${contador_id}`, function(data){
				if(data && data.length > 0){
					data.map(item => {
						$("#modal_conexion").append(`
							<option value="${item.id}" data-tables='${JSON.stringify(item.tables)}'>${item.name}</option>
						`);
					});
				}
			});
			*/
			$.getJSON(`/buscar_alertas_bd/${contador_id}`, function(data){
				if(data && data.length > 0){
					data.map(item => {
						$("#modal_conexion").append(`
							<option value="${item.id}" data-conexion='${JSON.stringify(item)}'>${item.name}</option>
						`);
					});
				}
			});
		}
	});
	$(document).on("change", "#modal_conexion", function(e){
		e.preventDefault();
		const conexion_id = $(e.target).val();
		$("#modal_table").html(`<option value="">Seleccione la tabla</option>`);
		$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		if(conexion_id !== ''){
			const conexion_tables = $("option:selected", $(e.target)).attr("data-conexion");
			const conexion_json = conexion_tables !== '' ? JSON.parse(conexion_tables) : {};
			if(conexion_json.id){
				axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
				axios.defaults.headers.common['X-CSRF-TOKEN'] = $("#form-nuevas-alertas input[name=\"_token\"]").val();
				axios.post('/buscar_alertas_tablas', conexion_json).then(res => {
					if([200, 201].includes(res.status)){
						res.data.tables.map( item => {
							$("#modal_table").append(`
								<option value="${item.name}" data-fields='${JSON.stringify(item.fields)}'>${item.name}</option>
							`);
						});
					}
				});
			}
		}
		/*
		if(conexion_id === ''){
			$("#modal_table").html(`<option value="">Seleccione la tabla</option>`);
			$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		}else if(conexion_tables !== ''){
			const _tables = JSON.parse(conexion_tables);
			_tables.map( item => {
				$("#modal_table").append(`
					<option value="${item.name}" data-fields='${JSON.stringify(item.fields)}'>${item.name}</option>
				`);
			});
		}
		*/
	});
	$(document).on("change", "#modal_table", function(e){
		e.preventDefault();
		const table_name = $(e.target).val();
		const table_fields = $("option:selected", $(e.target)).attr("data-fields");
		if(table_name === ''){
			$("#modal_field").html(`<option value="">Seleccione el campo</option>`);
		}else if(table_fields !== ''){
			const _fields = JSON.parse(table_fields);
			_fields.map( item => {
				$("#modal_field").append(`
					<option value="${item.name}">${item.name}</option>
				`);
			});
		}
	});
	$(document).on("click", "#btnInsertarNuevaLinea", function(e){
		e.preventDefault();
		let errors = 0;
		if($("#modal_empresa").val()===""){
			$("#modal_empresa").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_contador").val()===""){
			$("#modal_contador").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_conexion").val()===""){
			$("#modal_conexion").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_table").val()===""){
			$("#modal_table").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_field").val()===""){
			$("#modal_field").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_condicion").val()===""){
			$("#modal_condicion").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}
		if($("#modal_limite").val()===""){
			$("#modal_limite").after(`
				<div class="error" style="color: rgb(208, 35, 35); font-size: 13px;">Este campo es obligatorio</div>
			`);
			errors++;
		}

		if(errors > 0) return;

		$("#tblAlertas tbody .tr-empty").remove();
		const current_item = $("#tblAlertas tbody tr").length;
		const bdJson = {
			enterprise_id: $("#modal_empresa").val(),
			enterprise_name: $("option:selected", $("#modal_empresa")).text(),
			meter_id: $("#modal_contador").val(),
			meter_name: $("option:selected", $("#modal_contador")).text(),
			conection_id: $("#modal_conexion").val(), 
			conection_name: $("option:selected", $("#modal_conexion")).text(), 
			table_name: $("#modal_table").val(), 
			field_name: $("#modal_field").val(), 
			condition: $("#modal_condicion").val(), 
			limit: $("#modal_limite").val()
		};
		const currentDate = new Date();
		const default_alert_name = `Alerta ${currentDate.getFullYear()}-${currentDate.getMonth()+1}-${currentDate.getDate()}`;

		let payload = {
			contador: contador_label,
			conexion: JSON.stringify(bdJson), 
			frecuencia_mes: "0", 
			frecuencia_dia: "0", 
			avisos: "1", 
			nombre_alerta: default_alert_name, 
			destinatarios: '', 
			activado: false
		};
		let url_endpoint = `/nuevas_alertas/${alerta_user_id}`;
		if(modal_cache && modal_cache.alerta_id){
			payload = {
				conexion: JSON.stringify(bdJson)
			};
			url_endpoint = `/actualizar_alertas/${modal_cache.alerta_id}`;
		}
		axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
		axios.defaults.headers.common['X-CSRF-TOKEN'] = $("#form-nuevas-alertas input[name=\"_token\"]").val();
		axios.post(url_endpoint, payload).then(res => {
			closeModalAlerta();
			window.top.location.reload();
		});
	});
	$(document).on("click", "button[name=\"btnGuardarAlerta\"]", function(e){
		e.preventDefault();
		$(`.alert-danger`).removeClass('alert-danger');

		const rowTR = $(e.target).parent().parent();
		const alerta_id = rowTR.attr("data-id");

		const currentDate = new Date();
		const default_alert_name = `Alerta ${currentDate.getFullYear()}-${currentDate.getMonth()+1}-${currentDate.getDate()}`;
		const payload = {
			conexion: rowTR.find(`[name="conexion[${alerta_id}]"]`).val(), 
			frecuencia_mes: rowTR.find(`input[name="frecuencia_mes[${alerta_id}]"]`).val(), 
			frecuencia_dia: rowTR.find(`input[name="frecuencia_dia[${alerta_id}]"]`).val(), 
			avisos: rowTR.find(`[name="avisos[${alerta_id}]"]`).val(),
			nombre_alerta: rowTR.find(`[name="nombre_alerta[${alerta_id}]"]`).val(),
			destinatarios: rowTR.find(`[name="destinatarios[${alerta_id}]"]`).val(), 
			activado: rowTR.find(`[name="activado[${alerta_id}]"]`).is(":checked")
		};

		if(payload.nombre_alerta === ''){
			rowTR.find(`[name="nombre_alerta[${alerta_id}]"]`).addClass('alert-danger');
			return;
		}
		if(!customIsValidEmailList(payload.destinatarios)){
			rowTR.find(`[name="destinatarios[${alerta_id}]"]`).addClass('alert-danger');
			return;
		}

		if(payload.nombre_alerta === '') 
			payload.nombre_alerta = default_alert_name;
		axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
		axios.defaults.headers.common['X-CSRF-TOKEN'] = rowTR.find("form input[name=\"_token\"]").val();
		axios.post(`/actualizar_alertas/${alerta_id}`, payload).then(res => {
			window.top.location.reload();
		});
	});
	$(document).on("click", "button[name=\"btnBorrarAlerta\"]", function(e){
		e.preventDefault();
		const rowTR = $(e.target).parent().parent();
		const alerta_id = rowTR.attr("data-id");

		if(confirm("¿Deseas borrar la alerta?")){
			axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
			axios.defaults.headers.common['X-CSRF-TOKEN'] = rowTR.find("form input[name=\"_token\"]").val();
			axios.post(`/eliminar_alertas/${alerta_id}`).then(res => {
				window.top.location.reload();
			});
		}
	});
	$(document).on("click", "button[name=\"btnEditarAlerta\"]", function(e){
		e.preventDefault();
		const rowTR = $(e.target).parent().parent();
		const alerta_id = rowTR.attr("data-id");
		const conexion_data = rowTR.find(`[name="conexion[${alerta_id}]"]`).val();
		openModalAlerta({
			alerta_id: alerta_id, 
			conexion_data: JSON.parse(conexion_data)
		});
	});
</script>