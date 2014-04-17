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
    <i class="icon-home on-left"></i>
    {lang('home')}
</h1>
<div>
    <p>{lang('main_p_2')}</p>
    <br>
    <p>{sprintf(lang('main_p_5'),$documentscount)}</p>
    {if $user_is_admin}
        <legend>{lang('main_p_20')}</legend>
        {foreach from=$tasks item=task}
            <p>{$task->status}</p>
        {/foreach}
    {/if}
</div>
{/strip}
