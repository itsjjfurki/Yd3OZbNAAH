<?php

/**
 * Acts as a DTO for ConstructionStages fields used in POST and PATCH requests
 */
class ConstructionStagesData
{
    /**
     * @var
     */
	public $name;

    /**
     * @var
     */
	public $start_date;

    /**
     * @var
     */
	public $end_date;

    /**
     * @var
     */
	public $duration;

    /**
     * @var
     */
	public $duration_unit;

    /**
     * @var
     */
	public $color;

    /**
     * @var
     */
	public $external_id;

    /**
     * @var
     */
	public $status;

    /**
     * Sets fields and passes them to ConstructionStagesValidation class
     * @param $data
     * @throws Exception
     */
	public function __construct($data) {
		if(is_object($data)) {

			$vars = get_object_vars($this);

			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {
					$this->$name = trim($data->$name);
				}

                if (Api::$requestMethod === 'patch' && ! isset($data->$name)) {
                    unset($this->$name);
                }
			}
		}

        new ConstructionStagesValidation(get_object_vars($this));
	}
}