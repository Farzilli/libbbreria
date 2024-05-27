<?php

class QueryBuilder
{
    const DESC = 'DESC'; //used in: orderBy()
    const ASC = 'ASC'; //used in: orderBy()

    const INNER_JOIN = 'INNER JOIN'; //used in: join()
    const LEFT_JOIN = 'LEFT JOIN'; //used in: join()
    const RIGHT_JOIN = 'RIGHT JOIN'; //used in: join()
    const FULL_JOIN = 'FULL JOIN'; //used in: join()
    const AND = 'AND'; //used in: where()
    const OR = 'OR'; //used in: where()

    protected $table;
    protected $columns = '*';
    protected $conditions = [];
    protected $orderBy;
    protected $groupBy;
    protected $joins = [];
    protected $distinct = false;
    protected $logicalOperator = self::AND;
    protected $insertColumns;
    protected $insertValues;
    protected $updateData;

    public function from($tables)
    {
        // ->from([
        //     'TableName1' => ""
        //     'TableName2' => "l"
        // ])
        if (is_array($tables)) {
            $fromClause = [];
            foreach ($tables as $table => $alias) {
                $fromClause[] = !empty($alias) ? "$table AS $alias" : $table;
            }
            $this->table = implode(', ', $fromClause);
        } else {
            $this->table = $tables;
        }
        return $this;
    }

    public function insert($columns)
    {
        // ->insert(["c1", "c2", "c3"])
        $this->insertColumns = $columns;
        return $this;
    }

    public function values(...$valueSets)
    {
        // ->values(["v1", "v2", "v3"], ["v1", "v2", "v3"])
        $this->insertValues = $valueSets;
        return $this;
    }

    public function update($data)
    {
        // ->update([
        //     'column1' => 'value1',
        //     'column2' => 'value2',
        // ])
        $this->updateData = $data;
        return $this;
    }

    public function select($columns)
    {
        // ->select([
        //     "TableName.column" => "",
        //     "TableName.column2" => "p2",
        // ])
        if (is_array($columns)) {
            $selectedColumns = [];
            foreach ($columns as $name => $alias) {
                $selectedColumns[] = !empty($alias) ? "$name AS $alias" : $name;
            }
            $this->columns = implode(', ', $selectedColumns);
        } else {
            $this->columns = $columns;
        }
        return $this;
    }

    public function distinct($distinct = true)
    {
        // ->distinct()
        // ->distinct(true)
        // ->distinct(false)
        $this->distinct = $distinct;
        return $this;
    }

    public function where($conditions, $afterlogicalOperator = self::AND, $logicalOperator = self::AND)
    {
        // ->where(
        //     [
        //         ["TableName.column", "like", "%x%"],
        //         ["TableName.column", "operator(<, >, =,!=)", "n"]
        //     ],
        //     QueryBuilder::AND_OPERATOR,
        //     QueryBuilder::OR_OPERATOR
        // )
        // ->where(
        //     [
        //         ["l.id", ">", "30"],
        //     ],
        //     QueryBuilder::AND_OPERATOR
        // )
        $this->logicalOperator = $logicalOperator;
        if (!is_array($conditions)) die("Conditions must be provided as an array.");

        if (count($conditions) === 1) {
            $condition = $conditions[0];
            if (count($condition) !== 3) die("Each condition must be an array with three elements: [column, operator, value].");
            $this->conditions[] = "{$condition[0]} {$condition[1]} '{$condition[2]}'";
        } else {
            $this->conditions[] = '(';
            foreach ($conditions as $index => $condition) {
                if (count($condition) !== 3) die("Each condition must be an array with three elements: [column, operator, value].");

                $operator = strtoupper($condition[1]) == 'LIKE' ? 'LIKE' : $condition[1];
                $this->conditions[] = "{$condition[0]} $operator '{$condition[2]}'";

                if (count($conditions) - 1 != $index) $this->conditions[] = " {$logicalOperator} ";
            }
            $this->conditions[] = ')';
        }

        $this->conditions[] = " {$afterlogicalOperator} ";
        return $this;
    }

