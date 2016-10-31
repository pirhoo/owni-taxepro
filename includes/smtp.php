<?php

function smtpmail($mail_to, $subject, $message, $headers = ''){
	global $config;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers != '')
	{
		if (is_array($headers))
		{
			if (sizeof($headers) > 1)
			{
				$headers = join("\n", $headers);
			}
			else
			{
				$headers = $headers[0];
			}
		}
		$headers = chop($headers);

		// Make sure there are no bare linefeeds in the headers
		$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$header_array = explode("\r\n", $headers);
		@reset($header_array);

		$headers = '';
		while(list(, $header) = each($header_array))
		{
			if (preg_match('#^cc:#si', $header))
			{
				$cc = preg_replace('#^cc:(.*)#si', '\1', $header);
			}
			else if (preg_match('#^bcc:#si', $header))
			{
				$bcc = preg_replace('#^bcc:(.*)#si', '\1', $header);
				$header = '';
			}
			$headers .= ($header != '') ? $header . "\r\n" : '';
		}

		$headers = chop($headers);
		$cc = explode(', ', $cc);
		$bcc = explode(', ', $bcc);
	}

	if (trim($subject) == '')
	{
		return false;
	}

	if (trim($message) == '')
	{
		return false;
	}

	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if( !$socket = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 20) )
	{
		return false;
	}

	// Wait for reply
	server_parse($socket, "220", __LINE__);

	// Do we want to use AUTH?, send RFC2554 EHLO, else send RFC821 HELO
	// This improved as provided by SirSir to accomodate
	if( !empty($config['smtp_username']) && !empty($config['smtp_password']) )
	{ 
		fputs($socket, "EHLO " . $config['smtp_host'] . "\r\n");
		if(!server_parse($socket, "250", __LINE__)) {
			$error = "Erreur lors de la connection au serveur"."\n";
		}

		fputs($socket, "AUTH LOGIN\r\n");
		if(!server_parse($socket, "334", __LINE__)) {
			$error = "Erreur lors de l'authentification !"."\n";
		}

		fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
		if(!server_parse($socket, "334", __LINE__)) {
			$error = "Erreur lors de l'authentification !"."\n";
		}

		fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
		//server_parse($socket, "235", __LINE__);
		if(!server_parse($socket, "235", __LINE__)) {
			$error = "Erreur lors de l'authentification !"."\n";
		}
	}
	else
	{
		fputs($socket, "HELO " . $config['smtp_host'] . "\r\n");
		if(!server_parse($socket, "250", __LINE__)) {
			$error = "Une erreur s'est produite lors de la connection au serveur, le mail n'a pas été envoyé !"."\n";
		}
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	fputs($socket, "MAIL FROM: <" . $config['admin_email'] . ">\r\n");
	if(!server_parse($socket, "250", __LINE__)) {
		$error = "Une erreur s'est produite lors des présentations MAIL FROM, le mail n'a pas été envoyé !"."\n";
	}
	
	/*
	// Specify each user to send to and build to header.
	$to_header = '';
	// Add an additional bit of error checking to the To field.
	if (preg_match('#[^ ]+\@[^ ]+#', $mail_to))
	{
		fputs($socket, "RCPT TO: <$mail_to>\r\n");
		if(!server_parse($socket, "250", __LINE__)) {
			$error = "Erreur, adresse d'envoi incorrecte, le mail n'a pas été envoyé !";
		}
	}
	*/
	
	// Ok now do the CC and BCC fields...
	$mail_to = explode(";",$mail_to);
	//@reset($mail_to);
	while(list(,$to_address) = each($mail_to))
	{
		// Add an additional bit of error checking to bcc header...
		$to_address = (trim($to_address) == '') ? 'Undisclosed-recipients:;' : trim($to_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $to_address))
		{
			fputs($socket, "RCPT TO: <$to_address>\r\n");
			if(!server_parse($socket, "250", __LINE__)) {
				$error = "Une erreur s'est produite : $to_address adresse d'envoi incorrecte, le mail n'a pas été envoyé !"."\n";
			}
		}
		else {
			$error = "Une erreur s'est produite : $to_address adresse d'envoi incorrecte, le mail n'a pas été envoyé !"."\n";
		}
	}

	// Ok now do the CC and BCC fields...
	@reset($bcc);
	while(list(, $bcc_address) = @each($bcc))
	{
		// Add an additional bit of error checking to bcc header...
		$bcc_address = trim($bcc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $bcc_address))
		{
			fputs($socket, "RCPT TO: <$bcc_address>\r\n");
			if(!server_parse($socket, "250", __LINE__)) {
				$error = "Une erreur s'est produite lors de l'envoi des BCC, le mail n'a pas été envoyé !"."\n";
			}
		}
	}

	@reset($cc);
	while(list(, $cc_address) = @each($cc))
	{
		// Add an additional bit of error checking to cc header
		$cc_address = trim($cc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $cc_address))
		{
			fputs($socket, "RCPT TO: <$cc_address>\r\n");
			if(!server_parse($socket, "250", __LINE__)) {
				$error = "Une erreur s'est produite lors de l'envoi des CC, le mail n'a pas été envoyé !"."\n";
			}
		}
	}

	// Ok now we tell the server we are ready to start sending data
	fputs($socket, "DATA\r\n");

	// This is the last response code we look for until the end of the message.
	server_parse($socket, "354", __LINE__);

	// Send the Subject Line...
	fputs($socket, "Subject: $subject\r\n");

	// Now the To Header.
	fputs($socket, "To: ".$mail_to[0]."\r\n");

	// Now any custom headers....
	fputs($socket, "$headers\r\n\r\n");

	// Ok now we are ready for the message...
	fputs($socket, "$message\r\n");

	// Ok the all the ingredients are mixed in let's cook this puppy...
	fputs($socket, ".\r\n");
	if(!server_parse($socket, "250", __LINE__)) {
		$error = "Erreur lors de l'envoi de la purée, mail non envoyé é ".$mail_to[0]."\n";
	}

	// Now tell the server we are done and close the socket...
	fputs($socket, "QUIT\r\n");
	fclose($socket);
	
	if (empty($error)) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

function server_parse($socket, $response, $line = __LINE__) {
	$server_response = '';
	while (substr($server_response, 3, 1) != ' ') 
	{
		if (!($server_response = fgets($socket, 1024))) 
		{ 
			return false;
		} 
	} 

	if (!(substr($server_response, 0, 3) == $response)) 
	{ 
		return false;
	} 
	else {
		return true;
	}
}


?>