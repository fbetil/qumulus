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
    <i class="icon-search on-left"></i>
    {lang('research')}
</h1>
<div>
    <p>{lang('search_p_1')}</p>
    <form id="form_search" name="form_search"  method="POST" action="{$url_index}/search/post">
        <input name="csrf_test_name" type="hidden" value="{$csrf_hash}" />
        <div class="input-control text">
            <input type="text" name="search_terms" placeholder="{lang('research_in_qumulus')}" />
            <button class="btn-search"></button>
        </div>
    </form>
</div>

{* Search results *}
<div id="search-result" class="hidden">
    <h3>
        <em></em> {lang('search_h_1')} <em></em>
    </h3>
    <div>
        <p>{lang('search_p_2')}</p>
        <div class="button-set" data-role="button-set">
            <button class="active bg-darkGreen image-button">{lang('music')}<i class="icon-music bg-green"></i></button>
            <button class="active bg-darkOrange image-button">{lang('photos')}<i class="icon-pictures bg-orange"></i></button>
            <button class="active bg-darkRed image-button">{lang('movies')}<i class="icon-film bg-red"></i></button>
            <button class="active bg-darkBlue image-button">{lang('documents')}<i class="icon-libreoffice bg-cobalt"></i></button>
            <button class="active bg-darkBrown image-button">{lang('archives')}<i class="icon-file-zip bg-brown"></i></button>
        </div>
        <br>
    </div>
    <div id="search-tiles">
    </div>
</div>

{* Specific scripts *}
<script type="text/javascript">
    $(document).ready(function(){
        $("div[data-role=button-set]").buttonset();
    });

    $("#form_search").submit(function( event ) {
        event.preventDefault();
        
        $.ajax({
            url: $("#form_search").attr('action'),
            dataType: "json",
            type: 'POST',
            data: {
                'csrf_test_name': $("#form_search input[name=csrf_test_name]").val(),
                'search_terms': $("#form_search input[name=search_terms]").val()
            },
            success: function(data) {
                if(data.result) {
                    $('#search-tiles').empty();
                    $('#search-result').show();
                    $('#search-result > h3:first em:first').html(data.documents.length);
                    $('#search-result > h3:first em:last').html($("#form_search input[name=search_terms]").val());
                    $(data.documents).each(function(){
                        $('#search-tiles').append(generateTile(this));
                    });
                }
            }
        });
    });
</script>
{/strip}
