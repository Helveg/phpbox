# phpbox
Implementation of several aspects of the Box v2.0 API. STILL VERY HEAVILY UNDER DEVELOPMENT (package is 5 days old, any feedback on GitHub would be appreciated)

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

## Model

I have created classes for the following objects in the Box API: Collaboration, File, Folder, Group, GroupMembership, SharedLink and User. More will follow.

Each of these objects has a Manager. These managers are used to create, request, delete, update, or perform any other action on the corresponding endpoint in the Box API. Some examples:

```php
$folder = $box->Folder->create("0", "Bobs Burgers Fan Theories"); // Parent id/object and name.
$myFile = $box->File->request("123456789", ["trashed_at","modified_at"]); // Request file object with 2 extra fields trashed_at & modified_at
```

Similar calls can be made on each of these managers and a detailed explanation will follow when v0.1beta is released which should contain most of the objects on Box.


## Exchange Tokens

Full access tokens can be exchanged for tokens with limited permissions to pass into less secure or untrusted services or environments (e.g. client-side). See the Box Documentation on [Scopes](https://developer.box.com/docs/scopes) for more information on the scope parameter. If the token parameter is omitted the access token is used. Tokens can only be exchanged for more restrictive ones. If the folder parameter is omitted the root folder of the old token is used.

```php
$oldToken = $box->getAccessToken();
$folder = "0"; // Root folder
$scope = ["root_readonly"];
$readonly_token = $box->requestExchangeToken($scope, $folder, $oldToken);
```
