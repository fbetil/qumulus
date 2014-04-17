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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Music extends CI_Controller {

	public function Index()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
        //Retrieve tags
        $tags = $this->qumulus->databaseGetTags(array('audio'))['audio'];

        //Get randoms albums
        $albums = array();
        $file_formats = array_keys($this->config->item('file_format_map'), 'audio');
        
        $this->db->select('min(guid) as guid, track_artist, track_album');
        $this->db->where_in('file_format', $file_formats);
        $this->db->group_by('track_artist, track_album');
        $q_documents = $this->db->get('documents');
        
        $albums = $q_documents->result();
        $rand_keys = array_flip(array_rand($albums, 12));

        $rand_albums = array_intersect_key($albums, $rand_keys);
        
	    //Render view
	    $this->qumulus->htmlRenderView('music'.DS.'index.php', array('tags'=>$tags, 'rand_albums'=>$rand_albums, 'albums'=>$albums));
	}
	
	public function Play($guid) {
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    
	    //Retrieve document
        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->fileRenderEmptyPng();

        $document = $q_document->row();

        //Retrieve file path
	    $real_path = $this->qumulus->sources[$document->file_source]['path'].$document->file_dirname.DS.$document->file_basename;

	    //Send file
        $this->qumulus->fileStreamFile($real_path, $guid.'.'.$document->file_format, $document->file_mimetype);
	}
	
	public function Cover($guid) {
	    //Security verification
	    if (!$this->qumulus->authHaveAuthorization('logged') && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();
	    
	    //Retrieve document
        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->fileRenderEmptyPng();

        $document = $q_document->row();

	    //Get album cover real path
	    $cover_path = DATA_PATH.DS.'music_covers'.DS.md5($document->track_artist.'/'.$document->track_album).'.jpg';

        if(file_exists($cover_path)){   //Return existing cover
            $this->qumulus->fileSendFile($cover_path, basename($cover_path), 'image/jpeg');
        }else{ //Return empty image
            $this->qumulus->fileRenderEmptyPng();
        }
	}

}
