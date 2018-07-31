<?php

// creates secret.php and config.php with user supplied values and performs initial database import
// see README for usage

print "\n";











// cli options setup
$longopts  = array(
// all values optional
    "user::",
    "pass::",
    "db::",
    "host::",
    "baseurl::",        
    "mysql_path::",
);
// get cli options
$options = getopt("",$longopts);

// use localhost if host is not specified
if (!isset($options["host"])) {
    $options["host"] = "localhost";
}


$mysqlLocation = $options['mysql_path']??"";





// read existing secrets.php or create new one
// check if secrets.php has already been created
if (file_exists("../include/secrets.php")) {
    // secrets.php exists
    print "Using existing secrets.php \n";
} else {
    // secrets.php does not exist

    // options needed for secrets.php
    $seclist = array("user","pass","db","host");

    // check if the needed options exist
    foreach ($seclist as $value) {
        if (!isset($options[$value])) {
        // needed parameter missing from cli options
        // alert user and exit
            print "Option missing: $value \n";
            print "Please specify with: --$value=something \n";
            exit;
        }
    }

    // create new secrets.php from template using
    print "Creating new secrets.php from template \n";
	$secretsTemplate = file_get_contents("../include/secrets.php.sample");

    // add parameters to secrets.php    
    print "Adding parameters to secrets.php\n";
    foreach ($seclist as $value) {
		$secretsTemplate = str_replace("%%".$value."%%",$options[$value],$secretsTemplate); 
    }  
	file_put_contents("../include/secrets.php",$secretsTemplate);
}


// load secrets.php
require_once "../include/secrets.php";
print "Successfully loaded secrets.php\n";

$pass = preg_replace('/./i','*',$mysql_pass);
print("User: $mysql_user \n");
print("Pass: $pass \n");
print("DB: $mysql_db \n");
print("Host: $mysql_host \n");



// load sql
print "Loading SQL statemnts\n";
foreach(['biotorrents','guest_user','avps','countries','searchcloud','categories','reputationlevel','stylesheets','licenses'] as $f){ 
// set up loop for sql import    
$cmd = $mysqlLocation."mysql -u $mysql_user -p$mysql_pass -h $mysql_host $mysql_db < "
    .'../SQL/'.$f.'.sql';
    // . '; done';
exec($cmd);
}









// check if config.php has been created
if (file_exists("../include/config.php")) {
    // config.php exists
    print "Existing config.php found \n";
} else {
    // config.php does not exist
    
    
    // options needed for config.php
    $conflist = array("baseurl");
    
    // check if the needed options exist
    foreach ($conflist as $value) {
        if (!isset($options[$value])) {
        // needed parameter missing from cli options
        // alert user and exit
            print "Option missing: $value \n";
            print "Please specify with: --$value=something \n";
            exit;
        }
    }
    
    // copy from template
    print "Creating config.php from template \n";
	$configTemplate = file_get_contents("../include/config.php.sample");

// add baseurl to config.php
    print "Adding ".$options["baseurl"]." to config.php\n"
        .'$DEFAULTBASEURL = '
        .$options["baseurl"]
        ."\n";
	$configTemplate = str_replace("%%BASEURL%%",$options["baseurl"],$configTemplate);
	file_put_contents("../include/config.php",$configTemplate);
}    

print "\nInstall complete.\n\n";

?>
