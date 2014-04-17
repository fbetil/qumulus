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

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', pathinfo($_SERVER["SCRIPT_FILENAME"])['dirname']);

class Assets {
    
    static function Render($type, $sources = null){
        if (!$sources) die();
        if (!in_array($type, array('javascript', 'css'))) die();
        
        $files = array();
        $modified = 0;
        $root = pathinfo($_SERVER["SCRIPT_FILENAME"])['dirname'].DS;
        
        foreach(explode(',', $sources) as $file){
            if (!file_exists($root.$file) || (strpos(ROOT_PATH.DS.'assets'.DS, realpath($root.$file))!==false) || !in_array(pathinfo($root.$file, PATHINFO_EXTENSION), array('js', 'css'))) continue;
            $files[] = $root.$file;
            $age = filemtime($root.$file);
            if($age > $modified) {
                $modified = $age;
            }
        }
        
        $offset = 60 * 60 * 24 * 7;
        header ('Expires: ' . gmdate ("D, d M Y H:i:s", time() + $offset) . ' GMT');
        
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified) {
            header("HTTP/1.0 304 Not Modified");
            header ('Cache-Control:');
        } else {
            header ('Cache-Control: max-age=' . $offset);
            header ('Content-type: text/'.$type.'; charset=UTF-8');
            header ('Pragma:');
            header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $modified )." GMT");
            
            ob_start('ob_gzhandler');
            
            foreach($files as $file) {
                if(strpos(basename($file),'.min.')===false) {
                    ob_start(array('Assets','Minify'.ucfirst($type)));
                    include($file);
                    ob_end_flush();
                } else {
                    include($file);
                }
            }
            
            ob_end_flush();
        }
    }
    
    static function MinifyJavascript($buffer) {
        /* remove comments */
        $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
        /* remove other spaces before/after ) */
        $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);
        return $buffer;
    }
    
    static function MinifyCss($buffer) {
        /* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);
        /* remove other spaces before/after ; */
        $buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
        $buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
        $buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);
        return $buffer;
    }
    
}

if (isset($_GET['js'])) Assets::Render('javascript', $_GET['js']);
if (isset($_GET['css'])) Assets::Render('css', $_GET['css']);