<?php

use \Calctool\Models\Project;
use \Calctool\Models\ProductGroup;
use \Calctool\Models\ProductCategory;
use \Calctool\Models\ProductSubCategory;
use \Calctool\Models\Chapter;
use \Calctool\Calculus\CalculationOverview;
use \Calctool\Models\ProjectType;
use \Calctool\Models\Activity as ProjectActivity;
use \Calctool\Models\FavoriteActivity;
use \Calctool\Models\PartType;
use \Calctool\Models\Part;
use \Calctool\Calculus\CalculationEndresult;
use \Calctool\Models\Tax;
use \Calctool\Models\CalculationLabor;
use \Calctool\Calculus\CalculationRegister;
use \Calctool\Models\CalculationMaterial;
use \Calctool\Models\CalculationEquipment;
use \Calctool\Models\EstimateLabor;
use \Calctool\Models\EstimateMaterial;
use \Calctool\Models\EstimateEquipment;

$common_access_error = false;
$project = Project::find(Route::Input('project_id'));
if (!$project || !$project->isOwner())
	$common_access_error = true;
?>

@extends('layout.master')

@section('title', 'Calculeren')

@push('style')
@endpush

@push('scripts')
<script src="/plugins/summernote/summernote.min.js"></script>
<script src="/plugins/jquery.number.min.js"></script>
@endpush

