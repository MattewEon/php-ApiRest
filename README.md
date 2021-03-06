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
    - [2.6 Guards](#2.6)
    - [2.7 Exceptions](#2.7)
- [3. Future improvements](#3)

# Updates

- 21 Dec. 2019
    - Added `PDOUtils` class
    - Cleaned `Repository` class in order to use `PDOUtils`
- 19 Dec. 2019
    - Updated isLogged function to check the token validity
- 15 Dec. 2019
    - Improved route detection to use the one with less parameters
- 15 Jul. 2018
    - Added exception feature
        - Added `ApiException.php` class
        - Updated `Rest::handleRequest` to return a HTTP 409 when exception is raised
- 11 Jul. 2018
    - Added `Rest::existFile` function
- 23 Jun. 2018
    - Improved Services with the `Singleton` Design Pattern
        - This is fixing circular includes on services constructor
- 6 Jun. 2018
    - Updated the way that route are checked, and now allow static route to override route with parameters
- 24 May 2018
    - Added GET parameters support for `Controller->getAll` function
- 5 March 2018
    - Added `Rest::scalePicture` function
    - Added `dbIgnore` feature
- 22 Sept. 2017
    - Added `Controller->getByFields` functions
    - Added `Rest::uploadFile` function
        - Added `Rest::$uploadDir`
        - Added `Rest::getUploadDir()`
        - Added `Rest::configureUploadDir()`
        - Added `Rest::createDirectoryRecursive()`
- 22 Oct. 2017
    - Added `Guards` feature
        - Added `Role` Enumeration
    - Added `KeyValue` and `KeyValueList` structure class
        - Updated `Service` and `Repository` classes
    - Added `Credentials` class
- 23 Oct. 2017
    - Added `deleteByField()` function
    - Updated `getByField()` function, it returns now `Model[]` instead of `Model`

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
    - Guards feature
        - Used to protect your data with access rights
    - KeyValueList & KeyValue Classes
       - Used to filter your requests
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
require_once __DIR__ . "/ApiRest/RestAPI.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, enctype, token');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$restAPI = new RestAPI("localhost", "myDB", "root", "");
// You can add an argument to improve the security of your session like this :
//$restAPI = new RestAPI("localhost", "myDB", "root", "", "m*;rO)P7^)3'k[F'S~h0Lx7{zN%`6S");

Rest::configureUploadDir("rest-upload");
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
    - Check if guards are okay
    - Call the associated function
    - Process query(ies) and return the result
    
In the `user` directory, you will find an example to add paths to your API REST for a `User` object. Each files related
to a specific object / class / table will have to be in the same folder and follow the same rules as User :
- `/modelname/`
    - `include.php` 
    - `ModelName.controller.php` 
    - `ModelName.model.php` 
    - `ModelName.repository.php` 
    - `ModelName.service.php`
    
## 2.2 <a name="2.2"></a> Controllers

Controllers are the the first layer called by the RestAPI. They have to be
named like this : `modelClassName + "Controller"`. By default, theses paths
are generated by default :
- GET `api.php/modelClassName`
    - Get all data
    - Filterable with GET parameters
- GET `api.php/modelClassName/$id`
    - Get by ID
- POST `api.php/modelClassName`
    - Create data
- PUT `api.php/modelClassName`
    - Update data
- DELETE `api.php/modelClassName/$id`
    - Delete data with ID

By default, each path generated is associated to the [Guard](#2.6) LoginGuard. If you want to remove the guard, you'll
have to override the ApiRoute by declaring it in your controller.

You can add a filter on the getAll function by adding GET values on the url. Example :  
`api.php/modelClassName?lastname=Doe`

Here is an example of a UserController class :
```php
<?php
// FILE rest/user/User.controller.php
require_once __DIR__ . "/../ApiRest/Rest.php";
require_once __DIR__ . "/../ApiRest/Controller.php";

class UserController extends Controller {

	public function __construct(string $modelName) {
		parent::__construct($modelName);

		$this->createApiRoute(Rest::PUT, 'login', "login");

		$this->createApiRoute(Rest::POST, 'picture', "uploadPicture", [new LoginGuard()]);
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

You can override a route with another without removing it, if they don't have the same
amount of parameters. For example, theses routes will be kept :
 ```php
 $this->createApiRoute(Rest::GET, '$id', "getById");
 $this->createApiRoute(Rest::GET, 'current', "getCurrent");
 ```
 If possible, the route 'current' will be triggered. If it's not, the '$id' route will be triggered
 .

## 2.3 <a name="2.3"></a> Services

Services are the next layer called by controllers : They are between Controller
classes and Repository classes. Services are used to get data from `Repository`
classes and make process the data.

`Service` classes also can call others services to cross data and make
special process.

By default, functions associated to Controller's paths are generated : 
- `getAll()`
- `getByID(string $id)`
- `create()`
- `update()`
- `delete(string $id)`

Here is an example of a UserService class :
```php
<?php
// FILE rest/user/User.service.php
require_once __DIR__ . "/../ApiRest/Service.php";

class UserService extends Service {
	public function login(User $user): string {
		return $this->repository->login($user);
	}
	
	function initialize() { }
}
```

If you want to use another `Service` in theses class, you have to declare
it and to initialize the `Service` class in the `initialize` function like this:
```php
<?php
class UserService extends Service {
	/** @var $bookService BookService */
	public $bookService;

	public function login(User $user): string {
		return $this->repository->login($user);
	}
	
	function initialize() {
		$this->bookService = BookService::getInstance("Book");
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
// FILE rest/user/User.repository.php
require_once __DIR__ . "/../ApiRest/Repository.php";

class UserRepository extends Repository {

	public function __construct() {
		parent::__construct("user", 'User');
	}

	public function login(User $user): string {
		$fields = new KeyValueList([
			new KeyValue("name", $user->name),
			new KeyValue("password", $user->password)
		]);

		$matchedUsers = $this->getByFields($fields);

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

If you want to use custom SQL queries, you can use PDOUtils class in order
to execute them. Here is an example :
```php
$query = "< CUSTOM_SELECT_QUERY >";
$PDOStatement = PDOUtils::executeQuery($query);
return static::getByPDOStatement($PDOStatement);
```

There some functions you can use from `PDOUtils` :
- `executeQuery(query)`
    - Execute a SQL query
- `executeQueryWithParameter(query, keyValue)`
    - Execute a SQL query with one parameter
- `executeQueryWithParameters(query, KeyValueList)`
    - Execute a SQL query with several parameters

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

There is another function : `addDbIgnore("field1", ...)` used to specify which
fields are not stored in the dataBase.

Model classes can be retrieved from JSON using this static function :
`Model::fromJSON(stdClass $data)`.

## 2.6 <a name="2.6"></a> Guards

Guards are used to restrict data access depending on the user's role. Three Guards are created by default :
- `AdminGuard`
- `ModeratorGuard`
- `LoginGuard`

They all extends the `Guard` abstract class, and implements the `authorizeAccess(): bool` function. You can create your
own guard too following the same rules, and then adding a new line in the `include.php` file in the `Guard` directory.

Guards are called in the [Controllers](#2.2) classes, when you will declare the route. Note that a route can have 0, 1
or multiple guards.

## 2.7 <a name="2.7"></a> Exceptions

Exception can be raised when there is an issue to be printed displayed in the front-end website. To do so, you have to
throw an `ApiException` and the server will respond with a `HTTP 409` error.

The `ApiException` class extends the `Exception` class, but it does not use parameters from the superclass.  
This class is made in a way to easily use some internationalization Frondend tool: it's containing a `key` parameter
which can be the key of the translation in the JSON file, and a `parameters` parameter which contain the data to be
transmitted. For example, here is an ApiException and the associated JSON internationalization file :

```php
throw new ApiException("api-error.error1", ["name" => 'John']);
```

```json
{
  "api-error": {
    "error1": {
      "title": "Hey !",
      "text": "Hello {{name}}"
    }
  }
}
```

When using an ApiException, these data will be stored in the `error` property of the `HttpErrorResponse` JavaScript
item.


# 3. <a name="3"></a> Future improvements

Add the capacity of forcing the type of each Model's field
Otherwise, No future improvements planned now. Doesn't mean that the package will not be updated !