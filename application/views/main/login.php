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
    <i class="icon-key on-left"></i>
    {lang('login')}
</h1>
<div class="span5 middlescreen" >
    <p>{lang('main_p_1')}</p>
    <form id="form_login" name="form_login"  method="POST" action="{$url_index}/main/login">
        <div>
        <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
        <input name="login_username" type="text" value="" placeholder="{lang('username')}" data-transform="input-control" />
        <input name="login_password" type="password" value="" placeholder="{lang('password')}" data-transform="input-control" />
        </div>
        <input name="form_login" type="submit" value="{lang('connect')}" />
    </form>
</div>

{* Scripts *}
<script>
    $(document).ready(function(){
        $("#form_login").submit(function( event ) {
            event.preventDefault();
    
            $.ajax({
                url: $("#form_login").attr('action'),
                dataType: "json",
                type: 'POST',
                data: formGenerateDataObject('form_login'),
            }).always(function(data){
                if(data.type == 'notice'){
                    window.location.reload();
                } else {
                    notify(data);
                }
            });
        });
    });
</script>
{/strip}
{/block}
