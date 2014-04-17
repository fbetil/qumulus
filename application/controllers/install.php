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

class Install extends CI_Controller {

	public function Index()
	{
	    //Security verification
	    $q_admin = $this->db->get_where('users', array('is_admin'=>true));

        //Exit if admin already exists
        if ($q_admin->num_rows()) $this->qumulus->html403();

        //Render view
	    $this->qumulus->htmlRenderView('install'.DS.'index.php');
	}
	
	public function Setup()
	{
	    //Security verification
	    $q_admin = $this->db->get_where('users', array('is_admin'=>true));

        //Exit if admin already exists
        if ($q_admin->num_rows()) $this->qumulus->html403();
	    
	    //Validate input data
	    $this->load->library('form_validation');
	    $this->form_validation->set_rules('setup_username', lang('install_label_1'), 'required|alpha_numeric');
	    $this->form_validation->set_rules('setup_lastname', lang('install_label_2'), 'required');
	    $this->form_validation->set_rules('setup_firstname', lang('install_label_3'), 'required');
	    $this->form_validation->set_rules('setup_email', lang('install_label_4'), 'required|valid_email');
	    $this->form_validation->set_rules('setup_password', lang('install_label_5'), 'required|min_length[8]|max_length[50]');
	    $this->form_validation->set_rules('setup_password_confirm', lang('install_label_6'), 'required|matches[setup_password]');

	    if(!$this->form_validation->run()) {
	        //Return to setup page
	        $view_data['message'] = validation_errors('<p class="text-warning">','</p>');

            //Render install view
    		$this->qumulus->htmlRenderView('install'.DS.'index.php', $view_data);
    		
	    }else{
            //Retrieve fields
            $inputdata = $this->input->post(null);
            
            $hash_password = $this->qumulus->hasher->HashPassword($inputdata['setup_password']);
            if (strlen($hash_password) < 20) die(lang('phpass_error'));
            
            //Create first account
            $admin = array(
                'username'=>$inputdata['setup_username'],
                'firstname'=>$inputdata['setup_firstname'],
                'lastname'=>$inputdata['setup_lastname'],
                'email'=>$inputdata['setup_email'],
                'password'=>$hash_password,
                'is_admin'=>true,
                'prefs_language'=>lang('_lang_'),
                'prefs_startapp'=>'main/parameters',
                'prefs_music_restore_last_playlist'=>false,
                'prefs_files_restore_last_path'=>false,
                'prefs_shares_restore_last_path'=>false
                );

            $this->db->insert('users', $admin);
            
            //Create tasks row
            $tasks = array(
                array('name'=>'cli/indexingfiles', 'status'=>'<b>'.lang('cli/indexingfiles').':</b> '.lang('cli_message_6')),
                array('name'=>'cli/generatingpreviews', 'status'=>'<b>'.lang('cli/generatingpreviews').':</b> '.lang('cli_message_6')),
                array('name'=>'cli/generatingthumbnails', 'status'=>'<b>'.lang('cli/generatingthumbnails').':</b> '.lang('cli_message_6')),
                array('name'=>'cli/lastfmcoversdownload', 'status'=>'<b>'.lang('cli/lastfmcoversdownload').':</b> '.lang('cli_message_6'))
                );
            $this->db->insert_batch('tasks', $tasks);
            
            
            //Create default parameters
            $parameters = array(
                array('name'=>'previews_size','value'=>800),
                array('name'=>'thumbnails_size','value'=>200),
                array('name'=>'photos_get_tags_from_path','value'=>true),
                array('name'=>'documents_tree','value'=>serialize(array())),
                array('name'=>'documents_tags','value'=>serialize(array())),
                array('name'=>'lastfm_api_key','value'=>'')
                );
            $this->db->insert_batch('parameters', $parameters);

            //Create default upload source
            $source = array(
                'name'=>lang('uploads'),
                'path'=>realpath(DATA_PATH.DS.'upload'),
                'types'=>'all'
                );
            $this->db->insert('sources', $source);

            //Create default public share
            $share = array(
                'name'=>lang('public'),
                'visualization'=>'tiles',
                'password'=>null,
                'token'=>bin2hex(openssl_random_pseudo_bytes(32)),
                'documents'=>serialize(array()),
                'documents_tree'=>serialize(array())
                );
            $this->db->insert('shares', $share);
            
            //Login
            $this->qumulus->authLogin($inputdata['setup_username'], $inputdata['setup_password']);
            
    	    //Load parameters, sources, preferences, lang
    	    $this->qumulus->loadParameters();
    	    $this->qumulus->loadSources();
    	    $this->qumulus->loadPreferences();
    	    $this->qumulus->loadLanguage();
            
            //Display parameters page
            $this->session->set_flashdata('notify', lang('install_message_1'));
            $this->qumulus->htmlRenderView('layout.php');
	    }
	}

}
