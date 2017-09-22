<?php
require_once __DIR__ . "/JWT.php";

/** Contains constants and static functions
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class Rest {
	const GET = "GET";
	const POST = "POST";
	const PUT = "PUT";
	const DELETE = "DELETE";

	/** @var PDO */
	public static $db;
	/** @var string */
	public static $secretKey;
	/** @var string */
	public static $uploadDir;

	/** Check if $method is GET POST PUT DELETE
	 *
	 * @param string $method
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
		Rest::$db = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $userName, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']); // connexion à la BDD
		Rest::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/** Get the current User's id
	 *
	 * @return string
	 */
	public static function getTokenID(): string {
		return self::tokenToID(self::getToken());
	}

	/** Get the id contained by the Token
	 *
	 * @param string $token
	 * @return mixed
	 */
	public static function tokenToID(string $token) {
		$tokenArray = JWT::decode($token, self::$secretKey);
		return $tokenArray->id;
	}

	/** Get the token send via HTTP headers
	 *
	 * @return string token
	 * @throws Exception
	 */
	public static function getToken(): string {
		if (!self::isLogged())
			throw new Exception("HTTP_TOKEN header is empty");

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
	 * @param $id
	 * @return string token
	 */
	public static function IDToToken($id): string {
		return JWT::encode(["id" => $id], self::$secretKey);
	}

	/** Set the uploadDir parameter
	 *
	 * @param string $directory
	 */
	public static function configureUploadDir(string $directory) {
		Rest::$uploadDir = $directory;

		if (!is_dir(Rest::getUploadDir()))
			Rest::createDirectoryRecursive(Rest::$uploadDir);
	}

	/** Create a Directory Recursively
	 *
	 * @param string $directory
	 */
	public static function createDirectoryRecursive(string $directory) {
		$dir = __DIR__ . "/../..";
		foreach (explode("/", $directory) as $folder) {
			$dir .= "/" . $folder;
			if (!is_dir($dir))
				mkdir($dir);
		}
	}

	/** Get the upload directory path
	 *
	 * @return string
	 */
	public static function getUploadDir() {
		return __DIR__ . "/../../" . Rest::$uploadDir;
	}

	/** Upload a file to the uploadDir
	 *
	 * @param string $fileName
	 * @param string $newFileName
	 * @return bool
	 * @throws Exception if file don't exist
	 */
	public static function uploadFile(string $fileName, string $newFileName): bool {
		if (!isset($_FILES[$fileName]))
			throw new Exception("File $fileName is not present in \$_FILES (" . join(", ", array_keys($_FILES)) . ")");

		$file = $_FILES[$fileName];
		$tmp_name = $file["tmp_name"][0];

		return move_uploaded_file($tmp_name, Rest::getUploadDir() . "/" . $newFileName);
	}
}