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

class Main extends CI_Controller {

	public function Index()
	{
	    //Check if user is logged
	    if ($this->qumulus->authHaveAuthorization('logged')) {
	        //Render home page
	        $this->qumulus->htmlRenderView('layout.php');
	    }else{
	        //Forward to login action
	        $this->Login();
	    }
	}

	public function Login()
	{
	   
		// If 'Login' form has been submited, attempt to log the user in.
		if ($this->input->post('form_login')) {
    		// Set validation rules.
    		$this->load->library('form_validation');
    		$this->form_validation->set_rules('login_username', lang('username'), 'required');
    		$this->form_validation->set_rules('login_password', lang('password'), 'required|max_length[50]');
    
    		// Run the validation.
    		if ($this->form_validation->run()) {
    		    $inputdata = $this->input->post(null);
    		    
    			// Verify login data.
    			$logged = $this->qumulus->authLogin($inputdata['login_username'], $inputdata['login_password']);
                
    			// Reload page, if login was successful, sessions will have been created that will then further redirect verified users.
    			if ($logged) {
    			    $this->session->set_flashdata('notify', lang('welcome'));
    			    $this->qumulus->htmlNotify(lang('welcome'), 'notice');
    			}
    			
    			$this->qumulus->htmlNotify(lang('connection_error'), 'warning');
                
    		}else{	
    			// Set validation errors.
    			$this->qumulus->htmlNotify(validation_errors(), 'warning');
    		}
		}

        //Render login view
		$this->qumulus->htmlRenderView('main'.DS.'login.php');
	}
	
	public function Logout()
	{
	    //Do logout
	    $this->qumulus->authLogout();
	    
        //redirect to main page
        redirect();
	}
	
	public function Home()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();

	    //Get some data
	    $documentscount = $this->db->count_all('documents');
	    //Get tasks info
	    $tasks = $this->db->get('tasks')->result();