    public function orderBy($column, $direction = self::ASC)
    {
        // ->orderBy("TableName.column", QueryBuilder::ASC)
        // ->orderBy("TableName.column", QueryBuilder::DESC)
        if (
            $direction !== self::ASC &&
            $direction !== self::DESC
        ) die("orderBy works with ASC/DESC, {$direction} does not exist!");

        $this->orderBy = [$column, $direction];
        return $this;
    }

    public function groupBy($columns)
    {
        // ->groupBy("TableName.column")
        // ->groupBy(["TableName.column", "TableName.column2"])
        $this->groupBy = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function join($table, $firstColumn, $operator, $secondColumn, $type = self::INNER_JOIN)
    {
        // ->join("TableNameN", "TableName.column", '=', "TableName2.column") default is INNER_JOIN
        // ->join("TableNameN", "TableName.column", '=', "TableName2.column", "INNER_JOIN")
        // ->join("TableNameN", "TableName.column", '=', "TableName2.column", "LEFT_JOIN")
        // ->join("TableNameN", "TableName.column", '=', "TableName2.column", "RIGHT_JOIN")
        // ->join("TableNameN", "TableName.column", '=', "TableName2.column", "FULL_JOIN")
        if (
            $type !== self::INNER_JOIN &&
            $type !== self::LEFT_JOIN &&
            $type !== self::RIGHT_JOIN &&
            $type !== self::FULL_JOIN
        ) die("join works with: INNER_JOIN/LEFT_JOIN/RIGHT_JOIN/FULL_JOIN, {$type} does not exist!");

        $this->joins[] = [$type, $table, $firstColumn, $operator, $secondColumn];
        return $this;
    }

    public function sum($column, $asName = "total")
    {
        // ->sum("TableName.column")
        // ->sum("TableName.column", "custom as name")
        $this->columns = "SUM({$column}) AS {$asName}";
        return $this;
    }

    public function count($column, $asName = "count")
    {
        // ->count("TableName.column")
        // ->count("TableName.column", "custom as name")
        $this->columns = "COUNT({$column}) AS {$asName}";
        return $this;
    }

    public function delete()
    {
        if (empty($this->conditions)) {
            die("Conditions are required for deletion.");
        }
        $query = "DELETE FROM {$this->table}";
        $query .= " WHERE " . implode(array_slice($this->conditions, 0, count($this->conditions) - 1));
        return $query . ";";
    }

    public function build()
    {
        if (empty($this->table)) {
            die("Table name is required.");
        }

        if (!empty($this->insertColumns) && !empty($this->insertValues)) {
            $columnsStr = implode(', ', $this->insertColumns);
            $valuesStr = [];
            foreach ($this->insertValues as $valueSet) {
                $valuesStr[] = '(' . implode(', ', array_map(function ($value) {
                    return "'$value'";
                }, $valueSet)) . ')';
            }
            $query = "INSERT INTO {$this->table} ({$columnsStr}) VALUES " . implode(', ', $valuesStr) . ";";
            return $query;
        }

        if (!empty($this->updateData)) {
            $updateValues = [];
            foreach ($this->updateData as $column => $value) {
                $updateValues[] = "$column = '$value'";
            }
            $query = "UPDATE {$this->table} SET " . implode(', ', $updateValues);
            if (!empty($this->conditions)) {
                $query .= " WHERE " . implode(array_slice($this->conditions, 0, count($this->conditions) - 1));
            }
            return $query . ";";
        }

        $query = $this->distinct ? "SELECT DISTINCT {$this->columns} FROM {$this->table}" : "SELECT {$this->columns} FROM {$this->table}";

        // Join clauses
        foreach ($this->joins as $join) {
            list($type, $table, $firstColumn, $operator, $secondColumn) = $join;
            $query .= " $type $table ON $firstColumn $operator $secondColumn";
        }

        // Where clauses
        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(array_slice($this->conditions, 0, count($this->conditions) - 1));
        }

        // Group by clause
        if (!is_null($this->groupBy)) {
            $query .= " GROUP BY {$this->groupBy}";
        }

        // Order by clause
        if (!is_null($this->orderBy)) {
            $query .= " ORDER BY {$this->orderBy[0]} {$this->orderBy[1]}";
        }

        return $query . ";";
    }
}
