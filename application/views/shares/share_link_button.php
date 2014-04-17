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
<div class="button-dropdown on-left-more">
    <i class="icon-share-2 dropdown-toggle" title="{lang('share')}"></i>
    <ul class="dropdown-menu" data-role="dropdown">
        {foreach from=$shares item=share}
        <li class="place-left"><a href="javascript:void(0)" onclick="sharesAction({$share->id}, {if $multiple}{literal}$('table.files-tableview td').map(function(){return $(this).attr('data-guid')}).get(){/literal}{else}['{$document->guid}']{/if})" >{$share->name}</a></li>
        {/foreach}
    </ul>
</div>
{/strip}
