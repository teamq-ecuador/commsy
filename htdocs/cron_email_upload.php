<?php

// http://www.linuxscope.net/articles/mailAttachmentsPHP.html

function get_mime_type(&$structure) {
   $primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
   if($structure->subtype) {
   	return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
   }
   return "TEXT/PLAIN";
}

function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) {
   $prefix = null;
   if(!$structure) {
   	$structure = imap_fetchstructure($stream, $msg_number);
   }
   if($structure) {
   	if($mime_type == get_mime_type($structure)) {
   		if(!$part_number) {
   			$part_number = "1";
   		}
   		$text = imap_fetchbody($stream, $msg_number, $part_number);
   		if($structure->encoding == 3) {
   			return imap_base64($text);
   		} else if($structure->encoding == 4) {
   			return imap_qprint($text);
   		} else {
   		return $text;
   	   }
	  	}
	   
	  	// multipart message
		if($structure->type == 1) {
	  		while(list($index, $sub_structure) = each($structure->parts)) {
	  			if($part_number) {
	  				$prefix = $part_number . '.';
	  			}
	  			$data = get_part($stream, $msg_number, $mime_type, $sub_structure,$prefix .    ($index + 1));
	  			if($data) {
	  				return $data;
	  			}
	  		}
	  	}
  	}
  	return false;
}

function getFile($strFileType,$strFileName,$fileContent) {
  	$ContentType = "application/octet-stream";
   
  	if ($strFileType == ".asf") 
  		$ContentType = "video/x-ms-asf";
  	if ($strFileType == ".avi")
  		$ContentType = "video/avi";
  	if ($strFileType == ".doc")
  		$ContentType = "application/msword";
  	if ($strFileType == ".zip")
  		$ContentType = "application/zip";
  	if ($strFileType == ".xls")
  		$ContentType = "application/vnd.ms-excel";
  	if ($strFileType == ".gif")
  		$ContentType = "image/gif";
  	if ($strFileType == ".jpg" || $strFileType == "jpeg")
  		$ContentType = "image/jpeg";
  	if ($strFileType == ".wav")
  		$ContentType = "audio/wav";
  	if ($strFileType == ".mp3")
  		$ContentType = "audio/mpeg3";
  	if ($strFileType == ".mpg" || $strFileType == "mpeg")
  		$ContentType = "video/mpeg";
  	if ($strFileType == ".rtf")
  		$ContentType = "application/rtf";
  	if ($strFileType == ".htm" || $strFileType == "html")
  		$ContentType = "text/html";
  	if ($strFileType == ".xml") 
  		$ContentType = "text/xml";
  	if ($strFileType == ".xsl") 
  		$ContentType = "text/xsl";
  	if ($strFileType == ".css") 
  		$ContentType = "text/css";
  	if ($strFileType == ".php") 
  		$ContentType = "text/php";
  	if ($strFileType == ".asp") 
  		$ContentType = "text/asp";
  	if ($strFileType == ".pdf")
  		$ContentType = "application/pdf";
   
	if (substr($ContentType,0,4) == "text") {
	   return imap_qprint($fileContent);
	} else {
		return imap_base64($fileContent);
	}
}

