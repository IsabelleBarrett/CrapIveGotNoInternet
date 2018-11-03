<? 
// functions file
function checkAsset($asId, $usId, $usGroups)
{
	$db = new DB;
	$db->query("SELECT asId,asDesc,asGroups,asUsers,asTitle FROM assets WHERE asId = '$asId'");
	$db->next_record();
	list($asId,$asDesc,$asGroups,$asUsers,$asTitle) = $db->Record;
	$asGroupsArray = explode("__",$asGroups);
	$asUsersArray = explode("__",$asUsers);
	$usGroupsArray = explode("__",$usGroups);
	if(array_intersect($usGroupsArray, $asGroupsArray) OR in_array($usId,$asUsersArray))
		return $db->Record;
	else
		return false;
}
//new preferences system - 
function implode_with_keys($glue, $array) {
        $output = array();
        foreach( $array as $key => $item ) $output[] = $key . "=" . $item;

        return implode($glue, $output);
} 
function explode_with_keys($seperator, $string)
{
        $output=array();
        $array=explode($seperator, $string);
        foreach ($array as $value) {
                $row=explode("=",$value);
                $output[$row[0]]=$row[1];
        }
        return $output;
} 
function listOrder($prefId, $prefVal, $prefVar, $linkTitle)
{
	// split into constuents [0] = field name [1] = ASC or DESC
	$searchArray = split(" ", $prefVal);
	// check whether the column is selected at all
	if ($prefVar == $searchArray[0])
	{
		if ($searchArray[1]=="ASC")
			$ret = "<div onClick='document.form1.pref.value=\"$prefVar DESC\";document.form1.prefId.value=\"$prefId\";document.form1.submit();' ><a href='#'>$linkTitle: <img src='../images/arrow_down.gif' border='0'></a></div>";
		else
		    $ret = "<div onClick='document.form1.pref.value=\"$prefVar ASC\";document.form1.prefId.value=\"$prefId\";document.form1.submit();' ><a href='#'>$linkTitle: <img src='../images/arrow_up.gif' border='0'></a></div>";
	
	}
	else
		$ret = "<div onClick='document.form1.pref.value=\"$prefVar ASC\";document.form1.prefId.value=\"$prefId\";document.form1.submit();'><a href='#'>$linkTitle: </a></div>";
	return $ret;
}
function pageNav2($currentPage=0,$maxRecords=1,$num_rows=0 , $formname = "form1", $formOff = 0)
{
//Normal pagenav but with the new Nov2010 redesign layout
	$val = "<table cellpadding='0' cellspacing='0' width='100%' style='color:#FFFFFF; font-weight:bold; font-size:12px'>
<tr style='height:36px'>
    <td><img src='../images/nov2010Redesign/pageNavLeft.gif' /></td>
    <td style='background-image:url(../images/nov2010Redesign/pageNav1px.gif); background-repeat:repeat-x; width:99%'>";
	if (!$formOff) $val .= "<input name='currentPage' type='hidden' id='currentPage' value='".$currentPage."'>";
	if (!$num_rows)
	{
		$val .= "No records were found";
	}
	 else
	{
		$pages = $num_rows/$maxRecords;
		//	decide which pages to show
		$maxDisplayPages = 20;
		if ($pages <= $maxDisplayPages OR $currentPage <= ($maxDisplayPages/2))
		{
			$startDisplayPage = 0;
			$endDisplayPage = $maxDisplayPages;
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		} 
		else
		{
			$startDisplayPage = $currentPage - ($maxDisplayPages/2);
			$endDisplayPage = $currentPage + ($maxDisplayPages/2);
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		}
		$startRecord = $currentPage*$maxRecords+1;
		$finishRecord = $currentPage*$maxRecords+$maxRecords;
		if ($finishRecord > $num_rows) $finishRecord = $num_rows;
		$records_to_display = $finishRecord - $startRecord+1;
		// resets page value to the last if deletion occurs reducing number of pages displayed
		if ($currentPage > $pages) $currentPage = $pages;
		//$val .= "<table id='navTable' style='width:100%; border:1px solid #ff0000'><tr><td>
		$val .= "Results: ".$startRecord."-".$finishRecord." of ".$num_rows;
		
		if ($pages>=1)
		{
		
		
		
			$val .= "<span style='padding-left:80px'>Page:</span>";
			if ($currentPage > 0) 
			{
				$val .= "<span onClick=\"document.".$formname.".currentPage.value = '".($currentPage-1)."'; document.$formname.submit();\"><a href='#' style='color:#ffffff' class='button'>&nbsp;&lt;Prev&nbsp;</a></span>";
			}else
			{
				$val .= "<span>&nbsp;&lt;Prev&nbsp;</span>";
			} for ($i=$startDisplayPage; $i < $endDisplayPage; $i++)
			{ 
				if ($currentPage != $i) 
				{ 
					$val .= "<span onClick=\"document.".$formname.".currentPage.value = '$i'; document.$formname.submit();\"><a href='#' style='color:#ffffff' class='button'>&nbsp;".($i+1)."&nbsp;</a></span>";
				}else
				{
					$val .= "<span><a href='#' class='buttonFlat' style='cursor:default; color:#ffffff'>&nbsp;".($i+1)."&nbsp;</a></span>";
				} 
			} 
			if ($currentPage+1 < $pages) 
			{ 
				$val .= "<span onClick=\"document.".$formname.".currentPage.value = '".($currentPage+1)."'; document.$formname.submit();\"><a href='#' style='color:#ffffff' class='button'>&nbsp;Next&gt;&nbsp;</a></span>";
			} else {
				$val .= "<span style='color:#ffffff'>&nbsp;Next&gt;&nbsp;</span>";
			}
		
		
		
		}
		//$val .= "</td></tr></table>";
		
		if ($pages > $maxDisplayPages){
			$val .= "<span style='padding-left:80px'>  Go to Page: <select name='pageSelector$formOff' id='pageSelector' onChange=\"document.".$formname.".currentPage.value = document.".$formname.".pageSelector$formOff.options.selectedIndex-1;document.$formname.submit();\">";
			$val .= "<option></option>";
			for($i=0;$i<$pages;$i++)
			{        
				$val .= "<option value=".$i.">".($i+1)."</option>";
			}
			$val .= "</select></span>";
			//$val .= "</td></tr><tr><td>";
		}
	}
	$val .= "</td>
    <td><img src='../images/nov2010Redesign/pageNavRight.gif' /></td>
</tr>
</table>";
	return $val;
}

