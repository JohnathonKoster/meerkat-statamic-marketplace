@extends('layout')

@section('content')
<form action="{{ $submit }}" method="post">
{{ csrf_field() }}
<div id="publish-controls" class="head sticky">
    <h1 id="publish-title">
        <span>{{ meerkat_trans('settings.license') }}</span>
    </h1>
    <div class="controls">
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">{{ meerkat_trans('settings.license_submit') }}</button>
        </div>
    </div>
</div>
<div class="card">
    <div>
        <div class="publish-main">
            <div class="publish-fields">
                <div class="form-group section-fieldtype width-100 ">
                    <div class="field-inner">
                        <label class="block">{{ meerkat_trans('settings.license') }}</label>
                        <small class="help-block"><p>{{ meerkat_trans('settings.license_instruct') }}</p></small>
                    </div>
                </div>
                <div class="form-group text-fieldtype width-100 ">
                    <div class="field-inner">
                        <label class="block">{{ meerkat_trans('settings.license_key') }}</label>
                        <small class="help-block"><p>{!! meerkat_trans('settings.license_key_instruct') !!}</p></small>
                        <input tabindex="0" class="form-control type-text" type="text" name="license_key" value="{{ $license_key }}" />

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection