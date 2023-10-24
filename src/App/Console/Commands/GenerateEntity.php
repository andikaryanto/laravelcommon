<?php

namespace LaravelCommon\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class GenerateEntity extends Command
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $table = '';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $entityProps = [];

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $className = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $nameSpace = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $entityName = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $ymlName = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $primaryField = '';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $props = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:make:entity
        {table : Table in database}
        {entity : Entity to be created}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create entity from a table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $table = $this->argument('table');
        $entityName = $this->argument('entity');

        $this->setTable($table);
        $this->setEntityName($entityName);
        $this->setYmlName(str_replace(' ', '', ucwords(str_replace('_', ' ', $table))));

        $entityArray = explode('\\', $this->getEntityName());
        $this->setClassName($entityArray[count($entityArray) - 1]);

        array_splice($entityArray, 0, 1);
        array_pop($entityArray);
        $this->setNameSpace(implode("\\", $entityArray));

        $schemInformations = DB::select(DB::raw("DESC $table"));
        foreach ($schemInformations as $schemInformation) {
            if ($schemInformation->Key == 'PRI') {
                $this->setPrimaryField($schemInformation->Field);
            }
            $this->addProp($schemInformation);
        }

        $this->createYmlFile();
        $this->createEntityFile();
    }

    protected function createEntityFile()
    {

        $properties = '';
        foreach ($this->entityProps as $entityProp) {
            $type = $entityProp['type'];
            $property = $entityProp['property'];
            $properties .= "\tprotected ?{$type} $" . $property . " = null;\n";
        }

        $functions = '';
        foreach ($this->entityProps as $entityProp) {
            $property = $entityProp['property'];
            $function = ucfirst($entityProp['property']);
            $type = $entityProp['type'];
            $returnType = $entityProp['nullable'] ? "?" . $entityProp['type'] : $entityProp['type'];
            $functions .= "\t/**\n" .
            "\t * Set $property \n" .
            "\t *\n" .
            "\t * @param $returnType $property\n" .
            "\t * @return self\n" .
            "\t */\n" .
            "\tprotected function set{$function}({$returnType} $" . $property . "): {$this->className}\n" .
            "\t{\n" .
            "\t\t$" . "this->{$property} = $" . $property . ";\n" .
            "\t\treturn $" . "this;\n" .
            "\t}\n\n" .
            "\t/**\n" .
            "\t * Get $property \n" .
            "\t *\n" .
            "\t * @return $returnType\n" .
            "\t */\n" .
            "\tprotected function get{$function}(): {$returnType} \n" .
            "\t{\n" .
            "\t\treturn $" . "this->{$property};\n " .
            "\t}\n\n";
            ;
        }

        $entityFile = "<?php
namespace App\\{$this->getNameSpace()};

use LaravelCommon\App\Models\BaseEntity;

class {$this->getClassName()} extends BaseEntity
{
{$properties}
{$functions}
}
        ";
        $entityPath = app_path() . '\\' . $this->getNameSpace();

        if (!file_exists($entityPath)) {
            mkdir($entityPath . '\\', 777, true);
        }
        file_put_contents(app_path() . '\\' .  $this->getNameSpace() . '\\' . $this->getClassName() . '.php', $entityFile);
    }

    protected function createYmlFile()
    {
        $entityData['table'] = $this->getTable();
        $entityData['primaryKey'] = $this->getPrimaryField();
        $entityData['props'] = $this->props;

        $yml[$this->entityName] = $entityData;
        $yaml = Yaml::dump($yml, 4, 2);

        $entityMappingPath = str_replace('/', '\\', app('config')->get('common-config')['entity']['mapping']['app']);
        $mapPath = explode('\\', $this->getNameSpace());

        array_splice($mapPath, 0, 1);
        $strMapPath = $entityMappingPath . '\\' . implode('', $mapPath) . $this->getClassName();

        file_put_contents($strMapPath  . '.yml', $yaml);
    }

    protected function addProp($schemInformation)
    {

        $keyField = $this->makeField($schemInformation->Field);
        $propData = $this->createProp($schemInformation);

        $this->createEntityProp($schemInformation, $keyField);

        $this->props[$keyField] = $propData;
    }

    protected function createEntityProp($schemInformation, $keyField)
    {
        if (
            $schemInformation->Key != 'PRI' &&
            $schemInformation->Field != 'created_at' &&
            $schemInformation->Field != 'created_by' &&
            $schemInformation->Field != 'updated_at' &&
            $schemInformation->Field != 'updated_by'
        ) {
            $this->entityProps[] = [
                'property' => $keyField,
                'nullable' => $schemInformation->Null == 'YES',
                'type' => $this->getdbColumnType($schemInformation->Type)
            ];
        }
    }

    protected function getdbColumnType(string $dbColumnType)
    {
        $type = '';
        if (strpos($dbColumnType, 'int') !== false) {
            $type = 'int';
        } elseif (strpos($dbColumnType, 'varchar') !== false) {
            $type = 'string';
        } elseif (strpos($dbColumnType, 'tinyint(1)') !== false) {
            $type = 'bool';
        } elseif (strpos($dbColumnType, 'decimal') !== false) {
            $type = 'float';
        } elseif (
            strpos($dbColumnType, 'timestamp') !== false ||
            strpos($dbColumnType, 'datetime') !== false
        ) {
            $type = 'DateTime';
        }
        return $type;
    }

    protected function createProp($schemInformation)
    {
        $propData['field'] = $schemInformation->Field;
        $propData['isEntity'] = false;
        $propData['type'] = $this->getdbColumnType($schemInformation->Type);

        if ($schemInformation->Null == 'NO' && $schemInformation->Key != 'PRI') {
            $propData['rule'] = 'required';
        }

        if (
            (strpos($schemInformation->Type, 'varchar') !== false ||
                strpos($schemInformation->Type, 'char') !== false) &&
            $schemInformation->Key != 'PRI'
        ) {
            $propData['rule'] = isset($propData['rule'])
                ? $propData['rule'] . '|max:' . filter_var($schemInformation->Type, FILTER_SANITIZE_NUMBER_INT)
                : 'max:' . filter_var($schemInformation->Type, FILTER_SANITIZE_NUMBER_INT);
        }

        return $propData;
    }

    protected function makeField(string $field)
    {
        $field = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        $field[0] = strtolower($field[0]);
        return $field;
    }


    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getPrimaryField()
    {
        return $this->primaryField;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $primaryField  Undocumented variable
     *
     * @return  self
     */
    public function setPrimaryField(string $primaryField)
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $table  Undocumented variable
     *
     * @return  self
     */
    public function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getYmlName()
    {
        return $this->ymlName;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $ymlName  Undocumented variable
     *
     * @return  self
     */
    public function setYmlName(string $ymlName)
    {
        $this->ymlName = $ymlName;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $entityName  Undocumented variable
     *
     * @return  self
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $className  Undocumented variable
     *
     * @return  self
     */
    public function setClassName(string $className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getNameSpace()
    {
        return $this->nameSpace;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $nameSpace  Undocumented variable
     *
     * @return  self
     */
    public function setNameSpace(string $nameSpace)
    {
        $this->nameSpace = $nameSpace;

        return $this;
    }
}
