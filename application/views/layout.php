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
<!DOCTYPE html>
{strip}
<html>
	<head>
		<title>{lang('qumulus')}</title>
		
		<meta charset="utf-8">
		
		<meta name="description" content="personnal cloud software" />
		<meta name="keywords" content="qumulus,cloud,personal,lightweight" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <meta name="author" content="Florian BETIL" />

        <link rel="icon" href="{$url_base}/assets/img/favicon.ico" />
        <link rel="stylesheet" href="{$url_base}/assets.php?css=./assets/third_party/Metro-UI-CSS/min/iconFont.min.css,./assets/third_party/Metro-UI-CSS/min/metro-bootstrap.min.css,./assets/third_party/Metro-UI-CSS/min/metro-bootstrap-responsive.min.css,./assets/css/qumulus.css,./assets/third_party/photobox/photobox/photobox.css">
        <script src="{$url_base}/assets.php?js=./assets/third_party/jQuery/jquery-2.1.0.min.js,./assets/third_party/jQueryUI/jquery-ui.min.js"></script>

	</head>
	<body>
        <div class="metro">
    		{* Navigation Bar *}
            <nav class="navigation-bar light">
                <div class="navigation-bar-content container">
                    <div class="element">
                        <a class="dropdown-toggle" href="#">{lang('qumulus')}</a>
                        <ul class="dropdown-menu" data-role="dropdown">
                            {if $user_is_admin && !$user_anonymous }
                            <li><a href="javascript:void(0);" onclick="startApp('main/parameters')" data-appname="main/parameters"><i class="icon-cog on-left"></i>{lang('settings')}</a></li>
                            <li class="divider"></li>
                            {/if}
                            <li><a href="http://fbetil.bl.ee/index.php?id=qumulus" target="blank"><img src="./assets/img/favicon.png" class="on-left" alt="{lang('homepage')}" />{lang('homepage')}</a></li>
                        </ul>
                    </div>
                    
                    {if $user_is_logged && !$user_anonymous}
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('home')}" href="javascript:void(0);" onclick="startApp('main/home')" data-appname="main/home"><i class="icon-home"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('research')}" href="javascript:void(0);" onclick="startApp('search/index')" data-appname="search/index"><i class="icon-search"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('files')}" href="javascript:void(0);" onclick="startApp('files/index')" data-appname="files/index"><i class="icon-folder"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('shares')}" href="javascript:void(0);" onclick="startApp('shares/index')" data-appname="shares/index"><i class="icon-share-2"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('music')}" href="javascript:void(0);" onclick="startApp('music/index')" data-appname="music/index"><i class="icon-music"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('photos')}" href="javascript:void(0);" onclick="startApp('photos/index')" data-appname="photos/index"><i class="icon-pictures"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('movies')}" href="javascript:void(0);" onclick="startApp('movies/index')" data-appname="movies/index"><i class="icon-film"></i></a>
                    <div class="element-divider"></div>
                    <a class="element brand" title="{lang('uploads')}" href="javascript:void(0);" onclick="startApp('main/upload')" data-appname="main/upload"><i class="icon-upload-2"></i></a>
                    <div class="element-divider"></div>
    
                    <div class="element place-right">
                        <a class="dropdown-toggle" href="#">{$user_label}</a>
                        <ul class="dropdown-menu place-right" data-role="dropdown">
                            <li><a href="javascript:void(0);" onclick="startApp('main/preferences')" data-appname="main/preferences"><i class="icon-equalizer on-left"></i>{lang('preferences')}</a></li>
                            <li class="divider"></li>
                            <li><a href="javascript:void(0)" onclick="doLogout()"><i class="icon-exit on-left"></i>{lang('logout')}</a></li>
                        </ul>
                    </div>
                    <div class="element-divider place-right"></div>
    		        <a id="nav-button-jukebox" title="{lang('jukebox')}" class="element brand place-right" href="javascript:void(0);" onclick="toggle('jukebox');" ><i class="icon-playlist"></i></a>
                    <div class="element-divider place-right"></div>
                    <div class="place-right">
                        {include file="music/player.php"}
                    </div>
                    <div class="element-divider place-right"></div>
                    {/if}
                </div>
            </nav>
            
            {* Player *}
            {if $user_is_logged && !$user_anonymous}
    		<div id="jukebox" class="hidden  bg-grayLighter">
    		    <div class="jp-cover"></div>
    		    <div class="jp-playlist"></div>
    		</div>
    		{/if}
    		
    		{* Page *}
    		<div class="page">
    		    {* Content *}
    		    <div class="page-region">
    		        <div class="page-region-content container">
                    {block name="app"}{/block}
    		        </div>
    		    </div>
    		    
                {* Footer *}
                <div class="page-footer">
                    <div class="page-footer-content container">
                    
                    </div>
                </div>
    		</div>
    		
    		{* Languages and dynamic variables *}
    		<script>
    		    var q_url_index = '{$url_index}';
    		    var q_url_base = '{$url_base}';
    		    
    		    var q_lang_delete = '{lang("lang_delete")|escape:javascript}';
    		    var q_lang_please_wait = '{lang("please_wait")|escape:javascript}';
    		    var q_lang_files_message_1 = '{lang("files_message_1")|escape:javascript}';
    		    var q_lang_music_message_1 = '{lang("music_message_1")|escape:javascript}';
    		    var q_lang_documents_message_2 = '{lang("documents_message_2")|escape:javascript}';
    		    var q_lang_photos_message_1 = '{lang("photos_message_1")|escape:javascript}';
    		    var q_lang_shares_message_6 = '{lang("shares_message_6")|escape:javascript}';
    		    var q_lang_shares_message_4 = '{lang("shares_message_4")|escape:javascript}';
    		    var q_lang_shares_message_2 = '{lang("shares_message_2")|escape:javascript}';
    		    var q_lang_shares_message_3 = '{lang("shares_message_3")|escape:javascript}';
    		    
    		    var q_csrf_hash = '{$csrf_hash|default:""}';
    		    var q_notify = '{$notify|default:""|escape:javascript}';
    		    var q_app = '{$app|default:""}';
    		    
    		    var q_user_prefs_files_restore_last_path = {$user_prefs.files_restore_last_path|default:'false'};
    		    var q_user_prefs_shares_restore_last_path = {$user_prefs.shares_restore_last_path|default:'false'};
    		    var q_user_prefs_music_restore_last_playlist = {$user_prefs.music_restore_last_playlist|default:'false'};
    		    var q_user_prefs_startapp = '{$user_prefs.startapp|default:""}';
    		    
    		    var q_user_anonymous = {$user_anonymous|var_export:true};
    		</script>
            <script src="{$url_base}/assets.php?js=./assets/third_party/Metro-UI-CSS/min/metro.min.js,./assets/third_party/jQuery.jPlayer/jquery.jplayer.min.js,./assets/third_party/jQuery.jPlayer/add-on/jplayer.playlist.min.js,./assets/third_party/photobox/photobox/jquery.photobox.min.js,./assets/js/qumulus.js"></script>
        </div>
	</body>
</html>
{/strip}
