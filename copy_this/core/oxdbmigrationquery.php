<?php
/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Migration query class. All migration queries must extend this class
 *
 * Migration class filename must match timestamp_classname.php format
 */
abstract class oxDbMigrationQuery extends oxMigrationQuery
{

    /**
     * This method in the subclass must return the list of columns that should be added or removed
     * @return array
     */
    abstract protected function getTables();

    /* TODO add support for indexes
    protected function getIndexes()
    {
        return [];
    }
    */

    /**
     * @var string $sSql the sql string that's being generated
     */
    protected $sSql = "";

    /**
     * @var array $aSql array of sql parts to be combined
     */
    protected $aSql;

    /**
     * @var bool $blUp up or down migration
     */
    protected $blUp = true;

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->alterTables();
    }

    /**
     * iterates over the table definitions and build and execute the sql statements
     */
    protected function alterTables()
    {
        $aTables = $this->getTables();
        $this->sSql = "";
        foreach ($aTables as $sTable => $aTableDef) {
            $this->alterColumns($sTable, $aTableDef['columns']);
            if ($this->sSql != "") {
                $this->sSql = "ALTER TABLE $sTable " . $this->sSql;
            }
            $this->executeSqlStm();
        }
    }

    /**
     * execute a sql statement and rests the sql string
     */
    protected function executeSqlStm()
    {
        $oDb = oxDb::getDb();
        $this->_oOutput->writeLn($this->sSql);
        $oDb->execute($this->sSql);
        $this->sSql = "";
    }

    /**
     * builds the sql to create or drom columns for a table
     * @param string $sTable the name of the table
     * @param array $aColumns the array of columns
     */
    protected function alterColumns($sTable, $aColumns)
    {
        foreach ($aColumns as $aColumnInfo) {
            $this->alterColumn($sTable, $aColumnInfo[0], $aColumnInfo[1]);
        }
        if($this->aSql) {
            $this->sSql = join(',', $this->aSql);
        }
    }

    /**
     * builds the sql to create or drom columns for a table
     * @param string $sTable the name of the table
     * @param string $sColumn the name of the column
     * @param array $sColumnDef the column definition
     */
    protected function alterColumn($sTable, $sColumn, $sColumnDef)
    {

        $blExists = $this->_columnExists($sTable, $sColumn);
        if ($this->blUp) {
            if (!$blExists) {
                $this->aSql[] = "ADD COLUMN `$sColumn` $sColumnDef";
            }
        } else {
            if ($blExists) {
                $this->aSql[] = "DROP COLUMN `$sColumn`";
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->blUp = false;
        $this->alterTables();
    }
}
