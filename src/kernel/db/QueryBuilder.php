<?php

namespace import\kernel\db;

use yii\db\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    public function insertDuplicate(string $table, array $columns, array $row): string
    {
        $sql = $this->batchInsert($table, $columns, $row);
        $sql .= ' ON DUPLICATE KEY UPDATE ';

        $schema = $this->db->getSchema();
        $last   = end($columns);

        foreach ($columns as $i => $column) {
            $columns[$i] = $schema->quoteColumnName($column);
            $sql         .= $schema->quoteColumnName($column) . ' = VALUES(' . $schema->quoteColumnName($column) . ')';

            if ($column !== $last) {
                $sql .= ', ';
            }
        }

        return $sql;
    }
}