function newPageNav($currentPage=0,$maxRecords=1,$num_rows=0 , $formname = "form1", $formOff = 0)
{

	$val = "";
	if (!$formOff) $val .= "<input name='currentPage' type='hidden' id='currentPage' value='".$currentPage."'>";
	if (!$num_rows)
	{
		$val .= "No records were found";
	}
	 else
	{
		$pages = $num_rows/$maxRecords;
		//	decide which pages to show
		$maxDisplayPages = 20;
		if ($pages <= $maxDisplayPages OR $currentPage <= ($maxDisplayPages/2))
		{
			$startDisplayPage = 0;
			$endDisplayPage = $maxDisplayPages;
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		} 
		else
		{
			$startDisplayPage = $currentPage - ($maxDisplayPages/2);
			$endDisplayPage = $currentPage + ($maxDisplayPages/2);
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		}
		$startRecord = $currentPage*$maxRecords+1;
		$finishRecord = $currentPage*$maxRecords+$maxRecords;
		if ($finishRecord > $num_rows) $finishRecord = $num_rows;
		$records_to_display = $finishRecord - $startRecord+1;
		// resets page value to the last if deletion occurs reduceing number of pages displayed
		if ($currentPage > $pages) $currentPage = $pages;
		$val .= "<table id='navTable' style='width:100%;'><tr><td style=\"float:left; margin-left:20px\">Results: ".$startRecord."-".$finishRecord." of ".$num_rows;
		if ($pages > $maxDisplayPages){
			$val .= "<br>Go to Page: <select name='pageSelector$formOff' id='pageSelector' onChange=\"document.".$formname.".currentPage.value = document.".$formname.".pageSelector$formOff.options.selectedIndex-1;document.$formname.submit();\">";
			$val .= "<option></option>";
			for($i=0;$i<$pages;$i++)
			{        
				$val .= "<option value=".$i.">".($i+1)."</option>";
			}
			$val .= "</select></div></td></tr><tr><td><div align='center'>";
		}
		
		if ($pages>=1)
		{
			$val .= "
			<nav>
				<ul class=\"pagination\" style=\"width:100%;margin-left:20px\">
			<div style='float:left'></div>";
			if ($currentPage > 0) 
			{
				$val .= "
				<li onClick=\"document.".$formname.".currentPage.value = '".($currentPage-1)."'; document.$formname.submit();\" style='float:left;'>
      					<a href=\"#\" aria-label=\"Previous\">
        					<span aria-hidden=\"true\">&laquo;</span>
      					</a>
				</li>";
			}else
			{
				$val .= "
				<li style='float:left;'>
      					<a href=\"#\" aria-label=\"Previous\">
        					<span aria-hidden=\"true\">&laquo;</span>
      					</a>
				</li>
				";
			} 
			for ($i=$startDisplayPage; $i < $endDisplayPage; $i++)			{ 
				if ($currentPage != $i) 
				{ 
					$val .= "<li onClick=\"document.".$formname.".currentPage.value = '$i'; document.$formname.submit();\" style='float:left'><a href='#' class='button'>&nbsp;".($i+1)."&nbsp;</a></il>";
				}else
				{
					$val .= "<li style='float:left;' class=\"active\"><a href='#' class='buttonFlat' style='cursor:default'>&nbsp;".($i+1)."&nbsp;</a></li>";
				} 
			} 
			if ($currentPage+1 < $pages) 
			{ 
				$val .= "
				<li onClick=\"document.".$formname.".currentPage.value = '".($currentPage+1)."'; document.$formname.submit();\" style='float:left'>
						      <a href=\"#\" aria-label=\"Next\">
        							<span aria-hidden=\"true\">&raquo;</span>
      						  </a>
	  			</li>";
			} else {
				$val .= "
				<li style='float:right;'>
      					<a href=\"#\" aria-label=\"Next\">
        					<span aria-hidden=\"true\">&laquo;</span>
      					</a>
				</li>";
			}
		}
		$val .= "</td></tr></table></ul></nav>";
	}
	return $val;

}



