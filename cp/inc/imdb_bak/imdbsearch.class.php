<?php
/*
This file is part of imdbphp.

    imdbphp is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    imdbphp is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with imdbphp; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once ("imdb_config.php");
require_once ("imdb.class.php");

 /** Search the IMDB for a title and obtain the movies IMDB ID
  * @package Api
  * @class imdbsearch
  * @extends imdb_config
  */
 class imdbsearch extends imdb_config{
  var $page = "";
  var $name = NULL;
  var $resu = array();
  var $url = "http://www.imdb.com/";

  /** Read the config
   * @constructor imdbsearch
   */
  function imdbsearch() {
    $this->imdb_config();
  }

  /** Set the name (title) to search for
   * @method setsearchname
   */
  function setsearchname ($name) {
   $this->name = $name;
   $this->page = "";
   $this->url = NULL;
  }

  /** Set the URL
   * @method seturl
   */
  function seturl($url){
   $this->url = $url;
  } 

  /** Create the IMDB URL for the movie search
   * @method mkurl
   * @return string url
   */
  function mkurl () {
   if ($this->url !== NULL){
    $url = $this->url;
   }else{
   $url = "http://".$this->imdbsite."/find?q=".urlencode($this->name).
#          "&restrict=Movies+only&GO.x=0&GO.y=0&GO=search;tt=1"; // Sevec ori
          ";tt=on;nm=on;mx=20"; // Izzy
#          ";more=tt;nr=1"; // @moonface variant (untested)
   }
   return $url;
  }

  /** Setup search results
   * @method results
   * @return array results
   */
  function results ($url="") {
   if ($this->page == "") {
     if (empty($url)) $url = $this->mkurl();
     $be = new IMDB_Request($url);
     $be->sendrequest();
     $fp = $be->getResponseBody();
     if ( !$fp ){
       if ($header = $be->getResponseHeader("Location")){
        if (strpos($header,$this->imdbsite."/find?")) {
          return $this->results($header);
          break(4);
        }
        #--- @moonface variant (not tested)
        # $idpos = strpos($header, "/Title?") + 7;
        # $this->resu[0] = new imdb( substr($header, $idpos,7));
        #--- end @moonface / start sevec variant
        $url = explode("/",$header);
        $id  = substr($url[count($url)-2],2);
        $this->resu[0] = new imdb($id);
        #--- end Sevec variant
        return $this->resu;
       }else{
        return NULL;
       }
     }
     $this->page = $fp;
   } // end (page="")

   $searchstring = array( '<A HREF="/title/tt', '<A href="/title/tt', '<a href="/Title?', '<a href="/title/tt');
   $i = 0;
#echo "Walking searchstring for this page:";
#echo "<div style='border:1px solid black'>".htmlentities($this->page)."</div>";
#echo "<div style='border:1px solid black'>".$this->page."</div>";
   foreach( $searchstring as $srch){
#echo "<ul><li>Testing ".htmlentities($srch)."</li>";
    $res_e = 0;
    $res_s = 0;
    $len = strlen($srch);

$stop = 0;


$tentimes = 1;

    while ((($res_s = strpos ($this->page, $srch, $res_e)) > 10)) {

//    if ($stop < 10) {
      $res_e = strpos ($this->page, "(", $res_s);
      $tmpres = new imdb ( substr($this->page, $res_s+$len, 7));
      $ts = strpos($this->page, ">",$res_e) +1;
      $te = strpos($this->page,"<",$ts);
      $tmpres->main_title = substr($this->page,$ts,$te-$ts);
#      $tmpres->main_title=substr ($this->page, $res_s + 28, $res_e - $res_s - 28);
#      if ($pos = strpos($tmpres->main_title,">"))
#        $tmpres->main_title = substr($tmpres->main_title,$pos+1);
      $ts = strpos($this->page,"(",$te) +1;
      $te = strpos($this->page,")",$ts);
      $tmpres->main_year=substr($this->page,$ts,$te-$ts);
#      $tmpres->main_year=substr($this->page, $res_e+1, 4);
      $i++;
	  $tentimes++;
      $this->resu[$i] = $tmpres;
	  if ( $tentimes == 5 )
	    break(2);
//	$stop++;
//     }
    }
#echo "</ul>\n";
   }
#echo "Search completed, '".count($this->resu)."' results.<br>\n";
  # $stop++;

   return $this->resu;
  }
} // end class imdbsearch
?>
