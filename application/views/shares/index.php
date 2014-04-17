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
    <i class="icon-share-2 on-left"></i>
    {lang('shares')}
</h1>
<div>
    <p>{lang('shares_p_1')}</p>
    {if $here == '/'}<button class="default place-right" onclick="$('div[data-share-editor=0]').show()" >{lang('new')}</button>{/if}
    <br>
    <nav class="breadcrumbs">
        <ul>
            <li><a href="javascript:void(0);" onclick="sharesNavigate();"><i class="icon-share-2"></i></a></li>
            {foreach from=$places item=place name=places}
            <li {if $smarty.foreach.places.last}class="active"{/if}><a href="javascript:void(0);" onclick="sharesNavigate('{$place.path|escape:javascript}');">{$place.name}</a></li>
            {/foreach}
        </ul>
    </nav>
    
    {* New share form *}
    {if $here == '/'}
    <div data-share-editor="0" class="hidden">
        <br>
        <form id="form_share_0" name="form_share_0"  method="POST" action="{$url_index}/shares/post">
            <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
            <input name="share_id" type="hidden" value="0" />
            <label>{lang('shares_label_1')}</label>
            <div class="input-control text" data-role="input-control">
                <input type="text" name="share_name" placeholder="{lang('shares_label_1')}" value="">
                <button class="btn-clear" tabindex="-1"></button>
            </div>
            <div class="input-control switch margin10" data-role="input-control">
                <label>
                    {lang('shares_label_2')}{nbs(6)}
                    <input type="checkbox" name="share_protected" />
                    <span class="check"></span>
                </label>
            </div>
            <div class="input-control text" data-role="input-control">
                <input type="password" name="share_password" placeholder="{lang('password')}" value="">
                <button class="btn-reveal" tabindex="-1"></button>
            </div>
            <div class="input-control select">
                <label>{lang('shares_label_4')}</label>
                <select name="share_visualization">
                <option value="tree" >{lang('shares_option_1')}</option>
                <option value="tiles" selected>{lang('shares_option_2')}</option>
                </select>
            </div>
            <div class="input-control switch margin10" data-role="input-control">
                <label>
                    {lang('shares_label_5')}{nbs(6)}
                    <input type="checkbox" name="share_new_token" checked/>
                    <span class="check"></span>
                </label>
            </div>
            <br>
            <input name="share_submit" type="submit" value="{lang('save')}" class="default place-right" />
        </form>
        <br>
    </div>
    {/if}

    <table class="table hovered shares-tableview">
        <thead>
            <tr><th>
                    {sprintf(lang('shares_th_1'), count($documents))}
                    <span class="place-right">
                        {if $here != '/'}
                        <i class="icon-remove on-left-more" onclick="sharesAction({$currentshare->id}, {literal}$('table.shares-tableview td').map(function(){return $(this).attr('data-guid')}).get(){/literal}, 'unlink', '{$place.path|escape:javascript}')" title="{lang('remove')}"></i>
                        {/if}
                    </span>
            </th></tr>
        </thead>
        <tbody>
            {foreach from=$shares item=share}
                <tr><td onclick="sharesNavigate('/s{$share->id}/');">
                    <i class="icon-share-2 on-left-more"></i>
                    {$share->name}
                    <span class="place-right">
                        <i class="icon-pencil on-left-more" onclick="sharesEdit({$share->id})" title="{lang('edit')}"></i>
                        <i class="icon-eye on-left-more" onclick="sharesOpen('{$share->token}')" title="{lang('open')}"></i>
                        <i class="icon-mail on-left-more" onclick="sharesNotify('{$share->token}'{if $share->password != null }, true{/if})" title="{lang('notify')}"></i>
                    </span>
                </td></tr>
                <tr data-share-editor="{$share->id}" class="hidden bg-grayLighter">
                    <td>
                        <form id="form_share_{$share->id}" name="form_share_{$share->id}"  method="POST" action="{$url_index}/shares/post">
                            <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
                            <input name="share_id" type="hidden" value="{$share->id}" />
                            <label>{lang('shares_label_1')}</label>
                            <div class="input-control text" data-role="input-control">
                                <input type="text" name="share_name" placeholder="{lang('shares_label_1')}" value="{$share->name}">
                                <button class="btn-clear" tabindex="-1"></button>
                            </div>
                            <div class="input-control switch margin10" data-role="input-control">
                                <label>
                                    {lang('shares_label_2')}{nbs(6)}
                                    <input type="checkbox" name="share_protected" {if $share->password != null }checked{/if}/>
                                    <span class="check"></span>
                                </label>
                            </div>
                            <div class="input-control text" data-role="input-control">
                                <input type="password" name="share_password" placeholder="{lang('password')}" value="">
                                <button class="btn-reveal" tabindex="-1"></button>
                            </div>
                            <div class="input-control select">
                                <label>{lang('shares_label_4')}</label>
                                <select name="share_visualization">
                                <option value="tree" {if $share->visualization == 'tree' }selected{/if}>{lang('shares_option_1')}</option>
                                <option value="tiles" {if $share->visualization == 'tiles' }selected{/if}>{lang('shares_option_2')}</option>
                                </select>
                            </div>
                            <div class="input-control switch margin10" data-role="input-control">
                                <label>
                                    {lang('shares_label_5')}{nbs(6)}
                                    <input type="checkbox" name="share_new_token" />
                                    <span class="check"></span>
                                </label>
                            </div>
                            <div class="input-control switch margin10 fg-darkRed" data-role="input-control">
                                <label>
                                    {lang('shares_label_8')}{nbs(6)}
                                    <input type="checkbox" name="share_delete" />
                                    <span class="check"></span>
                                </label>
                            </div>
                            <br>
                            <input name="share_token" type="submit" value="{lang('save')}" class="default place-right" />
                        </form>
                    </td>
                </tr>
            {/foreach}
            {foreach from=$subdirectories item=subdirectory}
                <tr><td onclick="sharesNavigate('{$subdirectory.path|escape:javascript}');">
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
                        <i class="icon-remove on-left-more" onclick="sharesAction({$currentshare->id}, ['{$document->guid}'], 'unlink', '{$place.path|escape:javascript}')" title="{lang('remove')}"></i>
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'audio'}
                    <td data-guid="{$document->guid}" data-track_title="{$document->track_title|escape}" data-track_album="{$document->track_album|escape}" data-track_artist="{$document->track_artist|escape}" data-track_format="{$document->file_format}">
                    <i class="icon-music on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        <i class="icon-remove on-left-more" onclick="sharesAction({$currentshare->id}, ['{$document->guid}'], 'unlink', '{$place.path|escape:javascript}')" title="{lang('remove')}"></i>
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'document'}
                    <td data-guid="{$document->guid}" >
                    <i class="icon-libreoffice on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        <i class="icon-remove on-left-more" onclick="sharesAction({$currentshare->id}, ['{$document->guid}'], 'unlink', '{$place.path|escape:javascript}')" title="{lang('remove')}"></i>
                    </span>
                    </td>
                {elseif $file_format_map[$document->file_format] == 'archive'}
                    <td data-guid="{$document->guid}" >
                    <i class="icon-file-zip on-left-more"></i>
                    {$document->file_basename}
                    <span class="place-right">
                        <i class="icon-remove on-left-more" onclick="sharesAction({$currentshare->id}, ['{$document->guid}'], 'unlink', '{$place.path|escape:javascript}')" title="{lang('remove')}"></i>
                    </span>
                    </td>
                {/if}
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>

{* Specific scripts *}
<script type="text/javascript">
    $("form").submit(function( event ) {
        var form = this;
        
        event.preventDefault();

        $.ajax({
            url: $(form).attr('action'),
            dataType: "json",
            type: 'POST',
            data: formGenerateDataObject($(form).attr('name'))
        }).always(function(data){
            notify(data);
            
            if(data.type == 'notice') sharesNavigate();
        });
    });
</script>
{/strip}