<?php if($common_access_error){ ?>
@section('content')
<div id="wrapper">
	<section class="container">
		<div class="alert alert-danger">
			<i class="fa fa-frown-o"></i>
			<strong>Fout</strong>
			Dit project bestaat niet
		</div>
	</section>
</div>
@stop
<?php }else{ ?>

@section('content')
<script type="text/javascript">
	Number.prototype.formatMoney = function(c, d, t){
	var n = this,
	    c = isNaN(c = Math.abs(c)) ? 2 : c,
	    d = d == undefined ? "." : d,
	    t = t == undefined ? "," : t,
	    s = n < 0 ? "-" : "",
	    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
	    j = (j = i.length) > 3 ? j % 3 : 0;
	   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	};
	$(document).ready(function() {

        $("body").on("change", ".form-control-sm-number", function(){
            $(this).val(parseFloat($(this).val().split('.').join('').replace(',', '.')).formatMoney(2, ',', '.'));
        });

		var $newinputtr;
		$('.toggle').click(function(e){
			$id = $(this).attr('id');
			if ($(this).hasClass('active')) {
				if (sessionStorage.toggleOpen{{Auth::id()}}){
					$toggleOpen = JSON.parse(sessionStorage.toggleOpen{{Auth::id()}});
				} else {
					$toggleOpen = [];
				}
				if (!$toggleOpen.length)
					$toggleOpen.push($id);
				for(var i in $toggleOpen){
					if ($toggleOpen.indexOf( $id ) == -1)
						$toggleOpen.push($id);
				}
				sessionStorage.toggleOpen{{Auth::id()}} = JSON.stringify($toggleOpen);
			} else {
				$tmpOpen = [];
				if (sessionStorage.toggleOpen{{Auth::id()}}){
					$toggleOpen = JSON.parse(sessionStorage.toggleOpen{{Auth::id()}});
					for(var i in $toggleOpen){
						if($toggleOpen[i] != $id)
							$tmpOpen.push($toggleOpen[i]);
					}
				}
				sessionStorage.toggleOpen{{Auth::id()}} = JSON.stringify($tmpOpen);
			}
		});
		if (sessionStorage.toggleOpen{{Auth::id()}}){
			$toggleOpen = JSON.parse(sessionStorage.toggleOpen{{Auth::id()}});
			for(var i in $toggleOpen){
				$('#'+$toggleOpen[i]).addClass('active').children('.toggle-content').toggle();
			}
		}
		$('#tab-calculate').click(function(e){
			sessionStorage.toggleTabCalc{{Auth::id()}} = 'calculate';
		});
		$('#tab-estimate').click(function(e){
			sessionStorage.toggleTabCalc{{Auth::id()}} = 'estimate';
		});
		$('#tab-summary').click(function(e){
			sessionStorage.toggleTabCalc{{Auth::id()}} = 'summary';
			$('#summary').load('summary/project-{{ $project->id }}');
		});
		$('#tab-endresult').click(function(e){
			sessionStorage.toggleTabCalc{{Auth::id()}} = 'endresult';
			$('#endresult').load('endresult/project-{{ $project->id }}');
		});
		if (sessionStorage.toggleTabCalc{{Auth::id()}}){
			$toggleOpenTab = sessionStorage.toggleTabCalc{{Auth::id()}};
			$('#tab-'+$toggleOpenTab).addClass('active');
			$('#'+$toggleOpenTab).addClass('active');
			$('#tab-'+$toggleOpenTab).trigger("click");
		} else {
			sessionStorage.toggleTabCalc{{Auth::id()}} = 'calculate';
			$('#tab-calculate').addClass('active');
			$('#calculate').addClass('active');
		}
		$(".complete").click(function(e){
			$loc = $(this).attr('data-location');
			window.location.href = $loc;
		});
		$("body").on("change", ".form-control-sm-number", function(){
			$(this).val(parseFloat($(this).val().split('.').join('').replace(',', '.')).formatMoney(2, ',', '.'));
		});
		$(".radio-activity").change(function(){
			if ($(this).val()==2) {
				$(this).closest('.toggle-content').find(".rate").html('<input name="rate" type="text" value="{{ number_format($project->hour_rate, 2,",",".") }}" class="form-control-sm-number labor-amount lsave">');
			} else {
				$(this).closest('.toggle-content').find(".rate").text('{{ number_format($project->hour_rate, 2,",",".") }}');
			}
			$.post("/calculation/updatepart", {project: {{ $project->id }}, value: this.value, activity: $(this).attr("data-id")}).fail(function(e) { console.log(e); });
		});
		$(".select-tax").change(function(){
			$.post("/calculation/updatetax", {project: {{ $project->id }}, value: this.value, activity: $(this).attr("data-id"), type: $(this).attr("data-type")}).fail(function(e) { console.log(e); });
		});
		$(".select-estim-tax").change(function(){
			$.post("/calculation/updateestimatetax", {project: {{ $project->id }}, value: this.value, activity: $(this).attr("data-id"), type: $(this).attr("data-type")}).fail(function(e) { console.log(e); });
		});
		$("body").on("change", ".newrow", function(){
			var i = 1;
			if($(this).val()){
				if(!$(this).closest("tr").next().length){
					var $curTable = $(this).closest("table");
					$curTable.find("tr:eq(1)").clone().removeAttr("data-id").find("input").each(function(){
						$(this).val("").removeClass("error-input").attr("id", function(_, id){ return id + i });
					}).end().find(".total-ex-tax, .total-incl-tax").text("").end().appendTo($curTable);
					$("button[data-target='#myModal']").on("click", function() {
						$newinputtr = $(this).closest("tr");
					});
					i++;
				}
			}
		});
		$("body").on("change", ".dsave", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/calc/updatematerial", {
					id: $curThis.closest("tr").attr("data-id"),
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("change", ".esave", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/calc/updateequipment", {
					id: $curThis.closest("tr").attr("data-id"),
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);app/views/calc/more_closed.blade.php
				});
			}
		});
		$("body").on("change", ".lsave", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/calc/updatelabor", {
					id: $curThis.closest("tr").attr("data-id"),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val()
						if (rate) {
							rate = rate.toString().split('.').join('').replace(',', '.');
						} else {
							rate = {{ $project->hour_rate }};
						}
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".lsave", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/calc/newlabor", {
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val()
						if (rate) {
							rate.toString().split('.').join('').replace(',', '.');
						} else {
							rate = {{$project->hour_rate}};
						}
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".dsave", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/calc/newmaterial", {
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".esave", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/calc/newequipment", {
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("change", ".dsavee", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/estim/updatematerial", {
					id: $curThis.closest("tr").attr("data-id"),
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("change", ".esavee", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/estim/updateequipment", {
					id: $curThis.closest("tr").attr("data-id"),
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("change", ".lsavee", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id")){
				$.post("/calculation/estim/updatelabor", {
					id: $curThis.closest("tr").attr("data-id"),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val()
						if (rate) {
							rate = rate.toString().split('.').join('').replace(',', '.');
						} else {
							rate = {{$project->hour_rate}};
						}
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".lsavee", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/estim/newlabor", {
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val()
						if (rate) {
							rate = rate.toString().split('.').join('').replace(',', '.');
						} else {
							rate = {{$project->hour_rate}};
						}
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".dsavee", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/estim/newmaterial", {
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("blur", ".esavee", function(){
			var flag = true;
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				return false;
			$curThis.closest("tr").find("input").each(function(){
				if(!$(this).val())
					flag = false;
			});
			if(flag){
				$.post("/calculation/estim/newequipment", {
					name: $curThis.closest("tr").find("input[name='name']").val(),
					unit: $curThis.closest("tr").find("input[name='unit']").val(),
					rate: $curThis.closest("tr").find("input[name='rate']").val(),
					amount: $curThis.closest("tr").find("input[name='amount']").val(),
					activity: $curThis.closest("table").attr("data-id"),
					project: {{ $project->id }},
				}, function(data){
					var json = data;
					$curThis.closest("tr").find("input").removeClass("error-input");
					if (json.success) {
						$curThis.closest("tr").attr("data-id", json.id);
						var rate = $curThis.closest("tr").find("input[name='rate']").val().toString().split('.').join('').replace(',', '.');
						var amount = $curThis.closest("tr").find("input[name='amount']").val().toString().split('.').join('').replace(',', '.');
						var profit = $curThis.closest("tr").find('td[data-profit]').data('profit');
						$curThis.closest("tr").find(".total-ex-tax").text('€ '+$.number(rate*amount,2,',','.'));
						$curThis.closest("tr").find(".total-incl-tax").text('€ '+$.number(rate*amount*((100+profit)/100),2,',','.'));
						var sub_total = 0;
						$curThis.closest("tbody").find(".total-ex-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
						var sub_total_profit = 0;
						$curThis.closest("tbody").find(".total-incl-tax").each(function(index){
							var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
							if (_cal)
								sub_total_profit += _cal;
						});
						$curThis.closest("table").find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
					} else {
						$.each(json.message, function(i, item) {
							if(json.message['name'])
								$curThis.closest("tr").find("input[name='name']").addClass("error-input");
							if(json.message['unit'])
								$curThis.closest("tr").find("input[name='unit']").addClass("error-input");
							if(json.message['rate'])
								$curThis.closest("tr").find("input[name='rate']").addClass("error-input");
							if(json.message['amount'])
								$curThis.closest("tr").find("input[name='amount']").addClass("error-input");
						});
					}
				}).fail(function(e){
					console.log(e);
				});
			}
		});
		$("body").on("click", ".sdeleterow", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/calc/deletematerial", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					var body = $curThis.closest("tbody");
					var table = $curThis.closest("table");
					$curThis.closest("tr").remove();
					var sub_total = 0;
					body.find(".total-ex-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total += _cal;
					});
					table.find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
					var sub_total_profit = 0;
					body.find(".total-incl-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total_profit += _cal;
					});
					table.find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".edeleterow", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/calc/deleteequipment", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					var body = $curThis.closest("tbody");
					var table = $curThis.closest("table");
					$curThis.closest("tr").remove();
					var sub_total = 0;
					body.find(".total-ex-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total += _cal;
					});
					table.find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
					var sub_total_profit = 0;
					body.find(".total-incl-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total_profit += _cal;
					});
					table.find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".sdeleterowe", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/estim/deletematerial", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					var body = $curThis.closest("tbody");
					var table = $curThis.closest("table");
					$curThis.closest("tr").remove();
					var sub_total = 0;
					body.find(".total-ex-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total += _cal;
					});
					table.find('.mat_subtotal').text('€ '+$.number(sub_total,2,',','.'));
					var sub_total_profit = 0;
					body.find(".total-incl-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total_profit += _cal;
					});
					table.find('.mat_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".edeleterowe", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/estim/deleteequipment", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					var body = $curThis.closest("tbody");
					var table = $curThis.closest("table");
					$curThis.closest("tr").remove();
					var sub_total = 0;
					body.find(".total-ex-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total += _cal;
					});
					table.find('.equip_subtotal').text('€ '+$.number(sub_total,2,',','.'));
					var sub_total_profit = 0;
					body.find(".total-incl-tax").each(function(index){
						var _cal = parseFloat($(this).text().substring(2).split('.').join('').replace(',', '.'));
						if (_cal)
							sub_total_profit += _cal;
					});
					table.find('.equip_subtotal_profit').text('€ '+$.number(sub_total_profit,2,',','.'));
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".ldeleterow", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/calc/deletelabor", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					$curThis.closest("tr").find("input").val("0,00");
					$curThis.closest("tr").find(".total-ex-tax").text('€ 0,00');
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".ldeleterowe", function(){
			var $curThis = $(this);
			if($curThis.closest("tr").attr("data-id"))
				$.post("/calculation/estim/deletelabor", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
					$curThis.closest("tr").find("input").val("0,00");
					$curThis.closest("tr").find(".total-ex-tax").text('€ 0,00');
				}).fail(function(e) { console.log(e); });
		});
		$("body").on("click", ".deleteact", function(e){
			e.preventDefault();
			if(confirm('Weet je het zeker?')){
				var $curThis = $(this);
				if($curThis.attr("data-id"))
					$.post("/calculation/deleteactivity", {project: {{ $project->id }}, activity: $curThis.attr("data-id")}, function(){
						$('#toggle-activity-'+$curThis.attr("data-id")).hide('slow');
					}).fail(function(e) { console.log(e); });
			}
		});
		$("body").on("click", ".deletechap", function(e){
			e.preventDefault();
			if(confirm('Het verwijderen van een onderdeel verwijdert al je werkzaamheden onder het onderdeel, zowel onder Calculatie als onder Stelposten (indien gebruikt).\n\nWilt u het onderdeel toch verwijderen?')){
				var $curThis = $(this);
				if($curThis.attr("data-id"))
					$.post("/calculation/deletechapter", {project: {{ $project->id }}, chapter: $curThis.attr("data-id")}, function(){
						$curThis.closest('.toggle-chapter').hide('slow');
					}).fail(function(e) { console.log(e); });
			}
		});
		$("body").on("click", ".lsavefav", function(e){
			e.preventDefault();
			var $curThis = $(this);
			if ($curThis.attr("data-id")) {
				$.post("/calculation/calc/savefav", {project: {{ $project->id }}, activity: $curThis.attr("data-id")}, function(){
					$curThis.text('Opgeslagen als favoriet');
				}).fail(function(e) { console.log(e); });
			}
		});

		$("body").on("click", ".esavefav", function(e){
			e.preventDefault();
			var $curThis = $(this);
			if ($curThis.attr("data-id")) {
				$.post("/calculation/estim/savefav", {project: {{ $project->id }}, activity: $curThis.attr("data-id")}, function(){
					$curThis.text('Opgeslagen als favoriet');
				}).fail(function(e) { console.log(e); });
			}
		});

		var $favchapid;
		var $faviscalc;
		$('.lfavselect').click(function(e) {
			$favchapid = $(this).attr('data-id');
			$faviscalc = true;
		});

		$('.efavselect').click(function(e) {
			$favchapid = $(this).attr('data-id');
			$faviscalc = false;
		});

		$('.favlink').click(function(e) {
			if ($faviscalc)
				window.location.href = '/calculation/project-{{ $project->id }}/chapter-' + $favchapid + '/fav-' + $(this).attr('data-id');
			else
				window.location.href = '/estimate/project-{{ $project->id }}/chapter-' + $favchapid + '/fav-' + $(this).attr('data-id');
		});

		$('.changename').click(function(e) {
			$activityid = $(this).attr('data-id');
			$activity_name = $(this).attr('data-name');
			$('#nc_activity').val($activityid);
			$('#nc_activity_name').val($activity_name);
		});

		$req = false;
		$("#search").keyup(function() {
			$val = $(this).val();
			if ($val.length > 2 && !$req) {
				$req = true;
				$.post("/material/search", {project: {{ $project->id }}, query: $val}, function(data) {
					if (data) {
						$('#tbl-material tbody tr').remove();
						$.each(data, function(i, item) {
							$('#tbl-material tbody').append('<tr><td><a data-name="'+item.description+'" data-unit="'+item.punit+'" data-price="'+item.pricenum+'" href="javascript:void(0);">'+item.description+'</a></td><td>'+item.unit+'</td><td>'+item.price+'</td><td>'+item.tprice+'</td></tr>');
						});
						$('#tbl-material tbody a').on("click", onmaterialclick);
						$req = false;
					}
				});
			}
		});

		$('#group').change(function(){
			$val = $(this).val();
			$.post("/material/search", {group:$val}, function(data) {
				if (data) {
					$('#tbl-material tbody tr').remove();
					$.each(data, function(i, item) {
						$('#tbl-material tbody').append('<tr><td><a data-name="'+item.description+'" data-unit="'+item.punit+'" data-price="'+item.pricenum+'" href="javascript:void(0);">'+item.description+'</a></td><td>'+item.unit+'</td><td>'+item.price+'</td><td>'+item.tprice+'</td></tr>');
					});
					$('#tbl-material tbody a').on("click", onmaterialclick);
					$req = false;
				}
			});
		});

		$('.getsub').change(function(e){
			var $name = $('#group2 option:selected').attr('data-name');
			var $value = $('#group2 option:selected').val();

			$.get('/material/subcat/' + $name + '/' + $value, function(data) {
				$('#group').find('option').remove();
			    $.each(data, function(idx, item){
				    $('#group').append($('<option>', {
				        value: item.id,
				        text: item.name
				    }));
			    });

				$.post("/material/search", {group:data[0].id}, function(data) {
					if (data) {
						$('#tbl-material tbody tr').remove();
						$.each(data, function(i, item) {
							$('#tbl-material tbody').append('<tr><td><a data-name="'+item.description+'" data-unit="'+item.punit+'" data-price="'+item.pricenum+'" href="javascript:void(0);">'+item.description+'</a></td><td>'+item.unit+'</td><td>'+item.price+'</td><td>'+item.tprice+'</td></tr>');
						});
						$('#tbl-material tbody a').on("click", onmaterialclick);
						$req = false;
					}
				});
			    
			});

		});

		$("button[data-target='#myModal']").click(function(e) {
			$newinputtr = $(this).closest("tr");
		});
		function onmaterialclick(e) {
			$newinputtr.find("input[name='name']").val($(this).attr('data-name'));
			$newinputtr.find("input[name='unit']").val($(this).attr('data-unit'));
			$newinputtr.find("input[name='rate']").val($(this).attr('data-price'));
			$newinputtr.find(".newrow").change();
			$('#myModal').modal('toggle');
		}
		var $notecurr;
		$('.notemod').click(function(e) {
			$notecurr = $(this);
			$curval = $(this).attr('data-note');
			$curid = $(this).attr('data-id');
			$('.summernote').code($curval);
			$('#noteact').val($curid);
		});
		$('#descModal').on('hidden.bs.modal', function() {
			$.post("/calculation/noteactivity", {project: {{ $project->id }}, activity: $('#noteact').val(), note: $('.summernote').code()}, function(){
				$notecurr.attr('data-note', $('.summernote').code());
				$('.summernote').code('');
			}).fail(function(e) { console.log(e); });
		});

        $('.summernote').summernote({
            height: $(this).attr("data-height") || 200,
            toolbar: [
                ["style", ["bold", "italic", "underline", "strikethrough", "clear"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["media", ["link", "picture"]],
            ]
        });
	});

