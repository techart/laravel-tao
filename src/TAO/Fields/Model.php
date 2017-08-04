<?php

namespace TAO\Fields;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TAO\Fields;
use Ramsey\Uuid\Uuid;

/**
 * Class Model
 * @package TAO\Fields
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use Fields\Utils\Model\Schema,
        Fields\Utils\Model\Events,
        Fields\Utils\Model\Access,
        Fields\Utils\Model\Admin;

    /**
     * @var bool
     */
    public $incrementing = false;
    /**
     * @var string
     */
    protected $idType = 'uuid';

    /**
     * @var string
     */
    protected $adminType = 'table';

    /**
     * @var array
     */
    protected $fields = array();
    /**
     * @var
     */
    protected $processedFields;

    /**
     * @var array
     */
    protected $errors = array();


    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = app()->tao->connectionNameFor($this->getTable());
        if ($this->idType == 'auto_increment') {
            $this->incrementing = true;
        }
        $this->updateSchemaIfNecessary();
        $this->setupFields();
    }

    /**
     * @return mixed
     */
    public function getDatatype()
    {
        return \TAO::datatypeCodeByClass(get_class($this));
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @param $message
     * @param null $column
     */
    public function error($message, $column = null)
    {
        if ($column) {
            $this->errors[$column] = $message;
        } else {
            $this->errors[] = $message;
        }
    }

    /**
     * @return mixed
     */
    abstract public function fields();

    /**
     * @return mixed
     */
    public function calculatedFields()
    {
        $fields = $this->fields();
        return $fields;
    }

    /**
     * @return array
     */
    protected function processedFields()
    {
        if (is_null($this->processedFields)) {
            $count = 0;
            $this->processedFields = array();
            foreach ($this->calculatedFields() as $field => $data) {
                $count++;
                if (!isset($data['weight'])) {
                    $data['weight'] = $count;
                }
                $this->processedFields[$field] = $data;
            }
        }
        return $this->processedFields;
    }

    /**
     * @return array
     * @throws Exception\UndefinedField
     */
    public function fieldsObjects()
    {
        $fields = array();
        foreach (array_keys($this->calculatedFields()) as $name) {
            $fields[$name] = $this->field($name);
        }
        return $fields;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception\UndefinedField
     */
    public function field($name)
    {
        if (!isset($this->fields[$name])) {
            $fields = $this->calculatedFields();
            if (!isset($fields[$name])) {
                throw new Fields\Exception\UndefinedField($name, get_class($this));
            }
            $this->fields[$name] = app()->taoFields->create($name, $fields[$name], $this);
        }
        return $this->fields[$name];
    }

    /**
     * @throws Exception\UndefinedField
     */
    public function setupFields()
    {
        foreach (array_keys($this->calculatedFields()) as $field) {
            $this->field($field)->setupDefault();
        }
    }

    /**
     * @return Uuid
     */
    public function generateNewId()
    {
        return Uuid::uuid4();
    }

    /**
     * @param $filter
     * @return $this
     */
    public function applyFilter($filter)
    {
        return $this;
    }

    /**
     * @param $request
     * @return bool
     */
    public function route($request)
    {
        return false;
    }
}