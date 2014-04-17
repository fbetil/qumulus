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
    <i class="icon-cog on-left"></i>
    {lang('settings')}
</h1>
<div>
    <p>{lang('main_p_4')}</p>
    <br>
    <form id="form_parameters" name="form_parameters"  method="POST" action="{$url_index}/main/parameters">
        <input type="hidden" name="application" value="main/parameters">
        <input type="hidden" name="csrf_test_name" value="{$csrf_hash}">
        <input name="form_parameters" class="default place-right" type="submit" value="{lang('save')}" />
        <br>
        <legend>{lang('main_p_7')}</legend>
        <p>{lang('main_p_12')}</p>
        <table class="table striped bordered" id="table_sources">
            <thead>
                <tr>
                    <th>{lang('main_label_4')}</th>
                    <th>{lang('main_label_5')}</th>
                    <th>{lang('main_label_6')}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$sources item=source name=sources }
                <tr data-sourceid="{$source->id}"><td>
                <div class="input-control text" data-role="input-control">
                    <input type="text" name="sources_name_{$source->id}" placeholder="{lang('main_label_4')}" value="{$source->name}">
                    <button class="btn-clear" tabindex="-1"></button>
                </div>
                </td><td>
                <div class="input-control text" data-role="input-control">
                    <input type="text" name="sources_path_{$source->id}" placeholder="{lang('main_label_5')}" value="{$source->path}">
                    <button class="btn-clear" tabindex="-1"></button>
                </div>
                </td><td>
                <div class="input-control select">
                    <select name="sources_types_{$source->id}">
                    <option value="all" {if $source->types == 'all' }selected{/if}>{lang('all')}</option>
                    <option value="audio" {if $source->types == 'audio' }selected{/if}>{lang('music')}</option>
                    <option value="image" {if $source->types == 'image' }selected{/if}>{lang('photos')}</option>
                    <option value="document" {if $source->types == 'document' }selected{/if}>{lang('documents')}</option>
                    <option value="archive" {if $source->types == 'archive' }selected{/if}>{lang('archives')}</option>
                    </select>
                </div>
                </td><td>
                {if $smarty.foreach.sources.index != 0}
                <a href="javascript:void(0)" onclick="deleteSource('{$source->id}')">{lang('delete')}</a>
                {/if}
                </td></tr>
                {/foreach}
            </tbody>
        </table>
        <a href="javascript:void(0)" onclick="addSource()">{lang('main_a_1')}</a>
        <br>
        <br>
        <legend>{lang('main_p_8')}</legend>
        <label>{lang('main_label_2')}</label>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="parameters_thumbnails_size" placeholder="200" value="{$parameters.thumbnails_size}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <label>{lang('main_label_1')}</label>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="parameters_previews_size" placeholder="800" value="{$parameters.previews_size}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <div class="input-control switch margin10" data-role="input-control">
            <label>
                {lang('main_label_3')}{nbs(6)}
                <input type="checkbox" name="parameters_photos_get_tags_from_path" {if $parameters.photos_get_tags_from_path == 1 }checked{/if}/>
                <span class="check"></span>
            </label>
        </div>
        <legend>{lang('main_p_23')}</legend>
        <p>{lang('main_label_18')}</p>
        <label>{lang('main_label_19')}</label>
        <div class="input-control text" data-role="input-control">
            <input type="text" name="parameters_lastfm_api_key" placeholder="" value="{$parameters.lastfm_api_key}">
            <button class="btn-clear" tabindex="-1"></button>
        </div>
        <legend>{lang('main_p_6')}</legend>
        <p style="display:inline">{lang('main_p_9')}{nbs(6)}
        <div class="button-dropdown">
            <button class="dropdown-toggle default"><i class="icon-rocket on-left"></i>{lang('choose')}</button>
            <ul class="dropdown-menu" data-role="dropdown">
                {foreach from=$sources item=source }
                <li><a href="javascript:void(0)" onclick="startTask('cli/indexingfiles', {$source->id})"><i class="icon-folder on-left"></i>{$source->name}</a></li>
                {/foreach}
                <li class="divider"></li>
                <li><a href="javascript:void(0)" onclick="startTask('cli/indexingfiles')"><i class="icon-rocket on-left"></i>{lang('all')}</a></li>
            </ul>
        </div>
        </p>
        <p>{lang('main_p_10')}{nbs(6)}<button class="default" onclick="startTask('cli/generatingthumbnails')"><i class="icon-rocket on-left"></i>{lang('start')}</button></p>
        <p>{lang('main_p_11')}{nbs(6)}<button class="default" onclick="startTask('cli/generatingpreviews')"><i class="icon-rocket on-left"></i>{lang('start')}</button></p>
        <p>{lang('main_p_24')}{nbs(6)}<button class="default" onclick="startTask('cli/lastfmcoversdownload')"><i class="icon-rocket on-left"></i>{lang('start')}</button></p>
        <p>{lang('main_p_25')}{nbs(6)}<button class="default" onclick="startTask('cli/lastfmretrievetags')"><i class="icon-rocket on-left"></i>{lang('start')}</button></p>
    </form>
</div>

{* Specific scripts *}
<script type="text/javascript">
    $("ul[data-role='dropdown']").dropdown();

    $("#form_parameters").submit(function( event ) {
        event.preventDefault();

        $.ajax({
            url: $("#form_parameters").attr('action'),
            dataType: "json",
            type: 'POST',
            data: formGenerateDataObject('form_parameters')
        }).always(function(data){
            notify(data);
            
            if(data.type == 'notice') startApp('main/parameters');
        });
    });
</script>
{/strip}
