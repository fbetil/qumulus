<?php
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

//File format mapping
$config['file_format_map'] = array(
    'jpeg' => 'image',
    'jpg' => 'image',
    'png' => 'image',
    
    'ogg' => 'audio',
    'flac' => 'audio',
    'wav' => 'audio',
    'mp3' => 'audio',
    
    'odt' => 'document',
    'fodt' => 'document',
    'odp' => 'document',
    'fodp' => 'document',
    'ods' => 'document',
    'fods' => 'document',
    'doc' => 'document',
    'docx' => 'document',
    'xls' => 'document',
    'xlsx' => 'document',
    'ppt' => 'document',
    'pptx' => 'document',
    'pdf' => 'document',
    'mht' => 'document',
    
    'zip' => 'archive',
    'rar' => 'archive'
    );
    
$config['file_office_extensions'] = array('odt','fodt','odp','fodp','ods','fods','doc','docx','xls','xlsx','ppt','pptx');
    
//tiles configuration
$config['tiles_icon'] = array(
    'audio'=>'music',
    'image'=>'pictures',
    'document'=>'libreoffice',
    'pictures_album'=>'grid-view',
    'music_album'=>'grid-view',
    'archive'=>'file-zip'
    );
$config['tiles_bgcolor'] = array(
    'audio'=>'green',
    'image'=>'orange',
    'document'=>'darkBlue',
    'photos_album'=>'orange',
    'music_album'=>'green',
    'archive'=>'brown'
    );
$config['tiles_image'] = array(
    'audio'=>'/music/cover/',
    'image'=>'/photos/thumb/',
    'document'=>'/documents/thumb/',
    'photos_album'=>'/photos/thumb/',
    'music_album'=>'/music/cover/',
    'archive'=>'/documents/thumb/'
    );
    
//Internal viewer supperted formats
$config['viewerjs_formats'] = array('odt','odp','ods','fodt','fods','pdf');
    
//Database document fields mapping
$config['document_fields']['title'] = array('image'=>'photo_title', 'audio'=>'track_title', 'document'=>'file_basename', 'archive'=>'file_basename');
$config['document_fields']['album'] = array('image'=>null, 'audio'=>'track_album', 'document'=>null, 'archive'=>null);
$config['document_fields']['artist'] = array('image'=>null, 'audio'=>'track_artist', 'document'=>null, 'archive'=>null);