</script>
<div class="modal fade" id="nameChangeModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST" action="/calculation/calc/rename_activity" accept-charset="UTF-8">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel2">Naam werkzaamheid</h4>
			</div>

			<div class="modal-body">
				<div class="form-horizontal">
					{!! csrf_field() !!}
					<div class="form-group">
						<div class="col-md-4">
							<label>Naam</label>
						</div>
						<div class="col-md-12">
							<input value="" name="activity_name" id="nc_activity_name" class="form-control" />
							<input value="" name="activity" id="nc_activity" type="hidden" class="form-control" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default">Opslaan</button>
			</div>
		</div>
		</form>
	</div>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">Producten</h4>
			</div>

			<div class="modal-body">
				
			          <div class="form-group input-group input-group-lg">
			            <input type="text" id="search" value="" class="form-control" placeholder="Zoek in volledig lijst">
			              <span class="input-group-btn">
			                  <select id="group2" class="btn getsub" style="background-color: #E5E7E9; color:#000">
			                    <option value="0" selected>of sorteer op categrie</option>
			                    @foreach (ProductGroup::all() as $group)
			                    <option data-name="group" value="{{ $group->id }}">{{ $group->group_name }}</option>
			                      @foreach (ProductCategory::where('group_id', $group->id)->get() as $cat)
			                      <option data-name="cat" value="{{ $cat->id }}"> - {{ $cat->category_name }}</option>
			                      @endforeach
			                    @endforeach
			                  </select>
			                </span>
			                <span class="input-group-btn">
			                  <select id="group" class="btn" style="background-color: #E5E7E9; color:#000">
			                  <option value="0" selected>en subcategrie</option>
			                  @foreach (ProductSubCategory::all() as $subcat)
			                    <option value="{{ $subcat->id }}">{{ $subcat->sub_category_name }}</option>
			                  @endforeach
			                  </select>
			                </span>
			          </div>
			          <div class="table-responsive">
			            <table id="tbl-material" class="table table-hover">
			              <thead>
			                <tr>
			                  <th>Omschrijving</th>
			                  <th>Eenheid</th>
			                  <th>Prijs per eenheid</th>
			                  <th>Totaalprijs</th>
			                </tr>
			              </thead>
			              <tbody>
						</tbody>
					</table>
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>

		</div>
	</div>
