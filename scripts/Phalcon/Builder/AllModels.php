<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Builder;

use Phalcon\Utils;
use Phalcon\Script\Color;

/**
 * AllModels Class
 *
 * Builder to generate all models
 *
 * @package Phalcon\Builder
 */
class AllModels extends Component
{
    public $exist = [];

    /**
     * Create Builder object
     *
     * @param array $options Builder options
     * @throws BuilderException
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['force'])) {
            $options['force'] = false;
        }

        if (!isset($options['abstract'])) {
            $options['abstract'] = false;
        }

        parent::__construct($options);
    }

    public function build()
    {
        if ($this->options->offsetExists('directory')) {
            $this->path->setRootPath($this->options->get('directory'));
        }

        $this->options->offsetSet('directory', $this->path->getRootPath());

        if (gettype($this->options->get('config')) == 'object') {
            $config = $this->options->get('config');
        } else {
            $config = $this->getConfig();
        }

        if (!$modelsDir = $this->options->get('modelsDir')) {
            if (!isset($config->application->modelsDir)) {
                throw new BuilderException("Builder doesn't know where is the models directory.");
            }
            $modelsDir = $config->application->modelsDir;
        }

        $modelsDir = rtrim($modelsDir, '/\\') . DIRECTORY_SEPARATOR;
        $modelPath = $modelsDir;
        if (false == $this->isAbsolutePath($modelsDir)) {
            $modelPath = $this->path->getRootPath($modelsDir);
        }

        $this->options->offsetSet('modelsDir', $modelPath);

        $forceProcess = $this->options->get('force');

        $defineRelations = $this->options->get('defineRelations', false);
        $defineForeignKeys = $this->options->get('foreignKeys', false);
        $genSettersGetters = $this->options->get('genSettersGetters', false);
        $mapColumn = $this->options->get('mapColumn', false);

        $adapter = $config->database->adapter;
        $this->isSupportedAdapter($adapter);

        $adapter = 'Mysql';
        if (isset($config->database->adapter)) {
            $adapter = $config->database->adapter;
        }

        if (is_object($config->database)) {
            $configArray = $config->database->toArray();
        } else {
            $configArray = $config->database;
        }

        $adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        unset($configArray['adapter']);

        /**
         * @var \Phalcon\Db\Adapter\Pdo\AbstractPdo $db
         */
        $db = new $adapterName($configArray);

        if ($this->options->has('schema')) {
            $schema = $this->options->get('schema');
        } else {
            $schema = Utils::resolveDbSchema($config->database);
        }

        $hasMany = [];
        $belongsTo = [];
        $foreignKeys = [];
        $referenceList = [];
        if ($defineRelations || $defineForeignKeys) {
            foreach ($db->listTables($schema) as $name) {
                if ($defineRelations) {
                    if (!isset($hasMany[$name])) {
                        $hasMany[$name] = [];
                    }
                    if (!isset($belongsTo[$name])) {
                        $belongsTo[$name] = [];
                    }
                }
                if ($defineForeignKeys) {
                    $foreignKeys[$name] = [];
                }

                $camelCaseName = Utils::camelize($name);
                $refSchema = ($adapter != 'Postgresql') ? $schema : $config->database->dbname;
                $referenceList[$name] = $db->describeReferences($name, $schema);

                foreach ($referenceList[$name] as $reference) {
                    $columns = $reference->getColumns();
                    $referencedColumns = $reference->getReferencedColumns();
                    $referencedModel = Utils::camelize($reference->getReferencedTable());
                    if ($defineRelations) {
                        if ($reference->getReferencedSchema() == $refSchema) {
                            if (count($columns) == 1) {
                                $belongsTo[$name][] = [
                                    'referencedModel' => $referencedModel,
                                    'fields' => $columns[0],
                                    'relationFields' => $referencedColumns[0],
                                    'options' => $defineForeignKeys ? ['foreignKey'=>true] : null
                                ];
                                $hasMany[$reference->getReferencedTable()][] = [
                                    'camelizedName' => $camelCaseName,
                                    'fields' => $referencedColumns[0],
                                    'relationFields' => $columns[0]
                                ];
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($db->listTables($schema) as $name) {
                if ($defineRelations) {
                    $hasMany[$name] = [];
                    $belongsTo[$name] = [];
                    $foreignKeys[$name] = [];
                }
                $referenceList[$name] = $db->describeReferences($name, $schema);
            }
        }

        foreach ($db->listTables($schema) as $name) {
            $className = ($this->options->has('abstract') ? 'Abstract' : '');
            $className .= Utils::camelize($name);

            if (!file_exists($modelPath . $className . '.php') || $forceProcess) {
                if (isset($hasMany[$name])) {
                    $hasManyModel = $hasMany[$name];
                } else {
                    $hasManyModel = [];
                }

                if (isset($belongsTo[$name])) {
                    $belongsToModel = $belongsTo[$name];
                } else {
                    $belongsToModel = [];
                }

                if (isset($foreignKeys[$name])) {
                    $foreignKeysModel = $foreignKeys[$name];
                } else {
                    $foreignKeysModel = [];
                }

                $modelBuilder = new Model([
                    'name' => $name,
                    'config' => $config,
                    'schema' => $schema,
                    'extends' => $this->options->get('extends'),
                    'namespace' => $this->options->get('namespace'),
                    'force' => $forceProcess,
                    'hasMany' => $hasManyModel,
                    'belongsTo' => $belongsToModel,
                    'foreignKeys' => $foreignKeysModel,
                    'genSettersGetters' => $genSettersGetters,
                    'genDocMethods' => $this->options->get('genDocMethods'),
                    'directory' => $this->options->get('directory'),
                    'modelsDir' => $this->options->get('modelsDir'),
                    'mapColumn' => $mapColumn,
                    'abstract' => $this->options->get('abstract'),
                    'referenceList' => $referenceList,
                    'camelize' => $this->options->get('camelize'),
                    'annotate' => $this->options->get('annotate'),
                ]);

                $modelBuilder->build();
            } else {
                if ($this->isConsole()) {
                    print Color::info(sprintf('Skipping model "%s" because it already exist', Utils::camelize($name)));
                } else {
                    $this->exist[] = $name;
                }
            }
        }
    }
}