function pageNav($currentPage=0,$maxRecords=1,$num_rows=0 , $formname = "form1", $formOff = 0)
{
	$val = "";
	if (!$formOff) $val .= "<input name='currentPage' type='hidden' id='currentPage' value='".$currentPage."'>";
	if (!$num_rows)
	{
		$val .= "No records were found";
	}
	 else
	{
		$pages = $num_rows/$maxRecords;
		//	decide which pages to show
		$maxDisplayPages = 20;
		if ($pages <= $maxDisplayPages OR $currentPage <= ($maxDisplayPages/2))
		{
			$startDisplayPage = 0;
			$endDisplayPage = $maxDisplayPages;
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		} 
		else
		{
			$startDisplayPage = $currentPage - ($maxDisplayPages/2);
			$endDisplayPage = $currentPage + ($maxDisplayPages/2);
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		}
		$startRecord = $currentPage*$maxRecords+1;
		$finishRecord = $currentPage*$maxRecords+$maxRecords;
		if ($finishRecord > $num_rows) $finishRecord = $num_rows;
		$records_to_display = $finishRecord - $startRecord+1;
		// resets page value to the last if deletion occurs reduceing number of pages displayed
		if ($currentPage > $pages) $currentPage = $pages;
		$val .= "<table id='navTable' style='width:100%;'><tr><td>Results: ".$startRecord."-".$finishRecord." of ".$num_rows;
		if ($pages > $maxDisplayPages){
			$val .= "<br>Go to Page: <select name='pageSelector$formOff' id='pageSelector' onChange=\"document.".$formname.".currentPage.value = document.".$formname.".pageSelector$formOff.options.selectedIndex-1;document.$formname.submit();\">";
			$val .= "<option></option>";
			for($i=0;$i<$pages;$i++)
			{        
				$val .= "<option value=".$i.">".($i+1)."</option>";
			}
			$val .= "</select></div></td></tr><tr><td><div align='center'>";
		}
		if ($pages>=1)
		{
			$val .= "<div style='float:left'>Page:</div>";
			if ($currentPage > 0) 
			{
				$val .= "<div onClick=\"document.".$formname.".currentPage.value = '".($currentPage-1)."'; document.$formname.submit();\" style='float:left;'><a href='#' class='button'>&nbsp;&lt;Prev&nbsp;</a></div>";
			}else
			{
				$val .= "<div style='float:left'>&nbsp;&lt;Prev&nbsp;</div>";
			} for ($i=$startDisplayPage; $i < $endDisplayPage; $i++)
			{ 
				if ($currentPage != $i) 
				{ 
					$val .= "<div onClick=\"document.".$formname.".currentPage.value = '$i'; document.$formname.submit();\" style='float:left'><a href='#' class='button'>&nbsp;".($i+1)."&nbsp;</a></div>";
				}else
				{
					$val .= "<div style='float:left;'><a href='#' class='buttonFlat' style='cursor:default'>&nbsp;".($i+1)."&nbsp;</a></div>";
				} 
			} 
			if ($currentPage+1 < $pages) 
			{ 
				$val .= "<div onClick=\"document.".$formname.".currentPage.value = '".($currentPage+1)."'; document.$formname.submit();\" style='float:left'><a href='#' class='button'>&nbsp;Next&gt;&nbsp;</a></div>";
			} else {
				$val .= "<div style = 'float:left'>&nbsp;Next&gt;&nbsp;</div>";
			}
		}
		$val .= "</td></tr></table>";
	}
	return $val;
}
function archive($idVar, $idVal, $table, $archiveVar)
{
	// new instance of DB
	$db = new DB;
	// get current value
	$archived = $db->getval("SELECT $archiveVar FROM $table WHERE $idVar = $idVal","$archiveVar");
	// currently archived?
	if ($archived) 
	{
		$val = "<a href=\"Javascript:document.getElementById('archiveQuery').value='$table SET $archiveVar=0 WHERE $idVar=$idVal'; document.form1.submit();\"><img src='../images/nov2010Redesign/icons/unarchive.gif' alt='De-Archive' title='De-Archive' border='0' </a>";
	}
	else 
	{ 
		$val = "<a href=\"Javascript: document.getElementById('archiveQuery').value='$table SET $archiveVar=1 WHERE $idVar=$idVal'; document.form1.submit();\"><img src='../images/nov2010Redesign/icons/archive.gif' alt='Archive' title='Archive' border='0' ></a>";
	}
	return $val;
}
function querymenu($list_name, $query, $column_list, $column_value=0, $selected_value='', $tab=0, $size=0, $allowMultiple = 0, $disable=0, $startWithBlank=1, $startText="", $jscript="", $separator=" ", $extra="")
{
	$db = new DB;
	$db->query($query);
	$val = "<select id=\"".$list_name."\" name=\"".$list_name."\" $jscript $extra ";
	$val .= ($tab) ? "tabindex='".$tab."'" : "";
	if ($size) $val .= " size='".$size."'";
	if ($allowMultiple) $val .= " multiple";
	if ($disable) $val .= " disabled";
	$val .= ">";
	
	if($startWithBlank==1) $val .= "<option value='' >$startText</option>";
  	while($db->next_record())
	{
		$column_list_array = explode(",",$column_list);
		$list = "";
		foreach($column_list_array as $value)
		{
			$list .= trim($db->Record[trim($value)]).$separator;
		}
		//$list = $db->Record[$column_list];
		if ($column_value)
		{
			$value = $db->Record[$column_value];
		} 
		else 
		{
			$value = $db->Record[$column_list];
		}
    	$val .= "<option value='".$value."'";
		if (is_array($selected_value))
		{
			$val .= (in_array($value, $selected_value)) ? "selected>" : ">";
		}
		else
		{
			$val .= ($value==$selected_value) ? "selected>" : ">";
		}
		$val .= $list."</option>";
	}
	$val .= "</select>";
	return $val;
}














