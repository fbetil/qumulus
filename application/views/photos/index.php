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
    <i class="icon-pictures on-left"></i>
    {lang('photos')}
</h1>
<div>
    <p>{lang('photos_p_1')}</p>
    <legend>{lang('photos_legend_1')}
        <span class="place-right">
            <i class="icon-image on-left-more" onclick="photosPreview({literal}$('#photos-tiles [data-guid]').map(function(){return $(this).attr('data-guid')}).get(){/literal})" title="{lang('show')}"></i>
        </span>
    </legend>
    <div id="photos-tiles">
    {foreach from=$photos item=photo}
        <div class="tile oneandhalf oneandhalf-vertical" onclick="photosPreview(['{$photo->guid}'])" data-guid="{$photo->guid}" data-photo_title="{$photo->photo_title|escape:javascript}">
            <div class="tile-content image"><img src="{$url_index}/photos/thumb/{$photo->guid}"/></div>
            <div class="brand bg-dark opacity">
                <span class="text">{$photo->photo_title}</span>
            </div>
        </div>
    {/foreach}
    </div>
    <legend>{lang('photos_legend_2')}</legend>
    <div id="cloud">
    {foreach from=$tags item=tag}
        <a href="javascript:void(0);" onclick="photosShowForTag('{$tag.name|escape:javascript}')" class="{$tag.class}">{$tag.name}</a>
    {/foreach}
    </div>
</div>
{/strip}
