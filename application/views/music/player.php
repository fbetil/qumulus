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
<div id="player">
    <div id="jplayer" class="jp-jplayer"></div>
    <div id="jplayer_container" class="jp-audio">
        <div class="jp-type-playlist">
            <div class="jp-gui jp-interface">
                <ul class="jp-controls">
                    <li><a href="javascript:;" class="jp-play fg-gray" tabindex="1"><i class="icon-play"></i></a></li>
                    <li><a href="javascript:;" class="jp-pause fg-gray" tabindex="1"><i class="icon-pause"></i></a></li>
                    <li><a href="javascript:;" class="jp-stop fg-gray" tabindex="1"><i class="icon-stop"></i></a></li>
                    <li><a href="javascript:;" class="jp-previous fg-gray" tabindex="1"><i class="icon-first"></i></a></li>
                    <li><a href="javascript:;" class="jp-next fg-gray" tabindex="1"><i class="icon-last"></i></a></li>
                </ul>
                <div class="jp-progress">
                    <div class="jp-seek-bar">
                        <div class="jp-play-bar"></div>
                    </div>
                </div>
            </div>

            <div class="jp-no-solution">
                <span>{lang('update_required')}</span>
                {sprintf(lang('flash_update_required'), '<a href="http://get.adobe.com/flashplayer/" target="_blank">')}</a>.
            </div>
            
            <div class="hidden">
        		<div class="jp-playlist">
        			<ul>
        				<li></li>
        			</ul>
        		</div>
		    </div>
        </div>
    </div>
</div>
{/strip}

