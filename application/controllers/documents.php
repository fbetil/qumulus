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

class Documents extends CI_Controller {

	public function Thumb($guid) {
	    //Security verification
	    if (!$this->qumulus->authHaveAuthorization('logged') && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();

        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->fileRenderEmptyPng();

        $document = $q_document->row();
        
	    //Get info from file real path
	    $defaulticon = dirname(SELF).DS.'assets'.DS.'img'.DS.'icons'.DS.'default.png';
	    $documenticon = dirname(SELF).DS.'assets'.DS.'img'.DS.'icons'.DS.$document->file_format.'.png';

	    //Redirect to icon
	    if(file_exists($documenticon)){
	        redirect(base_url().'/assets/img/icons/'.$document->file_format.'.png');
	    }else{
	        redirect(base_url().'/assets/img/icons/default.png');
	    }

	}
	
	public function View($guid, $file_basename){
	    //Security verification
	    if (!$this->qumulus->authHaveAuthorization('logged') && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();
	    
	    //Retrieve document
        $q_document = $this->db->get_where('documents', array('guid'=>$guid));
        
	    if (!$q_document->num_rows()) $this->qumulus->html403();

        $document = $q_document->row();
        
        //Retrieve file path
	    $real_path = $this->qumulus->sources[$document->file_source]['path'].(($document->file_dirname == DS)?DS:$document->file_dirname.DS).$document->file_basename;

	    //Send file
        $this->qumulus->fileSendFile($real_path, $document->file_basename, $document->file_mimetype);

	}
	
	public function Delete() {
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
        
        //Retrieve fields
        $inputdata = $this->input->post(null);
        //Validate input data
        $this->load->library('form_validation');
        $this->form_validation->set_rules('documents_guids', lang('documents_label_1'), 'required|callback_guids_check');
        
        if(!$this->form_validation->run()) {
            //Return message
            $this->qumulus->htmlNotify(validation_errors(), 'warning');
        }else{
            $guids = json_decode($inputdata['documents_guids'], true);
            
            $documents = $this->db->where_in('guid', $guids)->get('documents')->result();
            foreach($documents as $document){
                //delete from filesystem
                unlink($this->qumulus->sources[$document->file_source]['path'].$document->file_dirname.DS.$document->file_basename);
                
                //Delete documents from database
                $this->db->delete('documents', array('guid'=>$document->guid));
            }

            //Notify
            $this->qumulus->htmlNotify(lang('documents_message_1'), 'notice', null, true);
        }
	}
	

	public function Guids_Check($json){
        return $this->qumulus->validGuids($json);
    }
	
}
