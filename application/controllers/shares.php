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

class Shares extends CI_Controller {

    public function Index(){
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    $path = $this->input->post('path') ?: DS;

        if ($path == DS){
            //Retrieve declared shares
    	    $subdirectories = array();
    	    $places = array();
    	    $documents = array();
    	    
    	    $shares = $this->db->get('shares')->result();
    	    $share = null;
        }else{
            $shares = array();
            //Retrieve share
    	    $shareid = substr($path,2,strpos($path,DS, 1) -2);
    	    $dirname = substr($path, strpos($path,DS, 1));
    	    $sourceid = ($dirname == DS) ? null : substr($dirname,2,strpos($dirname,DS, 1) -2);

            $q_share = $this->db->get_where('shares', array('id'=>$shareid));
            
    	    if (!$q_share->num_rows()) $this->qumulus->html403();
    
            $share = $q_share->row();

            //Get places
            $place = DS.'s'.$shareid;
    	    $places = array(array('name'=>$share->name,'path'=>DS.'s'.$shareid.DS));
    	    if($sourceid) {
    	        $place .= DS.'s'.$sourceid;
    	        $places[] = array('name'=>$this->qumulus->sources[$sourceid]['name'],'path'=>$place.DS);
    	        //Remove sourceid from dirname
    	        $dirname = substr($dirname, strpos($dirname,DS, 1));
    	    }

            foreach(array_filter(explode(DS, trim($dirname,DS))) as $pathpart){
                $place .= DS.$pathpart;
                $places[] = array('name'=>$pathpart, 'path'=>$place.DS);
            }

            //Get direct subdirectories
            $subdirectories = array();
            $treefolder = array('s'.$share->id => unserialize($share->documents_tree));
            foreach(array_filter(explode(DS, trim($path,DS))) as $key => $pathpart){
                $treefolder = $treefolder[$pathpart];
            }
            foreach(array_keys($treefolder) as $subdirectory){
                if ($sourceid){
                    $subdirectories[] = array('name'=>$subdirectory, 'path'=>$path.$subdirectory.DS);
                }else{
                    $subdirectories[] = array('name'=>$this->qumulus->sources[substr($subdirectory,1)]['name'], 'path'=>$path.$subdirectory.DS);
                }
            }

            //Get direct documents
            $guids = (unserialize($share->documents)) ?: array('guid');
            $this->db->where_in('guid', $guids);
            $this->db->where('CONCAT(file_source, file_dirname) =', ($dirname == DS) ? $sourceid.$dirname : $sourceid.rtrim($dirname, DS));
            $documents = $this->db->get('documents')->result();
        }
        
        //Render view
	    $this->qumulus->htmlRenderView('shares'.DS.'index.php', array('here'=>$path, 'places'=>$places, 'subdirectories'=>array_filter($subdirectories), 'documents'=>$documents, 'file_format_map'=>$this->config->item('file_format_map'), 'shares'=>$shares, 'currentshare'=>$share));

    }
    
    public function Post(){
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    
	    $message = '';
	    
        //Retrieve fields
        $inputdata = $this->input->post(null);
        //Validate input data
        $this->load->library('form_validation');
        $this->form_validation->set_rules('share_id', lang('identifier'), 'required|integer');
        $this->form_validation->set_rules('share_name', lang('shares_label_1'), 'required');
        $this->form_validation->set_rules('share_protected', lang('shares_label_2'), 'required|boolean');
        $this->form_validation->set_rules('share_password', lang('password'), 'min_length[3]|max_length[50]');
        $this->form_validation->set_rules('share_visualization', lang('shares_label_4'), 'required');
        $this->form_validation->set_rules('share_new_token', lang('shares_label_5'), 'required|boolean');
        $this->form_validation->set_rules('share_delete', lang('shares_label_8'), 'boolean');

        if(!$this->form_validation->run()) {
            //Return message
            $this->qumulus->htmlNotify(validation_errors(), 'warning');
        }else{
            if ((isset($inputdata['share_delete'])) && ($inputdata['share_delete'] == 1)){
                //get share
                $q_share = $this->db->get_where('shares', array('id'=>$inputdata['share_id']));
                
        	    if (!$q_share->num_rows()) $this->qumulus->html403();
        
                $this->db->delete('shares', array('id'=>$inputdata['share_id']));
                
                //Return result
                $this->qumulus->htmlNotify(lang('shares_message_7'), 'notice', null, true);

            }else{
                $q_share = $this->db->get_where('shares', array('id'=>$inputdata['share_id']));
                
                //Get existing share or create new one
        	    $share = ($q_share->num_rows()) ? $q_share->row_array() : array('documents'=>serialize(array()), 'documents_tree'=>serialize(array()));

                $share['name'] = $inputdata['share_name'];
                $share['visualization'] = $inputdata['share_visualization'];
                //Hash password if sent
                if ($inputdata['share_protected'] == 1 && !empty($inputdata['share_password'])) {
                    $hash_password = $this->qumulus->hasher->HashPassword($inputdata['share_password']);
                    if (strlen($hash_password) < 20)  $message = lang('phpass_error');
                    
                    $share['password'] = $hash_password;
                }elseif($inputdata['share_protected'] == 0){
                    $share['password'] = null;
                }
                //Generate new token if sent or new share
                if ($inputdata['share_id'] == 0 || $inputdata['share_new_token'] == 1) $share['token'] = bin2hex(openssl_random_pseudo_bytes(32));

                //Saving share
                if(!isset($share['id'])){
                    $this->db->insert('shares', $share);
                }else{
                    $this->db->update('shares', $share, array('id'=>$share['id']));
                }
                
                //Return result
                $this->qumulus->htmlNotify(lang('shares_message_1'), 'notice', null, true);

            }
            
        }
        
    }
    
