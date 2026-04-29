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
        <div class="fc-tab">sign extended</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>sign.page_title</label>
                    <input type="text" name="sign.page_title" value="{{ $translations['sign.page_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.signed_hint</label>
                    <input type="text" name="sign.signed_hint" value="{{ $translations['sign.signed_hint'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.default_comment</label>
                    <input type="text" name="sign.default_comment" value="{{ $translations['sign.default_comment'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.email_warning</label>
                    <input type="text" name="sign.email_warning" value="{{ $translations['sign.email_warning'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree_yes</label>
                    <input type="text" name="sign.agree_yes" value="{{ $translations['sign.agree_yes'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree_no</label>
                    <input type="text" name="sign.agree_no" value="{{ $translations['sign.agree_no'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.h2_line1</label>
                    <input type="text" name="sign.h2_line1" value="{{ $translations['sign.h2_line1'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.h2_line2</label>
                    <input type="text" name="sign.h2_line2" value="{{ $translations['sign.h2_line2'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.btn</label>
                    <input type="text" name="sign.btn" value="{{ $translations['sign.btn'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.btn_arrow</label>
                    <input type="text" name="sign.btn_arrow" value="{{ $translations['sign.btn_arrow'] ?? '' }}">
                </div>
            </div>
            <div class="trans-group">
                <h3>Placeholders</h3>
                <div class="trans-row">
                    <label>sign.ph_name</label>
                    <input type="text" name="sign.ph_name" value="{{ $translations['sign.ph_name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_surname</label>
                    <input type="text" name="sign.ph_surname" value="{{ $translations['sign.ph_surname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_email</label>
                    <input type="text" name="sign.ph_email" value="{{ $translations['sign.ph_email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_password</label>
                    <input type="text" name="sign.ph_password" value="{{ $translations['sign.ph_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_city</label>
                    <input type="text" name="sign.ph_city" value="{{ $translations['sign.ph_city'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_nickname</label>
                    <input type="text" name="sign.ph_nickname" value="{{ $translations['sign.ph_nickname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.ph_comment</label>
                    <input type="text" name="sign.ph_comment" value="{{ $translations['sign.ph_comment'] ?? '' }}">
                </div>
            </div>
            <div class="trans-group">
                <h3>Labels</h3>
                <div class="trans-row">
                    <label>sign.lbl_name</label>
                    <input type="text" name="sign.lbl_name" value="{{ $translations['sign.lbl_name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_surname</label>
                    <input type="text" name="sign.lbl_surname" value="{{ $translations['sign.lbl_surname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_email</label>
                    <input type="text" name="sign.lbl_email" value="{{ $translations['sign.lbl_email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_password</label>
                    <input type="text" name="sign.lbl_password" value="{{ $translations['sign.lbl_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_city</label>
                    <input type="text" name="sign.lbl_city" value="{{ $translations['sign.lbl_city'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_nickname</label>
                    <input type="text" name="sign.lbl_nickname" value="{{ $translations['sign.lbl_nickname'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.lbl_comment</label>
                    <input type="text" name="sign.lbl_comment" value="{{ $translations['sign.lbl_comment'] ?? '' }}">
                </div>
            </div>
            <div class="trans-group">
                <h3>Agreement &amp; Privacy</h3>
                <div class="trans-row">
                    <label>sign.privacy_hint</label>
                    <input type="text" name="sign.privacy_hint" value="{{ $translations['sign.privacy_hint'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree1_title</label>
                    <input type="text" name="sign.agree1_title" value="{{ $translations['sign.agree1_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree2_title</label>
                    <input type="text" name="sign.agree2_title" value="{{ $translations['sign.agree2_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>sign.agree3_title</label>
                    <input type="text" name="sign.agree3_title" value="{{ $translations['sign.agree3_title'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">petition show page</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>show.page_title</label>
                    <input type="text" name="show.page_title" value="{{ $translations['show.page_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.btn_sign_now</label>
                    <input type="text" name="show.btn_sign_now" value="{{ $translations['show.btn_sign_now'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.google_continue</label>
                    <input type="text" name="show.google_continue" value="{{ $translations['show.google_continue'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.or</label>
                    <input type="text" name="show.or" value="{{ $translations['show.or'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.auth_hint_split</label>
                    <input type="text" name="show.auth_hint_split" value="{{ $translations['show.auth_hint_split'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.auth_hint_stack</label>
                    <input type="text" name="show.auth_hint_stack" value="{{ $translations['show.auth_hint_stack'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_sign_title</label>
                    <input type="text" name="show.box_sign_title" value="{{ $translations['show.box_sign_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_shoutbox</label>
                    <input type="text" name="show.box_shoutbox" value="{{ $translations['show.box_shoutbox'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_goal</label>
                    <input type="text" name="show.box_goal" value="{{ $translations['show.box_goal'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.goal_signatures</label>
                    <input type="text" name="show.goal_signatures" value="{{ $translations['show.goal_signatures'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.goal_label</label>
                    <input type="text" name="show.goal_label" value="{{ $translations['show.goal_label'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_latest</label>
                    <input type="text" name="show.box_latest" value="{{ $translations['show.box_latest'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.latest_empty</label>
                    <input type="text" name="show.latest_empty" value="{{ $translations['show.latest_empty'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.latest_browse_all</label>
                    <input type="text" name="show.latest_browse_all" value="{{ $translations['show.latest_browse_all'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_information</label>
                    <input type="text" name="show.box_information" value="{{ $translations['show.box_information'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.info_by</label>
                    <input type="text" name="show.info_by" value="{{ $translations['show.info_by'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.info_in</label>
                    <input type="text" name="show.info_in" value="{{ $translations['show.info_in'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.info_target</label>
                    <input type="text" name="show.info_target" value="{{ $translations['show.info_target'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_tags</label>
                    <input type="text" name="show.box_tags" value="{{ $translations['show.box_tags'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.tags_empty</label>
                    <input type="text" name="show.tags_empty" value="{{ $translations['show.tags_empty'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_embed</label>
                    <input type="text" name="show.box_embed" value="{{ $translations['show.box_embed'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.embed_direct</label>
                    <input type="text" name="show.embed_direct" value="{{ $translations['show.embed_direct'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.embed_html</label>
                    <input type="text" name="show.embed_html" value="{{ $translations['show.embed_html'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.embed_forum_no_title</label>
                    <input type="text" name="show.embed_forum_no_title" value="{{ $translations['show.embed_forum_no_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.embed_forum_with_title</label>
                    <input type="text" name="show.embed_forum_with_title" value="{{ $translations['show.embed_forum_with_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>show.box_widgets</label>
                    <input type="text" name="show.box_widgets" value="{{ $translations['show.box_widgets'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">thanks page</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>thanks.title_created</label>
                    <input type="text" name="thanks.title_created" value="{{ $translations['thanks.title_created'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.title_signed</label>
                    <input type="text" name="thanks.title_signed" value="{{ $translations['thanks.title_signed'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.h1_created</label>
                    <input type="text" name="thanks.h1_created" value="{{ $translations['thanks.h1_created'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.h1_signed</label>
                    <input type="text" name="thanks.h1_signed" value="{{ $translations['thanks.h1_signed'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.p_created</label>
                    <input type="text" name="thanks.p_created" value="{{ $translations['thanks.p_created'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.p_signed</label>
                    <input type="text" name="thanks.p_signed" value="{{ $translations['thanks.p_signed'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.suggestions</label>
                    <input type="text" name="thanks.suggestions" value="{{ $translations['thanks.suggestions'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.no_suggestions</label>
                    <input type="text" name="thanks.no_suggestions" value="{{ $translations['thanks.no_suggestions'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.invite</label>
                    <input type="text" name="thanks.invite" value="{{ $translations['thanks.invite'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>thanks.petition_fallback</label>
                    <input type="text" name="thanks.petition_fallback" value="{{ $translations['thanks.petition_fallback'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">navbar</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>nav.explore</label>
                    <input type="text" name="nav.explore" value="{{ $translations['nav.explore'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>nav.magazine</label>
                    <input type="text" name="nav.magazine" value="{{ $translations['nav.magazine'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>nav.help</label>
                    <input type="text" name="nav.help" value="{{ $translations['nav.help'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>nav.logout</label>
                    <input type="text" name="nav.logout" value="{{ $translations['nav.logout'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>nav.startfree</label>
                    <input type="text" name="nav.startfree" value="{{ $translations['nav.startfree'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">profile</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>profile.heading</label>
                    <input type="text" name="profile.heading" value="{{ $translations['profile.heading'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.my_data</label>
                    <input type="text" name="profile.my_data" value="{{ $translations['profile.my_data'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.reg_date</label>
                    <input type="text" name="profile.reg_date" value="{{ $translations['profile.reg_date'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.my_petitions</label>
                    <input type="text" name="profile.my_petitions" value="{{ $translations['profile.my_petitions'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.account_deletion</label>
                    <input type="text" name="profile.account_deletion" value="{{ $translations['profile.account_deletion'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.enter_password</label>
                    <input type="text" name="profile.enter_password" value="{{ $translations['profile.enter_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.confirm_delete_lbl</label>
                    <input type="text" name="profile.confirm_delete_lbl" value="{{ $translations['profile.confirm_delete_lbl'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.delete</label>
                    <input type="text" name="profile.delete" value="{{ $translations['profile.delete'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.first_name</label>
                    <input type="text" name="profile.first_name" value="{{ $translations['profile.first_name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.last_name</label>
                    <input type="text" name="profile.last_name" value="{{ $translations['profile.last_name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.identify_as</label>
                    <input type="text" name="profile.identify_as" value="{{ $translations['profile.identify_as'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.id_full</label>
                    <input type="text" name="profile.id_full" value="{{ $translations['profile.id_full'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.id_name</label>
                    <input type="text" name="profile.id_name" value="{{ $translations['profile.id_name'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.id_nick</label>
                    <input type="text" name="profile.id_nick" value="{{ $translations['profile.id_nick'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.change_email</label>
                    <input type="text" name="profile.change_email" value="{{ $translations['profile.change_email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.new_email</label>
                    <input type="text" name="profile.new_email" value="{{ $translations['profile.new_email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.confirm_email</label>
                    <input type="text" name="profile.confirm_email" value="{{ $translations['profile.confirm_email'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.change_password</label>
                    <input type="text" name="profile.change_password" value="{{ $translations['profile.change_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.current_password</label>
                    <input type="text" name="profile.current_password" value="{{ $translations['profile.current_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.new_password</label>
                    <input type="text" name="profile.new_password" value="{{ $translations['profile.new_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.confirm_new_password</label>
                    <input type="text" name="profile.confirm_new_password" value="{{ $translations['profile.confirm_new_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>profile.save_changes</label>
                    <input type="text" name="profile.save_changes" value="{{ $translations['profile.save_changes'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="trans-section">
        <div class="fc-tab">forgot / reset password</div>
        <div class="fc-box">
            <div class="trans-group">
                <div class="trans-row">
                    <label>auth.forgot_title</label>
                    <input type="text" name="auth.forgot_title" value="{{ $translations['auth.forgot_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.send_reset</label>
                    <input type="text" name="auth.send_reset" value="{{ $translations['auth.send_reset'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.back_login</label>
                    <input type="text" name="auth.back_login" value="{{ $translations['auth.back_login'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.reset_title</label>
                    <input type="text" name="auth.reset_title" value="{{ $translations['auth.reset_title'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.new_password</label>
                    <input type="text" name="auth.new_password" value="{{ $translations['auth.new_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.confirm_password</label>
                    <input type="text" name="auth.confirm_password" value="{{ $translations['auth.confirm_password'] ?? '' }}">
                </div>
                <div class="trans-row">
                    <label>auth.reset_submit</label>
                    <input type="text" name="auth.reset_submit" value="{{ $translations['auth.reset_submit'] ?? '' }}">
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
