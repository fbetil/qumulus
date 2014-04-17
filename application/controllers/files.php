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

class Files extends CI_Controller {

	public function Index()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    $path = $this->input->post('path') ?: DS;

        if ($path == DS){
    	    $subdirectories = array();
    	    foreach($this->qumulus->sources as $sourceid => $source){
    	        $subdirectories[] = array('name'=>$source['name'], 'path'=>DS.'s'.$sourceid.DS);
    	    }
    	    $readonly = true;
    	    
    	    $places = array();
    	    $documents = array();
        }else{
    	    $sourceid = substr($path,2,strpos($path,DS, 1) -2);
    	    $dirname = substr($path, strpos($path,DS, 1));
    	    
    	    $readonly = ($sourceid == 1) ? false : true;
    	    
            //Get places
            $place = DS.'s'.$sourceid;
    	    $places = array(array('name'=>$this->qumulus->sources[$sourceid]['name'],'path'=>DS.'s'.$sourceid.DS));
            foreach(array_filter(explode(DS, trim($dirname,DS))) as $pathpart){
                $place .= DS.$pathpart;
                $places[] = array('name'=>$pathpart, 'path'=>$place.DS);
            }
            
            //Get direct subdirectories
            $subdirectories = array();
            $treefolder = unserialize($this->qumulus->parameters['documents_tree']);
            foreach(array_filter(explode(DS, trim($path,DS))) as $pathpart){
                $treefolder = $treefolder[$pathpart];
            }
            foreach(array_keys($treefolder) as $subdirectory){
                $subdirectories[] = array('name'=>$subdirectory, 'path'=>$path.$subdirectory.DS);
            }

            //Get direct documents
            $this->db->where('file_source', $sourceid);
            $this->db->where('file_dirname', ($dirname == DS) ? $dirname : rtrim($dirname, DS));
            $documents = $this->db->get('documents')->result();
            
        }
        
        //Retrieve shares
        $shares = $this->db->get('shares')->result();
        
        //Render view
	    $this->qumulus->htmlRenderView('files'.DS.'index.php', array('here'=>$path, 'places'=>$places, 'subdirectories'=>array_filter($subdirectories), 'documents'=>$documents, 'file_format_map'=>$this->config->item('file_format_map'), 'viewerjs_formats'=>$this->config->item('viewerjs_formats'), 'shares'=>$shares, 'readonly'=>$readonly));
	}
	
    public function Download() {
	    //Security verification
	    //Permissions are checked after

		// Set validation rules.
		$this->load->library('form_validation');
		$this->form_validation->set_rules('download_documents', lang('files_label_1'), 'required|callback_guids_check');

		// Run the validation.
		if ($this->form_validation->run()) {
		    
		    $inputdata = $this->input->post(null);
		    
            //get downloads
            $guids = json_decode($inputdata['download_documents'], true);

            //Check permission
            foreach($guids as $guid){
                if (!$this->qumulus->authHaveAuthorization('logged')  && !$this->qumulus->sharesHaveGuidPermission($guid)) $this->qumulus->html403();
            }
            
            if(count($guids) == 1){
        	    //Retrieve document
                $q_document = $this->db->get_where('documents', array('guid'=>$guid));
                
        	    if (!$q_document->num_rows()) $this->qumulus->html403();
        
                $document = $q_document->row();
                
                //Retrieve file path
        	    $real_path = $this->qumulus->sources[$document->file_source]['path'].(($document->file_dirname == DS)?DS:$document->file_dirname.DS).$document->file_basename;
        
        	    //Send file
                $this->qumulus->fileSendFile($real_path, $document->file_basename, $document->file_mimetype);

            }else{ //Generate a zip file
                $zipname = md5($inputdata['download_documents']);
                $zip = $zip = new ZipArchive();
                $zip->open(TMP_PATH.DS.$zipname.'.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
                
                //Add each document
                foreach ($guids as $guid){
            	    //Retrieve document
                    $q_document = $this->db->get_where('documents', array('guid'=>$guid));
                    
            	    if (!$q_document->num_rows()) $this->qumulus->html403();
            
                    $document = $q_document->row();
            	    
                    //Retrieve file path
            	    $real_path = $this->qumulus->sources[$document->file_source]['path'].(($document->file_dirname == DS)?DS:$document->file_dirname.DS).$document->file_basename;

                    $zip->addFile($real_path,$document->file_basename);
                }
                
                //Close zip
                $zip->close();
        
        	    //Send zip file and delete it
                $this->qumulus->fileSendFile(TMP_PATH.DS.$zipname.'.zip', 'Qumulus.zip');
            }
            
		}else{
			// Return forbidden page
			$this->qumulus->html403();
		}
	}
	
    public function Json_Check($json){
        return $this->qumulus->validJson($json);
    }
	
	public function Guids_Check($json){
        return $this->qumulus->validGuids($json);
    }
	
}
