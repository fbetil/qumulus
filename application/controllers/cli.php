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

class Cli extends CI_Controller {

	public function IndexingFiles($filter_source_id = false){
	    //Security verification
        if (!$this->qumulus->authHaveAuthorization('cli_request') && !$this->qumulus->authHaveAuthorization('admin')) $this->qumulus->html403();

	    //Send response to browser if ajax call
	    if ($this->input->is_ajax_request()) $this->qumulus->htmlSendResponseToBrowser(sprintf(lang('cli_message_8'),lang('cli/indexingfiles')));

	    //set php config
	    set_time_limit(0);
	    ini_set('memory_limit', -1);
	    
	    $scanned_files = array();
	    $filescount = 0;

	    foreach($this->qumulus->sources as $sourceid => $source) {
	        // exit if unique source scan
	        if ($filter_source_id != false && $sourceid != $filter_source_id) continue;
	        
    	    //Set progression
            $this->taskSetProgression('cli/indexingfiles', lang('cli_message_3'));
            
    	    $files = $this->qumulus->fileGetDirectoryContent($source['path']);
    	    
    	    //Add or update metadata
    	    foreach($files as $realpath){
    	        $metadata = $this->qumulus->fileGetMetadata($realpath);
                    
    	        $pseudopath = substr($realpath, strlen($source['path']));

    	        if (!$metadata || !isset($metadata['fileformat']) || !isset($this->config->item('file_format_map')[$metadata['fileformat']]) || !in_array($metadata['fileformat'],$source['types'] )) continue;

                //Add file to scanned files
    	        $scanned_files[] = DS.$sourceid.$pseudopath;
    	        $filescount++;
    	    
    	        //Store document
    	        $this->qumulus->databaseStoreDocument($sourceid, $pseudopath, $metadata);
    	        
    	        //Store progression
    	        $this->taskSetProgression('cli/indexingfiles', sprintf(lang('cli_message_1'), $filescount));
    	        
    	    }
	    }
	    
	    //remove old entries
        $this->taskSetProgression('cli/indexingfiles', lang('cli_message_2'));
        
	    if ($filter_source_id == false) {
	        $documents = $this->db->get('documents')->result();
	    }else{
	        $documents = $this->db->get_where('documents', array('file_source'=>$filter_source_id))->result();
	    }
	    
	    foreach($documents as $document){
	        $pseudopath = str_replace(DS.DS,DS, DS.$document->file_source.$document->file_dirname.DS.$document->file_basename);

	        if (!in_array($pseudopath, $scanned_files)) {
	            $this->db->delete('documents', array('id'=>$document->id));
	        }
	    }
	    
	    //Generate documents tree and tags
        $this->taskSetProgression('cli/indexingfiles', lang('cli_message_7'));
        $this->qumulus->databaseStoreDocumentsTree();
        
        $this->taskSetProgression('cli/indexingfiles', lang('cli_message_11'));
        $this->qumulus->databaseStoreDocumentsTags();

        //Reset progression
        $this->taskSetProgression('cli/indexingfiles', lang('cli_message_6'));
        
	}
	
	public function GeneratingPreviews($force = null) {
	    //Security verification
        if (!$this->qumulus->authHaveAuthorization('cli_request') && !$this->qumulus->authHaveAuthorization('admin')) $this->qumulus->html403();
	    
	    //Send response to browser if ajax call
	    if ($this->input->is_ajax_request()) $this->qumulus->htmlSendResponseToBrowser(sprintf(lang('cli_message_8'),lang('cli/generatingpreviews')));
	    
        $this->generateImages('previews', $force);
	}
	
	public function GeneratingThumbnails($force = null) {
	    //Security verification
        if (!$this->qumulus->authHaveAuthorization('cli_request') && !$this->qumulus->authHaveAuthorization('admin')) $this->qumulus->html403();
	    
	    //Send response to browser if ajax call
	    if ($this->input->is_ajax_request()) $this->qumulus->htmlSendResponseToBrowser(sprintf(lang('cli_message_8'),lang('cli/generatingthumbnails')));
	    
        $this->generateImages('thumbnails', $force);
    }
    