function archive2($idVar, $idVal, $table, $archiveVar)
{
	// new instance of DB
	$db = new DB;
	// get current value
	$archived = $db->getval("SELECT $archiveVar FROM $table WHERE $idVar = $idVal","$archiveVar");
	// currently archived?
	if ($archived) 
	{
		$val = "<a href=\"Javascript:document.getElementById('archiveQuery').value='$table SET $archiveVar=0 WHERE $idVar=$idVal'; document.form1.submit();\">
		<span class=\"glyphicon glyphicon-folder-close\" style=\"font-size:12px; color:#ffffff; 
         background-color:#CC0000; padding:6px 6px; margin:12px 0px\"></span>
		</a>";
	}
	else 
	{ 
		$val = "<a href=\"Javascript: document.getElementById('archiveQuery').value='$table SET $archiveVar=1 WHERE $idVar=$idVal'; document.form1.submit();\">
        <span class=\"glyphicon glyphicon-folder-open\" style=\"font-size:12px; color:#ffffff; 
         background-color:#3c515e; padding:6px 6px; margin:12px 0px\">
            </span>
		</a>";
	}
	return $val;
}









































function back($back)
{
	/*if(!$back) $back = -1; else $back--;
	//$val = "<input type=button value='BACK' onClick='history.go($back)'>";
	$val = "<input type=image style='border:0px' src='../images/buttons/back.jpg' onClick='history.go($back)'>";
    $val .="<input name='back' type='hidden' id='back' value='$back'>";
    return $val;
	*/
	
	$val = "<a href='#' onclick='history.back(); return false;'><img src='../images/buttons/back.jpg' alt='Back' border='0' /></a>";
	return $val;
}
function back2($back) 
{ //Same as back() but using the Nov 2010 redesign image
	
	$val = "<a href='#' onclick='history.back(); return false;'><img src='../images/nov2010Redesign/buttons/back.gif' alt='Back' border='0' /></a>";
	return $val;
}
//style="background-image:url(../images/nov2010Redesign/inputboxes/topleft.gif); width:101px; height:21px" 
function datePicker($field, $fieldVal,$left=0, $onChange="",$pages=2)
{
	if($left) {
	$var = "<img style='margin:0px;margin-right:2px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' /><iframe title='Date Picker' src='iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe><input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "";
	} else {
	$var = "<input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "<img style='margin:0px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' />
	<iframe title='Date Picker' src='iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe>";
	}
	return $var;
}
function datePickerNewDesign($field, $fieldVal,$left=0, $onChange="",$pages=2)
{ //same as function datePicker but with the nov2010 redesign
	if($left) {
	$var = "<img style='margin:0px;margin-right:2px; cursor:pointer; vertical-align:middle;background-image:url(../images/nov2010Redesign/inputboxes/topleft.gif); width:101px; height:21px' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' /><iframe title='Date Picker' src='iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='newInputJobs' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe><input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "";
	} else {
	$var = "<input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." 		style=\"background-image:url(../images/nov2010Redesign/inputboxes/topleft.gif); width:101px; height:21px; border:1px solid #ff0000\" class=\"newInputJobs\" \">";
	$var .= "<img style='margin:0px;cursor:pointer; vertical-align:middle; ' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' />
	<iframe title='Date Picker' src='iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe>";
	}
	return $var;
}
function datePickerInvoice($field, $fieldVal,$left=0, $onChange="",$pages=2)
{
	if($left) {
	$var = "<img style='margin:0px;margin-right:2px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' /><iframe title='Date Picker' src='../iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe><input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "";
	} else {
	$var = "<input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "<img style='margin:0px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' />
	<iframe title='Date Picker' src='../iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe>";
	}
	return $var;
}

