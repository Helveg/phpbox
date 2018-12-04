<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Objects\{Object, Item, Folder, File, User};
use PhpBox\Exception\BoxException;

// Thanks to cletus@StackOverflow https://stackoverflow.com/users/18393/cletus
function from_camel_case($input) {
  preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
  $ret = $matches[0];
  foreach ($ret as &$match) {
    $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
  }
  return implode('_', $ret);
}

class Box {
  const baseUrl = "https://api.box.com/2.0/";
  private $AccessToken;
  private $config;
  private $lastResponseCode;
  private $lastResponse;

  public function __construct(Config $config, $token = "") {
    $this->config = $config;
    if($token != "") {
      $this->AccessToken = new Token($this, $token);
    } else {
      $this->requestAccessToken();
    }
  }

  public function requestAccessToken() {
    $appDetails = $this->config->getAppDetails();
    $assertion = $this->config->writeAssertion();
    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', $this->config->getAuthenticationUrl(), [
      'form_params' => [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $assertion,
        'client_id' => $appDetails->ID,
        'client_secret' => $appDetails->Secret
      ]
    ]);
    if($response->getStatusCode() == 200) {
      $data = $response->getBody()->getContents();
      $this->AccessToken = new Token($this, json_decode($data));
    }
    return false;
  }

  public function requestExchangeToken($scopes = ["base_preview", "item_download"], $folder = NULL, $token = NULL) {
    if($token == NULL) {
      $token = $this->getAccessToken();
    }
    $client = new \GuzzleHttp\Client();
    $params = [
      'subject_token' => (string)$token,
      'subject_token_type' => 'urn:ietf:params:oauth:token-type:access_token',
      'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
      'scope' => implode(" ", $scopes)
    ];
    if ($folder != NULL) {
      if ($folder instanceof Item && $folder->isFolder()) {
        $folder = (string)($folder->getId());
      }
      $params['resource'] = Folder::endpointUrl.$folder;
    }
    $response = $client->request('POST', $this->config->getAuthenticationUrl(), [
      'form_params' => $params
    ]);
    return new Token($this, json_decode($response->getBody()->getContents()));
  }

  public function getAccessToken() {
    if(!Token::isValid($this->AccessToken)) {
      $this->requestAccessToken();
    }
    return $this->AccessToken;
  }

  private function guzzle($method, $endpoint, $params, $responseHandler = NULL) {
    $client = new \GuzzleHttp\Client(["base_uri" => self::baseUrl, "http_errors" => false]);
    try {
      $response = $client->request($method, $endpoint, $params);
    }
    finally {
      $this->lastResponse = $response;
      if($response instanceof \GuzzleHttp\Psr7\Response) {
        $this->lastResponseCode = $response->getStatusCode();
        if($responseHandler != NULL) {
          return $responseHandler($this, $response);
        }
        else {
          switch($response->getStatusCode()) {
            case 200:
            case 201:
              return json_decode($response->getBody()->getContents());
          }
        }
      } else {
        $this->lastResponseCode = 0;
      }
      return false;
    }
  }

  public function getResponseCode() {
    return $this->lastResponseCode;
  }

  public function getResponse() {
    return $this->lastResponse;
  }

  private function guzzleObject($url, $fields = [], $headers = []) {
    $headers = array_merge($this->getDefaultHeaders(), $headers);
    return $this->guzzle('GET', $url, [
      'headers' => $headers,
      'query' => self::fieldsQuery($fields)
    ]);
  }

  private static function fieldsQuery($fields, $query = []) {
    if(!empty($fields)) {
      $query['fields'] = implode(',', $fields);
    }
    return $query;
  }

  public function requestFolder($id = "0", $fields = []) {
    if($id instanceof Object && $id->isFolder()) $id = $id->getId();
    if($ret = $this->guzzleObject("folders/$id", $fields)) $ret = new Folder($this, $ret);
    return $ret;
  }

  public function requestFile($id, $fields = []) {
    if($id instanceof Object && $id->isFile()) $id = $id->getId();
    if($ret = $this->guzzleObject("files/$id", $fields)) $ret = new File($this, $ret);
    return $ret;
  }

  public function requestUser($id = "me", $fields = []) {
    if($id instanceof Object && $id->isUser()) $id = $id->getId();
    if($ret = $this->guzzleObject("users/$id", $fields)) $ret = new User($this, $ret);
    return $ret;
  }

  public function requestGroup($id, $fields = []) {
    if($id instanceof Object && $id->isGroup()) $id = $id->getId();
    if($ret = $this->guzzleObject("groups/$id", $fields)) $ret = new Group($this, $ret);
    return $ret;
  }

  public function requestGroupMembership($id, $fields = []) {
    if($id instanceof Object && $id->isGroupMembership()) $id = $id->getId();
    if($ret = $this->guzzleObject("group_memberships/$id", $fields)) $ret = new GroupMembership($this, $ret);
    return $ret;
  }

  private function guzzleCreate($object, $params, $fields) {
    $endpoint = from_camel_case(basename($object))."s/";
    $classname = "\\PhpBox\\Objects\\$object";
    $response = $this->guzzle('POST', $endpoint, [
      'headers' => $this->getDefaultHeaders(),
      'query' => self::fieldsQuery($fields),
      'json' => $params
    ]);
    if($response) {
      return new $classname($this, $response);
    }
    return false;
  }

  public function createAppUser(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    $params['is_platform_access_only'] = true;
    return $this->guzzleCreate("User", $params, $fields);
  }

  public function createGroup(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    if(!($ret = $this->guzzleCreate("Group", $params, $fields))) {
      $error = $this->getResponseCode();
      if($error == 409) {
        throw new BoxException("A group with this name already exists", 409);
      }
    }
    return $ret;
  }

  public function getDefaultHeaders() {
    $token = $this->getAccessToken();
    return [
        'Authorization' => "Bearer $token",
        'Accept'        => 'application/json',
    ];
  }
}

?>
