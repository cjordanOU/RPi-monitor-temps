<?php
	# ------ Created by Cameron Jordan 2021
	# ------ Monitors temperature of CPU on Pi

	#TODO: Add flags for changing file size before overwrite, how often to interval temp, or advanced mode

	# ------ Variable Declarations
	$i=0;
	$promptStop = "Press Ctrl + C to stop the script" . PHP_EOL;
	$promptToFile = "Would you like to log the results to a file? [Y/n]: ";
	$storeAnswer = "";
	$shouldStore = FALSE;
	$storePrompt = FALSE;
	$promptCount = 0;
	$currentUser = exec("whoami");
	$currentTimestamp = time();
	$currentFileSize = 0;
	$maxFileSize = 32768;
	$sleepInterval = 2;
	$moreOptions = FALSE;
	$enableOverwrite = FALSE;
	$disableColoredText = FALSE;
	$shouldPrompt = TRUE;
	
	# ------ Argument Logic
	if (count($argv) > 1) {
		$argSize = count($argv);
		$argPos = 0;
		while ($argPos < $argSize) {
			$currentArg = substr($argv[$argPos],0,1);

			if ($currentArg == "F") {
				$maxFileSize = preg_replace('/[^0-9]/', '', $argv[$argPos]);
				if (empty($maxFileSize) == TRUE) {
					echo "WARNING: MAX FILE SIZE NOT PROPERLY SET! DEFAULTING TO 32768 BYTES!". PHP_EOL;
					$maxFileSize = 32768;
				}
				elseif ((in_array("V",$argv)) == TRUE) {
					echo "MAX FILE SIZE OF MONITORING FILE IS: $maxFileSize BYTES". PHP_EOL;
				}
			}
			if ($currentArg == "I") {
				$sleepInterval = preg_replace('/[^0-9]/', '', $argv[$argPos]);
				if (empty($sleepInterval) == TRUE && $sleepInterval != 0) {
					echo "WARNING: MONITORING INTERVAL NOT PROPERLY SET! DEFAULTING TO 2 SECONDS!". PHP_EOL;
					$sleepInterval = 2;
				}
				elseif ((in_array("V",$argv)) == TRUE) {
					echo "MONITORING INTERVAL SET FOR: $sleepInterval SECONDS" . PHP_EOL;
				}
			}
			if ($currentArg == "O") {
				$enableOverwrite = TRUE;
				if ((in_array("V",$argv)) == TRUE) {
					echo "OVERWRITE FILE ENABLED" . PHP_EOL;
				}
			}
			if ($currentArg == "C") {
				$disableColoredText = TRUE;
				if ((in_array("V",$argv)) == TRUE) {
					echo "COLORED TEXT DISABLED" . PHP_EOL;
				}
			}
			if ($currentArg == "A") {
				$shouldPrompt = FALSE;
				if ((in_array("V",$argv)) == TRUE) {
					echo "CTRL + C PROMPTS DISABLED" . PHP_EOL;
				}
			}
			if ($currentArg == "N") {
				$storePrompt = TRUE;
			}
			if ($currentArg == "Y") {
				$storePrompt = TRUE;
				$shouldStore = TRUE;
				if (!file_exists("/home/$currentUser/Documents/Monitor-temps")) {
					mkdir("/home/$currentUser/Documents/Monitor-temps");
				}
				$filepath = "/home/$currentUser/Documents/Monitor-temps/monitorInfo.txt";
			}

			$argPos++;
		}
	}

	
	while ($storePrompt == FALSE) {
		echo $promptToFile;
		$storeAnswer = trim(fgets(STDIN));
		$storeAnswer = substr($storeAnswer,0,1);
		if (strpbrk($storeAnswer,'YyNn')) {
			if (strpbrk($storeAnswer, 'Yy')) {
				if (!file_exists("/home/$currentUser/Documents/Monitor-temps")) {
					mkdir("/home/$currentUser/Documents/Monitor-temps");
				}
				$filepath = "/home/$currentUser/Documents/Monitor-temps/monitorInfo.txt";
				echo "Temperature Monitoring will be stored to $filepath" . PHP_EOL;
				$shouldStore = TRUE;
				$storePrompt = TRUE;
			}
			else {
				#FILE NOT STORED SO MOVE ON
				$storePrompt = TRUE;
			}
		}
		else {
			echo "ERROR: Please Enter Either 'Y' or 'n' ";
		}
	}

	while ($i <= 1) {
		# Uses vcgencmd in shell to get cpu tumperature
		$temp = exec("vcgencmd measure_temp");
		
		# ------ Calculates temp in farenheit
		$temptrim = substr($temp,0,-2);
		$temptrim = substr($temptrim,-3);
		$temptrim = (float)$temptrim;
		$ftemp = ($temptrim * 1.8) + 32;

		# ------ Prompts the user to press Ctrl + C every 20 lines
		if ($shouldPrompt == TRUE) {
			if ($promptCount == 0) {
				echo " --- $promptStop";
				$promptCount++;
			}
			else {
				$promptCount++;
				if ($promptCount >= 19) {
					$promptCount = 0;
				}
			}
		}
		

		# ------ Outputs temperature and time info
		$outputInfo = $temp . " (". number_format($ftemp,1) . "'F) ". date("h:i:sa") . PHP_EOL;
		
		if ($temptrim < 50 && $disableColoredText == FALSE){
			echo "\e[1;32m$outputInfo\e[0m";
		}
		if ($temptrim >= 50 && $temptrim < 70 && $disableColoredText == FALSE){
			echo "\e[1;33m$outputInfo\e[0m";
		}
		if ($temptrim >= 70 && $disableColoredText == FALSE) {
			echo "\e[0;31m$outputInfo\e[0m";
		}
		if ($disableColoredText == true) {
			echo $outputInfo;
		}
		
		if ($shouldStore == TRUE && $currentFileSize < $maxFileSize && $enableOverwrite == FALSE) {
			file_put_contents($filepath,$outputInfo,FILE_APPEND);
			
		}
		if ($shouldStore == TRUE && $currentFileSize < $maxFileSize && $enableOverwrite == TRUE) {
			file_put_contents($filepath,$outputInfo);
			$currentFileSize++;
		}
		if ($shouldStore == TRUE && $currentFileSize >= $maxFileSize) {
			$currentTimestamp = time();
			$filepath = "/home/$currentUser/Documents/Monitor-temps/monitorInfo-$currentTimestamp.txt";
			file_put_contents($filepath,$outputInfo,FILE_APPEND);
			#$currentFileSize = filesize("/home/$currentUser/Documents/Monitor-temps/monitorInfo-$currentTimestamp.txt");
		}
		if ($sleepInterval != 0) {
			sleep($sleepInterval);
		}
	}
?>