    public function Action(){
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    
        //Retrieve fields
        $inputdata = $this->input->post(null);

        //Validate input data
        $this->load->library('form_validation');
        $this->form_validation->set_rules('share_id', lang('identifier'), 'required|integer');
        $this->form_validation->set_rules('share_documents', lang('shares_label_6'), 'required|callback_guids_check');
        $this->form_validation->set_rules('share_action', lang('shares_label_7'), 'required|alpha');

        if(!$this->form_validation->run()) {
            //Return message
            $this->qumulus->htmlNotify(validation_errors(), 'warning');
        }else{
            //get share
            $q_share = $this->db->get_where('shares', array('id'=>$inputdata['share_id']));
            
            //Exit if share don't exists
    	    if (!$q_share->num_rows()) $this->qumulus->html403();
    	    
    	    $share = $q_share->row();
    	    
            $documents = json_decode($inputdata['share_documents'], true);
            
            //Update documents
            $currentdocuments = unserialize($share->documents);

            switch($inputdata['share_action']){
                case 'link':
                    $documents = array_unique(array_merge($currentdocuments, $documents));
                    break;
                case 'unlink':
                    $documents = array_filter($currentdocuments, function($k) use ($documents){return (!in_array($k, $documents));});
                    break;
            }
            $share->documents = serialize($documents);

            //Rebuild documents tree
            $documents_tree = $this->GenerateDocumentsTree($documents);

            $share->documents_tree = serialize($documents_tree);
            
            //save share
            $this->db->update('shares', $share, array('id'=>$share->id));
            
            //Return result
            $this->qumulus->htmlNotify(lang('shares_message_5'), 'notice', null, true);
        }
        
    }
    