function datePickerDiary($field, $fieldVal,$left=0, $onChange="",$pages=2){
	//This function is called from appFiles/dayDiary.php - need to change the path to the files called within the function
	if($left) {
	$var = "<img style='margin:0px;margin-right:2px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' /><iframe title='Date Picker' src='../system/iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe><input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "";
	} else {
	$var = "<input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "<img style='margin:0px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' />
	<iframe title='Date Picker' src='../system/iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe>";
	}
	return $var;
}

function datePicker2($field, $fieldVal,$left=0, $onChange="",$pages=2)
{
	if($left) {
	$var = "<img style='margin:0px;margin-right:2px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' /><iframe title='Date Picker' src='../system/iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='true'></iframe><input name='".$field."' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "";
	} else {
	$var = "<input name='".$field."' style='background-color:#FFFFFF' type='text' id='".$field."' value='".$fieldVal."' size='8' onchange=\"dtstart=this.value.lastIndexOf('/'); if(dtstart>=3) if (this.value.substring(dtstart+1).length==2) this.value=this.value.substring(0,dtstart+1)+convertYear(this.value.substring(dtstart+1)); ".$onChange." \">";
	$var .= "<img style='margin:0px; padding:0px; cursor:pointer; vertical-align:middle' alt='cal' title='Date Picker' src='../images/calendar.gif' border='0' onClick='flipcal(\"cal".$field."\");' />
	<iframe title='Date Picker' src='../system/iframe_2up_cal.php?field=".$field."&fieldVal=".$fieldVal."&pages=".$pages."' class='picker' id='cal".$field."' FRAMEBORDER='0' allowtransparency='false'></iframe>";
	}
	return $var;
}

