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
        Fields\Utils\Model\Admin,
        Fields\Utils\Model\Urls,
        Fields\Utils\Model\View;

    /**
     * @var bool
     */
    public $incrementing = false;
    /**
     * @var array
     */
    public $childs = [];

    /**
     * @var bool
     */
    public $isTree = false;

    public $isFirstBranch = false;

    public $isLastBranch = false;

    public $prevBranch = false;

    public $nextBranch = false;

    public $typeTitle = false;

    /**
     * @var string
     */
    protected $parentKeyField = 'parent_id';

    /**
     * @var string
     */
    protected $idType = 'auto_increment';

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
        if (!$this->typeTitle) {
            $this->typeTitle = get_class($this);
        }
    }

    /**
     * @return mixed
     */
    public function getDatatype()
    {
        return \TAO::datatypeCodeByClass(get_class($this));
    }

    public function getDatatypeObject()
    {
        return \TAO::datatype($this->getDatatype());
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

    public function title()
    {
        return isset($this->attributes['title']) ? $this->attributes['title'] : $this->getKey();
    }

    /**
     * @param $filter
     * @return $this
     */
    public function applyFilter($filter)
    {
        return $this;
    }

    public function ordered()
    {
        return $this->orderBy($this->getKeyName());
    }

    /**
     * @return array
     */
    public function allRows()
    {
        $out = [];
        foreach ($this->ordered()->get() as $row) {
            $out[$row->getKey()] = $row;
        }
        return $out;
    }

    /**
     * @param bool $args
     * @return array
     */
    public function itemsForSelect($args = false)
    {
        if ($this->isTree) {
            return $this->treeForSelect($args);
        }
        $out = [];
        if (is_string($args) && $m = \TAO::regexp('{(.+)=(.+)}', $args)) {
            $out[$m[1]] = $m[2];
        }
        foreach ($this->allRows() as $row) {
            $out[$row->getKey()] = $row->title();
        }
        return $out;
    }

    /**
     * @param bool $args
     * @return array
     */
    public function treeForSelect($args = false)
    {

        $tree = $this->buildTree();

        if (is_string($args) && $m = \TAO::regexp('{(.+)=(.+)}', $args)) {
            $out = [$m[1] => $m[2]];
        }
        $this->buildTreeForSelect($tree, '-&nbsp;&nbsp;&nbsp;', $out);
        return $out;
    }

    /**
     * @param $tree
     * @param $prefix
     * @param $out
     */
    protected function buildTreeForSelect(&$tree, $prefix, &$out)
    {
        foreach ($tree as $key => $row) {
            $out[$key] = $prefix . $row->title();
            $this->buildTreeForSelect($row->childs, $prefix . '-&nbsp;&nbsp;&nbsp;', $out);
        }
    }

    /**
     * @return array
     */
    public function buildTree()
    {
        $rows = $this->allRows();
        return $this->buildTreeBranch(0, $rows);
    }

    /**
     * @param $root
     * @param $src
     * @return array
     */
    public function buildTreeBranch($root, &$src)
    {
        $out = [];
        $first = true;
        $prev = false;
        foreach ($src as $key => $row) {
            $pid = $row[$this->parentKeyField];
            if ($row[$this->parentKeyField] == $root) {
                if ($first) {
                    $row->isFirstBranch = true;
                    $first = false;
                }
                if ($prev) {
                    $row->prevBranch = $prev;
                    $prev->nextBranch = $row;
                    $prev->isLastBranch = false;
                }

                $row->childs = $this->buildTreeBranch($key, $src);
                $out[$key] = $row;
                $prev = $row;
                $row->isLastBranch = true;
            }
        }
        return $out;
    }

    protected function getHomeSubDir()
    {
        $id = $this->getKey();
        if (empty($id)) {
            return false;
        }
        if (is_int($id)) {
            $p1 = str_pad(floor($id/1000), 4, '0', STR_PAD_LEFT);
            $p2 = str_pad($id, 8, '0', STR_PAD_LEFT);
        } else {
            $p1 = substr($id, 0, 2);
            $p2 = $id;
        }
        return 'datatypes/'.$this->getDatatype()."/{$p1}/{$p2}";
    }

    public function getHomeDir()
    {
        $sub = $this->getHomeSubDir();
        if (!$sub) {
            return false;
        }
        $dir = "public/{$sub}";
        if (!\Storage::exists($dir)) {
            \Storage::makeDirectory($dir);
        }
        return $dir;
    }

    public function getPrivateHomeDir()
    {
        $dir = $this->getHomeSubDir();
        if (!\Storage::exists($dir)) {
            \Storage::makeDirectory($dir);
        }
        return $dir;
    }

    public function getItemByUrl($url)
    {
        return $this->where('url', $url)->where('isactive', 1);
    }

    public function getItemById($id)
    {
        $item = $this->find($id);
        return $item->isactive? $item : null;
    }

    public function getItems($data = [])
    {
        return $this->ordered()->where('isactive', 1);
    }

    public function typeTitle()
    {
        return $this->typeTitle;
    }

    public function selector()
    {
        $selector = app()->make(\TAO\Selector::class);
        $selector->mnemocode = $this->getDatatype();
        $selector->datatype = $this;
        $selector->title = $this->typeTitle();
        return $selector;
    }

}