        //Render view
	    $this->qumulus->htmlRenderView('main'.DS.'index.php', array('documentscount'=>$documentscount, 'tasks'=>$tasks));
	}
	
	public function Parameters()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('admin');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    //Save parameters
	    if ($this->input->post('form_parameters')){
            //Retrieve fields
            $inputdata = $this->input->post(null);
            //Validate input data
            $this->load->library('form_validation');
            $this->form_validation->set_rules('parameters_thumbnails_size', lang('main_label_2'), 'required|integer');
            $this->form_validation->set_rules('parameters_previews_size', lang('main_label_1'), 'required|integer');
            $this->form_validation->set_rules('parameters_photos_get_tags_from_path', lang('main_label_3'), 'required|boolean');
            $this->form_validation->set_rules('parameters_lastfm_api_key', lang('main_label_19'), 'required|alpha_numeric');
            foreach(array_keys($inputdata) as $inputname){
                if (substr($inputname,0,13) == 'sources_name_'){
                    $sourceid = substr(strrchr($inputname, '_'), 1);
                    $this->form_validation->set_rules('sources_name_'.$sourceid, lang('main_label_4'), 'required');
                    $this->form_validation->set_rules('sources_path_'.$sourceid, lang('main_label_5'), 'required');
                    $this->form_validation->set_rules('sources_types_'.$sourceid, lang('main_label_6'), 'required');
                }
            }

            if(!$this->form_validation->run()) {
        	    //Return
        	    $this->qumulus->htmlNotify(validation_errors(), 'warning');
            }else{
                //Saving parameters
                foreach (array('thumbnails_size','previews_size','photos_get_tags_from_path','lastfm_api_key') as $parametername){
                    $parameter = array(
                        'value'=>$inputdata['parameters_'.$parametername]
                        );
                    
                    $this->db->update('parameters', $parameter, array('name'=>$parametername));
                }
                
                //Saving sources and ...
                $sourcesid = array();
                foreach(array_keys($inputdata) as $inputname){
                    if (substr($inputname,0,13) == 'sources_name_'){
                        $sourceid = substr(strrchr($inputname, '_'), 1);
                        
                        $source = array(
                            'name' => $inputdata['sources_name_'.$sourceid],
                            'path' => $inputdata['sources_path_'.$sourceid],
                            'types' => $inputdata['sources_types_'.$sourceid]
                            );
                        
                        if(!is_numeric($sourceid)){
                            $this->db->insert('sources', $source);
                            $sourceid = $this->db->insert_id();
                        }else{
                            $this->db->update('sources', $source, array('id'=>$sourceid));
                        }
                        
                        $sourcesid[] = $sourceid;
                    }
                }
                
                //delete old ones
                $this->db->where_not_in('id', $sourcesid);
                $this->db->delete('sources');
                
                //Reload parameters and sources
        	    $this->qumulus->loadSources();                
        	    $this->qumulus->loadParameters();
        	    
        	    //Return
        	    $this->qumulus->htmlNotify(lang('main_message_2'), 'notice', null, true);
            }

	    }
	    
	    //Retrieve sources
	    $sources = $this->db->get('sources')->result();

        //Render view
	    $this->qumulus->htmlRenderView('main'.DS.'parameters.php', array('parameters'=>$this->qumulus->parameters, 'sources'=>$sources));
	}
	
	public function Preferences()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    //Retrieve current user
	    $q_user = $this->db->get_where('users', array('id'=> $this->session->userdata('user_id')));
	    
	    if (!$q_user->num_rows()) $this->qumulus->html403();
	    
	    $user = $q_user->row();
	    
	    //Save preferences
	    if ($this->input->post('form_preferences')){
            //Validate input data
            $this->load->library('form_validation');
            $this->form_validation->set_rules('preferences_username', lang('main_label_7'), 'required|alpha_numeric');
            $this->form_validation->set_rules('preferences_lastname', lang('main_label_8'), 'required');
            $this->form_validation->set_rules('preferences_firstname', lang('main_label_9'), 'required');
            $this->form_validation->set_rules('preferences_email', lang('main_label_10'), 'required|valid_email');
            $this->form_validation->set_rules('preferences_password', lang('main_label_11'), 'min_length[8]|max_length[50]');
            $this->form_validation->set_rules('preferences_password_confirm', lang('main_label_12'), 'matches[preferences_password]');
            $this->form_validation->set_rules('preferences_language', lang('main_label_13'), 'required');
            $this->form_validation->set_rules('preferences_startapp', lang('main_label_14'), 'required');
            $this->form_validation->set_rules('preferences_files_restore_last_path', lang('main_label_15'), 'required|boolean');
            $this->form_validation->set_rules('preferences_shares_restore_last_path', lang('main_label_17'), 'required|boolean');
            $this->form_validation->set_rules('preferences_music_restore_last_playlist', lang('main_label_16'), 'required|boolean');
            
            if(!$this->form_validation->run()) {
        	    //Return
        	    $this->qumulus->htmlNotify(validation_errors(), 'warning');
            }else{
                //Retrieve fields
                $inputdata = $this->input->post(null);
                
                $user->username = $inputdata['preferences_username'];
                $user->lastname = $inputdata['preferences_lastname'];
                $user->firstname = $inputdata['preferences_firstname'];
                $user->email = $inputdata['preferences_email'];
                $user->prefs_language = $inputdata['preferences_language'];
                $user->prefs_startapp = $inputdata['preferences_startapp'];
                $user->prefs_files_restore_last_path = $inputdata['preferences_files_restore_last_path'];
                $user->prefs_shares_restore_last_path = $inputdata['preferences_shares_restore_last_path'];
                $user->prefs_music_restore_last_playlist = $inputdata['preferences_music_restore_last_playlist'];
                
                //Secure password and store
                if(!empty($inputdata['preferences_password'])) {
                    $hash_password = $this->qumulus->hasher->HashPassword($inputdata['preferences_password']);
                    if (strlen($hash_password) < 20) die(lang('phpass_error'));
                    
                    $user->password = $hash_password;
                }
                
                $this->db->update('users', $user, array('id' => $user->id));
                
                //Reload preferences and language
        	    $this->qumulus->loadPreferences();
        	    $this->qumulus->loadLanguage();
                
        	    //Return
        	    $this->qumulus->htmlNotify(lang('main_message_1'), 'notice', null, true);
            }
	    }
	    
        //Render view
	    $this->qumulus->htmlRenderView('main'.DS.'preferences.php', array('user'=>$user));
	}
	
	public function Upload() {
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    
        $sourceid = 1;
        $source = $this->qumulus->sources[$sourceid];
            
        if (empty($_FILES)) {
            //Render view
    	    $this->qumulus->htmlRenderView('main'.DS.'upload.php', array('destination'=>$this->qumulus->sources[1]['name'], 'file_format'=>implode(',', $source['types'])));
        }else{
            //Save uploaded file in upload directory
            $tempFile = $_FILES['file']['tmp_name'];
            $targetPath = $source['path'].DS;
            $targetFile =  $targetPath.$_FILES['file']['name'];
            $pseudopath = '/'.$_FILES['file']['name'];
            
            //Get metadata
            if (in_array(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),  $this->config->item('file_office_extensions'))) {
        	    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        	    $mime_type = finfo_file($finfo, $tempFile);
        	    $fileformat = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        	    $filesize = filesize ($tempFile);
        	    
        	    $metadata = array('mime_type'=>$mime_type, 'fileformat'=>$fileformat, 'filesize'=>$filesize );
        	    
            }else{
                $metadata = $this->qumulus->fileGetMetadata($tempFile);
            }
            
    	    if (!$metadata || !isset($metadata['fileformat']) || !isset($this->config->item('file_format_map')[$metadata['fileformat']]) || !in_array($metadata['fileformat'],$source['types'] )) {
    	        die(json_encode(array('result'=>false, 'msg'=>lang('main_message_3'))));
    	    }

            //move file in source
            move_uploaded_file($tempFile,$targetFile);
            
            //Store document and retrieve it
            $document_id = $this->qumulus->databaseStoreDocument($sourceid, $pseudopath, $metadata);
            $q_document = $this->db->get_where('documents', array('id'=>$document_id));
            $document = $q_document->row();
        
            //Return result tile
            $document_type = $this->config->item('file_format_map')[$document->file_format];
            $field_title = $this->config->item('document_fields')['title'][$document_type];
            $field_album = $this->config->item('document_fields')['album'][$document_type];
	            
            $tile = array(
                'guid'=>$document->guid,
                'title'=>($field_title) ? $document->$field_title : null,
                'album'=>($field_album) ? $document->$field_album : null,
                'artist'=>null,
                'type'=>$document_type,
                'tile_icon'=>$this->config->item('tiles_icon')[$document_type],
                'tile_bgcolor'=>$this->config->item('tiles_bgcolor')[$document_type],
                'tile_image'=>site_url().$this->config->item('tiles_image')[$document_type].$document->guid
                );

            die(json_encode(array('result'=>true, 'tile'=>$tile), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            
        }
	}
}
