/*

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

*/

function doLogout(){
    $.ajax({
        url: q_url_index+'/main/logout',
        cache: false,
        dataType: "html",
        type: 'GET'
    }).done(function(data){
        location.reload(true);
    });
}

function doUpload(event) {
    event.preventDefault();
    
    $(event.dataTransfer.files).each(function(){
        var fd = new FormData();
        fd.append('csrf_test_name', q_csrf_hash);
        fd.append('file', this);
        
        $.ajax({
            type: "POST",
            url: q_url_index+"/main/upload",
            data: fd,
            contentType: false,
            dataType: "json",
            processData: false,
            success: function(data){
                switch(data.result){
                    case true:
                        $('#uploadedDiv').append(generateTile(data.tile));
                        break;
                    case false:
                        $.Notify({ content: data.msg, style: { background: 'red', color: 'black' } });
                        break;
                }
            }
        });
    });
}

function deleteSource(sourceid){
    $("#table_sources tr[data-sourceid='"+sourceid+"']").remove();
}

function addSource(sourceid){
    var rand = 'new'+Math.floor((Math.random()*100000)+1000);
    
    $("#table_sources tbody tr:first").clone().appendTo("#table_sources");
    var newTr = $("#table_sources tbody tr:last");
    
    $(newTr).attr('data-sourceid',rand);
    $(newTr).find('input[name="sources_name_1"]').attr('name','sources_name_'+rand).val('');
    $(newTr).find('input[name="sources_path_1"]').attr('name','sources_path_'+rand).val('');
    $(newTr).find('select[name="sources_types_1"]').attr('name','sources_types_'+rand).val('all');
    $(newTr).find('td:last').append('<a href="javascript:void(0)" onclick="deleteSource(\''+rand+'\')">'+q_lang_delete+'</a>');
}

function startTask(taskname, param1) {
    event.preventDefault();
    
    if (!param1) param1 = '';
    
    $.ajax({
        url: q_url_index+'/'+taskname+'/'+param1,
        cache: false,
        timeout: 1000,
    }).always(function(data){
        startApp('main/home');
    });
}
    
function notify(notification){
    switch(notification.type){
        case 'notice':
            bgcolor = 'green';
            fgcolor = 'white';
            break;
        case 'warning':
            bgcolor = 'red';
            fgcolor = 'white';
            break;
    }
    
    $.Notify({ style: { background: bgcolor, color: fgcolor }, caption: notification.title, content: notification.content });
}

function formGenerateDataObject(formname){
    var formData = { };
    
    $("#"+formname+" input, #"+formname+" select").each(function(){
        switch($(this).attr('type')){
            case 'checkbox':
                formData[$(this).attr('name')] = ($(this).prop('checked') === true) ? 1 : 0;
                break;
            default:
                formData[$(this).attr('name')] = $(this).val();
                break;
        }
    });
    
    return formData;
}

function toggle(item){
    $('#'+item).toggle();
    
    ($('#'+item).is(':visible'))?$("#nav-button-"+item).addClass('bg-grayLight'):$("#nav-button-"+item).removeClass('bg-grayLight');
}

function startApp(application) {
    /* Show waiting message */
    $('div.page-region-content').html('<p><img src="'+q_url_base+'/assets/img/icon_loading.gif"/>'+q_lang_please_wait+'</p>');

    /* Start application */
    $.ajax({
        url: q_url_index+'/'+application,
        cache: false,
        dataType: "html",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'application': application
        },
        success: function(data) {
            $('div.page-region-content').html(data);
        },
        statusCode: {
            403: function() {
                window.location.href = q_url_index;
            }
        }
    });
        
    /* If app is files/index, restore last path */
    if (q_user_prefs_files_restore_last_path == true && localStorage.getItem('files_last_path') && application == 'files/index') filesNavigate(localStorage.getItem('files_last_path'));

    /* If app is shares/index, restore last path */
    if (q_user_prefs_shares_restore_last_path == true &&  localStorage.getItem('shares_last_path') && application == 'shares/index') sharesNavigate(localStorage.getItem('shares_last_path'));
    
    /* Activate navbar button */
    $("nav a.element[data-appname]").removeClass('bg-grayLight');
    $("nav a.element[data-appname='"+application+"']").addClass('bg-grayLight');
    
    /* Store last application */
    localStorage.setItem('lastapp', application);
}

function generateTile(document){
    var tile =  '<div class="tile oneandhalf oneandhalf-vertical" data-tiletype="'+document.type+'" onclick="photosPreview([\''+document.guid+'\']);" data-guid="'+document.guid+'" data-photo_title="'+document.title+'">';
        tile += '<div class="tile-content image"><img src="'+document.tile_image+'"/></div>';
        tile += '<div class="brand bg-dark opacity">';
        tile += '<span class="text">'+(document.title || '')+'<br>'+(document.album || '')+'<br>'+(document.artist || '')+'</span>';
        tile += '<span class="badge bg-'+document.tile_bgcolor+'"><i class="icon-'+document.tile_icon+'"></i></span>';
        tile += '</div>';
        tile += '</div>';

    return tile;
}

