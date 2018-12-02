# phpbox
Implementation of several aspects of the Box v2.0 API

## Installation
Install via `composer require robindeschepper/phpbox`.

## Getting Started

Currently it only connects via a Box [JWT](https://developer.box.com/docs/construct-jwt-claim-manually) JSON config file. Accessing your box app is done like this:
```php
require_once("vendor/autoload.php");
use PhpBox\Box;
use PhpBox\Config\JsonConfig;

$config = new JsonConfig("path/to/box_config.json");
$box = new Box($config);
```

This will use your json config file to request an access token via JWT. The token is stored in the PhpBox object and automatically refreshed if it expires.

## Filesystem

Currently I have only implemented `requestFolder` to retrieve a Folder object. Multiple of these calls can be used to explore the Box tree.

```php
$root = $box->requestFolder(); // Get root folder
// This subfolders and files are received in the item_collection of the first root folder:
$myPictures = $root->getItemByName("Pictures"); 
// To explicitly fetch the content of a subfolder another request is required
$myPictures = $box->requestFolder($myPictures);
```

## Exchange Tokens

Full access tokens can be exchanged for tokens with limited permissions. See the Box Documentation on [Scopes](https://developer.box.com/docs/scopes) for more information on the scope parameter. If the token parameter is omitted the access token is used. Tokens can only be exchanged for more restrictive ones. If the folder parameter is omitted the root folder of the old token is used.

```php
$oldToken = $box->getValidAccessToken();
$folder = "0"; // Root folder
$scope = ["root_readonly"];
$readonly_token = $box->requestExchangeToken($scope, $folder, $oldToken);
```