function mysqlDt($date) {
	$searchQuery = "";
		if($date) {
			$dateArray = explode("/",$date);
			$day = $dateArray[0];
			$month = $dateArray[1];
			$year = $dateArray[2];
		}
		$searchQuery = @date("Y-m-d",mktime(0,0,0,$month,$day,$year));
		if (($searchQuery=='1970-01-01') OR ($searchQuery=='1999-11-30')) $searchQuery = '0000-00-00';
	return $searchQuery;
}
function mysql2phpDate($date,$returnformat="d/m/Y") {
	if($date AND $date!='0000-00-00') {
		$dateArray = explode("-",$date);
		$day = substr($dateArray[2],0,2);
		$month = $dateArray[1];
		$year = $dateArray[0];
		return @date($returnformat,mktime(0,0,0,$month,$day,$year));
	} else {
		return '';
	}
	
}
function my2phpdt2($date,$returnformat="d/m/Y H:i:s") {
//	$date =  "2009-08-18 09:50:00"
	if($date AND $date!='0000-00-00') {
		$time = substr($date,11);
		$date = substr($date,0,10);
		$dateArray = explode("-",$date);
		$timeArray = explode(":",$time);
		$day = substr($dateArray[2],0,2);
		$month = $dateArray[1];
		$year = $dateArray[0];
		$hour = $timeArray[0];
		$mins = $timeArray[1];
		$secs = $timeArray[2];
		return @date($returnformat,mktime($hour,$mins,$secs,$month,$day,$year));
	} else {
		return '';
	}
	
}

function after ($this, $inthat)
{
   if (!is_bool(strpos($inthat, $this)))
   return substr($inthat, strpos($inthat,$this)+strlen($this));
};

function after_last ($this, $inthat)
{
   if (!is_bool(strrevpos($inthat, $this)))
   return substr($inthat, strrevpos($inthat, $this)+strlen($this));
};

function before ($this, $inthat)
{
   return substr($inthat, 0, strpos($inthat, $this));
};

function before_last ($this, $inthat)
{
   return substr($inthat, 0, strrevpos($inthat, $this));
};

function between ($this, $that, $inthat)
{
 return before($that, after($this, $inthat));
};

