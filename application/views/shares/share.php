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

{extends file="layout.php"}
{block name="app"}
{strip}
<h1>
    <i class="icon-share-2 on-left"></i>
    {if isset($share) && isset($documents) }{$share->name}{else}{lang('shares')}{/if}
</h1>

{if isset($share) && isset($documents) }
    {if $share->visualization == 'tree'}
        <div>
            <p>{lang('shares_p_4')}</p>
            <br>
            <nav class="breadcrumbs">
                <ul>
                    <li><a href="javascript:void(0);" onclick="sharesAnonymousNavigate('/');"><i class="icon-share-2"></i></a></li>
                    {foreach from=$places item=place name=places}
                    <li {if $smarty.foreach.places.last}class="active"{/if}><a href="javascript:void(0);" onclick="sharesAnonymousNavigate('{$place.path|escape:javascript}');">{$place.name}</a></li>
                    {/foreach}
                </ul>
            </nav>

            <table class="table hovered shares-tableview">
                <thead>
                    <tr><th>
                            {sprintf(lang('shares_th_1'), count($documents))}
                            <span class="place-right">
                                <i class="icon-image on-left-more" onclick="sharesAnonymousPhotosPreview({literal}$('table.shares-tableview td[data-photo_title]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('show')}"></i>
                                <i class="icon-play-alt on-left-more" onclick="sharesMusicPlay({literal}$('table.shares-tableview td[data-track_title]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('play')}"></i>
                                <i class="icon-playlist on-left-more"></i>
                                <i class="icon-download-2 on-left-more" onclick="sharesAnonymousDownload({literal}$('table.shares-tableview td').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('download')}"></i>
                            </span>
                    </th></tr>
                </thead>
                <tbody>
                    {foreach from=$subdirectories item=subdirectory}
                        <tr><td onclick="sharesAnonymousNavigate('{$subdirectory.path|escape:javascript}');">
                            <i class="icon-folder-2 on-left-more"></i>
                            {$subdirectory.name}
                        </td></tr>
                    {/foreach}
                    {foreach from=$documents item=document}
                        <tr>
                        {if $file_format_map[$document->file_format] == 'image'}
                            <td data-guid="{$document->guid}" data-photo_title="{$document->photo_title|escape}" >
                            <i class="icon-pictures on-left-more"></i>
                            {$document->file_basename}
                            <span class="place-right">
                                <i class="icon-image on-left-more" onclick="sharesAnonymousPhotosPreview(['{$document->guid}'])" title="{lang('show')}"></i>
                                <i class="icon-download-2 on-left-more" onclick="sharesAnonymousDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                            </span>
                            </td>
                        {elseif $file_format_map[$document->file_format] == 'audio'}
                            <td data-guid="{$document->guid}" data-track_title="{$document->track_title|escape}" data-track_album="{$document->track_album|escape}" data-track_artist="{$document->track_artist|escape}" data-track_format="{$document->file_format}">
                            <i class="icon-music on-left-more"></i>
                            {$document->file_basename}
                            <span class="place-right">
                                <i class="icon-play-alt on-left-more" onclick="sharesMusicPlay(['{$document->guid}'])" title="{lang('play')}"></i>
                                <i class="icon-playlist on-left-more"></i>
                                <i class="icon-download-2 on-left-more" onclick="sharesAnonymousDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                            </span>
                            </td>
                        {elseif $file_format_map[$document->file_format] == 'document'}
                            <td data-guid="{$document->guid}" data-track_format="{$document->file_format}">
                            <i class="icon-libreoffice on-left-more"></i>
                            {$document->file_basename}
                            <span class="place-right">
                                {if in_array($document->file_format, $viewerjs_formats) }<i class="icon-eye on-left-more" onclick="sharesAnonymousView('{$document->guid}','{$document->file_basename|escape:javascript}');" title="{lang('preview')}"></i>{/if}
                                <i class="icon-download-2 on-left-more" onclick="sharesAnonymousDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                            </span>
                            </td>
                        {elseif $file_format_map[$document->file_format] == 'archive'}
                            <td data-guid="{$document->guid}" data-track_format="{$document->file_format}">
                            <i class="icon-file-zip on-left-more"></i>
                            {$document->file_basename}
                            <span class="place-right">
                                <i class="icon-download-2 on-left-more" onclick="sharesAnonymousDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                            </span>
                            </td>
                        {/if}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {elseif $share->visualization == 'tiles'}
        <div>
        <p>{lang('shares_p_4')}</p>
        <br>
        {foreach from=$documents item=document}
        <div class="tile oneandhalf oneandhalf-vertical" data-tiletype="{$document.type}" 
            {if $file_format_map[$document.file_format] == 'image'}
                data-photo_title="{$document.title|escape:javascript}" onclick="sharesAnonymousPhotosPreview(['{$document.guid}'])"
            {elseif in_array($document.file_format, $viewerjs_formats) }
                onclick="sharesAnonymousView('{$document.guid}','{$document.file_basename|escape:javascript}');"
            {/if}
            data-guid="{$document.guid}" onclick="sharesAnonymousDownload(['{$document.guid}'])" >
            <div class="tile-content image"><img src="{$document.tile_image}"/></div>
            <div class="brand bg-dark opacity">
                <span class="text">{$document.title}<br>{$document.album}<br>{$document.artist}</span>
                <span class="badge bg-{$document.tile_bgcolor}"><i class="icon-{$document.tile_icon}"></i></span>
            </div>
            <span class="tile-toolbar bg-gray fg-white">
                <i class="icon-download-2 on-left-more place-right" style="border: solid white 5px; padding: 10px; border-radius: 50%" onclick="sharesAnonymousDownload(['{$document.guid}'])" title="{lang('download')}"></i>
            </span>
        </div>
        {/foreach}
        </div>
    {/if}
{else}
    <div class="span5 middlescreen" >
    <p>{lang('shares_p_2')}</p>
    <form id="form_share" name="form_share"  method="POST" action="{$url_index}/shares/share/{$token}">
        <div>
        <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
        <input name="share_password" type="password" value="" placeholder="{lang('password')}" data-transform="input-control" />
        </div>
        <input name="share_token" type="submit" value="{lang('continue')}" />
    </form>
    </div>
{/if}

{* Scripts *}
<script>
    $("#form_share").submit(function( event ) {
        event.preventDefault();

        $.ajax({
            url: $("#form_share").attr('action'),
            dataType: "json",
            type: 'POST',
            data: formGenerateDataObject('form_share'),
        }).always(function(data){
            if(data.type == 'notice'){
                window.location.reload();
            } else {
                notify(data);
            }
        });
    });
</script>
{/strip}
{/block}