</div>
<div class="modal fade" id="myFavAct" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">Favoriete werkzaamheden</h4>
			</div>

			<div class="modal-body">
				
			          <div class="table-responsive">
			            <table class="table table-hover">
			              <thead>
			                <tr>
			                  <th>Omschrijving</th>
			                  <th class="text-right">Aangemaakt</th>
			                </tr>
			              </thead>
			              <tbody>
			              	@foreach (FavoriteActivity::where('user_id', Auth::id())->orderBy('created_at')->get() as $favact)	
			              	<tr>
			              		<td><a class="favlink" href="#" data-id="{{ $favact->id }}">{{ $favact->activity_name }}</a></td>
			              		<td class="text-right">{{ $favact->created_at->toDateString() }}</td>
			              	</tr>
			              	@endforeach
						  </tbody>
					</table>
					<!--<input type="hidden" name="noteact" id="favact" />-->
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>

		</div>
	</div>
</div>
<div class="modal fade" id="descModal" tabindex="-1" role="dialog" aria-labelledby="descModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">Omschrijving werkzaamheid</h4>
			</div>
			
			<div class="modal-body">
				<div class="form-group">
					<div class="col-md-12">
						<textarea name="note" id="note" rows="5" class="form-control summernote"></textarea>
						<input type="hidden" name="noteact" id="noteact" />
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>

		</div>
	</div>
</div>

<div class="modal fade" id="myYouTube" tabindex="-1" role="dialog" aria-labelledby="mYouTubeLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<iframe width="1280" height="720" src="https://www.youtube.com/embed/Pm3vU0ropSE?rel=0;" frameborder="0" allowfullscreen></iframe>

				<div class="form-horizontal">


				</div>
			</div>

		</div>
	</div>
</div>

