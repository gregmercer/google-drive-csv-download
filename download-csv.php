<?php
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

$download_doc_title = 'the title of document goes here';

$client = new Google_Client();
// Get your credentials from the console
$client->setClientId('your client goes here');
$client->setClientSecret('your client secret goes here');
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
$client->setScopes(array('https://www.googleapis.com/auth/drive'));

$service = new Google_DriveService($client);

$authUrl = $client->createAuthUrl();

//Request authorization
print "Please visit:\n$authUrl\n\n";
print "Please enter the auth code:\n";
$authCode = trim(fgets(STDIN));

// Exchange authorization code for access token
$accessToken = $client->authenticate($authCode);
$client->setAccessToken($accessToken);

$files = $service->files;
$list_files = $files->listFiles();

foreach ($list_files['items'] as $key => $value) {
	$title = $value['title'];
	if ($title == $download_doc_title) {
		print "title = $title\n";
		$id = $value['id'];
		$data = $service->files->get($value['id']);
		$file = new Google_DriveFile($data);
		$export_links = $file->getExportLinks();
		$csv_link = $export_links['application/pdf'];
		$csv_link = str_replace('=pdf', '=csv', $csv_link);
		print "csv link = $csv_link\n";
		print_r(downloadFile($csv_link));
		break;
	}
}

function downloadFile($downloadUrl) {
  if ($downloadUrl) {
    $request = new Google_HttpRequest($downloadUrl, 'GET', null, null);
    $httpRequest = Google_Client::$io->authenticatedRequest($request);
    if ($httpRequest->getResponseHttpCode() == 200) {
      return $httpRequest->getResponseBody();
    }
  }
}

?>
