<?php
require_once __DIR__ . "/JWT.php";
require_once __DIR__ . "/Credentials.php";

/** Contains constants and static functions
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class Rest {
	const GET    = "GET";
	const POST   = "POST";
	const PUT    = "PUT";
	const DELETE = "DELETE";

	/** @var PDO */
	public static $db;
	/** @var string */
	public static $secretKey;
	/** @var string */
	public static $uploadDir = "rest/uploads";

	/** Check if $method is GET POST PUT DELETE
	 *
	 * @param string $method
	 *
	 * @return bool
	 */
	public static function isAMethod(string $method): bool {
		return in_array(strtoupper($method), [Rest::GET, Rest::POST, Rest::PUT, Rest::DELETE]);
	}

	/** Connect with PDO
	 *
	 * @param $host
	 * @param $dbName
	 * @param $userName
	 * @param $password
	 */
	public static function MysqlConnect($host, $dbName, $userName, $password) {
		Rest::$db = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $userName, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
		Rest::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/** Get the current User's id
	 *
	 * @return Credentials
	 */
	public static function getTokenID() {
		return self::tokenToID(self::getToken());
	}

	/** Get the id contained by the Token
	 *
	 * @param string $token
	 *
	 * @return Credentials
	 */
	public static function tokenToID(string $token): Credentials {
		$tokenArray = JWT::decode($token, self::$secretKey);

		return new Credentials($tokenArray->id, $tokenArray->role);
	}

	/** Get the token send via HTTP headers
	 *
	 * @return string token
	 * @throws Exception
	 */
	public static function getToken(): string {
		if (!self::isLogged()) throw new Exception("HTTP_TOKEN header is empty");

		return $_SERVER["HTTP_TOKEN"];
	}

	/** Check if user is logged via HTTP Token
	 *
	 * @return bool
	 */
	public static function isLogged(): bool {
		return isset($_SERVER["HTTP_TOKEN"]);
	}

	/** Encode id to get the Token
	 *
	 * @param $credentials Credentials
	 *
	 * @return string token
	 */
	public static function IDToToken(Credentials $credentials): string {
		return JWT::encode($credentials, self::$secretKey);
	}

	/** Set the uploadDir parameter
	 *
	 * @param string $directory
	 */
	public static function configureUploadDir(string $directory) {
		Rest::$uploadDir = $directory;

		if (!is_dir(Rest::getUploadDir())) Rest::createDirectoryRecursive(Rest::$uploadDir);
	}

	/** Create a Directory Recursively
	 *
	 * @param string $directory
	 */
	public static function createDirectoryRecursive(string $directory) {
		$dir = __DIR__ . "/../..";
		foreach (explode("/", $directory) as $folder) {
			$dir .= "/" . $folder;
			if (!is_dir($dir)) mkdir($dir);
		}
	}

	/** Get the upload directory path
	 *
	 * @return string
	 */
	public static function getUploadDir() {
		return __DIR__ . "/../../" . Rest::$uploadDir;
	}


	/**
	 * Check $_FILES array if the file given exist or not
	 *
	 * @param string $fileName
	 *
	 * @return bool
	 */
	public static function existFile(string $fileName): bool {
		return isset($_FILES[ $fileName ]);
	}


	/** Upload a file to the uploadDir
	 *
	 * @param string $fileName
	 * @param string $newFileName
	 *
	 * @return bool
	 * @throws Exception if file don't exist
	 */
	public static function uploadFile(string $fileName, string $newFileName): bool {
		if (!Rest::existFile($fileName))
			throw new Exception("File $fileName is not present in \$_FILES (" . join(", ", array_keys($_FILES)) . ")");

		$file     = $_FILES[ $fileName ];
		$tmp_name = $file["tmp_name"];

		return move_uploaded_file($tmp_name, Rest::getUploadDir() . "/" . $newFileName);
	}

	/** Change scale of a picture and save it in a new file
	 *
	 * @param string $input  input file name
	 * @param string $output output file name
	 * @param float  $scale  scale of the new picture
	 */
	public static function scalePicture(string $input, string $output, float $scale) {
		// Get the size and new size
		list($width, $height) = getimagesize($input);
		$new_width  = $width * $scale;
		$new_height = $height * $scale;

		// Scale the picture
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image   = imagecreatefromjpeg($input);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		imagepng($image_p, $output);
	}
}