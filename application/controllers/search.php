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

class Search extends CI_Controller {

	public function Index()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    if (!$this->input->post('application')) $this->qumulus->html403();
	    
	    //Render view
	    $this->qumulus->htmlRenderView('search'.DS.'index.php');
	}
	
	public function Post()
	{
	    //Security verification
	    $this->qumulus->authCheckAuthorization('logged');
	    
	    if (!$this->input->post('search_terms')) $this->qumulus->html403();
	    
	    //Validate input data
	    $this->load->library('form_validation');
	    $this->form_validation->set_rules('search_terms', lang('research'), 'required');
	    
	    if($this->form_validation->run()) {
	        //Prepare result and arrays
	        $result = array();

	        //Retrieve terms
	        $inputdata = $this->input->post(null);
	        
	        //Search document by title
	        $this->db->like('photo_title', $inputdata['search_terms']);
	        $this->db->or_like('track_title', $inputdata['search_terms']);
            $documents = $this->db->get('documents')->result();
	        
            //Add result
	        foreach($documents as $document){
	            if(!isset($this->config->item('file_format_map')[$document->file_format])) continue;
	            
	            $document_type = $this->config->item('file_format_map')[$document->file_format];
	            $field_title = $this->config->item('document_fields')['title'][$document_type];
	            $field_album = $this->config->item('document_fields')['album'][$document_type];
    	            
	            $result[] = array(
	                'guid'=>$document->guid,
	                'title'=>($field_title) ? $document->$field_title : null,
	                'album'=>($field_album) ? $document->$field_album : null,
	                'artist'=>null,
	                'type'=>$document_type,
	                'tile_icon'=>$this->config->item('tiles_icon')[$document_type],
	                'tile_bgcolor'=>$this->config->item('tiles_bgcolor')[$document_type],
	                'tile_image'=>site_url().$this->config->item('tiles_image')[$document_type].$document->guid
	                );
	        }

	        //Search for music albums or artist
	        $this->db->select('track_album, track_artist')->distinct();
	        $this->db->like('track_album', $inputdata['search_terms']);
	        $this->db->or_like('track_artist', $inputdata['search_terms']);
	        $this->db->where_in('file_format', array_keys($this->config->item('file_format_map'), 'audio'));
            $documents = $this->db->get('documents')->result();

            //Add result
	        foreach($documents as $document){
	            $this->db->where('track_album', $document->track_album);
	            $this->db->where('track_artist', $document->track_artist);
	            $this->db->where_in('file_format', array_keys($this->config->item('file_format_map'), 'audio'));
	            $this->db->limit(1);
	            $random_document = $this->db->get('documents')->row();

	            $result[] = array(
	                'guid'=>$random_document->guid,
	                'title'=>null,
	                'album'=>$document->track_album,
	                'artist'=>$document->track_artist,
	                'type'=>'music_album',
	                'tile_icon'=>$this->config->item('tiles_icon')['music_album'],
	                'tile_bgcolor'=>$this->config->item('tiles_bgcolor')['music_album'],
	                'tile_image'=>site_url().$this->config->item('tiles_image')['music_album'].$random_document->guid
	                );
	        }
	        
	        //Send data
	        die(json_encode(array('result'=>true, 'documents'=>$result), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	        
	    }else{
	        //Send error
	        die(json_encode(array('result'=>false, 'message'=>lang('execution_error')), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	    }
	}

}
