<?php

/**
 * Validation of fields for ConstructionStages
 */
class ConstructionStagesValidation
{
    /**
     * Validator methods
     * @var array|string[]
     */
    private array $validators;

    /**
     * Collection of errors
     * @var array
     */
    private array $validation_errors;

    /**
     * Checks if a validator method exist for a given field and executes it
     * @param $data
     * @throws Exception
     */
    public function __construct($data)
    {
        $this->validators = array_diff(get_class_methods($this), ['__construct']);

        foreach ($data as $field => $value) {
            $validator_method = 'validate_'.$field;
            if (in_array($validator_method, $this->validators)){
                call_user_func_array(array($this, $validator_method), [$value]);
            }
        }

        if (isset($this->validation_errors) && $this->validation_errors) {
            throw new Exception(json_encode($this->validation_errors));
        }
    }

    /**
     * Validates name field
     * @param $name
     * @return void
     */
    private function validate_name($name):void
    {
        $name = trim($name);

        if (empty($name)) {
            $this->validation_errors['name'][] = 'name field cannot be empty';
        }

        if (strlen($name) > 255) {
            $this->validation_errors['name'][] = 'name field can have a maximum of 255 characters';
        }
    }

    /**
     * Validates start_date field
     * @param $start_date
     * @return void
     */
    private function validate_start_date($start_date):void
    {
        $start_date = trim($start_date);

        if (empty($start_date))
        {
            $this->validation_errors['start_date'][] = 'start_date field cannot be empty';
        }

        if(Helpers::isISO8601DateTime($start_date))
        {
            $this->start_date = $start_date;
        } else {
            $this->validation_errors['start_date'][] = 'start_date should be a valid ISO-8601 datetime string';
        }
    }

    /**
     * Validates end_date field
     * @param $end_date
     * @return void
     * @throws Exception
     */
    private function validate_end_date($end_date):void
    {
        $end_date = trim($end_date);
        if (!empty($end_date)) {
            if(! Helpers::isISO8601DateTime($end_date))
            {
                $this->validation_errors['end_date'][] = 'end_date should be a valid ISO-8601 datetime string';
            }

            if (isset($this->start_date))
            {
                if(Helpers::isISO8601DateTime($end_date) && ! Helpers::compareDates($this->start_date, $end_date))
                {
                    $this->validation_errors['end_date'][] = 'end_date should be greater than start_date';
                }
            } else {
                $this->validation_errors['end_date'][] = 'start_date should be set';
            }
        }
    }

    /**
     * Validates duration_unit field
     * @param $duration_unit
     * @return void
     */
    private function validate_duration_unit($duration_unit):void
    {
        $duration_unit = trim($duration_unit);

        if (! empty($duration_unit))
        {
            $status_unit_consts = [
                ConstructionStagesConsts::DURATIONUNIT_DEFAULT,
                ConstructionStagesConsts::DURATIONUNIT_HOURS,
                ConstructionStagesConsts::DURATIONUNIT_WEEKS,
            ];

            if (! in_array($duration_unit, $status_unit_consts, true )) {
                $this->validation_errors['duration_unit'][] = 'duration_unit field can only be '.implode(', ', $status_unit_consts);
            }
        }
    }

    /**
     * Validates color field
     * @param $color
     * @return void
     */
    private function validate_color($color):void
    {
        $color = trim($color);

        if (!empty($color))
        {
            if(! Helpers::isValidHexColor($color))
            {
                $this->validation_errors['color'][] = 'color should be a valid hex color';
            }
        }
    }

    /**
     * Validates external_id field
     * @param $external_id
     * @return void
     */
    private function validate_external_id($external_id):void
    {
        $external_id = trim($external_id);

        if (! empty($external_id))
        {
            if (strlen($external_id) > 255) {
                $this->validation_errors['external_id'][] = 'external_id field can have a maximum of 255 characters';
            }
        }
    }

    /**
     * Validates status field
     * @param $status
     * @return void
     */
    private function validate_status($status):void
    {
        $status = trim($status);

        if (empty($status))
        {
            $this->validation_errors['status'][] = 'status field cannot be empty';
        }

        $status_consts = [
            ConstructionStagesConsts::STATUS_NEW,
            ConstructionStagesConsts::STATUS_PLANNED,
            ConstructionStagesConsts::STATUS_DELETED,
        ];

        if (! in_array($status, $status_consts, true )) {
            $this->validation_errors['status'][] = 'status field can only be '.implode(', ', $status_consts);
        }
    }
}