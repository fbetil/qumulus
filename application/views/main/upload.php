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
    <i class="icon-upload-2 on-left"></i>
    {lang('uploads')}
</h1>
<div>
    <p>{sprintf(lang('main_p_21'), $destination)}</p>
    <div id="dropDiv" ondrop="doUpload(event)" ondragover="event.preventDefault();" ondragenter="$('#dropDiv').addClass('doDrag')" ondragleave="$('#dropDiv').removeClass('doDrag')" class="bg-grayLighter">
        <p>{sprintf(lang('main_p_22'), $file_format)}</p>
    </div>
    <br>
    <div id="uploadedDiv">
    
    </div>
</div>
{/strip}
