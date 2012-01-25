<?php

/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi                                */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/*                                                                      */
/************************************************************************/
/*         Additional security & Abstraction layer conversion           */
/*                           2003 chatserv                              */
/*      http://www.nukefixes.com -- http://www.nukeresources.com        */
/************************************************************************/

if (!defined('MODULE_FILE')) {
	die ("You can't access this file directly...");
}

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
get_lang($module_name);
define('NO_EDITOR', 1); //For Php-Nuke 7.8 remove it for previous vertions

function listsections() {
    global $sitename, $prefix, $db, $module_name, $currentlang,$pagetitle;
    $pagetitle = " - "._SECINDEX;
	include ('header.php');
    $result = $db->sql_query("SELECT t1.secid, t1.color, t2.secname 
	from nuke_sections as t1, nuke_sections_titles as t2
	where t2.language='$currentlang' and t1.secid = t2.secid and t1.parentid = 0
	order by t1.color,t1.secid");
	
    OpenTable();
    echo "<center>"._SECWELCOME." $sitename.</center>";
    //"._YOUCANFIND."</center><br><br>
    echo "<table border=\"0\" align=\"center\">";
    $count = 0;
	while($row = $db->sql_fetchrow($result)) {
	    
	    $secid = intval($row['secid']);
	    $secname = $row['secname'];
		$seccolor = $row['color'];
        if ($count==3) {
        echo "<tr>";
        $count = 0;
        }
		echo "<td valign=\"top\" width=\"33%\">\n
		<table width=\"100%\" border=\"1\">
		<tr><td bgcolor=\"$seccolor\">
		<a href=\"modules.php?name=$module_name&amp;op=listarticles&amp;secid=$secid\" class=\"topnav\">
		<b>$secname</b></a>
		</td></tr>
		<tr><td>";
        listarticlessinheader($secid);

		listasubsecciones($secid);
		echo "</td></tr></table>";
	    $count++;
        if ($count==3) {
          echo "</tr>";
        }
        echo "</td>";
    }
    echo "</table></center>";
    CloseTable();
    include ('footer.php');
}

function listasubsecciones($secid,$level=1){
global $db,$currentlang,$module_name;

        $result = $db->sql_query("SELECT t1.secid, t1.color, t2.secname 
	    from nuke_sections as t1, nuke_sections_titles as t2
	    where t2.language='$currentlang' and t1.secid = t2.secid and t1.parentid = $secid
	    order by t1.secid");
	echo "<table border=\"0\" align=\"center\">";
	$count=0;	
	while($row = $db->sql_fetchrow($result)) {
	    $secid2 = intval($row['secid']);
	    $secname2 = $row['secname'];
		$seccolor = $row['color'];
		if ($count==1) {
          echo "<tr>";
          $count = 0;
        }
		echo "<td valign=\"top\" width=\"33%\">\n";
		echo "<table border=\"1\" width=\"95%\" align=\"center\">\n
		<tr><td bgcolor=\"$seccolor\">
		<a href=\"modules.php?name=$module_name&amp;op=listarticles&amp;secid=$secid2\" class=\"topnav\">
		$secname2</a>
		</td></tr>";
				$sublevel=$level+1;
		echo "<tr><td>";		
		listarticlessinheader($secid2,$sublevel-1);
		echo "</td></tr></table>";
		listasubsecciones($secid2,$sublevel);
		$count++;
        if ($count==1) {
          echo "</tr>";
        }
        echo "</td>";
	} // while
	echo "</table>";
}

function listarticlessinheader($secid,$espacios=0) {
    global $prefix, $multilingual, $currentlang, $db, $module_name;
	$multilingual = 1;
	if ($multilingual == 1) {
    $querylang = "AND language='$currentlang'";
    } else {
    $querylang = "";
    }
    //include ('header.php');
    $secid = intval($secid);
    $row_sec = $db->sql_fetchrow($db->sql_query("SELECT 
	secname from ".$prefix."_sections where secid='$secid'"));
    $secname = $row_sec['secname'];
	$query = "SELECT 
	t1.artid, t1.secid, t2.title, t1.counter 
	from nuke_sections_articles as t1, nuke_sections_articles_titles as t2
	where t1.secid='$secid' $querylang and t1.artid = t2.artid order by t1.artid";
    $result = $db->sql_query($query);
	$raya="";
        for ($i=0;$i<$espacios;$i++){
		  $raya.="<td>&nbsp;</td>";
		}
    //echo "<table border=\"0\" align=\"center\">\n";
    while($row = $db->sql_fetchrow($result)) {
        $artid = intval($row['artid']);
        $secid = intval($row['secid']);
        $title = $row['title'];
        $counter = $row['counter'];
        /*echo "
        <tr>
		$raya
		<td align=\"left\" nowrap>*/
		echo "<font class=\"content\">
        <li><a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid\">$title</a> 
        <a href=\"modules.php?name=$module_name&amp;op=printpage&amp;artid=$artid\">
      
        ";
    }
   // echo "</table>";
    
    
    //include ('footer.php');
}

function listarticles($secid) {
    global $prefix, $currentlang, $db, $module_name, $pagetitle;

    $querylang = "AND language='$currentlang'";
    
    $secid = intval($secid);
	$tempsecid = $secid; //Guarda id de sección para posteriormente seleccionar anterior y siguiente
    $row_sec = $db->sql_fetchrow($db->sql_query("SELECT secname,metadescrip from ".$prefix."_sections_titles where secid='$secid' $querylang"));
    $secname = $row_sec['secname'];
	$sloganaux = $row_sec['metadescrip'];
	$pagetitle = " - ".$secname;
	include ('header.php');
    $result = $db->sql_query("SELECT 
	t1.artid,  t2.title,  t1.counter 
	from nuke_sections_articles as t1, nuke_sections_articles_titles as t2 
	where t1.secid='$secid' and t2.language='$currentlang' and t1.artid = t2.artid");
   
    OpenTable();
	$row = $db->sql_fetchrow($db->sql_query("SELECT color from ".$prefix."_sections where secid='$secid'") );
	$seccolor=$row['color'];
	echo "<table border=\"1\" align=\"center\"><tr><td bgcolor=\"$seccolor\">";
    echo "<center><font class=\"boxtitle\">
     <b>$secname</b>.<br>$sloganaux</font></center>
    </td></tr>
	<tr><td>";
    while($row = $db->sql_fetchrow($result)) {
        $artid = intval($row['artid']);
        $secid = intval($row['secid']);
        $title = $row['title'];
        $content = $row['content'];
        $counter = $row['counter'];
        echo "
        <font class=\"content\">
        <li><a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid\">$title</a> ";
       /* <a href=\"modules.php?name=$module_name&amp;op=printpage&amp;artid=$artid\"><img src=\"images/print.gif\" border=\"0\" Alt=\""._PRINTER."\" width=\"15\" height=\"11\"></a>
        
        ";        */
    }
    /*echo "</table>
    <br><br><br><center>";*/
	// Consigue y despliega secciones hijas
	$sql="SELECT * from nuke_sections where parentid='$tempsecid'";
	$hijas = $db->sql_numrows($db->sql_query($sql) );
	if ($hijas > 0){
	  listasubsecciones($tempsecid);
	}
	echo "</tr></td></table>\n";
	
	 CloseTable();
	 echo "<br><center>";
	
	// Consigue y desplieg{a sección previa
$sql="SELECT t1.secid, t2.secname 
	    from nuke_sections as t1, nuke_sections_titles as t2
	    where t2.language='$currentlang' and t1.secid = t2.secid and t1.secid < $tempsecid
	    order by t1.secid DESC limit 1";
$result = $db->sql_query($sql);		
$row_sec = $db->sql_fetchrow($result);
if ($db->sql_numrows($result) > 0){
	$prev_name=$row_sec['secname'];	
	$prev_id=intval($row_sec['secid']);
	echo "<a href=\"modules.php?name=$module_name&amp;op=listarticles&amp;secid=$prev_id\"><img src=\"modules/$module_name/images/nanterior.gif\" title=\""._PREVIOUS." ($prev_name)\"></a>";
} // if num_rows > 0	
	
	echo " <a href=\"modules.php?name=$module_name\"><img src=\"modules/$module_name/images/regresoseccion.jpg\" title=\""._SECRETURN."\"</a> ";

// Consigue y despliega sección Siguiente
$sql="SELECT t1.secid, t2.secname 
	    from nuke_sections as t1, nuke_sections_titles as t2
	    where t2.language='$currentlang' and t1.secid = t2.secid and t1.secid > $tempsecid
	    order by t1.secid ASC limit 1";
$result = $db->sql_query($sql);		
$row_sec = $db->sql_fetchrow($result);
if ($db->sql_numrows($result) > 0){
	$prev_name=$row_sec['secname'];	
	$prev_id=intval($row_sec['secid']);
	echo "<a href=\"modules.php?name=$module_name&amp;op=listarticles&amp;secid=$prev_id\"><img src=\"modules/$module_name/images/nsigiente.gif\" title=\""._NEXT." ($prev_name)\"></a>";
} // if num_rows > 0	
echo "</center>";
   
    include ('footer.php');
}

function viewarticle($artid, $page) {
    global $prefix, $db, $module_name, $pagetitle,$admin,$currentlang,$multilingual;
   
    $querylang = "AND language='$currentlang'";
    
    $artid = intval($artid);
    if (($page == 1) OR ($page == "")) {
	$db->sql_query("update ".$prefix."_sections_articles set counter=counter+1 where artid='$artid'");
    }
    $row = $db->sql_fetchrow($db->sql_query("SELECT
	t1.artid, t1.title, t1.metadescrip, t1.keywords, t2.content, t3.secid, t3.counter
	from nuke_sections_articles_titles as t1, nuke_sections_articles_content as t2,
	nuke_sections_articles as t3
	where 
	t1.artid='$artid' and t1.language='$currentlang' 
	and t1.artid = t2.artid and t2.language='$currentlang'
	and t1.artid = t3.artid"));
        $artid = intval($row['artid']);
        $secid = intval($row['secid']);
        $title = $row['title'];
        $content = $row['content'];
        $counter = $row['counter'];
		$metadescrip = $row['metadescrip'];
		$keywords = $row['keywords'];
    // Query para obtener el nombre de la sección

  $row2 = $db->sql_fetchrow($db->sql_query("SELECT secname from ".$prefix."_sections_titles
   where secid='$secid' and language='$currentlang'"));
	$secname = $row2['secname'];  
    $words = sizeof(explode(" ", $content));
	if ($keywords<>'') 
	  $submetakey = $keywords;
	if ($metadescrip<>'')
	  $submetadescrip = $metadescrip;  
	$pagetitle = " - $secname - $title";
	//echo "desde article $submetakey<br>$submetadescrip";
	include("header.php");
    
	echo "<center><h1>$title</h1></center>";
  OpenTable2();	
	$contentpages = explode( "cortapagina", $content );
    $pageno = count($contentpages);
    if ( $page=="" || $page < 1 )
	$page = 1;
    if ( $page > $pageno )
	$page = $pageno;
    $arrayelement = (int)$page;
    $arrayelement --;
	
    if ($pageno > 1) {
	echo "<p class=\"titulo2\" align=\"right\"><b>"._PAGE.": $page/$pageno</b></p>";
    }
	$descrip=$contentpages[$arrayelement];
    $numclub=substr($descrip,strpos($descrip,'cajabooking')+12,3);
    //$descrip1=ereg_replace("cajabooking=([0-9]*)",cajabook($numclub),$descrip);
	$descrip2=preg_replace("'solaris-text-(printpage|viewarticle)-([0-9]*).html'e","nombre_articulo(\\1,\\2)",$descrip);
    
	echo "$descrip2";
	CloseTable2();
	//echo $descrip;
    if($page >= $pageno) {
	  $next_page = "";
    } else {
	$next_pagenumber = $page + 1;
	if ($page != 1) {
	    $next_page .= "<img src=\"images/blackpixel.gif\" width=\"10\" height=\"2\" border=\"0\" alt=\"\"> &nbsp;&nbsp; ";
	}
	$next_page .= "<a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid&amp;page=$next_pagenumber\">"._NEXTPAGE." ($next_pagenumber/$pageno)</a> <a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid&amp;page=$next_pagenumber\"><img src=\"images/right.gif\" border=\"0\" alt=\""._NEXTPAGE."\"></a>";
    }

    if($page <= 1) {
	$previous_page = "";
    } else {
	$previous_pagenumber = $page - 1;
	$previous_page = "<a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid&amp;page=$previous_pagenumber\"><img src=\"images/left.gif\" border=\"0\" alt=\""._PREVIOUSPAGE."\"></a> <a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid&amp;page=$previous_pagenumber\">"._PREVIOUSPAGE." ($previous_pagenumber/$pageno)</a>";
    }
	
    /*echo "</td></tr>"
	."<tr><td align=\"center\">";
	*/
	echo "$previous_page &nbsp;&nbsp; $next_page<br><br>";
	echo "<center>";
	//Consigue artículo anterior	
	$query = "SELECT 
	t1.artid, t1.secid, t2.title 
	from nuke_sections_articles as t1, nuke_sections_articles_titles as t2
	where t1.secid='$secid' $querylang and t1.artid = t2.artid and t1.artid<$artid
	order by t1.artid DESC limit 1";
    $result = $db->sql_query($query);
	if ($db->sql_numrows($result) > 0){
		$row=$db->sql_fetchrow($result);
		$opartid = intval($row['artid']);
		$optitle = $row['title'];
		echo "<a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$opartid\"><img src=\"modules/$module_name/images/nanterior.gif\" title=\"$optitle\"></a>";
	} // if sql_numrows > 0
	
	
	echo " <a href=\"modules.php?name=$module_name&amp;op=listarticles&amp;secid=$secid\"><img src=\"modules/$module_name/images/regresoseccion.jpg\" title=\""._GOTO." $secname\"></a> ";

//Consigue artículo SIGUIENTE	
	$query = "SELECT 
	t1.artid, t1.secid, t2.title 
	from nuke_sections_articles as t1, nuke_sections_articles_titles as t2
	where t1.secid='$secid' $querylang and t1.artid = t2.artid and t1.artid>$artid
	order by t1.artid limit 1";
    $result = $db->sql_query($query);
	if ($db->sql_numrows($result) > 0){
		$row=$db->sql_fetchrow($result);
		$opartid = intval($row['artid']);
		$optitle = $row['title'];
		echo "<a href=\"modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$opartid\"><img src=\"modules/$module_name/images/nsigiente.gif\" title=\"$optitle\"></a>";
	} // if sql_numrows > 0
	
	echo "<br><font class=\"footmsg\">($words "._TOTALWORDS.")"
	."&nbsp;&nbsp;($counter "._READS.")</font> &nbsp;&nbsp;"
	."<a href=\"modules.php?name=$module_name&amp;op=printpage&amp;artid=$artid\"><img src=\"images/print.gif\" border=\"0\" Alt=\""._PRINTER."\" width=\"15\" height=\"11\"></a>"
	."</font>";
    
    echo "</center>";
	//CloseTable2();
    include ('footer.php');
	
}

function nombre_articulo1($modo,$artid){  // para que obtenga el nombre de un artículo
global $db,$prefix,$currentlang;
  $sql="SELECT title 
  from ".$prefix."_sections_articles_titles where artid='".$artid."' and language='$currentlang'";
  $renglon = $db->sql_fetchrow($db->sql_query($sql));
  $title=$renglon[title];
//$title.="$artid";
  $urlsection=strtolower( urlencode($title) );
  $articulo="$urlsection-$modo-$artid.html";
  //return $artid;
  return $articulo;
}

function PrintSecPage($artid) {
    global $site_logo, $nukeurl, $sitename, $datetime, $prefix, $db, $module_name,$currentlang;
    $artid = intval($artid);
	$row = $db->sql_fetchrow($db->sql_query("SELECT
	t1.artid, t1.title, t2.content
	from nuke_sections_articles_titles as t1, nuke_sections_articles_content as t2
	where 
	t1.artid='$artid' and t1.language='$currentlang' 
	and t1.artid = t2.artid and t2.language='$currentlang'"));
	$title = $row['title'];
	$content = $row['content'];
	//$content=ereg_replace("cajabooking=([0-9]*)",cajabook($numclub),$content);
	$content=preg_replace("'solaris-text-(printpage|viewarticle)-([0-9]*).html'e","nombre_articulo1(\\1,\\2)",$content);
    echo "
    <html>
    <head><title>$sitename - $title</title></head>
    <body bgcolor=\"#FFFFFF\" text=\"#000000\">
    <table align=\"center\" border=\"0\"><tr><td>
    <table align=\"center\" border=\"0\" width=\"800\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#000000\"><tr><td>
    <table border=\"0\" width=\"800\" cellpadding=\"20\" cellspacing=\"1\" bgcolor=\"#FFFFFF\"><tr><td>
    <center>
    <img src=\"images/$site_logo\" border=\"0\" alt=\"\"><br><br>
    <font class=\"content\">
    <b>$title</b></font><br>
    </center><font class=\"content\">
    $content<br><br>";
    echo "</td></tr></table></td></tr></table>
    <br><br><center>
    <font class=\"content\">
    "._COMESFROM." $sitename<br>
    <a href=\"$nukeurl\">$nukeurl</a><br><br>
    "._THEURL."<br>
    <a href=\"".$nukeurl."/modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid\">".$nukeurl."/modules.php?name=$module_name&amp;op=viewarticle&amp;artid=$artid</a></font></center>
    </td></tr></table>
    </body>
    </html>
    ";
}

switch($op) {

    case "viewarticle":
    viewarticle($artid, $page);
    break;

    case "listarticles":
    listarticles($secid);
    break;

    case "printpage":
    PrintSecPage($artid);
    break;
	
	case "listsections":
	listsections();
	break;

    default:
    listsections();
    //viewarticle(107,$page);
	break;

}

?>