<div id="wrapper">

	<section class="container fix-footer-bottom">

		@include('calc.wizard', array('page' => 'calculation'))

			<h2><strong>Calculeren</strong>&nbsp;<a class="fa fa-youtube-play" href="javascript:void(0);" data-toggle="modal" data-target="#myYouTube"></a></h2>

			<div class="tabs nomargin" ng-controller="SummaryCtrl">

				<ul class="nav nav-tabs">
					<li id="tab-calculate">
						<a href="#calculate" data-toggle="tab">
							<i class="fa fa-list"></i> Calculatie
						</a>
					</li>
					@if ($project->use_estimate)
					<li id="tab-estimate">
						<a href="#estimate" data-toggle="tab">
							<i class="fa fa-align-justify"></i> Stelposten
						</a>
					</li>
					@endif
					<li data-step="5" data-intro="Bekijk na het invullen van al je onderdelen & werkzaamheden de uittrekstaat van al je werkzaamheden." id="tab-summary">
						<a href="#summary" data-toggle="tab">
							<i class="fa fa-sort-amount-asc"></i> Uittrekstaat Calculeren
						</a>
					</li>
					<li data-step="6" data-intro="Bekijk het eindresultaat." id="tab-endresult">
						<a href="#endresult" data-toggle="tab">
							<i class="fa fa-check-circle-o"></i> Eindresultaat Calculeren
						</a>
					</li>
				</ul>

				<div class="tab-content">
					<div id="calculate" class="tab-pane">
						<div class="toogle">
							@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
							<div data-step="2" data-intro="Open het onderdeel door op de regel te klikken." id="toggle-chapter-{{ $chapter->id }}" class="toggle toggle-chapter">
								<label>{{ $chapter->chapter_name }}</label>
								<div data-step="3" data-intro="Maak een werkzaamheid aan, open deze door op de regel te klikken en druk daarna op volgende." class="toggle-content">

									<div class="toogle">
										<?php
										$activity_total = 0;
										foreach(ProjectActivity::where('chapter_id','=', $chapter->id)->whereNull('detail_id')->where('part_type_id','=',PartType::where('type_name','=','calculation')->first()->id)->orderBy('created_at')->get() as $activity) {
											if (Part::find($activity->part_id)->part_name=='contracting') {
												$profit_mat = $project->profit_calc_contr_mat;
												$profit_equip = $project->profit_calc_contr_equip;
												$activity_total = CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_contr_mat, $project->profit_calc_contr_equip);
											} else if (Part::find($activity->part_id)->part_name=='subcontracting') {
												$profit_mat = $project->profit_calc_subcontr_mat;
												$profit_equip = $project->profit_calc_subcontr_equip;
												$activity_total = CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_subcontr_mat, $project->profit_calc_subcontr_equip);
											}
										?>
										<div id="toggle-activity-{{ $activity->id }}" class="toggle toggle-activity">
											<label>
												<span>{{ $activity->activity_name }}</span>
												<span style="float: right;margin-right: 30px;">{{ '&euro; '.number_format($activity_total, 2, ",",".") }}</span>
											</label>
											<div data-step="4" data-intro="Calculeer de werkzaaheid door de regel die jij nodig hebt in te vullen" class="toggle-content">
												<div class="row">
													<div class="col-md-6">
														@if ($project->use_subcontract)
														<label class="radio-inline"><input data-id="{{ $activity->id }}" class="radio-activity" name="soort{{ $activity->id }}" value="{{ Part::where('part_name','=','contracting')->first()->id }}" type="radio" {{ ( Part::find($activity->part_id)->part_name=='contracting' ? 'checked' : '') }}/>Aanneming</label>
	    												<label class="radio-inline"><input data-id="{{ $activity->id }}" class="radio-activity" name="soort{{ $activity->id }}" value="{{ Part::where('part_name','=','subcontracting')->first()->id }}" type="radio" {{ ( Part::find($activity->part_id)->part_name=='subcontracting' ? 'checked' : '') }}/>Onderaanneming</label>
														@endif
													</div>
													
													<div class="col-md-6 text-right">
														<button id="pop-{{$chapter->id.'-'.$activity->id}}" data-id="{{ $activity->id }}" data-note="{{ $activity->note }}" data-toggle="modal" data-target="#descModal" class="btn btn-info btn-xs notemod">Omschrijving</button>
														<div class="btn-group" role="group">
														  <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Werkzaamheid&nbsp;&nbsp;<span class="caret"></span></button>
														  <ul class="dropdown-menu">
														    <li><a href="#" data-id="{{ $activity->id }}" class="lsavefav">Opslaan als favoriet</a></li>
														    <li><a href="#" data-id="{{ $activity->id }}" data-name="{{ $activity->activity_name }}" data-toggle="modal" data-target="#nameChangeModal" class="changename">Naam wijzigen</a></li>
														    <li><a href="#" data-id="{{ $activity->id }}" class="deleteact">Verwijderen</a></li>
														  </ul>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-md-2"><h4>Arbeid</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-labor" id="type" class="form-control-sm-text pointer select-tax">
															@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_labor_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
															@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">
													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">&nbsp;</th>
															<th class="col-md-1">Uurtarief</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">&nbsp;</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>

													<tbody>
														<tr data-id="{{ CalculationLabor::where('activity_id','=', $activity->id)->first()['id'] }}">
															<td class="col-md-5">Arbeidsuren</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><span class="rate">{!! Part::find($activity->part_id)->part_name=='subcontracting' ? '<input name="rate" type="text" value="'.number_format(CalculationLabor::where('activity_id','=', $activity->id)->first()['rate'], 2,",",".").'" class="form-control-sm-number labor-amount lsave">' : number_format($project->hour_rate, 2,",",".") !!}</span></td>
															<td class="col-md-1"><input data-id="{{ $activity->id }}" name="amount" type="text" value="{{ number_format(CalculationLabor::where('activity_id','=', $activity->id)->first()['amount'], 2, ",",".") }}" class="form-control-sm-number labor-amount lsave" /></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format(CalculationRegister::calcLaborTotal(Part::find($activity->part_id)->part_name=='subcontracting' ? CalculationLabor::where('activity_id','=', $activity->id)->first()['rate'] : $project->hour_rate, CalculationLabor::where('activity_id','=', $activity->id)->first()['amount']), 2, ",",".") }}</span></td>
															<td class="col-md-1 text-right"><button class="btn btn-danger ldeleterow btn-xs fa fa-times"></button></td>
														</tr>
													</tbody>
												</table>

												<div class="row">
													<div class="col-md-2"><h4>Materiaal</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-material" id="type" class="form-control-sm-text pointer select-tax">
															@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_material_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
															@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">
													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">Eenheid</th>
															<th class="col-md-1">&euro; / Eenh.</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">+ Winst %</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>

													<tbody>
														@foreach (CalculationMaterial::where('activity_id','=', $activity->id)->get() as $material)
														<tr data-id="{{ $material->id }}">
															<td class="col-md-5"><input name="name" id="name" type="text" value="{{ $material->material_name }}" class="form-control-sm-text dsave newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" value="{{ $material->unit }}" class="form-control-sm-text dsave" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" value="{{ number_format($material->rate, 2,",",".") }}" class="form-control-sm-number dsave" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" value="{{ number_format($material->amount, 2,",",".") }}" class="form-control-sm-number dsave" /></td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format($material->rate*$material->amount, 2,",",".") }}</span></td>
															<td class="col-md-1"><span class="total-incl-tax">{{ '&euro; '.number_format($material->rate*$material->amount*((100+$profit_mat)/100), 2,",",".") }}</span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_mat }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs sdeleterow fa fa-times"></button>
															</td>
														</tr>
														@endforeach
														<tr>
															<td class="col-md-5"><input name="name" id="name" type="text" class="form-control-sm-text dsave newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" class="form-control-sm-text dsave" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" class="form-control-sm-number dsave" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" class="form-control-sm-number dsave" /></td>
															<td class="col-md-1"><span class="total-ex-tax"></span></td>
															<td class="col-md-1"><span class="total-incl-tax"></span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_mat }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs sdeleterow fa fa-times"></button>
															</td>
														</tr>
													</tbody>
													<tbody>
														<tr>
															<td class="col-md-5"><strong>Totaal</strong></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><strong class="mat_subtotal">{{ '&euro; '.number_format(CalculationRegister::calcMaterialTotal($activity->id, $profit_mat), 2, ",",".") }}</span></td>
															<td class="col-md-1"><strong class="mat_subtotal_profit">{{ '&euro; '.number_format(CalculationRegister::calcMaterialTotalProfit($activity->id, $profit_mat), 2, ",",".") }}</span></td>
															<td class="col-md-1">&nbsp;</td>
														</tr>
													</tbody>
												</table>
												
												@if ($project->use_equipment)
												<div class="row">
													<div class="col-md-2"><h4>Overig</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-equipment" id="type" class="form-control-sm-text pointer select-tax">
															@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_equipment_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
															@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">
													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">Eenheid</th>
															<th class="col-md-1">&euro; / Eenh.</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">+ Winst %</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>
													<tbody>
														@foreach (CalculationEquipment::where('activity_id','=', $activity->id)->get() as $equipment)
														<tr data-id="{{ $equipment->id }}">
															<td class="col-md-5"><input name="name" id="name" type="text" value="{{ $equipment->equipment_name }}" class="form-control-sm-text esave newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" value="{{ $equipment->unit }}" class="form-control-sm-text esave" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" value="{{ number_format($equipment->rate, 2,",",".") }}" class="form-control-sm-number esave" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" value="{{ number_format($equipment->amount, 2,",",".") }}" class="form-control-sm-number esave" /></td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format($equipment->rate*$equipment->amount, 2,",",".") }}</span></td>
															<td class="col-md-1"><span class="total-incl-tax">{{ '&euro; '.number_format($equipment->rate*$equipment->amount*((100+$profit_equip)/100), 2,",",".") }}</span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_equip }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs edeleterow fa fa-times"></button>
															</td>
														</tr>
														@endforeach
														<tr>
															<td class="col-md-5"><input name="name" id="name" type="text" class="form-control-sm-text esave newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" class="form-control-sm-text esave" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" class="form-control-sm-number esave" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" class="form-control-sm-number esave" /></td>
															<td class="col-md-1"><span class="total-ex-tax"></span></td>
															<td class="col-md-1"><span class="total-incl-tax"></span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_equip }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs edeleterow fa fa-times"></button>
															</td>
														</tr>
													</tbody>
													<tbody>
														<tr>
															<td class="col-md-5"><strong>Totaal</strong></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><strong class="equip_subtotal">{{ '&euro; '.number_format(CalculationRegister::calcEquipmentTotal($activity->id, $profit_equip), 2, ",",".") }}</span></td>
															<td class="col-md-1"><strong class="equip_subtotal_profit">{{ '&euro; '.number_format(CalculationRegister::calcEquipmentTotalProfit($activity->id, $profit_equip), 2, ",",".") }}</span></td>
															<td class="col-md-1">&nbsp;</td>
														</tr>
													</tbody>
												</table>
												@endif
											</div>
										</div>
										<?php } ?>
									</div>

									<form method="POST" action="/calculation/calc/newactivity/{{ $chapter->id }}" accept-charset="UTF-8">
                                    {!! csrf_field() !!}
									<div class="row">
										<div class="col-md-6">

											<div class="input-group">
												<input type="text" class="form-control" name="activity" id="activity" value="" placeholder="Nieuwe Werkzaamheid">
												<span class="input-group-btn">
													<button class="btn btn-primary btn-primary-activity">Voeg toe</button>
												</span>
											</div>
										</div>
										<div class="col-md-6 text-right">
											<button type="button" class="btn btn-primary lfavselect" data-id="{{ $chapter->id }}" data-toggle="modal" data-target="#myFavAct">Favoriet selecteren</button>
											<button data-id="{{ $chapter->id }}" class="btn btn-danger deletechap">Onderdeel verwijderen</button>
										</div>
									</div>
									</form>
								</div>
							</div>
							@endforeach
						</div>

						<form method="POST" action="/calculation/newchapter/{{ $project->id }}" accept-charset="UTF-8">
                            {!! csrf_field() !!}
						<div><hr></div>
						<div class="row">
							<div class="col-md-6">
								<div class="input-group" data-step="1" data-intro="Voeg een onderdeel toe. Een soort hoofdstuk waar je werkzaamheden onder vallen.">
									<input type="text" class="form-control" name="chapter" id="chapter" value="" placeholder="Nieuw onderdeel">
									<span class="input-group-btn">
										<button class="btn btn-primary btn-primary-chapter">Voeg toe</button>
									</span>
								</div>
							</div>
						</div>
						</form>
					</div>

					@if ($project->use_estimate)
					<div id="estimate" class="tab-pane">
						<div class="toogle">

							@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
							<div id="toggle-chapter-{{ $chapter->id }}" class="toggle toggle-chapter">
								<label>{{ $chapter->chapter_name }}</label>
								<div class="toggle-content">

									<div class="toogle">
										<?php
										$activity_total = 0;
										foreach(ProjectActivity::where('chapter_id','=', $chapter->id)->whereNull('detail_id')->where('part_type_id','=',PartType::where('type_name','=','estimate')->first()->id)->orderBy('created_at')->get() as $activity) {
											$profit_mat = 0;
											if (Part::find($activity->part_id)->part_name=='contracting') {
												$profit_mat = $project->profit_calc_contr_mat;
												$profit_equip = $project->profit_calc_contr_equip;
												$activity_total = CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_contr_mat, $project->profit_calc_contr_equip);
											} else if (Part::find($activity->part_id)->part_name=='subcontracting') {
												$profit_mat = $project->profit_calc_subcontr_mat;
												$profit_equip = $project->profit_calc_subcontr_equip;
												$activity_total = CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_subcontr_mat, $project->profit_calc_subcontr_equip);
											}
										?>
										<div id="toggle-activity-{{ $activity->id }}" class="toggle toggle-activity">
											<label>
												<span>{{ $activity->activity_name }}</span>
												<span style="float: right;margin-right: 30px;">{{ '&euro; '.number_format($activity_total, 2, ",",".") }}</span>
											</label>
											<div class="toggle-content">
												<div class="row">
													<div class="col-md-6">
														@if ($project->use_subcontract)
														<label class="radio-inline"><input data-id="{{ $activity->id }}" class="radio-activity" name="soorte{{ $activity->id }}" value="{{ Part::where('part_name','=','contracting')->first()->id }}" type="radio" {{ ( Part::find($activity->part_id)->part_name=='contracting' ? 'checked' : '') }}/>Aanneming</label>
		    											<label class="radio-inline"><input data-id="{{ $activity->id }}" class="radio-activity" name="soorte{{ $activity->id }}" value="{{ Part::where('part_name','=','subcontracting')->first()->id }}" type="radio" {{ ( Part::find($activity->part_id)->part_name=='subcontracting' ? 'checked' : '') }}/>Onderaanneming</label>
		    											@endif
		    										</div>
		    										<div class="col-md-6 text-right">
														<button id="pop-{{$chapter->id.'-'.$activity->id}}" data-id="{{ $activity->id }}" data-note="{{ $activity->note }}" data-toggle="modal" data-target="#descModal" class="btn btn-info btn-xs notemod">Omschrijving</button>
														<div class="btn-group" role="group">
														  <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Werkzaamheid&nbsp;&nbsp;<span class="caret"></span></button>
														  <ul class="dropdown-menu">
														    <li><a href="#" data-id="{{ $activity->id }}" class="esavefav">Opslaan als favoriet</a></li>
														    <li><a href="#" data-id="{{ $activity->id }}" data-name="{{ $activity->activity_name }}" data-toggle="modal" data-target="#nameChangeModal" class="changename">naam wijzigen</a></li>
														    <li><a href="#" data-id="{{ $activity->id }}" class="deleteact">verwijderen</a></li>
														  </ul>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-md-2"><h4>Arbeid</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-labor" id="type" class="form-control-sm-text pointer select-estim-tax">
															@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_labor_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
															@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">

													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">&nbsp;</th>
															<th class="col-md-1">Uurtarief</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">&nbsp;</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>


													<tbody>
														<tr data-id="{{ EstimateLabor::where('activity_id','=', $activity->id)->first()['id'] }}">
															<td class="col-md-5">Arbeidsuren</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><span class="rate">{!! Part::find($activity->part_id)->part_name=='subcontracting' ? '<input name="rate" type="text" value="'.number_format(EstimateLabor::where('activity_id','=', $activity->id)->first()['rate'], 2,",",".").'" class="form-control-sm-number labor-amount lsavee">' : number_format($project->hour_rate, 2,",",".") !!}</span></td></td>
															<td class="col-md-1"><input data-id="{{ $activity->id }}" name="amount" type="text" value="{{ number_format(EstimateLabor::where('activity_id','=', $activity->id)->first()['amount'], 2, ",",".") }}" class="form-control-sm-number labor-amount lsavee" /></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format(CalculationRegister::calcLaborTotal(Part::find($activity->part_id)->part_name=='subcontracting' ? EstimateLabor::where('activity_id','=', $activity->id)->first()['rate'] : $project->hour_rate, EstimateLabor::where('activity_id','=', $activity->id)->first()['amount']), 2, ",",".") }}</span></td>
															<td class="col-md-1 text-right"><button class="btn btn-danger ldeleterowe btn-xs fa fa-times"></button></td>
														</tr>
													</tbody>
												</table>

												<div class="row">
													<div class="col-md-2"><h4>Materiaal</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-material" id="type" class="form-control-sm-text pointer select-estim-tax">
														@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_material_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
														@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">
													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">Eenheid</th>
															<th class="col-md-1">&euro; / Eenh.</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">+ Winst %</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>


													<tbody>
														@foreach (EstimateMaterial::where('activity_id','=', $activity->id)->get() as $material)
														<tr data-id="{{ $material->id }}">
															<td class="col-md-5"><input name="name" id="name" type="text" value="{{ $material->material_name }}" class="form-control-sm-text dsavee newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" value="{{ $material->unit }}" class="form-control-sm-text dsavee" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" value="{{ number_format($material->rate, 2,",",".") }}" class="form-control-sm-number dsavee" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" value="{{ number_format($material->amount, 2,",",".") }}" class="form-control-sm-number dsavee" /></td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format($material->rate*$material->amount, 2,",",".") }}</span></td>
															<td class="col-md-1"><span class="total-incl-tax">{{ '&euro; '.number_format($material->rate*$material->amount*((100+$profit_mat)/100), 2,",",".") }}</span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_mat }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs sdeleterowe fa fa-times"></button>
															</td>
														</tr>
														@endforeach
														<tr>
															<td class="col-md-5"><input name="name" id="name" type="text" class="form-control-sm-text dsavee newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" class="form-control-sm-text dsavee" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" class="form-control-sm-number dsavee" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" class="form-control-sm-number dsavee" /></td>
															<td class="col-md-1"><span class="total-ex-tax"></span></td>
															<td class="col-md-1"><span class="total-incl-tax"></span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_mat }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs sdeleterowe fa fa-times"></button>
															</td>
														</tr>
													</tbody>
													<tbody>
														<tr>
															<td class="col-md-5"><strong>Totaal</strong></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><strong class="mat_subtotal">{{ '&euro; '.number_format(CalculationRegister::estimMaterialTotal($activity->id, $profit_mat), 2, ",",".") }}</span></td>
															<td class="col-md-1"><strong class="mat_subtotal_profit">{{ '&euro; '.number_format(CalculationRegister::estimMaterialTotalProfit($activity->id, $profit_mat), 2, ",",".") }}</span></td>
															<td class="col-md-1">&nbsp;</td>
														</tr>
													</tbody>
												</table>

												@if ($project->use_equipment)
												<div class="row">
													<div class="col-md-2"><h4>Overig</h4></div>
													@if ($project->tax_reverse)
													<div class="col-md-1 text-right label label-info"><strong>BTW 0%</strong></div>
													<div class="col-md-2"></div>
													@else
													<div class="col-md-1 text-right"><strong>BTW</strong></div>	
													<div class="col-md-2">
														<select name="btw" data-id="{{ $activity->id }}" data-type="calc-equipment" id="type" class="form-control-sm-text pointer select-estim-tax">
														@foreach (Tax::all() as $tax)
															<?php
															if ($tax->id == 1)
																continue;
															?>
															<option value="{{ $tax->id }}" {{ ($activity->tax_equipment_id==$tax->id ? 'selected="selected"' : '') }}>{{ $tax->tax_rate }}%</option>
														@endforeach
														</select>
													</div>
													@endif
													<div class="col-md-6"></div>
												</div>

												<table class="table table-striped" data-id="{{ $activity->id }}">

													<thead>
														<tr>
															<th class="col-md-5">Omschrijving</th>
															<th class="col-md-1">Eenheid</th>
															<th class="col-md-1">&euro; / Eenh.</th>
															<th class="col-md-1">Aantal</th>
															<th class="col-md-1">Prijs</th>
															<th class="col-md-1">+ Winst %</th>
															<th class="col-md-1">&nbsp;</th>
														</tr>
													</thead>

													<tbody>
														@foreach (EstimateEquipment::where('activity_id','=', $activity->id)->get() as $equipment)
														<tr data-id="{{ $equipment->id }}">
															<td class="col-md-5"><input name="name" id="name" type="text" value="{{ $equipment->equipment_name }}" class="form-control-sm-text esavee newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" value="{{ $equipment->unit }}" class="form-control-sm-text esave" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" value="{{ number_format($equipment->rate, 2,",",".") }}" class="form-control-sm-number esavee" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" value="{{ number_format($equipment->amount, 2,",",".") }}" class="form-control-sm-number esavee" /></td>
															<td class="col-md-1"><span class="total-ex-tax">{{ '&euro; '.number_format($equipment->rate*$equipment->amount, 2,",",".") }}</span></td>
															<td class="col-md-1"><span class="total-incl-tax">{{ '&euro; '.number_format($equipment->rate*$equipment->amount*((100+$profit_equip)/100), 2,",",".") }}</span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_equip }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs edeleterowe fa fa-times"></button>
															</td>
														</tr>
														@endforeach
														<tr>
															<td class="col-md-5"><input name="name" id="name" type="text" class="form-control-sm-text esavee newrow" /></td>
															<td class="col-md-1"><input name="unit" id="name" type="text" class="form-control-sm-text esavee" /></td>
															<td class="col-md-1"><input name="rate" id="name" type="text" class="form-control-sm-number esavee" /></td>
															<td class="col-md-1"><input name="amount" id="name" type="text" class="form-control-sm-number esavee" /></td>
															<td class="col-md-1"><span class="total-ex-tax"></span></td>
															<td class="col-md-1"><span class="total-incl-tax"></span></td>
															<td class="col-md-1 text-right" data-profit="{{ $profit_equip }}">
																<button class="btn-xs fa fa-book" data-toggle="modal" data-target="#myModal"></button>
																<button class="btn btn-danger btn-xs edeleterowe fa fa-times"></button>
															</td>
														</tr>
													</tbody>
													<tbody>
														<tr>
															<td class="col-md-5"><strong>Totaal</strong></td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1">&nbsp;</td>
															<td class="col-md-1"><strong class="equip_subtotal">{{ '&euro; '.number_format(CalculationRegister::estimEquipmentTotal($activity->id, $profit_equip), 2, ",",".") }}</span></td>
															<td class="col-md-1"><strong class="equip_subtotal_profit">{{ '&euro; '.number_format(CalculationRegister::estimEquipmentTotalProfit($activity->id, $profit_equip), 2, ",",".") }}</span></td>
															<td class="col-md-1">&nbsp;</td>
														</tr>
													</tbody>
												</table>
												@endif
											</div>
										</div>
										<?php } ?>
									</div>
									<form method="POST" action="/calculation/estim/newactivity/{{ $chapter->id }}" accept-charset="UTF-8">
			                           {!! csrf_field() !!}
									<div class="row">
										<div class="col-md-6">

											<div class="input-group">
												<input type="text" class="form-control" name="activity" id="activity" value="" placeholder="Nieuwe Werkzaamheid">
												<span class="input-group-btn">
													<button class="btn btn-primary btn-primary-activity">Voeg toe</button>
												</span>
											</div>
										</div>
										<div class="col-md-6 text-right">
											<button type="button" class="btn btn-primary efavselect" data-id="{{ $chapter->id }}" data-toggle="modal" data-target="#myFavAct">Favoriet selecteren</button>
											<button data-id="{{ $chapter->id }}" class="btn btn-danger deletechap">Onderdeel verwijderen</button>
										</div>
									</div>
									</form>
								</div>
							</div>
							@endforeach
						</div>

						<form method="POST" action="/calculation/newchapter/{{ $project->id }}" accept-charset="UTF-8">
						{!! csrf_field() !!}
							<div><hr></div>
							<div class="row">
								<div class="col-md-6">
									<div class="input-group">
										<input type="text" class="form-control" name="chapter" id="chapter" value="" placeholder="Nieuw onderdeel">
										<span class="input-group-btn">
											<button class="btn btn-primary btn-primary-chapter">Voeg toe</button>
										</span>
									</div>
								</div>
							</div>
						</form>
					</div>
					@endif

					<div id="summary" class="tab-pane">
						<div class="row text-center">
							<img src="/images/loading_icon.gif" height="120" />
						</div>
					</div>

					<div id="endresult" class="tab-pane">
						<div class="row text-center">
							<img src="/images/loading_icon.gif" height="120" />
						</div>
					</div>
				</div>

			</div>


		</div>

	</section>

</div>

@stop

<?php } ?>
