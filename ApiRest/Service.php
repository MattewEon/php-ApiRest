<?php
require_once __DIR__ . "/Rest.php";
require_once __DIR__ . "/KeyValue.php";
require_once __DIR__ . "/KeyValueList.php";

/** Service class are used to link a controller to a Repository
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
abstract class Service {
	/** @var Repository */
	public $repository;

	/** Service constructor.
	 *
	 * @param string $moduleName
	 */
	public function __construct(string $moduleName) {
		$tableHandlerName = $moduleName . "Repository";
		$this->repository = new $tableHandlerName();
	}

	/** Get All lines
	 *
	 * @return Model[]
	 */
	public function getAll(): array { return $this->repository->getAll(); }

	/** Get a single line by ID
	 *
	 * @param mixed $id
	 *
	 * @return Model
	 */
	public function getById($id): Model { return $this->repository->getByID($id); }


	/** Get lines by Field
	 *
	 * @param KeyValue $field
	 *
	 * @return Model
	 */
	public function getByField(KeyValue $field): Model {
		return $this->repository->getByField($field);
	}


	/** Get lines by Fields
	 *
	 * @param KeyValueList $fields
	 *
	 * @return Model[]
	 */
	public function getByFields(KeyValueList $fields): array {
		return $this->repository->getByFields($fields);
	}

	/** Create a model
	 *
	 * @param Model $model
	 *
	 * @return Model
	 */
	public function create(Model $model): Model { return $this->repository->create($model); }

	/** Update model
	 *
	 * @param Model $model
	 *
	 * @return Model
	 */
	public function update(Model $model): Model { return $this->repository->update($model); }

	/** Delete a model
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function delete($id) {
		$this->repository->delete($id);
		return "";
	}

	/** Delete a model by KeyValue list
	 *
	 * @param KeyValueList $fields
	 *
	 * @return string
	 */
	public function deleteByFields(KeyValueList $fields) {
		$this->repository->deleteByFields($fields);
		return "";
	}
}