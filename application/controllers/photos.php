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

class Photos extends CI_Controller {

	public function Index()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
        //Retrieve tags
        $tags = $this->qumulus->databaseGetTags(array('image'))['image'];
        
        //Get randoms photos
        $photos = array();
        $file_formats = array_keys($this->config->item('file_format_map'), 'image');
        $count = $this->db->where_in('file_format', $file_formats)->count_all_results('documents');
        $offset = rand(1, $count - 12);

        $this->db->where_in('file_format', $file_formats);
        $this->db->order_by('guid');
        $q_documents = $this->db->get('documents', 12, $offset);
        
        $photos = $q_documents->result();
        
	    //Render view
	    $this->qumulus->htmlRenderView('photos'.DS.'index.php', array('tags'=>$tags, 'photos'=>$photos));
	}
	
	public function Tag(){
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    //Retrieve photos
        $file_formats = array_keys($this->config->item('file_format_map'), 'image');
        
        $this->db->where_in('file_format', $file_formats);
        $this->db->like('file_tags', '| '.$this->input->post('tag').' |');
        $this->db->order_by('photo_title');
        $photos = $this->db->get('documents')->result();
        
        //Retrieve associated tags
        $tags = $this->qumulus->databaseGetTags(array('image'))['image'];
        $photos_tags = array();
        $linked_tags = array();
        foreach($photos as $photo){
            $photos_tags = array_merge($photos_tags, $this->qumulus->databaseConvertToArray($photo->file_tags));
        }
        foreach($photos_tags as $tag){
            if ($tag == $this->input->post('tag')) continue;
            
            //toolRecursive_array_search
        }

        //Render view
	    $this->qumulus->htmlRenderView('photos'.DS.'tag.php', array('tag'=>$this->input->post('tag'), 'photos'=>$photos, 'tags'=>$linked_tags));
	}
	
	public function Preview($guid) {
	    //Security verification
	    if (!$this->qumulus->authHaveAuthorization('logged') && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();
	    
	    //Retrieve document
        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->fileRenderEmptyPng();

        $document = $q_document->row();

	    //Get info from file real path
	    $pseudo_path = DS.$document->file_source.$document->file_dirname.DS.$document->file_basename;
	    $real_path = $this->qumulus->sources[$document->file_source]['path'].$document->file_dirname.DS.$document->file_basename;
	    
	    //Generate preview if necessary
        if(!file_exists(DATA_PATH.DS.'photos_previews'.$pseudo_path)) $this->qumulus->fileGenerateImage($real_path, $pseudo_path, $document->file_mimetype, 'previews');

	    //Send file
        $this->qumulus->fileSendFile(DATA_PATH.DS.'photos_previews'.$pseudo_path, $guid.'.'.$document->file_format, $document->file_mimetype);

	}
	
	public function Thumb($guid) {
	    //Security verification
	    if (!$this->qumulus->authHaveAuthorization('logged') && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();
	    
	    //Retrieve document
        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->fileRenderEmptyPng();

        $document = $q_document->row();

	    //Get info from file real path
	    $pseudo_path = DS.$document->file_source.$document->file_dirname.DS.$document->file_basename;
	    $real_path = $this->qumulus->sources[$document->file_source]['path'].$document->file_dirname.DS.$document->file_basename;

	    //Generate thumb if necessary
        if(!file_exists(DATA_PATH.DS.'photos_thumbnails'.$pseudo_path)) $this->qumulus->fileGenerateImage($real_path, $pseudo_path, $document->file_mimetype, 'thumbnails');
                	    
	    //Send file
        $this->qumulus->fileSendFile(DATA_PATH.DS.'photos_thumbnails'.$pseudo_path, $guid.'.'.$document->file_format, $document->file_mimetype);

	}
	
}