function between_last ($this, $that, $inthat)
{
 return after_last($this, before_last($that, $inthat));
};
// USES
function strrevpos($instr, $needle)
{
   $rev_pos = strpos (strrev($instr), strrev($needle));
   if ($rev_pos===false) return false;
   else return strlen($instr) - $rev_pos - strlen($needle);
};
function getDirFiles($dirPath, $searchString=0)
{
	$filesArr = array();
	if ($handle = opendir($dirPath)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if ($file != "." && $file != "..")
			{
				if ($searchString)
				{
					if(stristr($file, $searchString)) $filesArr[] = trim($file);
				} 
				else $filesArr[] = trim($file);
			}
		} // end while
		closedir($handle);
	}  // end handle
	return $filesArr;    
}
function resizeJPG($imgFile, $width, $type="jpg") {
   // Get new dimensions
   list($width_orig, $height_orig) = getimagesize($imgFile);
   $height = (int) (($width / $width_orig) * $height_orig);

   // Resample
   $image_p = imagecreatetruecolor($width, $height);
   
   switch($type) {
   	case "image/gif": 
		$image = imagecreatefromgif($imgFile); 
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
   		imagegif($image_p, $imgFile, 65);
		break;
	case "image/png": 
		$image = imagecreatefrompng($imgFile); 
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
   		imagepng($image_p, $imgFile, 7);
		break;	
   	default: 
		$image = imagecreatefromjpeg($imgFile); 
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
   		imagejpeg($image_p, $imgFile, 65);
		break;
   }
}
function financial($val)
{
	$val = round($val,2);
	$decimal_pos = strpos($val,".");
	if (!$decimal_pos){$val .= ".00";}elseif((strlen($val)-$decimal_pos)==2){$val .= "0";}
	return $val;
}

function send_mail($emailaddress, $fromaddress, $emailsubject, $body, $attachments=false)
{
  $eol="\r\n";
  $mime_boundary=md5(time());
  
  # Common Headers
  $headers .= 'From: Homeserve<'.$fromaddress.'>'.$eol;
  $headers .= 'Reply-To: Homeserve<'.$fromaddress.'>'.$eol;
  $headers .= 'Return-Path: Homeserve<'.$fromaddress.'>'.$eol;    // these two to set reply address
  $headers .= "Message-ID: <".$mime_boundary."@".$_SERVER['SERVER_NAME'].">".$eol;
  $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

  # Boundry for marking the split & Multitype Headers
  $headers .= 'MIME-Version: 1.0'.$eol;
  $headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol;

  $msg = "";      
  
  if ($attachments !== false)
  {

    for($i=0; $i < count($attachments); $i++)
    {
      if (is_file($attachments[$i]["file"]))
      {   
        # File for Attachment
		if ($attachments[$i]["name"])
			$file_name = $attachments[$i]["name"];
		else
	        $file_name = substr($attachments[$i]["file"], (strrpos($attachments[$i]["file"], "/")+1));
        
        $handle=fopen($attachments[$i]["file"], 'rb');
        $f_contents=fread($handle, filesize($attachments[$i]["file"]));
        $f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
        fclose($handle);
        
        # Attachment
        $msg .= "--".$mime_boundary.$eol;
        $msg .= "Content-Type: ".$attachments[$i]["content_type"]."; name=\"".$file_name."\"".$eol;
        $msg .= "Content-Transfer-Encoding: base64".$eol;
        $msg .= "Content-Disposition: attachment; filename=\"".$file_name."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
        $msg .= $f_contents.$eol.$eol;
        
      }
    }
  }
  
  # Setup for text OR html
  $msg .= "Content-Type: multipart/alternative".$eol;
  
  # Text Version
/*  $msg .= "--".$mime_boundary.$eol;
  $msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol;
  $msg .= "Content-Transfer-Encoding: 8bit".$eol;
  $msg .= strip_tags(str_replace("<br>", "\n", $body)).$eol.$eol;
 */ 
  # HTML Version
  $msg .= "--".$mime_boundary.$eol;
  $msg .= "Content-Type: text/html; charset=iso-8859-1".$eol;
  $msg .= "Content-Transfer-Encoding: 8bit".$eol;
  $msg .= $body.$eol.$eol;
  
  # Finished
  $msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
    
  # SEND THE EMAIL
  ini_set(sendmail_from,$fromaddress);  // the INI lines are to force the From Address to be used !
  mail($emailaddress, $emailsubject, $msg, $headers);
  ini_restore(sendmail_from);
} // end send with attachments



// ---- 2014-10-01 James Wilkes added the following functions ---- //

