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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CI_Qumulus {
    
    private $CI;
    public $sources;
    public $parameters;
    public $preferences;
    public $hasher;
    public $phpic;

	function __construct()
	{
        $this->CI =& get_instance();

        //Define constants
        define('DS', DIRECTORY_SEPARATOR);
        define('ROOT_PATH', pathinfo($_SERVER["SCRIPT_FILENAME"])['dirname']);
        define('CACHE_PATH', ROOT_PATH.DS.'cache');
        define('DATA_PATH', ROOT_PATH.DS.'data');
        define('TMP_PATH', ROOT_PATH.DS.'data'.DS.'tmp');

        //Load phpass
        require('.'.DS.'application'.DS.'third_party'.DS.'phpass'.DS.'PasswordHash.php');
        $this->hasher = new PasswordHash(8, false);
        
        //Check installation
        if (!$this->CI->input->is_cli_request()) $this->databaseCheck();
        
	    //Initialize auth
	    $this->authInit();

	    //Load parameters, sources, preferences, lang
	    $this->loadParameters();
	    $this->loadSources();
	    $this->loadPreferences();
	    $this->loadLanguage();
	}
	
	private function databaseCheck(){
	    //Check if database is new
	    if (!$this->CI->db->table_exists('ci_sessions')) {
	        $this->databaseInstall();
	        
	        redirect('/install');
	    }
	    
	    //Check if admin account exist
	    if (!strstr($_SERVER['REQUEST_URI'],'/install')){
    	    $q_admin = $this->CI->db->get_where('users', array('is_admin'=>true));
    	    $admin = $q_admin->result();
    	    if (empty($admin)) redirect('/install');
	    }
	}
	
	private function databaseInstall(){
	    $this->CI->load->dbforge();
	    
	    //Create table ci_sessions
	    $ci_sessions = array(
	        'session_id'=>array('type'=>'VARCHAR', 'constraint'=>40, 'default'=>0),
	        'ip_address'=>array('type'=>'VARCHAR', 'constraint'=>45, 'default'=>0),
	        'user_agent'=>array('type'=>'VARCHAR', 'constraint'=>120),
	        'last_activity'=>array('type'=>'INT', 'constraint'=>10, 'unsigned'=>true, 'default'=>0),
	        'user_data'=>array('type'=>'TEXT')
	        );
	    $this->CI->dbforge->add_field($ci_sessions);
	    $this->CI->dbforge->create_table('ci_sessions', true);
	    
	    //Create table documents
	    $documents = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'guid'=>array('type'=>'VARCHAR', 'constraint'=>32),
	        'file_source'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'file_basename'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'file_dirname'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'file_format'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'file_mimetype'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'file_size'=>array('type'=>'BIGINT', 'constraint'=>20, 'unsigned'=>true),
	        'file_tags'=>array('type'=>'TINYTEXT', 'null'=>true),
	        'track_title'=>array('type'=>'VARCHAR', 'constraint'=>255, 'null'=>true),
	        'track_artist'=>array('type'=>'VARCHAR', 'constraint'=>255, 'null'=>true),
	        'track_album'=>array('type'=>'VARCHAR', 'constraint'=>255, 'null'=>true),
	        'track_number'=>array('type'=>'INT', 'constraint'=>3, 'null'=>true),
	        'track_duration'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'null'=>true),
	        'photo_title'=>array('type'=>'VARCHAR', 'constraint'=>255, 'null'=>true)
	        );
	    $this->CI->dbforge->add_field($documents);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->add_key('guid', true);
	    $this->CI->dbforge->create_table('documents', true);
	    
	    //Create table parameters
	    $parameters = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'name'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'value'=>array('type'=>'LONGTEXT')
	        );
	    $this->CI->dbforge->add_field($parameters);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->create_table('parameters', true);
	    
	    //Create table shares
	    $shares = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'name'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'visualization'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'password'=>array('type'=>'VARCHAR', 'constraint'=>255, 'null'=>true),
	        'token'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'documents'=>array('type'=>'LONGTEXT'),
	        'documents_tree'=>array('type'=>'LONGTEXT')
	        );
	    $this->CI->dbforge->add_field($shares);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->create_table('shares', true);

	    //Create table sources
	    $sources = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'name'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'path'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'types'=>array('type'=>'VARCHAR', 'constraint'=>255)
	        );
	    $this->CI->dbforge->add_field($sources);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->create_table('sources', true);

	    //Create table tasks
	    $tasks = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'name'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'status'=>array('type'=>'VARCHAR', 'constraint'=>255)
	        );
	    $this->CI->dbforge->add_field($tasks);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->create_table('tasks', true);

	    //Create table users
	    $users = array(
	        'id'=>array('type'=>'INT', 'constraint'=>9, 'unsigned'=>true, 'auto_increment'=>true),
	        'username'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'firstname'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'lastname'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'email'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'password'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'is_admin'=>array('type'=>'BOOLEAN'),
	        'prefs_language'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'prefs_startapp'=>array('type'=>'VARCHAR', 'constraint'=>255),
	        'prefs_music_restore_last_playlist'=>array('type'=>'BOOLEAN'),
	        'prefs_files_restore_last_path'=>array('type'=>'BOOLEAN'),
	        'prefs_shares_restore_last_path'=>array('type'=>'BOOLEAN')
	        );
	    $this->CI->dbforge->add_field($users);
	    $this->CI->dbforge->add_key('id', true);
	    $this->CI->dbforge->create_table('users', true);

	}
	
	function databaseStoreDocument($sourceid, $pseudopath, $metadata){
        //calculate guid
        $guid = md5(DS.$sourceid.$pseudopath);
        
        //retrieve existing document or create new one
        $q_document = $this->CI->db->get_where('documents', array('guid'=>$guid));
        
        $document = ($q_document->num_rows()) ? $q_document->row_array() : array('guid'=>$guid);

        //set required fields
        $path_parts = pathinfo($pseudopath);
        
        $document['file_source'] = $sourceid;
        $document['file_basename'] = $path_parts['basename'];
        $document['file_dirname'] = $path_parts['dirname'];
        
        //set extended
        $document['file_format'] = $metadata['fileformat'];
        $document['file_mimetype'] = $metadata['mime_type'];
        $document['file_size'] = $metadata['filesize'];
        foreach(array('id3v1','id3v2','vorbiscomment') as $tagstype){
            if (isset($metadata['tags'][$tagstype]['title'][0])) $document['track_title'] = $metadata['tags'][$tagstype]['title'][0];
            if (isset($metadata['tags'][$tagstype]['artist'][0])) $document['track_artist'] = $metadata['tags'][$tagstype]['artist'][0];
            if (isset($metadata['tags'][$tagstype]['album'][0])) $document['track_album'] = $metadata['tags'][$tagstype]['album'][0];
            if (isset($metadata['tags'][$tagstype]['tracknumber'][0])) $document['track_number'] = $metadata['tags'][$tagstype]['tracknumber'][0];
        }
        
        //specific actions
        if ($this->CI->config->item('file_format_map')[$metadata['fileformat']] == 'audio'){
            if (isset($metadata['playtime_seconds'])) $document['track_duration'] = $metadata['playtime_seconds'];
        }
        
        //Specific actions
        if ($this->CI->config->item('file_format_map')[$metadata['fileformat']] == 'image'){

            //Set image title from file name
            $document['photo_title'] = $path_parts['filename'];
            
            //Set tags from path parts
            if($this->parameters['photos_get_tags_from_path']){
                $document['file_tags'] = $this->databaseConvertFromArray(array(basename($path_parts['dirname'])));
            }
        }
        
        //save document
        if(!isset($document['id'])){
            $this->CI->db->insert('documents', $document);
            return $this->CI->db->insert_id();
        }else{
            $this->CI->db->update('documents', $document, array('id'=>$document['id']));
            return $document['id'];
        }
	}
	
    function databaseStoreDocumentsTree(){
	    //store documents tree snapshot
        $snapshot = array();
        
        $documents = $this->CI->db->select('file_source, file_dirname')->distinct()->get('documents')->result();

        foreach ($documents as $document){
            $path = array_filter(explode(DS,'s'.$document->file_source.$document->file_dirname));
            
            $out = array();
            while( $pop = array_pop($path) ) $out = array($pop => $out);

            $snapshot = array_merge_recursive($snapshot, $out);
        }
        
        $parameter = array('value'=>serialize($snapshot));
        $this->CI->db->update('parameters', $parameter, array('name'=>'documents_tree'));
    }
    
    function databaseStoreDocumentsTags(){
        //Search for tags
        $tags = array();

        foreach(array_unique(array_values($this->CI->config->item('file_format_map'))) as $file_type){
            $maxtagcount = 0;
            $file_type_tags = array();
            $file_type_formats = array_keys($this->CI->config->item('file_format_map'),  $file_type);
            
            //Get all tags for format
            $this->CI->db->select('file_tags');
            $this->CI->db->distinct();
            $this->CI->db->where_in('file_format', $file_type_formats);
            $documents = $this->CI->db->get('documents')->result();
        
            //Retrieve each tag
            foreach($documents as $document){
                $file_type_tags = array_merge($file_type_tags, $this->databaseConvertToArray($document->file_tags));
            }
            
            
            //Sort and store unique tags in array
            $file_type_tags = array_unique($file_type_tags);
            asort($file_type_tags);
            
            //Get count for each tag
            foreach($file_type_tags as &$tag){
                $this->CI->db->where_in('file_format', $file_type_formats);
                $this->CI->db->like('file_tags', '| '.$tag.' |');
                $count = $this->CI->db->count_all_results('documents');
    
                //Loop if no document with this tag
                if (!$count) continue;
                
                //Update tag
                $tag = array('name'=>$tag, 'count'=>$count);
                
                if($count > $maxtagcount) $maxtagcount = $count;
            }

            //Get class for each tag
            foreach($file_type_tags as &$tag){
                $tag['class'] = 'tag'.round($tag['count']*10/$maxtagcount);
            }
            
            $tags[$file_type] = $file_type_tags;
        }

        //Store tags in database
        $parameter = array('value'=>serialize($tags));
        $this->CI->db->update('parameters', $parameter, array('name'=>'documents_tags'));
    }
    
	function databaseGetTags($filter = array()){
	    //Check filter
	    if (empty($filter)) $filter = array_unique(array_values($this->CI->config->item('file_format_map')));
	    $filter = array_fill_keys($filter, array());

	    $tags = unserialize($this->CI->db->get_where('parameters', array('name'=>'documents_tags'))->row()->value);

        //Apply filter and return tags
        return array_intersect_key($tags, $filter);
	}
	
	function databaseConvertFromArray($tags){
	    asort($tags);
	    return '| '.implode(' | ', array_filter($tags)).' |';
	}
	
	function databaseConvertToArray($tags){
	    return array_filter(explode('|', str_replace(array('| ', ' |'), array('|', '|'), $tags)));
	}
	
	function loadParameters(){
	    $q_parameters = $this->CI->db->get('parameters');

        foreach($q_parameters->result() as $parameter){
            $this->parameters[$parameter->name] = $parameter->value;
        }
	}
	
	function loadPreferences(){
	    $this->preferences = array();

	    $q_user = $this->CI->db->get_where('users', array('id' => $this->CI->session->userdata('user_id')));

        //Exit if user don't exists
        if (!$q_user->num_rows()) return false;

	    $user = $q_user->row();
	    
	    foreach ($user as $propertyname => $propertyvalue){
	        if(substr($propertyname, 0, 6) == 'prefs_') $this->preferences[substr($propertyname, 6)] = $propertyvalue;
	    }
	}
	
	function loadLanguage(){
	    //Exit if user is not logged
	    if (!$this->CI->session->userdata('is_logged')) return false;
	    
	    //load default language of user
        $this->CI->lang->is_loaded = array();
        $this->CI->lang->language = array();
	    if(lang('_lang_') != $this->preferences['language']) $this->CI->lang->load('all', $this->preferences['language']);
	}
	
	function loadSources(){
	    $this->sources = array();

        $q_sources = $this->CI->db->get('sources');

	    $sources = $q_sources->result();
	    foreach ($sources as $source){
	        $types = ($source->types == 'all') ? array_keys($this->CI->config->item('file_format_map')) : array_keys($this->CI->config->item('file_format_map'), $source->types);
	        
	        $this->sources[$source->id] = array('name'=>$source->name, 'path'=>$source->path, 'types'=>$types);
	    }
	}

    function authInit() {
        //Load session class
        $this->CI->load->library('session');

        if($this->CI->session->userdata('is_logged') == true) {
            //TODO: Do something...
        }else{
            $this->CI->session->set_userdata('is_logged', false);
            $this->CI->session->set_userdata('is_cli_request', $this->CI->input->is_cli_request());
            $this->CI->session->set_userdata('is_admin', false);
            $this->CI->session->set_userdata('user_id', 0);
            $this->CI->session->set_userdata('user_label', null);
            
            if (!$this->CI->session->userdata('shares_tokens_permissions')) $this->CI->session->set_userdata('shares_tokens_permissions', array());
            if (!$this->CI->session->userdata('shares_guids_permissions')) $this->CI->session->set_userdata('shares_guids_permissions', array());
        }
    }
    
    function authHaveAuthorization($role){
        return ($this->CI->session->userdata('is_logged') && $this->CI->session->userdata('is_'.$role));
    }
    
    function authCheckAuthorization($role) {
        if (!$this->authHaveAuthorization($role)) $this->html403();
    }
    
    function authLogin($username, $password){
        //Retrieve user
	    $q_user = $this->CI->db->get_where('users', array('username'=>$username));
	    
        //Exit if user don't exists
        if (!$q_user->num_rows()) return false;

	    $user = $q_user->row();
        
        //Verify password
        $check = $this->hasher->CheckPassword($password, $user->password);
        
        //if password is not correct
        if (!$check) return false;
        
        //Load session
        $this->CI->session->set_userdata('is_logged', true);
        $this->CI->session->set_userdata('is_admin', $user->is_admin);
        $this->CI->session->set_userdata('user_id', $user->id);
        $this->CI->session->set_userdata('user_label', $user->firstname.' '.strtoupper($user->lastname));
        
        return true;
    }
    
    function authLogout(){
        //Reset session
        $this->CI->session->sess_destroy();
    }

	function htmlRenderView($view, $variables = array()){

	    $this->CI->load->library('smarty');

	    $variables = array_merge(array(
	        'csrf_hash'=>$this->CI->security->get_csrf_hash(),
	        'url_index'=>site_url(),
	        'url_base'=>base_url(),
	        'user_id'=>$this->CI->session->userdata('user_id'),
	        'user_is_logged'=>$this->CI->session->userdata('is_logged'),
	        'user_is_admin'=>$this->CI->session->userdata('is_admin'),
	        'user_label'=>$this->CI->session->userdata('user_label'),
	        'user_prefs'=>$this->preferences,
	        'user_anonymous'=>false,
	        'user_session_id'=>$this->CI->session->userdata('session_id'),
	        'notify'=>$this->CI->session->flashdata('notify')
	        ), $variables
	    );
	    
	    //Render view
	    $this->CI->smarty->view($view, $variables);
	}
	
	function html403() {
	    http_response_code(403);
	    die();
	}
	
	function htmlNotify($content, $type = 'notice', $title = null, $canCancel = false) {
	    if ($canCancel) $content .= '<br><br><div class="place-right"><a class="fg-white" href="javascript:void(0)" onclick="cancelAction()"><small>'.lang('cancel').'</small></a><br><br></div>';
	    
	    die (json_encode(array('content'=>$content, 'title'=>$title, 'canCancel'=>$canCancel, 'type'=>$type), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ));
	}
	
    function htmlSendResponseToBrowser($message) {
        ob_end_clean();
        header("Connection: close");
        ob_start();
        
        echo json_encode(array('content'=>$message, 'title'=>null, 'canCancel'=>false, 'type'=>'notice'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();
        flush();
    }
	
	function fileGetDirectoryContent($dirToScan){
	    //Exit if not a directory
	    if (!is_dir($dirToScan)) return false;
	    
	    //get recursive content
	    $files = array();
        $dirChilds = scandir($dirToScan); 
        foreach($dirChilds as $dirChild) { 
            if(substr($dirChild,0,1) === '.') {continue;} 
            if(is_file($dirToScan.DS.$dirChild)) {$files[]=$dirToScan.DS.$dirChild;continue;} 
            foreach($this->fileGetDirectoryContent($dirToScan.DS.$dirChild) as $dirChild) 
            { 
                $files[]=$dirChild; 
            } 
        }
        
        //Return array
        return $files;
	}
	
	function fileGetMetadata($file){
        //Return format for specific office formats
        if (in_array(pathinfo($file, PATHINFO_EXTENSION),  $this->CI->config->item('file_office_extensions'))) {
            //Return mime_type and extension
    	    return $this->fileGetFileInfo($file);
        }
        
	    //Load and start getID3
	    require_once('.'.DS.'application'.DS.'third_party'.DS.'getID3'.DS.'getid3'.DS.'getid3.php');
	    $getID3 = new getID3;
	    
        $metadata = $getID3->analyze($file);
        
        //Return metadata if minimum info are presents
        if (isset($metadata['mime_type']) && isset($metadata['fileformat']) && isset($metadata['filesize'])) return $metadata;
        
        //Return mime_type and extension
	    return $this->fileGetFileInfo($file);
	}
	
	function fileGetFileInfo($file){
	    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
	    
	    $mime_type = finfo_file($finfo, $file);
	    $fileformat = pathinfo ($file, PATHINFO_EXTENSION);
	    $filesize = filesize ($file);
	    
	    return array('mime_type'=>$mime_type, 'fileformat'=>$fileformat, 'filesize'=>$filesize );
	}
	
	function fileGenerateImage($input, $output, $mime_type, $image_format){
	    //Set size of image
	    $new_w = $this->parameters[$image_format.'_size'];
	    $new_h = $this->parameters[$image_format.'_size'];
	    
	    //Get info from file path
	    $output_path_parts = pathinfo($output);
	    
	    //set functions to call
	    switch($mime_type){
	        case 'image/jpeg':
	            $imagecreatefrom = 'imagecreatefromjpeg';
	            $imagesave = 'imagejpeg';
	            break;
	        case 'image/png':
	            $imagecreatefrom = 'imagecreatefrompng';
	            $imagesave = 'imagepng';
	            break;
	    }
	    
	    //set source image
	    $src_img = $imagecreatefrom($input);
	    imageinterlace($src_img, true);
	    
    	//Calculate output image size
        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);
        if ($old_x > $old_y) {
        	$image_w=$new_w;
        	$image_h=$old_y*($new_h/$old_x);
        }
        if ($old_x < $old_y) {
        	$image_w=$old_x*($new_w/$old_y);
        	$image_h=$new_h;
        }
        if ($old_x == $old_y) {
        	$image_w=$new_w;
        	$image_h=$new_h;
        }
        
        //Generate new image
        $dst_img = ImageCreateTrueColor($image_w,$image_h);
        imagecopyresampled($dst_img,$src_img,0,0,0,0,$image_w,$image_h,$old_x,$old_y);
        
        //Create output directory
        if(!file_exists(DATA_PATH.DS.'photos_'.$image_format.$output_path_parts['dirname'])) mkdir(DATA_PATH.DS.'photos_'.$image_format.$output_path_parts['dirname'], 0777, true);
        
        //save image
        $imagesave($dst_img, DATA_PATH.DS.'photos_'.$image_format.$output_path_parts['dirname'].DS.$output_path_parts['filename'].'.'.$output_path_parts['extension']);
        
        //delete image for free memory
        imagedestroy($dst_img); 
        imagedestroy($src_img); 
    }
    
    function fileStreamFile($location, $filename, $mimeType = false, $deleteAfter = false) {
        if (!$mimeType) $mimeType = 'application/octet-stream';
        
        if(!file_exists($location)) {
            header ("HTTP/1.0 404 Not Found");
            return;
        }
    
        $size=filesize($location);
        $time=date('r',filemtime($location));
        
        $fm=@fopen($location,'rb');
        if (!$fm) {
            header ("HTTP/1.0 505 Internal server error");
            return;
        }
        
        $begin=0;
        $end=$size;
        
        if(isset($_SERVER['HTTP_RANGE'])) {
            if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin=intval($matches[0]);
                if(!empty($matches[1])) $end=intval($matches[1]);
            }
        }
        
        if($begin>0||$end<$size) {
            header('HTTP/1.0 206 Partial Content');
        }else{
            header('HTTP/1.0 200 OK'); 
        }
        
        header("Content-Type: $mimeType");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache'); 
        header('Accept-Ranges: bytes');
        header('Content-Length:'.($end-$begin));
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Disposition: inline; filename=$filename");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $time");
        header('Connection: close'); 
        
        $cur=$begin;
        fseek($fm,$begin,0);
        
        while(!feof($fm)&&$cur<$end&&(connection_status()==0)){
            print fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
        }
        
        //delete file
        if($deleteAfter) unlink($location);
        
    }
    
    function fileSendFile($location, $filename, $mimeType = false, $deleteAfter = false){
        if (!$mimeType) $mimeType = 'application/octet-stream';

        $time=date('r',filemtime($location));
        
        header('Content-Type: '.$mimeType);
        header('Content-Length: '.filesize($location));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: public');
        header('Cache-Control:public, max-age=31536000');
        header("Last-Modified: $time");

        readfile($location);

        //delete file
        if($deleteAfter) unlink($location);
        
    }
    
    function fileRenderEmptyPng(){
        header('Content-Type: image/png');
        header("Expires: Mon, 1 Jan 2000 00:00:01 GMT"); 
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        die(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='));
    }
    
	function sharesAddTokenPermission($token){
	    //retrieve current tokens permissions
	    $tokens = $this->CI->session->userdata('shares_tokens_permissions');
	    
	    if (!in_array($token, $tokens)) $tokens[] = $token;
	    
	    //Store session
	    $this->CI->session->set_userdata('shares_tokens_permissions', $tokens);
	}
	
	function sharesRemoveTokenPermission($token){
	    //retrieve current tokens permissions
	    $tokens = $this->CI->session->userdata('shares_tokens_permissions');
	    
	    $tokens = array_filter($tokens, function($k) use ($token){return ($k != $token);});
	    
	    //Store session
	    $this->CI->session->set_userdata('shares_tokens_permissions', $tokens);
	}
	
	function sharesHaveTokenPermission($token){
	    //return token permission
	    return in_array($token, $this->CI->session->userdata('shares_tokens_permissions')) ;
	}
	
	function sharesAddGuidsPermission($guids){
	    //retrieve current guids permissions
	    $currents = $this->CI->session->userdata('shares_guids_permissions');
	    
	    $guids = array_unique(array_merge($currents, $guids));
	    
	    //Store session
	    $this->CI->session->set_userdata('shares_guids_permissions', $guids);
	}
	
	function sharesRemoveGuidsPermission($guids){
	    //retrieve current guids permissions
	    $currents = $this->CI->session->userdata('shares_guids_permissions');
	    
	    $guids = array_filter($currents, function($k){return (!in_array($k, $guids));});

	    //Store session
	    $this->CI->session->set_userdata('shares_guids_permissions', $guids);
	}
	
	function sharesHaveGuidPermission($guid){
	    //return guid permission
	    return in_array($guid, $this->CI->session->userdata('shares_guids_permissions')) ;
	}
	
    function validJson($json){
        return (json_decode($json) != null);
    }
    
    function validGuids($json){
        $json = json_decode($json, true);
        
        //Return false if not a valid json
        if (empty($json)) return false;
        
        foreach ($json as $guid){ //Check guid format
            if (!preg_match('/^[a-z0-9]{32}$/i', $guid)) return false;
        }
        
        //Finally return true
        return true;
    }
    
    function toolRecursiveArraySearch($needle,$haystack) {
        foreach($haystack as $key=>$value) {
            $current_key=$key;
            if($needle===$value or (is_array($value) && $this->toolRecursiveArraySearch($needle,$value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }
    
    function toolDump($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    
    function lastfmGetTrackTags($artist, $track) {
        //Check parameters
        if (empty($this->parameters['lastfm_api_key'])) return false;
        
        //Build query string
        $query = 'track='.urlencode($track).'&artist='.urlencode($artist);
        
	    //Retrieve track info from LastFm
	    $ws_url = 'http://ws.audioscrobbler.com/2.0/?api_key='.$this->parameters['lastfm_api_key'].'&format=json&method=track.getinfo&autocorrect=1&'.$query;
        $content = json_decode(file_get_contents($ws_url), true);

        //Exit if WS response error
        if(json_last_error() != JSON_ERROR_NONE) return false;
        
        //return tags
        $tags = array();
        if (isset($content['track']['toptags']['tag']) && is_array($content['track']['toptags']['tag'])) {
            foreach($content['track']['toptags']['tag'] as $tag) {
                $tags[] = $tag['name'];
            }
        }
        
        //return tags
        return $this->databaseConvertFromArray($tags);
        
    }
    
    function lastfmGetAlbumCover($artist, $album){
        //Check parameters
        if (empty($this->parameters['lastfm_api_key'])) return false;
        
        //Build query string
        $query = 'album='.urlencode($album).'&artist='.urlencode($artist);
        
	    //Retrieve album info from LastFm
	    $ws_url = 'http://ws.audioscrobbler.com/2.0/?api_key='.$this->parameters['lastfm_api_key'].'&format=json&method=album.getinfo&autocorrect=1&'.$query;
        $content = json_decode(file_get_contents($ws_url), true);
        
        //Exit if WS response error
        if(json_last_error() != JSON_ERROR_NONE || !isset($content['album']['image'][2]['#text']) || $content['album']['image'][2]['#text'] =='') return false;

        $cover_path = DATA_PATH.DS.'music_covers'.DS.md5($artist.'/'.$album).'.jpg';
        
        return (file_put_contents($cover_path, file_get_contents($content['album']['image'][2]['#text']))) ? true : false;

    }
}