function filesNavigate(path){
    if (!path) path = '/';
    $.ajax({
        url: q_url_index+'/files/index',
        cache: false,
        dataType: "html",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'path': path,
            'application': 'files/index'
        },
        success: function(data) {
            $('div.page-region-content').html(data);
        },
        statusCode: {
            403: function() {
                window.location.href = q_url_index;
            }
        }
    });
    
    /* Store last path */
    if(path) localStorage.setItem('files_last_path', path);
}

function sharesNavigate(path){
    if (!path) path = '/';
    $.ajax({
        url: q_url_index+'/shares/index',
        cache: false,
        dataType: "html",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'path': path,
            'application': 'shares/index'
        },
        success: function(data) {
            $('div.page-region-content').html(data);
        },
        statusCode: {
            403: function() {
                window.location.href = q_url_index;
            }
        }
    });
    
    /* Store last path */
   localStorage.setItem('shares_last_path', path);
}

function filesDownload(guids){
    
    if (guids.length === 0) {
        $.Notify({ content: q_lang_files_message_1, style: { background: 'blue', color: 'white' } });
        return;
    }
    
    var download = jQuery('<form>', {
        'action': q_url_index+'/files/download',
        'method': 'POST'
    }).append(jQuery('<input>', {
        'name': 'csrf_test_name',
        'value': q_csrf_hash,
        'type': 'hidden'
    })).append(jQuery('<input>', {
        'name': 'download_documents',
        'value': JSON.stringify(guids),
        'type': 'hidden'
    })).appendTo('body');

    download.submit();
    
    event.stopPropagation();
    event.cancelBubble = true;
}

function fileView(guid, filename) {
    window.open(q_url_base+'/assets/third_party/Viewer.js/#../../../index.php/documents/view/'+guid+'/'+filename);
    event.stopPropagation();
    event.cancelBubble = true;
}

function filesMusicPlay(guids){
    var tracks = Array();
    
    $(guids).each(function(){
        var track = $('td[data-guid="'+this+'"]');
        tracks.push({
            title: $(track).attr('data-track_artist')+' :: '+$(track).attr('data-track_album')+' :: '+$(track).attr('data-track_title'),
            mp3: q_url_index+'/music/play/'+this
            /*ogg: q_url_index+'/music/play/'+this */
            });
    });

    
    if (tracks.length === 0) {
        $.Notify({ content: q_lang_music_message_1, style: { background: 'blue', color: 'white' } });
        return;
    }

    $("#jplayer").jPlayer("destroy");
    
    new jPlayerPlaylist({
            jPlayer: "#jplayer",
            cssSelectorAncestor: "#jplayer_container"
        },
        tracks,
        {
            swfPath: q_url_base+"/assets/third_party/jQuery.jPlayer.2.5.0/",
            supplied: "oga, mp3",
            wmode: "window",
            smoothPlayBar: true,
            keyEnabled: true
        });

    /* Store last playlist */
    localStorage.setItem('music_last_playlist', guids);
}

function filesDelete(guids){
    if (guids.length === 0) {
        $.Notify({ content: q_lang_documents_message_2, style: { background: 'blue', color: 'white' } });
        return;
    }
    
    $.ajax({
        url: q_url_index+'/documents/delete',
        cache: false,
        dataType: "json",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'documents_guids': JSON.stringify(guids)
        }
    }).done(function(data){
        notify(data);
        filesNavigate();
    });
    
    event.stopPropagation();
    event.cancelBubble = true;
}

function photosShowForTag(tag){
    $.ajax({
        url: q_url_index+'/photos/tag',
        cache: false,
        dataType: "html",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'tag': tag,
            'application': 'photos/tag'
        },
        success: function(data) {
            $('div.page-region-content').html(data);
        },
        statusCode: {
            403: function() {
                window.location.href = q_url_index;
            }
        }
    });
}

function photosPreview(guids) {
    var photos = $('<div>');

    if (guids.length === 0) {
        $.Notify({ content: q_lang_photos_message_1, style: { background: 'blue', color: 'white' } });
        return;
    }
    
    $(guids).each(function(){
        var photo = $('[data-guid="'+this+'"]');
        $(photos).append('<a href="'+q_url_index+'/photos/preview/'+this+'"><img src="'+q_url_index+'/photos/thumb/'+this+'" title="'+$(photo).attr('data-photo_title')+'"></a>');
    });

    $(photos).photobox('a', { thumbs:true }).find('a:first').click();
    
}

