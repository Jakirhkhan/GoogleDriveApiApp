<?php
/**
 * Created by Jakir Hosen Khan.
 * User: Jakir Hosen Khan
 * Date: 4/28/16
 * Time: 6:42 PM
 */

require __DIR__ . '/vendor/autoload.php';
require 'config.php';


class GoogleDriveApi {


    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient() {
        $client = new Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        //$client->setScopes(SCOPES);
        $client->setScopes(array('https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/userinfo.profile'));
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
        $client->setAccessType('offline');
        //print_r(SCOPES);die;
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
        if (file_exists($credentialsPath)) {
            $accessToken = file_get_contents($credentialsPath);
//        print_r($accessToken);die;
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->authenticate($authCode);

            // Store the credentials to disk.
            if(!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, $accessToken);
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, $client->getAccessToken());
        }
        return $client;
    }



    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

// File upload

    function driveUpload($client){
        $service = new Google_Service_Drive($client);
        //print_r($client->getAccessToken());
        //die;
// create and upload a new Google Drive file, including the data
        try
        {
            $file = new Google_Service_Drive_DriveFile();
            //$parent = 'apiupload/';
            $folderId = '0B3xj84v0iahvUkxtajlSVTBSaWc';
            $file->setParents(array($folderId));

            //die;
            $file->setName(uniqid().'.jpg');
            $file->setDescription('A test document');
            $file->setMimeType('image/jpeg');

            $data = file_get_contents('Chrysanthemum.jpg');

            $createdFile = $service->files->create($file, array(
                'data' => $data,
                'mimeType' => 'image/jpeg',
                'uploadType' => 'multipart'
            ));
            echo '<pre>';
            print_r($createdFile);
        }
        catch (Exception $e)
        {
            print $e->getMessage();
        }
    }

    function FileList($service=null){
        // Print the names and IDs for up to 10 files.
        $optParams = array(
            'pageSize' => 10,
            'fields' => "nextPageToken, files(id, name)"
        );
        $results = $service->files->listFiles($optParams);

        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            print "Files:\n";
            foreach ($results->getFiles() as $file) {
                echo '<br/>';
                printf("%s (%s)\n", $file->getName(), $file->getId());
            }
        }
    }
} 