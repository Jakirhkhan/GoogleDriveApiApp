<?php
require 'GoogleDriveApi.php';
$gdapi = new GoogleDriveApi();
$client = $gdapi->getClient();
$gdapi->driveUpload($client);

die;

