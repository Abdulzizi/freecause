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
                    <label>form.image_external</label>
                    <input type="text" name="form.image_external" value="{{ $translations['form.image_external'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.youtube</label>
                    <input type="text" name="form.youtube" value="{{ $translations['form.youtube'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.target</label>
                    <input type="text" name="form.target" value="{{ $translations['form.target'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.community</label>
                    <input type="text" name="form.community" value="{{ $translations['form.community'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.community_url</label>
                    <input type="text" name="form.community_url" value="{{ $translations['form.community_url'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.city</label>
                    <input type="text" name="form.city" value="{{ $translations['form.city'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.submit</label>
                    <input type="text" name="form.submit" value="{{ $translations['form.submit'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.preview</label>
                    <input type="text" name="form.preview" value="{{ $translations['form.preview'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>form.cancel</label>
                    <input type="text" name="form.cancel" value="{{ $translations['form.cancel'] ?? '' }}">
                </div>
            </div>

            <div class="trans-group">
                <h3>Create Petition</h3>
                <div class="trans-row">
                    <label>petition.data</label>
                    <input type="text" name="petition.data" value="{{ $translations['petition.data'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.petition_data</label>
                    <input type="text" name="create.petition_data" value="{{ $translations['create.petition_data'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.select_one</label>
                    <input type="text" name="create.select_one" value="{{ $translations['create.select_one'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.signatures_sfx</label>
                    <input type="text" name="create.signatures_sfx" value="{{ $translations['create.signatures_sfx'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.tags_hint</label>
                    <input type="text" name="create.tags_hint" value="{{ $translations['create.tags_hint'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.update</label>
                    <input type="text" name="create.update" value="{{ $translations['create.update'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>create.login_required</label>
                    <input type="text" name="create.login_required" value="{{ $translations['create.login_required'] ?? '' }}">
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
                    <label>sign.password</label>
                    <input type="text" name="sign.password" value="{{ $translations['sign.password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.comment</label>
                    <input type="text" name="sign.comment" value="{{ $translations['sign.comment'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.city</label>
                    <input type="text" name="sign.city" value="{{ $translations['sign.city'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.nickname</label>
                    <input type="text" name="sign.nickname" value="{{ $translations['sign.nickname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree1</label>
                    <input type="text" name="sign.agree1" value="{{ $translations['sign.agree1'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree2</label>
                    <input type="text" name="sign.agree2" value="{{ $translations['sign.agree2'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree3</label>
                    <input type="text" name="sign.agree3" value="{{ $translations['sign.agree3'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.submit</label>
                    <input type="text" name="sign.submit" value="{{ $translations['sign.submit'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.already_signed</label>
                    <input type="text" name="sign.already_signed" value="{{ $translations['sign.already_signed'] ?? '' }}">
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
                    <label>auth.email</label>
                    <input type="text" name="auth.email" value="{{ $translations['auth.email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.password</label>
                    <input type="text" name="auth.password" value="{{ $translations['auth.password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.name</label>
                    <input type="text" name="auth.name" value="{{ $translations['auth.name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.surname</label>
                    <input type="text" name="auth.surname" value="{{ $translations['auth.surname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.forgot_password</label>
                    <input type="text" name="auth.forgot_password" value="{{ $translations['auth.forgot_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.submit</label>
                    <input type="text" name="auth.submit" value="{{ $translations['auth.submit'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.no_account</label>
                    <input type="text" name="auth.no_account" value="{{ $translations['auth.no_account'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.have_account</label>
                    <input type="text" name="auth.have_account" value="{{ $translations['auth.have_account'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.google_heading</label>
                    <input type="text" name="auth.google_heading" value="{{ $translations['auth.google_heading'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.continue_google</label>
                    <input type="text" name="auth.continue_google" value="{{ $translations['auth.continue_google'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.or</label>
                    <input type="text" name="auth.or" value="{{ $translations['auth.or'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.remember_me</label>
                    <input type="text" name="auth.remember_me" value="{{ $translations['auth.remember_me'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.resend_verification</label>
                    <input type="text" name="auth.resend_verification" value="{{ $translations['auth.resend_verification'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.not_member</label>
                    <input type="text" name="auth.not_member" value="{{ $translations['auth.not_member'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.sign_up_now</label>
                    <input type="text" name="auth.sign_up_now" value="{{ $translations['auth.sign_up_now'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.already_member</label>
                    <input type="text" name="auth.already_member" value="{{ $translations['auth.already_member'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.sign_in_now</label>
                    <input type="text" name="auth.sign_in_now" value="{{ $translations['auth.sign_in_now'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.create_account</label>
                    <input type="text" name="auth.create_account" value="{{ $translations['auth.create_account'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.accept_terms</label>
                    <input type="text" name="auth.accept_terms" value="{{ $translations['auth.accept_terms'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.nickname_hint</label>
                    <input type="text" name="auth.nickname_hint" value="{{ $translations['auth.nickname_hint'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.email_placeholder</label>
                    <input type="text" name="auth.email_placeholder" value="{{ $translations['auth.email_placeholder'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.password_placeholder</label>
                    <input type="text" name="auth.password_placeholder" value="{{ $translations['auth.password_placeholder'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">my petitions</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>myp.signed_heading</label>
                    <input type="text" name="myp.signed_heading" value="{{ $translations['myp.signed_heading'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>myp.signed_empty</label>
                    <input type="text" name="myp.signed_empty" value="{{ $translations['myp.signed_empty'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>myp.full_list</label>
                    <input type="text" name="myp.full_list" value="{{ $translations['myp.full_list'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>myp.created_heading</label>
                    <input type="text" name="myp.created_heading" value="{{ $translations['myp.created_heading'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>myp.created_empty</label>
                    <input type="text" name="myp.created_empty" value="{{ $translations['myp.created_empty'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">signatures page</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>sig.all_signatures</label>
                    <input type="text" name="sig.all_signatures" value="{{ $translations['sig.all_signatures'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.total</label>
                    <input type="text" name="sig.total" value="{{ $translations['sig.total'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.sign_link</label>
                    <input type="text" name="sig.sign_link" value="{{ $translations['sig.sign_link'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.no_signatures</label>
                    <input type="text" name="sig.no_signatures" value="{{ $translations['sig.no_signatures'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.anonymous</label>
                    <input type="text" name="sig.anonymous" value="{{ $translations['sig.anonymous'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.continue</label>
                    <input type="text" name="sig.continue" value="{{ $translations['sig.continue'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.goal</label>
                    <input type="text" name="sig.goal" value="{{ $translations['sig.goal'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sig.signatures</label>
                    <input type="text" name="sig.signatures" value="{{ $translations['sig.signatures'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">contacts page</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>contacts.title</label>
                    <input type="text" name="contacts.title" value="{{ $translations['contacts.title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.home</label>
                    <input type="text" name="contacts.home" value="{{ $translations['contacts.home'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.name_label</label>
                    <input type="text" name="contacts.name_label" value="{{ $translations['contacts.name_label'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.name_placeholder</label>
                    <input type="text" name="contacts.name_placeholder" value="{{ $translations['contacts.name_placeholder'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.email_label</label>
                    <input type="text" name="contacts.email_label" value="{{ $translations['contacts.email_label'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.email_placeholder</label>
                    <input type="text" name="contacts.email_placeholder" value="{{ $translations['contacts.email_placeholder'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.text_label</label>
                    <input type="text" name="contacts.text_label" value="{{ $translations['contacts.text_label'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.text_placeholder</label>
                    <input type="text" name="contacts.text_placeholder" value="{{ $translations['contacts.text_placeholder'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>contacts.submit</label>
                    <input type="text" name="contacts.submit" value="{{ $translations['contacts.submit'] ?? '' }}">
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
                    <label>common.view</label>
                    <input type="text" name="common.view" value="{{ $translations['common.view'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.search</label>
                    <input type="text" name="common.search" value="{{ $translations['common.search'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.filter</label>
                    <input type="text" name="common.filter" value="{{ $translations['common.filter'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.export</label>
                    <input type="text" name="common.export" value="{{ $translations['common.export'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.loading</label>
                    <input type="text" name="common.loading" value="{{ $translations['common.loading'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.no_results</label>
                    <input type="text" name="common.no_results" value="{{ $translations['common.no_results'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.confirm</label>
                    <input type="text" name="common.confirm" value="{{ $translations['common.confirm'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.yes</label>
                    <input type="text" name="common.yes" value="{{ $translations['common.yes'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.no</label>
                    <input type="text" name="common.no" value="{{ $translations['common.no'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.required</label>
                    <input type="text" name="common.required" value="{{ $translations['common.required'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>common.optional</label>
                    <input type="text" name="common.optional" value="{{ $translations['common.optional'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:20px; text-align:right;">
        <button class="fc-btn" type="submit">save translations</button>
    </div>
</form>
@endsection
