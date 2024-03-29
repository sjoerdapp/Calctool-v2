<?php

use \BynqIO\Dynq\Models\WholesaleType;
?>

@extends('layout.master')

@section('title', 'Nieuwe leverancier')

@section('content')

<?php
function getNewDebtorCode() {
    return mt_rand(1000000, 9999999);
}
?>

<script type="text/javascript">
$(document).ready(function() {
    function prefixURL(field) {
        var cur_val = $(field).val();
        if (!cur_val)
            return;
        var ini = cur_val.substring(0,4);
        if (ini == 'http')
            return;
        else {
            if (cur_val.indexOf("www") >=0) {
                $(field).val('http://' + cur_val);
            } else {
                $(field).val('http://www.' + cur_val);
            }
        }
    }

    $('#website').blur(function(e){
        prefixURL($(this));
    });

    $('#kvk').blur(function() {
        var kvkcheck = $(this).val();
        if (kvkcheck.length != 8) {
            $(this).parent().addClass('has-error');
        } else {
            $(this).parent().removeClass('has-error');
        }
    });

    $('#btw').blur(function() {
        var btwcheck = $(this).val();
        if (btwcheck.length != 14) {
            $(this).addClass("error-input");
        }else {
            $(this).removeClass("error-input");
        }
    });


    $('#telephone_com').blur(function() {
        var telcompcheck = $(this).val();
        if (telcompcheck.length != 12) {
            $(this).addClass("error-input");
        }else {
            $(this).removeClass("error-input");
        }
    });
    $('#relationkind').change(function(e) {
        if ($(this).val() == 2)
            $('.company').hide('slow');
        else
            $('.company').show('slow');
    });

    var zipcode = $('#zipcode').val();
    var number = $('#address_number').val();
    $('.autoappend').blur(function(e){
        if (number == $('#address_number').val() && zipcode == $('#zipcode').val())
            return;
        zipcode = $('#zipcode').val();
        number = $('#address_number').val();
        if (number && zipcode) {

            $.post("/api/v1/postaladdress", {
                zipcode: zipcode,
                number: number,
            }, function(data) {
                if (data) {
                    var json = data;
                    $('#street').val(json.street);
                    $('#city').val(json.city);
                    $("#province").find('option:selected').removeAttr("selected");
                    $('#province option[value=' + json.province_id + ']').attr('selected','selected');
                }
            });
        }
    });

});
</script>

<div id="wrapper">

    <section class="container">

        <div class="col-md-12">

            <div>
                <ol class="breadcrumb">
                  <li><a href="/">Dashboard</a></li>
                  <li><a href="/wholesale">Leveranciers</a></li>
                  <li>Nieuwe Leverancier</li>
                </ol>
            <div>

            @if (Session::has('success'))
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i>
                <strong>{{ Session::get('success') }}</strong>
            </div>
            @endif

            @if (count($errors) > 0)
            <div class="alert alert-danger">
                <i class="fa fa-frown-o"></i>
                <strong>Fouten in de invoer</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li><h5 class="nomargin">{{ $error }}</h5></li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h2><strong>Nieuwe</strong> leverancier</h2>

            <div class="white-row">
                <form method="POST" action="/wholesale/new" accept-charset="UTF-8">
                {!! csrf_field() !!}

                <h4 class="company">Bedrijfsgegevens</h4>
                <div class="row company">

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="company_name">Bedrijfsnaam*</label>
                            <input name="company_name" maxlength="50" id="company_name" type="text" value="{{ old('company_name') }}" class="form-control" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="company_type">Leverancier type*</label>
                            <select name="company_type" id="company_type" class="form-control pointer">
                            @foreach (WholesaleType::all() as $type)
                                <option value="{{ $type->id }}">{{ ucwords($type->type_name) }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input name="website" maxlength="180" id="website" type="url" value="{{ old('website') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="telephone_comp">Telefoonnummer</label>
                            <input name="telephone_comp" id="telephone_comp" type="text" minlength="12" maxlength="12" value="{{ old('telephone_comp') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input name="email" maxlength="80" id="email" type="email" value="{{ old('email') }}" class="form-control"/>
                        </div>
                    </div>

                </div>

                <h4>Adresgegevens</h4>
                <div class="row">

                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="address_number">Huis nr.*</label>
                            <input name="address_number" maxlength="5" id="address_number" type="text" value="{{ old('address_number') }}" class="form-control autoappend"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="zipcode">Postcode*</label>
                            <input name="zipcode" id="zipcode" maxlength="6" type="text" value="{{ old('zipcode') }}" class="form-control autoappend"/>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="street">Straat*</label>
                            <input name="street" maxlength="50" id="street" type="text" value="{{ old('street') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="city">Plaats*</label>
                            <input name="city" maxlength="35" id="city" type="text" value="{{ 	old('city') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="province">Provincie*</label>
                            <select name="province" id="province" class="form-control pointer">
                                @foreach (CalculatieTool\Models\Province::all() as $province)
                                    <option value="{{ $province->id }}">{{ ucwords($province->province_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="country">Land*</label>
                            <select name="country" id="country" class="form-control pointer">
                                @foreach (CalculatieTool\Models\Country::all() as $country)
                                    <option {{ $country->country_name=='nederland' ? 'selected' : '' }} value="{{ $country->id }}">{{ ucwords($country->country_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <h4>Opmerkingen</h4>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea name="note" id="note" rows="10" class="form-control">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                    </div>
                </div>
                </form>
            </div>

        </div>

    </section>

</div>
@stop