function sharesOpen(token){
    window.open(q_url_index+'/shares/share/'+token);
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesEdit(shareid){
    $('tr[data-share-editor='+shareid+']').toggle();
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesAction(shareid, guids, action, navigate){
    if (!action) action = 'link';
    
    if (guids.length === 0) {
        $.Notify({ content: q_lang_shares_message_6, style: { background: 'blue', color: 'white' } });
        return;
    }
    
    $.ajax({
        url: q_url_index+'/shares/action',
        cache: false,
        dataType: "json",
        type: 'POST',
        data: {
            'csrf_test_name': q_csrf_hash,
            'share_id': shareid,
            'share_documents': JSON.stringify(guids),
            'share_action': action
        }
    }).done(function(data){
        notify(data);
        if (navigate) sharesNavigate(navigate);
    });
    
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesNotify(token, password ){
    var subject = q_lang_shares_message_4;
    var body = q_lang_shares_message_2+'. '+q_url_index+"/shares/share/"+token+" . ";
    if (password) body+= q_lang_shares_message_3;

    var uri = "mailto:&subject=";
    uri += encodeURIComponent(subject);
    uri += "&body=";
    uri += encodeURIComponent(body);
    window.open(uri);
    
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesMusicPlay(guids){
    filesMusicPlay(guids);
}

function sharesAnonymousNavigate(token, path){

    var navigate = jQuery('<form>', {
        'action': q_url_index+'/shares/share/'+token,
        'method': 'POST'
    }).append(jQuery('<input>', {
        'name': 'csrf_test_name',
        'value': q_csrf_hash,
        'type': 'hidden'
    })).append(jQuery('<input>', {
        'name': 'path',
        'value': path,
        'type': 'hidden'
    })).append(jQuery('<input>', {
        'name': 'application',
        'value': 'shares/share',
        'type': 'hidden'
    })).appendTo('body');

    navigate.submit();
}

function sharesAnonymousDownload(guids){
    
    if (guids.length === 0) {
        alert(q_lang_files_message_1);
        return;
    }
    
    var download = jQuery('<form>', {
        'action': q_url_index+'/files/download',
        'method': 'POST'
    }).append(jQuery('<input>', {
        'name': 'csrf_test_name',
        'value': q_csrf_hash,
        'type': 'hidden'
    })).append(jQuery('<input>', {
        'name': 'download_documents',
        'value': JSON.stringify(guids),
        'type': 'hidden'
    })).appendTo('body');

    download.submit();
    
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesAnonymousView(guid, filename) {
    window.open(q_url_base+'/assets/third_party/Viewer.js/#../../../index.php/documents/view/'+guid+'/'+filename);
    event.stopPropagation();
    event.cancelBubble = true;
}

function sharesAnonymousPhotosPreview(guids) {
    var photos = $('<div>');

    if (guids.length === 0) {
        $.Notify({ content: q_lang_photos_message_1, style: { background: 'blue', color: 'white' } });
        return;
    }
    
    $(guids).each(function(){
        var photo = $('[data-guid="'+this+'"]');
        $(photos).append('<a href="'+q_url_index+'/photos/preview/'+this+'"><img src="'+q_url_index+'/photos/thumb/'+this+'" title="'+$(photo).attr('data-photo_title')+'"></a>');
    });

    $('#gallery_container').html();
    $(photos).appendTo('#gallery_container').photobox('a', { thumbs:true }).find('a:first').click();
    
}

/* On page ready, execute this */
$(document).ready(function(){
    //Align to center
    $('.middlescreen').css({
        position:'absolute',
        left: ($(window).width() - $('.middlescreen').outerWidth())/2,
        top: ($(window).height() - $('.middlescreen').outerHeight())/2
    });
    
    /* Bind jPlayer native playlist changes */
    $('#jplayer_container .jp-playlist').bind('DOMSubtreeModified', function() {
        $('#jukebox .jp-playlist').html($('#jplayer_container .jp-playlist').html());
        $('#jukebox .jp-playlist ul').show();
    });
    
    /* Show flash notification */
    if (q_notify !== '') $.Notify({ content: q_notify, style: { background: 'blue', color: 'white' } });
    
    new jPlayerPlaylist({
            jPlayer: "#jplayer",
            cssSelectorAncestor: "#jplayer_container"
        });

    /* Start desired application for logged user*/
    if (q_user_anonymous == true){
        /* Do nothing */
    }else if (q_app !== '') {
        startApp(q_app);
    }else if(q_user_prefs_startapp == 'last'){
        (localStorage.getItem('lastapp')) ? startApp(localStorage.getItem('lastapp')) : startApp('main/home');
    }else if (q_user_prefs_startapp !== ''){
        startApp(q_user_prefs_startapp);
    }
    
    /* Restore last playlist */
    if (q_user_prefs_music_restore_last_playlist == true && localStorage.getItem('music_last_playlist')) filesMusicPlay(localStorage.getItem('music_last_playlist'));
});