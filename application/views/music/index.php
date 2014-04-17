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
    <i class="icon-music on-left"></i>
    {lang('music')}
</h1>
<div>
    <p>{lang('music_p_1')}</p>
    
    <legend>{lang('music_legend_1')}
        <span class="place-right">
            <i class="icon-play-alt on-left-more" onclick="" title="{lang('play')}"></i>
            <i class="icon-playlist on-left-more"></i>
        </span>
    </legend>
    
    <div id="music-tiles">
    {foreach from=$rand_albums item=album}
        <div class="tile oneandhalf oneandhalf-vertical" onclick="">
            <div class="tile-content image"><img src="{$url_index}/music/cover/{$album->guid}"/></div>
            <div class="brand bg-dark opacity">
                <span class="text">{$album->track_album}<br>{$album->track_artist}</span>
            </div>
        </div>
    {/foreach}
    </div>
    
    <table class="table hovered music-tableview">
        <thead>
            <tr><th>
                    {sprintf(lang('music_th_1'), count($albums))}
                    <span class="place-right">
                        <i class="icon-play-alt on-left-more" onclick="" title="{lang('play')}"></i>
                        <i class="icon-playlist on-left-more"></i>
                    </span>
            </th></tr>
        </thead>
        <tbody>
            {foreach from=$albums item=album}
                <tr>
                    <td onclick="musicShowAlbum()">
                    <i class="icon-music on-left-more"></i>
                    {$album->track_artist} - {$album->track_album}
                    <span class="place-right">
                        <i class="icon-play-alt on-left-more" onclick="" title="{lang('play')}"></i>
                        <i class="icon-playlist on-left-more"></i>
                    </span>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    <legend>{lang('music_legend_2')}</legend>
    <div id="cloud">
    {foreach from=$tags item=tag}
        <a href="javascript:void(0);" onclick="musicShowForTag('{$tag.name|escape:javascript}')" class="{$tag.class}">{$tag.name}</a>
    {/foreach}
    </div>
</div>
{/strip}