function paginationAjax($divContainer, $divSource, $currentPage=0, $num_rows=0,$maxRecords=50, $maxDisplayPages = 20, $jqVars="", $extraScript="", $hidePageSelect = 0)
{
	$val = "<div class='paginationContainer'>";
	if (!$num_rows)
	{
		$val .= "No records were found";
	}
	else
	{
		// calculate number of pages
		$pages = $num_rows/$maxRecords;
		//	decide which pages to show
		if ($pages <= $maxDisplayPages || $currentPage <= ($maxDisplayPages/2))
		{
			$startDisplayPage = 0;
			$endDisplayPage = $maxDisplayPages;
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		} 
		else
		{
			$startDisplayPage = $currentPage - ($maxDisplayPages/2);
			$endDisplayPage = $currentPage + ($maxDisplayPages/2);
			if ($endDisplayPage > $pages) $endDisplayPage = $pages;
		}
		$startRecord = $currentPage*$maxRecords+1;
		$finishRecord = $currentPage*$maxRecords+$maxRecords;
		if ($finishRecord > $num_rows) $finishRecord = $num_rows;
		$records_to_display = $finishRecord - $startRecord+1;
		// resets page value to the last if deletion occurs reduceing number of pages displayed
		if ($currentPage > $pages) 
			$currentPage = $pages;

		$val .= "<div id='results'>Results: ".$startRecord."-".$finishRecord." of ".$num_rows."</div>";
		if ($pages > $maxDisplayPages && !$hidePageSelect){
			$val .= "<div id='pageSelect'>Go to to Page: <select name='pageSelector' id='pageSelector' >";
			$val .= "<option></option>";
			for($i=0;$i<$pages;$i++)
			{        
				$val .= "<option value=".$i.">".($i+1)."</option>";
			}
			$val .= "</select></div>";
		}
		if ($pages>=1)
		{
			$val .= "<div id='pages'>";
			
			if ($currentPage > 0) 
			{
				$val .= "<div title='".($currentPage-1)."' class='pagination' style='float:left;'><a href='#' class='pageButton'>&nbsp;&laquo; Prev&nbsp;</a></div>";
			}else
			{
				$val .= "<div style='float:left' class='' ><a href='#' class='pageButtonPassive'>&nbsp;&laquo; Prev&nbsp;</a></div>";
			} for ($i=$startDisplayPage; $i < $endDisplayPage; $i++) 
			{ 
				if ($currentPage != $i) 
				{ 
					$val .= "<div title='$i' class='pagination' style='float:left'><a href='#' class='pageButton'>&nbsp;".($i+1)."&nbsp;</a></div>";
				}else
				{
					$val .= "<div style='float:left;'><a href='#' class='pageButtonActive' style='cursor:default'>&nbsp;".($i+1)."&nbsp;</a></div>";
				} 
			} 
			if ($currentPage+1 < $pages) 
			{ 
				$val .= "<div title='".($currentPage+1)."' class='pagination' style='float:left'><a href='#' class='pageButton'>&nbsp;Next &raquo;&nbsp;</a></div>";
			} else {
				$val .= "<div style = 'float:left'>&nbsp;Next &raquo;&nbsp;</div>";
			}
			$val .= "</div>";
		}
		$val .= "<div style='clear:both'></div></div>
		
		<script>
			$extraScript
			$('.pagination').click( function() {
				$('$divContainer').html('<p class=\"loader\"><img src=\"assets/img/ajax-loader-large.gif\" width=\"300\" height=\"300\" ></p>');
				$('$divContainer').load('".$divSource."?currentPage='+$(this).attr('title'), { $jqVars });
			});		
			$('#pageSelector').change( function () {
				$('$divContainer').html('<p class=\"loader\"><img src=\"assets/img/ajax-loader-large.gif\" width=\"300\" height=\"300\" ></p>');
				$('$divContainer').load('".$divSource."?currentPage='+$(this).val(), { $jqVars });
				
			});
		</script>
		
		";
	}
	return $val;
}


function strtotimeuk($date) {
	$dateArray = explode("/",$date);
	return strtotime($dateArray[2]."-".$dateArray[1]."-".$dateArray[0]);
}


?>