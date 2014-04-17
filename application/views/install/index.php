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
    <i class="icon-database on-left"></i>
    {lang('setup')}
</h1>
<div class="span7 middlescreen" >
    {if (!empty($message)) }{$message}{/if}
    <p>{lang('install_p_1')}</p>
    <form id="form_setup" name="form_setup"  method="POST" action="{$url_index}/install/setup">
        <div>
        <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
        <input name="setup_username" type="text" value="" placeholder="{lang('install_label_1')}" data-transform="input-control" />
        <input name="setup_lastname" type="text" value="" placeholder="{lang('install_label_2')}" data-transform="input-control" />
        <input name="setup_firstname" type="text" value="" placeholder="{lang('install_label_3')}" data-transform="input-control" />
        <input name="setup_email" type="text" value="" placeholder="{lang('install_label_4')}" data-transform="input-control" />
        <input name="setup_password" type="password" value="" placeholder="{lang('install_label_5')}" data-transform="input-control" />
        <input name="setup_password_confirm" type="password" value="" placeholder="{lang('install_label_6')}" data-transform="input-control" />
        </div>
        <input name="form_setup" type="submit" value="{lang('setup')}" />
    </form>
</div>
{/strip}
{/block}
