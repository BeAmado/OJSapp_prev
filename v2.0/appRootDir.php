<?php

function appRootDir($appName = "OJSapp", $cwd = getcwd()) {
	//$cwd = getcwd();
	//echo "\ncwd vale $cwd\n";

	$subpaths = explode("/", $cwd);

	$base = $subpaths[count($subpaths) -1];

	if ($key = array_search($appName, $subpaths)) {
		if ($base === $appName) {
			 return $cwd;
		}
		while ($subpaths[count($subpaths) - 1] !== $appName) {
			array_pop($subpaths);
		}
		return implode("/", $subpaths);
	}

	$ls = scandir($cwd);

	if ($key = array_search($appName, $ls)) {
		return $cwd . "/" . $ls[$key];
	}

	//search inside each directory in $ls
	foreach ($ls as $dir) {
		$innerLS = scandir("$cwd/$dir");
		if ($key = array_search($appName, $innerLS)) {
			return "$cwd/$dir/" . $innerLS[$key];
		}
	}


	////// ask the user to provide the path to the app  ///////////////
	echo "\nCould not find the root directory for $appName.\n";
	$base_dir = readline("Please provide the full path to $appName: ");

	
}

appRootDir();
