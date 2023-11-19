<?php

/**
 * API Class to perform CRUD operations for construction stages
 *
 * @api
 */
class ConstructionStages
{
    /**
     * @var PDO
     */
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

    /**
     * Lists all construction stages
     * @return array
     *
     * @apiRoute /constructionStages
     * @requestMethod GET
     */
	public function getAll():array
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as start_date,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as end_date,
				duration,
				duration_unit,
				color,
				external_id,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    /**
     * Gets single construction stage
     * @param $id
     * @return array
     *
     * @apiRoute /constructionStages/{id}
     * @requestMethod GET
     */
	public function getSingle($id):array
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as start_date,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as end_date,
				duration,
				duration_unit,
				color,
				external_id,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    /**
     * Creates new construction stage
     * @param ConstructionStagesData $data
     * @return array
     *
     * @apiRoute /constructionStages
     * @requestMethod POST
     * @apiParam `name` `string` Maximum of 255 characters
     * @apiParam `start_date` `string|Datetime` is a valid date&time in iso8601 format i.e. 2022-12-31T14:59:00Z
     * @apiParam `end_date` `string|Datetime|null` is either null or a valid datetime which is later than the `start_date`
     * @apiParam `durationUnit` `string` is one of HOURS, DAYS, WEEKS or can be skipped (which fallbacks to default value of DAYS)
     * @apiParam `color` `string|null` is either null or a valid HEX color i.e. #FF0000
     * @apiParam `externalId` `string|null` is null or any string up to 255 characters in length
     * @apiParam `status` `string` is one of NEW, PLANNED or DELETED and the default value is NEW.
     */
	public function post(ConstructionStagesData $data):array
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, duration_unit, color, external_id, status)
			    VALUES (:name, :start_date, :end_date, :duration, :duration_unit, :color, :external_id, :status)
			");

        $duration_unit = empty($data->duration_unit) ? ConstructionStagesConsts::DURATIONUNIT_DEFAULT : $data->duration_unit;

		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->start_date,
			'end_date' => $data->end_date,
			'duration' => Helpers::calculateDuration($data->start_date, $data->end_date, $duration_unit),
			'duration_unit' => $duration_unit,
			'color' => $data->color,
			'external_id' => $data->external_id,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

    /**
     * Updates existing construction stage, rewrites only the fields which are sent by the user
     * @param ConstructionStagesData $data
     * @param $id
     * @return array
     *
     * @apiRoute /constructionStages/{id}
     * @requestMethod PATCH
     * @apiParam `name` `string` Maximum of 255 characters
     * @apiParam `start_date` `string|Datetime` is a valid date&time in iso8601 format i.e. 2022-12-31T14:59:00Z
     * @apiParam `end_date` `string|Datetime|null` is either null or a valid datetime which is later than the `start_date`
     * @apiParam `durationUnit` `string` is one of HOURS, DAYS, WEEKS or can be skipped (which fallbacks to default value of DAYS)
     * @apiParam `color` `string|null` is either null or a valid HEX color i.e. #FF0000
     * @apiParam `externalId` `string|null` is null or any string up to 255 characters in length
     * @apiParam `status` `string` is one of NEW, PLANNED or DELETED and the default value is NEW.
     */
    public function update(ConstructionStagesData $data, $id):array
    {
        $fields = [];
        $prep_fields = [];
        $values = [];

        if (count(get_object_vars($data)) > 0) {
            foreach ($data as $field => $value) {
                $fields[] = $field;
                $prep_fields[] = $field.'=:'.$field;
                $values[] = ($field === 'duration_unit' && empty($value)) ? ConstructionStagesConsts::DURATIONUNIT_DEFAULT : $value;
            }

            $stmt = $this->db->prepare("UPDATE construction_stages SET ". implode(', ', $prep_fields) ." WHERE id=:id");

            $stmt->bindParam(':id', $id);

            foreach ($fields as $key => $value) {
                $stmt->bindParam($value, $values[$key]);
            }

            $stmt->execute();
        }

        $existing_data = $this->getSingle($id)[0];

        $duration = Helpers::calculateDuration($existing_data['start_date'], $existing_data['end_date'], $existing_data['duration_unit']);

        $stmt = $this->db->prepare("UPDATE construction_stages SET duration= :duration WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':duration', $duration);
        $stmt->execute();

        return $this->getSingle($id);
    }

    /**
     * Deletes construction stage
     * @param $id
     * @return void
     * @throws Exception
     *
     * @apiRoute /constructionStages/{id}
     * @requestMethod DELETE
     */
    public function delete($id):void
    {
        $stmt = $this->db->prepare("UPDATE construction_stages SET status = '".ConstructionStagesConsts::STATUS_DELETED."' WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}