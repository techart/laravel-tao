<?php

namespace TAO\Fields;

use Illuminate\Database\Query\Builder;
use Ramsey\Uuid\UuidInterface;
use TAO\Fields;
use Ramsey\Uuid\Uuid;
use TAO\Selector;
use TAO\Type\Collection;

/**
 * Class Model
 * @package TAO\Fields
 *
 * @method orderBy(string $column, string $direction = 'asc')
 * @method \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null find(mixed $id, array $columns)
 * @method $this where(string|array|\Closure $column, string $operator = null, mixed $value = null, string $boolean = 'and')
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
        $this->setConnection(app()->tao->connectionNameFor($this->getTable()));
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
     * @param bool $forceType
     * @return Field
     * @throws Exception\UndefinedField
     */
    public function field($name, $forceType = false)
    {
        if ($forceType) {
            $fields = $this->processedFields();
            if (!isset($fields[$name])) {
                throw new Fields\Exception\UndefinedField($name, get_class($this));
            }
            $data = $fields[$name];
            $data['type'] = $forceType;
            return app()->taoFields->create($name, $data, $this);
        }
        if (!isset($this->fields[$name])) {
            $fields = $this->processedFields();
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
     * @return UuidInterface
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
     * @param $builder
     * @param $filter
     * @return $this
     */
    public function applyFilter($builder, $filter)
    {
        foreach ($filter as $name => $value) {
            if (!empty($value)) {
                $method = 'applyFilter'.camel_case($name);
                if (method_exists($this, $method)) {
                    $this->$method($builder, $value);
                }
            }
        }
        return $builder;
    }

    /**
     * @return Builder
     */
    public function ordered()
    {
        return $this->orderBy($this->getKeyName());
    }

    /**
     * @param bool $args
     * @return array
     */
    public function itemsForSelect($args = false)
    {
        $args = Collection::parseString($args);
        if ($this->isTree) {
            return $this->treeForSelect($args);
        }
        $out = Collection::numericKeysOnly($args);
        foreach ($this->ordered()->get() as $row) {
            $out[$row->getKey()] = $row->title();
        }
        return $out;
    }

    /**
     * @param array $args
     * @return array
     */
    public function treeForSelect($args = [])
    {
        $args = Collection::parseString($args);
        $tree = $this->buildTree($args);

        $out = Collection::numericKeysOnly($args);
        $this->buildTreeForSelect($tree, '-&nbsp;&nbsp;&nbsp;', $out);
        return $out;
    }

    /**
     * @param Model[] $tree
     * @param string $prefix
     * @param array $out
     */
    protected function buildTreeForSelect(&$tree, $prefix, &$out)
    {
        foreach ($tree as $key => $row) {
            $out[$key] = $prefix . $row->title();
            $this->buildTreeForSelect($row->childs, $prefix . '-&nbsp;&nbsp;&nbsp;', $out);
        }
    }

    /**
     * @param array $filter
     * @return Model[]
     */
    public function buildTree($filter = [])
    {
        $root = isset($filter['root']) ? $filter['root'] : 0;
        $maxDepth = isset($filter['max_depth']) ? $filter['max_depth'] : 10000;
        return $this->buildTreeFromRows($this->get()->getDictionary(), $root, $maxDepth);
    }

    /**
     * @param Builder $query
     * @param int $root
     * @param int $maxDepth
     * @return array
     */
    public function buildTreeFromQuery($query, $root = 0, $maxDepth = 10000)
    {
        return $this->buildTreeFromRows($query->get(), $root, $maxDepth);
    }

    /**
     * @param \Illuminate\Support\Collection|Model[] $rows
     * @param int $root
     * @param int $maxDepth
     * @return array
     */
    public function buildTreeFromRows($rows, $root = 0, $maxDepth = 10000)
    {
        return $this->buildTreeBranch($rows, $root, $maxDepth);
    }


    /**
     * @param \Illuminate\Support\Collection|Model[] $rows $rows
     * @param int $root
     * @param int $maxDepth
     * @return Model[]
     */
    public function buildTreeBranch($rows, $root, $maxDepth = 10000)
    {
        $out = [];
        $first = true;
        $prev = false;
        foreach ($rows as $key => $row) {
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

                if ($maxDepth>0) {
                    $row->childs = $this->buildTreeBranch($rows, $key,$maxDepth-1);
                }
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
            $p1 = str_pad(floor($id / 1000), 4, '0', STR_PAD_LEFT);
            $p2 = str_pad($id, 8, '0', STR_PAD_LEFT);
        } else {
            $p1 = substr($id, 0, 2);
            $p2 = $id;
        }
        return 'datatypes/' . $this->getDatatype() . "/{$p1}/{$p2}";
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
        return $item->isactive ? $item : null;
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
        $selector = app()->make(Selector::class);
        $selector->mnemocode = $this->getDatatype();
        $selector->datatype = $this;
        $selector->title = $this->typeTitle();
        return $selector;
    }

    public function validateField($name)
    {
        $cname = ucfirst(camel_case($name));
        $method = "validateField{$cname}";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->field($name)->validate();
    }

    public function validate()
    {
        foreach ($this->calculatedFields() as $name => $data) {
            $v = $this->validateField($name);
            if (is_string($v)) {
                $this->error($v, $name);
            }
        }
    }

    public function validateForAdmin()
    {
        return $this->validate();
    }
    
    public function __call($method, $args)
    {
        if ($m = \TAO::regexp('{^(.+)_belongs_to_many$}', $method)) {
            $field = $m[1];
            return $this->field($field)->belongsToMany();
        }
        return parent::__call($method, $args);
    }

}