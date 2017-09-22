# php-ApiRest

This repository contains a little framework capable of doing a RestAPI easily with PHP
##### Please don't hesitate to ask for new features or report a bug on Github! Thanks


# Summary

- [1. Features](#1)
- [2. How it works ?](#2)
    - [2.1 RestAPI](#2.1)
    - [2.2 Controllers](#2.2)
    - [2.3 Services](#2.3)
    - [2.4 Repositories](#2.4)
    - [2.5 Model](#2.5)
- [3. Future improvements](#3)

# Updates

- 22 Sept. 2017
    - Added `Controller->getByFields` functions
    - Added `Rest::uploadFile` function
        - Added `Rest::$uploadDir`
        - Added `Rest::getUploadDir()`
        - Added `Rest::configureUploadDir()`
        - Added `Rest::createDirectoryRecursive()`

# 1. <a name="1"></a>Features

- RestAPI Framework
    - Model Superclass
        - contains models, why asking ?
    - Controller Superclass
        - Here is where you will bind url / function
    - Service Superclass
        - Here is where you'll call the Repository
    - Repository Superclass
        - Get the data from the DB
- JWT Token
    - https://github.com/firebase/php-jwt
- File transfer
    
# 2. <a name="2"></a>How it works ?

## 2.1 <a name="2.1"></a> RestAPI

To start, you'll have to create a folder (I named it `/rest`)
and place the `ApiRest` folder inside. The main file will be `api.php` :
it will create the RestAPI, connect to the DB and listen for requests.

Here is the default content in this file :
```php
<?php
// FILE rest/api.php
require_once "ApiRest/RestAPI.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, enctype, token');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$restAPI = new RestAPI("localhost", "myDB", "root", "");
echo $restAPI->handleRequest();
```

Now the RestAPI is ready to use. You can check it with Postman and hit the
url with the `GET` method : `http://your-domain/your-path/rest/api.php/user`.

It's cool, but how the RestAPI handle that ?
- Get the word after `api.php` (here it is the word `user`)
- Check if `user` directory exists
- Check if `user/include.php` file exists
- Create the controller associated, named `UserController`
- Call the Controller's method `processUrl`
    - Verify the url match
    - Call the associated function
    - Process query(ies) and return the result

## 2.2 <a name="2.2"></a> Controllers

Controllers are the the first layer called by the RestAPI. They have to be
named like this : `modelClassName + "Controller"`. By default, theses paths
are generated by default :
- GET `api.php/modelClassName`
    - Get all data
- GET `api.php/modelClassName/$id`
    - Get by ID
- POST `api.php/modelClassName`
    - Create data
- PUT `api.php/modelClassName`
    - Update data
- DELETE `api.php/modelClassName/$id`
    - Delete data with ID

Here is an example of a UserController class :
```php
<?php
// FILE rest/User/User.controller.php
require_once __DIR__ . "/../ApiRest/Rest.php";
require_once __DIR__ . "/../ApiRest/Controller.php";

class UserController extends Controller {

	public function __construct(string $modelName) {
		parent::__construct($modelName);

		$this->createApiRoute(Rest::PUT, 'login', "login");

		$this->createApiRoute(Rest::POST, 'picture', "uploadPicture");
	}

	/* PUT */
	public static function login(array $params, stdClass $body): string {
		$user = User::fromJSON($body);
		return static::$service->login($user);
	}

	public static function uploadPicture(): string {
		$fileName = "htmlInputName";
		$newName = "profilePicture.png";
		Rest::uploadFile($fileName, $newName);
		return "";
	}
}
```

When you call `createApiRoute()` function, the second parameter correspond to
the path of the request. In this string, you can add dynamic parameters like
this `'customPath/$param1/$param2/anything'`.
Then, when the associated function will be called, you will find values of
`$param1` and `$param2` on the `$params` variable :
```php
var_dump($params);
// print :
// array(
//    "param1" => "value1",
//    "param2" => "value2",
//)
```

## 2.3 <a name="2.3"></a> Services

Services are the next layer called by controllers : They are between Controller
classes and Repository classes. Services are used to get data and make process
the data.

By default, functions associated to Controller's paths are generated : 
- `getAll()`
- `getByID(string $id)`
- `create()`
- `update()`
- `delete(string $id)`

Here is an example of a UserService class :
```php
<?php
// FILE rest/User/User.service.php
require_once __DIR__ . "/../ApiRest/Service.php";

class UserService extends Service {
	public function login(User $user): string {
		return $this->repository->login($user);
	}
}
```

## 2.4 <a name="2.4"></a> Repository

Repositories are the final layer called by Services. They contains SQL
queries and get models object from the DataBase.

Same as Services, by default theses functions are available : 
- `getAll()`
- `getByID(string $id)`
- `create()`
- `update()`
- `delete(string $id)`

Here is an example of UserRepository class :
```php
<?php
// FILE rest/User/UserRepository.php
require_once __DIR__ . "/../ApiRest/Repository.php";

class UserRepository extends Repository {

	public function __construct() {
		parent::__construct("user", 'User');
	}

	public function login(User $user): string {
		$matchedUsers = $this->getByFields(["name", "password"], $user);

		if (count($matchedUsers) != 1)
			throw new Exception("UserRepository->login() : \$matchedUsers have a size of " . count($matchedUsers) . " instead of 1 !");

		$loggedUser = $matchedUsers[0];
		if ($loggedUser)
			return Rest::IDToToken($loggedUser->id);
		else
			return "";
	}
}
```

## 2.5 <a name="2.5"></a> Model

Models are the classes representing a line in the DataBase. 

When data are retrieved from the DataBase, booleans won't be equals to
`true`/`false` but to `'1'`/`'0'`. To get back our stolen booleans, you can
call the function `preserveBooleans()` to convert boolean fields.

To mark a field like a boolean field, just call this function inside the
constructor : `addBoolean("booleanField1", "booleanField2", ...)`

On the same way, you can mark a field as jsonIgnore by calling the function
`addIgnore("ignoredField1", ...)`. Then, to convert Model's instances to
JSON call the `encode()` function !

Model classes can be retrieved from JSON using this static function :
`Model::fromJSON(stdClass $data)`.

# 3. <a name="3"></a> Future improvements

In the future, I would like to add some authorizations features to allow
requesting specific paths only if the user is logged and has the appropriate
role.