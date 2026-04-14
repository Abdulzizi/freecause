@extends('admin.layouts.app')

@section('title', 'Translations')

@push('head')
<style>
    .trans-section { margin-bottom: 20px; }
    .trans-group { 
        background: #f9f9f9; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        padding: 15px;
        margin-bottom: 15px;
    }
    .trans-group h3 {
        margin: 0 0 15px 0;
        font-size: 14px;
        color: #333;
        text-transform: uppercase;
    }
    .trans-row {
        display: flex;
        margin-bottom: 10px;
        align-items: flex-start;
    }
    .trans-row label {
        width: 200px;
        font-weight: 600;
        font-size: 13px;
        color: #555;
        padding-top: 6px;
    }
    .trans-row input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<h1>translations</h1>

<form method="post" action="{{ route('admin.translations.update') }}">
    @csrf
    <input type="hidden" name="locale" value="{{ $locale }}">

    <div class="fc-tab">select language</div>
    <div class="fc-box">
        <div class="fc-row">
            <label style="width:120px;">language</label>
            <select class="fc-select" onchange="window.location.href='{{ route('admin.translations.index') }}?locale='+this.value">
                @foreach($languages as $lang)
                    <option value="{{ $lang->code }}" {{ $lang->code === $locale ? 'selected' : '' }}>
                        {{ strtoupper($lang->code) }} - {{ $lang->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">petition forms</div>
        <div class="fc-box">
            <div class="trans-group">
                <h3>Petition</h3>
                <div class="trans-row">
                    <label>petition.create_title</label>
                    <input type="text" name="petition.create_title" value="{{ $translations['petition.create_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>petition.edit_title</label>
                    <input type="text" name="petition.edit_title" value="{{ $translations['petition.edit_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>petition.step_create</label>
                    <input type="text" name="petition.step_create" value="{{ $translations['petition.step_create'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>petition.step_share</label>
                    <input type="text" name="petition.step_share" value="{{ $translations['petition.step_share'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>petition.step_change</label>
                    <input type="text" name="petition.step_change" value="{{ $translations['petition.step_change'] ?? '' }}">
                </div>
            </div>

            <div class="trans-group">
                <h3>Form Labels</h3>
                <div class="trans-row">
                    <label>form.title</label>
                    <input type="text" name="form.title" value="{{ $translations['form.title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.description</label>
                    <input type="text" name="form.description" value="{{ $translations['form.description'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.goal_signatures</label>
                    <input type="text" name="form.goal_signatures" value="{{ $translations['form.goal_signatures'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.category</label>
                    <input type="text" name="form.category" value="{{ $translations['form.category'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.tags</label>
                    <input type="text" name="form.tags" value="{{ $translations['form.tags'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.image</label>
                    <input type="text" name="form.image" value="{{ $translations['form.image'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.submit</label>
                    <input type="text" name="form.submit" value="{{ $translations['form.submit'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">sign form</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>sign.title</label>
                    <input type="text" name="sign.title" value="{{ $translations['sign.title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.name</label>
                    <input type="text" name="sign.name" value="{{ $translations['sign.name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.surname</label>
                    <input type="text" name="sign.surname" value="{{ $translations['sign.surname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.email</label>
                    <input type="text" name="sign.email" value="{{ $translations['sign.email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.comment</label>
                    <input type="text" name="sign.comment" value="{{ $translations['sign.comment'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.submit</label>
                    <input type="text" name="sign.submit" value="{{ $translations['sign.submit'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.thanks</label>
                    <input type="text" name="sign.thanks" value="{{ $translations['sign.thanks'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">auth forms</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>auth.login</label>
                    <input type="text" name="auth.login" value="{{ $translations['auth.login'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.register</label>
                    <input type="text" name="auth.register" value="{{ $translations['auth.register'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.password</label>
                    <input type="text" name="auth.password" value="{{ $translations['auth.password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.forgot_password</label>
                    <input type="text" name="auth.forgot_password" value="{{ $translations['auth.forgot_password'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">common</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>common.save</label>
                    <input type="text" name="common.save" value="{{ $translations['common.save'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.cancel</label>
                    <input type="text" name="common.cancel" value="{{ $translations['common.cancel'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.delete</label>
                    <input type="text" name="common.delete" value="{{ $translations['common.delete'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.edit</label>
                    <input type="text" name="common.edit" value="{{ $translations['common.edit'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.search</label>
                    <input type="text" name="common.search" value="{{ $translations['common.search'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.yes</label>
                    <input type="text" name="common.yes" value="{{ $translations['common.yes'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.no</label>
                    <input type="text" name="common.no" value="{{ $translations['common.no'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:20px; text-align:right;">
        <button class="fc-btn" type="submit">save translations</button>
    </div>
</form>
@endsection