function email_to_commsy($mbox,$msgno){
	global $environment;
	global $portal_id_array;
	global $c_email_upload_email_account;
	
   $struct = imap_fetchstructure($mbox,$msgno);

   $header = imap_headerinfo($mbox,$msgno);
   $sender = $header->from[0]->mailbox.'@'.$header->from[0]->host;
   $subject = $header->subject;
   $body = imap_fetchbody($mbox,$msgno,1);
	
   // get additional Information from e-mail body
   $account = '';
   $secret = '';
   
   $body = preg_replace('/\r\n|\r/', "\n", $body);
   $body_array = explode("\n", $body);
   foreach($body_array as $body_line){
   	if(!empty($body_line)){
	   	if(stristr($body_line, 'Kennung:')){ // add translation
	   		$temp_body_line = str_ireplace('Kennung:', '', $body_line);
	   		$temp_body_line_array = explode(' ', trim($temp_body_line));
	   		$account = $temp_body_line_array[0];
	   	} else if(stristr($body_line, 'Passwort:')){ // add translation
	   		$temp_body_line = str_ireplace('Passwort:', '', $body_line);
	   		$temp_body_line_array = explode(' ', trim($temp_body_line));
	   		$secret = $temp_body_line_array[0];
	   	}
   	}
   }
   
	foreach($portal_id_array as $portal_id){
		$environment->setCurrentPortalID($portal_id);
		$user_manager = $environment->getUserManager();
		$user_manager->setContextArrayLimit($portal_id);
		$user_manager->setEMailLimit($sender);
		$user_manager->select();
		$user_list = $user_manager->get();
		$user = $user_list->getfirst();
		$found_users = array();
		while($user){
			if($account != ''){
				if($account == $user->getUserID()){
			   	$found_users[] = $user;
				}
			} else {
				$found_users[] = $user;
			}
			$user = $user_list->getnext();
		}
		
		foreach($found_users as $found_user){
			$private_room_user = $found_user->getRelatedPrivateRoomUserItem();
			$private_room = $private_room_user->getOwnRoom();

			if($private_room->getEmailToCommSy()){
			   $email_to_commsy_secret = $private_room->getEmailToCommSySecret();
			   
			   $result_mail = new cs_mail();
            $result_mail->set_to($sender);
            $result_mail->set_from_name('CommSy');
				$result_mail->set_from_email('commsy@commsy.net');
			   
			   if($secret == $email_to_commsy_secret){
			   	$private_room_id = $private_room->getItemID();
			   	
			   	$files = array();
			   	
				   if($struct->subtype == 'PLAIN'){
				   } else if ($struct->subtype == 'MIXED') {
				      // with attachment 
					   $contentParts = count($struct->parts);
					   if ($contentParts >= 2) {
						   for ($i=2;$i<=$contentParts;$i++) {
					   	   $att[$i-2] = imap_bodystruct($mbox,$msgno,$i);
					   	}
					   	for ($k=0;$k<sizeof($att);$k++) {
					   		$strFileName = $att[$k]->dparameters[0]->value;
					   		$strFileType = strrev(substr(strrev($strFileName),0,4));
					   		$fileContent = imap_fetchbody($mbox,$msgno,$k+2);
					   		$file = getFile($strFileType, $strFileName, $fileContent);
					   		
					   		// copy file to temp
					   		$temp_file = 'var/temp/'.$strFileName.'_'.getCurrentDateTimeInMySQL();
					   		file_put_contents($temp_file, $file);
					   		
					   		$temp_array = array();
			         		$temp_array['name'] = utf8_encode($strFileName);
			         		$temp_array['tmp_name'] = $temp_file;
			         		$temp_array['file_id'] = $temp_array['name'].'_'.getCurrentDateTimeInMySQL();
			         		$files[] = $temp_array;
					   	}
					   }
				   }
				   
				   $environment->setCurrentContextID($private_room_id);
				   $environment->setCurrentUser($private_room_user);
				   $environment->unsetLinkModifierItemManager();
				   $material_manager = $environment->getMaterialManager();
				   $material_item = $material_manager->getNewItem();
				   $material_item->setTitle(trim(str_replace($email_to_commsy_secret.':', '', $subject)));

				   $material_item->setDescription($body);
				   
			      // attach files to the material
				   $file_manager = $environment->getFileManager();
			      $file_manager->setContextLimit($private_room_id);
			      
			      $file_id_array = array();
			      foreach($files as $file){
					   $file_item = $file_manager->getNewItem();
				      $file_item->setTempKey($file["file_id"]);
			         $file_item->setPostFile($file);
				      $file_item->save();
				      $file_id_array[] = $file_item->getFileID();
			      }
				   $material_item->setFileIDArray($file_id_array);
			
				   $material_item->save();

				   // send e-mail with 'material created in your private room' back to sender
				   $result_mail->set_subject('Upload2CommSy - erfolgreich');
               $result_mail->set_message($body);
			   } else {
			   	// send e-mail with 'password or subject not correct' back to sender
			   	$result_mail->set_subject('Upload2CommSy - fehlgeschlagen');
               $result_mail->set_message($body);
			   }
			   
			   $result_mail->send();
			}
		}
	}
   
	// mark e-mail for deletion
	#imap_delete($mbox,$msgno);
}

chdir('..');

include_once('etc/commsy/development.php');
include_once('classes/cs_mail.php');

// setup commsy-environment
include_once('etc/cs_constants.php');
include_once('etc/cs_config.php');
include_once('classes/cs_environment.php');
$environment = new cs_environment();
$environment->setCacheOff();

$server_item = $environment->getServerItem();
$portal_id_array = $server_item->getPortalIDArray();

// open connection
$mbox = imap_open('{'.$c_email_upload_server.':'.$c_email_upload_server_port.'}', $c_email_upload_email_account, $c_email_upload_email_password);

// get messages
$message_count = imap_num_msg($mbox);
for ($msgno = 1; $msgno <= $message_count; ++$msgno) {
   email_to_commsy($mbox,$msgno);
}

// remove deleted e-mails
imap_expunge($mbox);

// close connection
imap_close($mbox);

?>