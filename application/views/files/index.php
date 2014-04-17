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
    <i class="icon-folder on-left"></i>
    {lang('files')}
</h1>
<div>
    <p>{lang('main_p_3')}</p>
    
    <nav class="breadcrumbs">
        <ul>
            <li><a href="javascript:void(0);" onclick="filesNavigate();"><i class="icon-folder"></i></a></li>
            {foreach from=$places item=place name=places}
            <li {if $smarty.foreach.places.last}class="active"{/if}><a href="javascript:void(0);" onclick="filesNavigate('{$place.path|escape:javascript}');">{$place.name}</a></li>
            {/foreach}
        </ul>
    </nav>

    <table class="table hovered files-tableview">
        <thead>
            <tr><th>
                    {sprintf(lang('files_th_1'), count($documents))}
                    <span class="place-right">
                        <i class="icon-image on-left-more" onclick="photosPreview({literal}$('table.files-tableview td[data-photo_title]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('show')}"></i>
                        <i class="icon-play-alt on-left-more" onclick="filesMusicPlay({literal}$('table.files-tableview td[data-track_title]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('play')}"></i>
                        <i class="icon-playlist on-left-more"></i>
                        <i class="icon-download-2 on-left-more" onclick="filesDownload({literal}$('table.files-tableview td[data-guid]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('download')}"></i>
                        {if !$readonly }<i class="icon-remove on-left-more" onclick="filesDelete({literal}$('table.files-tableview td[data-guid]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('delete')}"></i>{/if}
                    </span>
            </th></tr>
        </thead>
        <tbody>
            {foreach from=$subdirectories item=subdirectory}
                <tr><td onclick="filesNavigate('{$subdirectory.path|escape:javascript}');">
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
                        <i class="icon-image on-left-more" onclick="photosPreview(['{$document->guid}'])" title="{lang('show')}"></i>
                        <i class="icon-download-2 on-left-more" onclick="filesDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                        {include file="shares/share_link_button.php" multiple=false}
                        {if !$readonly }<i class="icon-remove on-left-more" onclick="filesDelete(['{$document->guid}'])" title="{lang('delete')}"></i>{/if}
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'audio'}
                    <td data-guid="{$document->guid}" data-track_title="{$document->track_title|escape}" data-track_album="{$document->track_album|escape}" data-track_artist="{$document->track_artist|escape}" data-track_format="{$document->file_format}">
                    <i class="icon-music on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        <i class="icon-play-alt on-left-more" onclick="filesMusicPlay(['{$document->guid}'])" title="{lang('play')}"></i>
                        <i class="icon-playlist on-left-more"></i>
                        <i class="icon-download-2 on-left-more" onclick="filesDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                        {include file="shares/share_link_button.php" multiple=false}
                        {if !$readonly }<i class="icon-remove on-left-more" onclick="filesDelete(['{$document->guid}'])" title="{lang('delete')}"></i>{/if}
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'document'}
                    <td data-guid="{$document->guid}" data-track_format="{$document->file_format}">
                    <i class="icon-libreoffice on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        {if in_array($document->file_format,$viewerjs_formats) }<i class="icon-eye on-left-more" onclick="fileView('{$document->guid}','{$document->file_basename|escape:javascript}');" title="{lang('preview')}"></i>{/if}
                        <i class="icon-download-2 on-left-more" onclick="filesDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                        {include file="shares/share_link_button.php" multiple=false}
                        {if !$readonly }<i class="icon-remove on-left-more" onclick="filesDelete(['{$document->guid}'])" title="{lang('delete')}"></i>{/if}
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'archive'}
                    <td data-guid="{$document->guid}" data-track_format="{$document->file_format}">
                    <i class="icon-file-zip on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        <i class="icon-download-2 on-left-more" onclick="filesDownload(['{$document->guid}'])" title="{lang('download')}"></i>
                        {include file="shares/share_link_button.php" multiple=false}
                        {if !$readonly }<i class="icon-remove on-left-more" onclick="filesDelete(['{$document->guid}'])" title="{lang('delete')}"></i>{/if}
                    </span>
                    </td>
                {/if}
                </tr>
            {/foreach}
            {if $here == '/' }
                <tr><td onclick="startApp('shares/index');">
                    <i class="icon-share-2 on-left-more"></i>
                    {lang('shares')}
                </td></tr>
            {/if}
        </tbody>
    </table>
</div>

{* Specific scripts *}
<script>
    $(document).ready(function(){
        $("ul[data-role='dropdown']").dropdown();
    });
</script>
{/strip}