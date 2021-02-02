# phpbox

This project is abandoned, I don't use Box or PHP anymore.

Implementation of several aspects of the Box v2.0 API.

[![Build Status](https://travis-ci.com/Helveg/phpbox.svg?branch=master)](https://travis-ci.com/Helveg/phpbox)

## Installation
Install via `composer require robindeschepper/phpbox`.

## Getting Started

Currently it connects via a Box [JWT](https://developer.box.com/docs/construct-jwt-claim-manually) JSON config file or through setting of the values on the Config object. Accessing your box app is done like this:
```php
require_once("vendor/autoload.php");
use PhpBox\Box;
use PhpBox\Config\JsonConfig;

$config = new JsonConfig("path/to/box_config.json");
$box = new Box($config);
```

This will use your json config file to request an access token via JWT. The token is stored in the PhpBox object and automatically refreshed if it expires.

## Model

I have created classes for the following objects in the Box API:

```
Collaboration, CollaborationWhitelistEntry,
     Comment, DevicePinner, Event, File, FileVersion,
     FileVersionRetention, Folder, Group, GroupMembership,
     Item, LegalHold, LegalHoldPolicy, LegalHoldPolicyAssignment,
     Metadata, MetadataCascadePolicy, MetadataTemplate,
     RecentItem, RetentionPolicy, RetentionPolicyAssignment, SharedLink,
     StoragePolicy, StoragePolicyAssignment, Task, TemplateField,
     TermsOfService, User, Webhook, WebLink
```

These objects are populated by analyzing the JSON payload received from Box using Guzzle requests. Each field in the returned JSON object will become a property in the PhpBox object.

### Example of JSON to PhpBox object

```json
{
	"id": "1234",
	"type": "file_version",
	"file": {
		"id": "125",
		"type": "file"
	},
	"version": "2"
}
```
This will become an object of type `PhpBox\Objects\FileVersion` with fields `id`, `type`, `version`and `file` (of type `PhpBox\Objects\File`).

### Managers

Some of these objects will have a Manager in the PhpBox object that is used to perform the actions described for each object in the Box API reference such as creating, requesting, deleting, updating, or many other actions.

```php
$folder = $box->Folder->create("0", "Bobs Burgers Fan Theories"); // Creates a folder. Parameters: 1) Parent id/object 2) name.
$myFile = $box->File->request("123456789", ["trashed_at","modified_at"]); // Request file object with 2 extra fields trashed_at & modified_at
```

Similar calls can be made on each of these managers and a detailed explanation will follow when v0.2.1 is released which will focus on implementing core actions shared among all managers such as creating or deleting objects.


## Exchange Tokens

Full access tokens can be exchanged for tokens with limited permissions to pass into less secure or untrusted services or environments (e.g. client-side). See the Box Documentation on [Scopes](https://developer.box.com/docs/scopes) for more information on the scope parameter. If the token parameter is omitted the access token is used. Tokens can only be exchanged for more restrictive ones. If the folder parameter is omitted the root folder of the old token is used.

```php
$oldToken = $box->getAccessToken();
$folder = "0"; // Root folder
$scope = ["root_readonly"];
$readonly_token = $box->requestExchangeToken($scope, $folder, $oldToken);
```