    public function Share($token = false) {
	    //Security verification
	    if (!$token) $this->qumulus->html403();

        //get share
        $q_share = $this->db->get_where('shares', array('token'=>$token));
        
        //Exit if share don't exists
	    if (!$q_share->num_rows()) $this->qumulus->html403();
	    
	    $share = $q_share->row();
	    
	    //Exit if token error
	    if (!$share) $this->qumulus->html403();

	    //if share is password protected and user don't have this token permission
	    if (!empty($share->password) && !$this->qumulus->sharesHaveTokenPermission($token)) {
    		// If 'Token' form has been submited, attempt to valid it.
    		if ($this->input->post('share_token')) {
        		// Set validation rules.
        		$this->load->library('form_validation');
        		$this->form_validation->set_rules('share_password', lang('password'), 'required|max_length[50]');
        
        		// Run the validation.
        		if ($this->form_validation->run()) {
        		    $inputdata = $this->input->post(null);
        		    
                    //Verify password
                    $check = $this->qumulus->hasher->CheckPassword($inputdata['share_password'], $share->password);
                    
                    //if password is not correct
                    if (!$check) {
                        //Reset token session permission
                        $this->qumulus->sharesRemoveTokenPermission($token);

                        //Set password error
                        $this->qumulus->htmlNotify(lang('shares_p_3'), 'warning');
                    }else{
                        //Add permission to user
                        $this->qumulus->sharesAddTokenPermission($token);
                        $this->qumulus->sharesAddGuidsPermission(unserialize($share->documents));

        			    $this->session->set_flashdata('notify', lang('welcome'));
        			    $this->qumulus->htmlNotify(lang('welcome'), 'notice');
                    }
        		}else{
                    //Reset token session permission
                    $this->qumulus->sharesRemoveTokenPermission($token);

        			// Set validation errors.
        			$this->qumulus->htmlNotify(validation_errors(), 'warning');
        		}
    		}
        	
    	    //Render view and exit
    	    $this->qumulus->htmlRenderView('shares'.DS.'share.php', array('user_anonymous'=> true, 'token'=>$token));
    	    return;
    	    
	    }elseif(empty($share->password)){ //If share is not password protected
	        //Add permission to user
            $this->qumulus->sharesAddTokenPermission($token);
            $this->qumulus->sharesAddGuidsPermission(unserialize($share->documents));
	    }

        //Set token session permission and guids
        $guids = (unserialize($share->documents)) ?: array('guid');
        
        switch($share->visualization){
            case 'tree':
        	    $path = $this->input->post('path') ?: DS;
        	    $sourceid = ($path == DS) ? null : substr($path,2,strpos($path,DS, 1) -2);
        	    $dirname = $path;
        	    $places = array();
        	    $place = '';

                if($sourceid) {
                    $place .= DS.'s'.$sourceid;
                    $places[] = array('name'=>$this->qumulus->sources[$sourceid]['name'],'path'=>$place.DS);
                    //Remove sourceid from dirname
                    $dirname = substr($path, strpos($path,DS, 1));
                }
                
                foreach(array_filter(explode(DS, trim($dirname,DS))) as $pathpart){
                    $place .= DS.$pathpart;
                    $places[] = array('name'=>$pathpart, 'path'=>$place.DS);
                }
                
                //Get direct subdirectories
                $subdirectories = array();
                $treefolder = unserialize($share->documents_tree);
                foreach(array_filter(explode(DS, trim($path,DS))) as $key => $pathpart){
                    $treefolder = $treefolder[$pathpart];
                }
                foreach(array_keys($treefolder) as $subdirectory){
                    if ($sourceid){
                        $subdirectories[] = array('name'=>$subdirectory, 'path'=>$path.$subdirectory.DS);
                    }else{
                        $subdirectories[] = array('name'=>$this->qumulus->sources[substr($subdirectory,1)]['name'], 'path'=>$path.$subdirectory.DS);
                    }
                }
        
                //Get direct documents
                $guids = (unserialize($share->documents)) ?: array('guid');
                $this->db->where_in('guid', $guids);
                $this->db->where('CONCAT(file_source, file_dirname) =', ($dirname == DS) ? $sourceid.$dirname : $sourceid.rtrim($dirname, DS));
                $documents = $this->db->get('documents')->result();

                //Render view
        	    $this->qumulus->htmlRenderView('shares'.DS.'share.php', array('user_anonymous'=> true, 'token'=>$token, 'share'=>$share, 'here'=>$path, 'places'=>$places, 'subdirectories'=>array_filter($subdirectories), 'documents'=>$documents, 'file_format_map'=>$this->config->item('file_format_map'), 'viewerjs_formats'=>$this->config->item('viewerjs_formats')));
        
                break;
            case 'tiles':
                //Retrieve documents
        	    $shareddocuments = array();

                //get documents
                $this->db->where_in('guid', $guids);
                $documents = $this->db->get('documents')->result();
                
    	        foreach($documents as $document){
    	            if(!isset($this->config->item('file_format_map')[$document->file_format])) continue;
    	            
    	            $document_type = $this->config->item('file_format_map')[$document->file_format];
    	            $field_title = $this->config->item('document_fields')['title'][$document_type];
    	            $field_album = $this->config->item('document_fields')['album'][$document_type];
    	            $field_artist = $this->config->item('document_fields')['artist'][$document_type];
    	            
    	            $shareddocuments[] = array(
    	                'guid'=>$document->guid,
    	                'file_basename'=>$document->file_basename,
    	                'file_format'=>$document->file_format,
    	                'title'=>($field_title) ? $document->$field_title : null,
    	                'album'=>($field_album) ? $document->$field_album : null,
    	                'artist'=>($field_artist) ? $document->$field_artist : null,
    	                'type'=>$document_type,
    	                'tile_icon'=>$this->config->item('tiles_icon')[$document_type],
    	                'tile_bgcolor'=>$this->config->item('tiles_bgcolor')[$document_type],
    	                'tile_image'=>site_url().$this->config->item('tiles_image')[$document_type].$document->guid
    	                );
    	        }

        	    //Render view
        	    $this->qumulus->htmlRenderView('shares'.DS.'share.php', array('user_anonymous'=> true, 'token'=>$token, 'share'=>$share, 'documents'=>$shareddocuments, 'file_format_map'=>$this->config->item('file_format_map'), 'viewerjs_formats'=>$this->config->item('viewerjs_formats')));

                break;
        }

    }
    
    private function GenerateDocumentsTree($guids){
        //Gererate tree snapshots for somes guids
        $snapshot = array();
        if (empty($guids)) $guids = array('guid');
        
        $documents = $this->db->select('file_source, file_dirname')->distinct()->where_in('guid', $guids)->get('documents')->result();

        foreach ($documents as $document){
            $path = array_filter(explode(DS,'s'.$document->file_source.$document->file_dirname));
            
            $out = array();
            while( $pop = array_pop($path) ) $out = array($pop => $out);

            $snapshot = array_merge_recursive($snapshot, $out);
        }

	    return $snapshot;
    }

    public function Json_Check($json){
        return $this->qumulus->validJson($json);
    }

	public function Guids_Check($json){
        return $this->qumulus->validGuids($json);
    }

}