	public function LastFmCoversDownload($force = null) {
	    //Security verification
        if (!$this->qumulus->authHaveAuthorization('cli_request') && !$this->qumulus->authHaveAuthorization('admin')) $this->qumulus->html403();
	    
	    //Send response to browser if ajax call
	    if ($this->input->is_ajax_request()) $this->qumulus->htmlSendResponseToBrowser(sprintf(lang('cli_message_8'),lang('cli/lastfmcoversdownload')));
	    
        //Parameters verification
	    if(empty($this->qumulus->parameters['lastfm_api_key'])) return false;
	    
        //Get albums
        $this->db->select('track_artist, track_album');
        $this->db->where_in('file_format',  array_keys($this->config->item('file_format_map'), 'audio'));
        $this->db->group_by('track_artist, track_album');
        $this->db->order_by('track_artist, track_album');
        $q_albums = $this->db->get('documents');

	    if (!$q_albums->num_rows()) return false;

	    //Set progression
        $this->taskSetProgression('cli/lastfmcoversdownload', sprintf(lang('cli_message_8'),lang('cli/lastfmcoversdownload')));
        
        $albums = $q_albums->result();
        $albumscount = 0;
        
        foreach($albums as $album){
            $albumscount++;
    	    //Get album cover storage path
    	    $cover_path = DATA_PATH.DS.'music_covers'.DS.md5($album->track_artist.'/'.$album->track_album).'.jpg';
    
            if(!file_exists($cover_path) || filesize($cover_path) == 0 || $force){   //Try to retrieve cover from lasffm
                $cover = $this->qumulus->lastfmGetAlbumCover($album->track_artist, $album->track_album);
            }
            
	        //Store progression
	        $this->taskSetProgression('cli/lastfmcoversdownload', sprintf(lang('cli_message_9'), $albumscount));
        }
        
        //Store progression
        $this->taskSetProgression('cli/lastfmcoversdownload', lang('cli_message_6'));
    }
    
    public function LastFmRetrieveTags($force = null) {
	    //Security verification
        if (!$this->qumulus->authHaveAuthorization('cli_request') && !$this->qumulus->authHaveAuthorization('admin')) $this->qumulus->html403();
	    
	    //Send response to browser if ajax call
	    if ($this->input->is_ajax_request()) $this->qumulus->htmlSendResponseToBrowser(sprintf(lang('cli_message_8'),lang('cli/lastfmretrievetags')));
	    
        //Parameters verification
	    if(empty($this->qumulus->parameters['lastfm_api_key'])) return false;
	    
        //Get tracks
        $this->db->select('guid, track_title, track_artist');
        $this->db->where_in('file_format',  array_keys($this->config->item('file_format_map'), 'audio'));
        if ($force == null) $this->db->where_in('file_tags', array(null, '|  |'));
        
        $q_tracks = $this->db->get('documents');

	    if (!$q_tracks->num_rows()) return false;

	    //Set progression
        $this->taskSetProgression('cli/lastfmretrievetags', sprintf(lang('cli_message_8'),lang('cli/lastfmretrievetags')));
        
        $tracks = $q_tracks->result();
        $trackscount = 0;
        
        foreach($tracks as $track){
            $trackscount++;
            
            //Retrieve tags
            $tags = $this->qumulus->lastfmGetTrackTags($track->track_artist, $track->track_title);
            
            //save document
            if ($tags) $this->db->update('documents', array('file_tags'=>$tags), array('guid'=>$track->guid));
            
	        //Store progression
	        $this->taskSetProgression('cli/lastfmretrievetags', sprintf(lang('cli_message_10'), $trackscount));
        }
        
	    //Generate documents tree and tags
        $this->taskSetProgression('cli/lastfmretrievetags', lang('cli_message_11'));
        $this->qumulus->databaseStoreDocumentsTags();
        
        //Store progression
        $this->taskSetProgression('cli/lastfmretrievetags', lang('cli_message_6'));
    }
	
	private function generateImages($image_format, $force) {
	    //set php config
	    set_time_limit(0);
	    ini_set('memory_limit', -1);
	    
	    //Init progression
        $this->taskSetProgression('cli/generating'.$image_format, lang('cli_message_4'));
        
        //loop on each image documents
        $documents = $this->db->where_in('file_mimetype', array('image/jpeg','image/png'))->get('documents')->result();
        
	    foreach($documents as $key => $document){
	        if(!isset($this->qumulus->sources[$document->file_source])) continue;
	        
    	    $pseudo_path = DS.$document->file_source.$document->file_dirname.DS.$document->file_basename;
    	    $real_path = $this->qumulus->sources[$document->file_source]['path'].$document->file_dirname.DS.$document->file_basename;

    	    //Store progression
            $this->taskSetProgression('cli/generating'.$image_format, sprintf(lang('cli_message_5'), $key));
            
    	    //Generate image if necessary
    	    if(!file_exists(DATA_PATH.DS.$image_format.$pseudo_path) || $force) $this->qumulus->fileGenerateImage($real_path, $pseudo_path, $document->file_mimetype, $image_format);
	    }
	    
        //Reset progression
        $this->taskSetProgression('cli/generating'.$image_format, lang('cli_message_6'));
	}
	
    private function taskSetProgression($taskname, $status) {
        //Set new progression item
        $task = array('status'=>'<b>'.lang($taskname).':</b> '.$status);
        
        $this->db->update('tasks',$task, array('name'=>$taskname));
    }
	

}
