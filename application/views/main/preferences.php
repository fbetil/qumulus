{*

-------------------------------------------------------------------------------
Qumulus - Personal cloud software

This file is part of Qumulus.

Qumulus is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Qumulus is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Qumulus.  If not, see <http://www.gnu.org/licenses/>.
    
Authors:

 Florian BETIL : fbetil@gmail.com
-------------------------------------------------------------------------------

*}
{strip}
<h1>
    <i class="icon-equalizer on-left"></i>
    {lang('preferences')}
</h1>
<div>
    <p>{lang('main_p_13')}</p>
    <br>
    <form id="form_preferences" name="form_preferences"  method="POST" action="{$url_index}/main/preferences">
        <input type="hidden" name="application" value="main/preferences">
        <input type="hidden" name="csrf_test_name" value="{$csrf_hash}">
        <input name="form_preferences" class="default place-right" type="submit" value="{lang('save')}" />
        <br>
        <legend>{lang('main_p_14')}</legend>
        <p>{lang('main_p_15')}</p>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="preferences_username" placeholder="{lang('main_label_7')}" value="{$user->username}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="preferences_lastname" placeholder="{lang('main_label_8')}" value="{$user->lastname}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="preferences_firstname" placeholder="{lang('main_label_9')}" value="{$user->firstname}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="preferences_email" placeholder="{lang('main_label_10')}" value="{$user->email}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control text" data-role="input-control">
            <input type="password" name="preferences_password" placeholder="{lang('main_label_11')}" value="">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control text" data-role="input-control">
            <input type="password" name="preferences_password_confirm" placeholder="{lang('main_label_12')}" value="">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <br>
        <br>
        <legend>{lang('main_p_16')}</legend>
        <label>{lang('main_label_13')}</label>
        <div class="input-control select">
            <select name="preferences_language">
            <option value="english" {if $user->prefs_language == 'english' }selected{/if}>{lang('main_option_1')}</option>
            <option value="french" {if $user->prefs_language == 'french' }selected{/if}>{lang('main_option_2')}</option>
            </select>
        </div>
        <label>{lang('main_label_14')}</label>
        <div class="input-control select">
            <select name="preferences_startapp">
            <option value="last" {if $user->prefs_startapp == 'last' }selected{/if}>{lang('main_option_3')}</option>
            <option value="main/home" {if $user->prefs_startapp == 'main/home' }selected{/if}>{lang('home')}</option>
            <option value="files/index" {if $user->prefs_startapp == 'files/index' }selected{/if}>{lang('files')}</option>
            <option value="search/index" {if $user->prefs_startapp == 'search/index' }selected{/if}>{lang('research')}</option>
            <option value="music/index" {if $user->prefs_startapp == 'music/index' }selected{/if}>{lang('music')}</option>
            <option value="photos/index" {if $user->prefs_startapp == 'photos/index' }selected{/if}>{lang('photos')}</option>
            <option value="movies/index" {if $user->prefs_startapp == 'movies/index' }selected{/if}>{lang('movies')}</option>
            </select>
        </div>
        <br>
        <br>
        <legend>{lang('main_p_17')}</legend>
        <div class="input-control switch margin10" data-role="input-control">
            <label>
                {lang('main_label_15')}{nbs(6)}
                <input type="checkbox" name="preferences_files_restore_last_path" {if $user->prefs_files_restore_last_path == 1 }checked{/if}/>
                <span class="check"></span>
            </label>
        </div>
        <legend>{lang('main_p_19')}</legend>
        <div class="input-control switch margin10" data-role="input-control">
            <label>
                {lang('main_label_17')}{nbs(6)}
                <input type="checkbox" name="preferences_shares_restore_last_path" {if $user->prefs_shares_restore_last_path == 1 }checked{/if}/>
                <span class="check"></span>
            </label>
        </div>
        <legend>{lang('main_p_18')}</legend>
        <div class="input-control switch margin10" data-role="input-control">
            <label>
                {lang('main_label_16')}{nbs(6)}
                <input type="checkbox" name="preferences_music_restore_last_playlist" {if $user->prefs_music_restore_last_playlist == 1 }checked{/if}/>
                <span class="check"></span>
            </label>
        </div>
    </form>
</div>

{* Specific scripts *}
<script type="text/javascript">
    $("#form_preferences").submit(function( event ) {
        event.preventDefault();

        $.ajax({
            url: $("#form_preferences").attr('action'),
            dataType: "json",
            type: 'POST',
            data: formGenerateDataObject('form_preferences'),
        }).always(function(data){
            notify(data);
            
            if(data.type == 'notice') startApp('main/preferences');
        });
    });
</script>
{/strip}
