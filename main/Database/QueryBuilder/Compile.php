<?php

namespace Main\Database\QueryBuilder;

use Main\Eloquent\Model;
use Main\Http\Exceptions\AppException;

class Compile
{
    public function compileSelect($distinct)
    {
        try {
            return $distinct ? "SELECT DISTINCT " : "SELECT ";
        } catch (AppException $e) {
            throw new AppException($e->getMessage());
        }
    }

    public function compileColumns($columns)
    {
        try {
            return is_array($columns) ? implode(', ', $columns) : '*';
        } catch (AppException $e) {
            throw new AppException($e->getMessage());
        }
    }

    public function compileFrom($table)
    {
        return " FROM {$table}";
    }

    public function compileJoins(array $joins)
    {
        foreach ($joins as $join) {
            switch (strtolower($join[4])) {
                case 'inner':
                    $sql = ' INNER JOIN';
                    break;
                case 'left':
                    $sql = ' LEFT JOIN';
                    break;
                case 'right':
                    $sql = ' RIGHT JOIN';
                    break;
                default:
                    $sql = ' INNER JOIN';
                    break;
            }
            $sql .= " {$join[0]} ON {$join[1]} {$join[2]} {$join[3]}";
        }
        return $sql;
    }

    public function compileWheres(array $wheres)
    {
        $sql = " WHERE";
        foreach ($wheres as $key => $where) {
            if ($key == 0) {
                if ($where[0] == 'start_where') {
                    $sql .= ' (';
                }
                if ($where[0] == 'start_or') {
                    $sql .= ' (';
                }
            } else {
                if ($where[0] == 'start_where') {
                    $sql .= ' AND (';
                }
                if ($where[0] == 'start_or') {
                    $sql .= ' OR (';
                }
            }

            if ($where[0] == 'end_where' || $where[0] == 'end_or') {
                $sql .= ') ';
            }
            if ($key == 0) {
                if ($where[0] !== 'start_where' && $where[0] !== 'end_where') {
                    $sql .= " {$where[0]} {$where[1]} ?";
                }
            } else {
                if ($wheres[$key - 1][0] !== 'start_where') {
                    if ($where[0] !== 'start_where' && $where[0] !== 'end_where' && $where[0] !== 'start_or' && $where[0] !== 'end_or') {
                        if ($wheres[$key - 1][0] !== 'start_or') {
                            $sql .= " $where[3] $where[0] $where[1] ?";
                        } else {
                            $sql .= " $where[0] $where[1] ?";
                        }
                    }
                } else {
                    if ($where[0] !== 'start_where' && $where[0] !== 'end_where' && $key != 0) {
                        $sql .= "$where[0] $where[1] ?";
                    }
                }
            }
        }
        return $sql;
    }

    public function compileGroups($groups)
    {
        $sql = " GROUP BY " . implode(', ', $groups);
        return $sql;
    }

    public function compileHavings(array $havings)
    {
        $sql = " HAVING";
        foreach ($havings as $key => $having) {
            $sql .= " $having[0] $having[1] $having[2]";
            if ($key < count($havings) - 1) {
                $sql .= (strtolower($having[3] === 'and') ? ' AND' : ' OR');
            }
        }
        return $sql;
    }

    public function compileOrders(array $orders)
    {
        $sql = " ORDER BY ";
        foreach ($orders as $key => $order) {
            $sql .= "$order[0] $order[1]";
            if ($key < count($orders) - 1) {
                $sql .= ", ";
            }
        }
        return $sql;
    }

    public function compileLimit($limit)
    {
        return ' LIMIT ' . (int) $limit;
    }

    public function compileOffset($offset)
    {
        return " OFFSET {$offset}";
    }

    public function compileWhereIn($wherein)
    {
        $sql = " WHERE {$wherein[0]} IN ";
        $array = explode(", ", $wherein[1]);
        foreach ($array as $key => $arr) {
            if ($key + 1 == count($array)) {
                $array[$key] = "'{$arr}'";
            } else {
                $array[$key] = "'{$arr}',";
            }
        }
        $string = implode("", $array);
        return " WHERE {$wherein[0]} IN ({$string})";
    }

    public function compileInsert($table, array $data)
    {
        foreach ($data as $key => $value) {
            $columns[] = $key;
            $values[] = "'$value'";
        }
        $columns = implode(', ', $columns);
        $values = implode(', ', $values);

        return "INSERT INTO $table($columns) VALUES ($values)";
    }

    public function compileCreate(Model $model, array $fillable, array $data)
    {
        try {
            foreach ($fillable as $column) {
                if (isset($data[$column])) {
                    $ucFirst = ucfirst($column);
                    $settingMethod = "set{$ucFirst}Attribute";
                    if (method_exists($model, $settingMethod)) {
                        $values[] = "'".call_user_func([$model, $settingMethod], $data[$column]) . "'";
                    } else {
                        $values[] = "'$data[$column]'";
                    }
                    $columns[] = $column;
                }
            }
            $columns = implode(', ', $columns);
            $values = implode(', ', $values);
            $table = $model->table();
            return "INSERT INTO $table($columns) VALUES ($values)";
        } catch (AppException $e) {
            throw new AppException($e->getMessage());
        }
    }

    public function compileLogin($table, array $cre)
    {
        foreach ($cre as $key => $dt) {
            $columns[] = $key;
            $values[] = "'$dt'";
        }
        return "SELECT * FROM {$table} WHERE {$columns[0]} = {$values[0]} AND {$columns[1]} = {$values[1]} LIMIT 1";
    }

    public function compileDelete($table)
    {
        return "DELETE FROM {$table}";
    }

    public function compileUpdate($table, array $arg)
    {
        $sql = "UPDATE {$table} SET ";
        foreach ($arg as $key => $dt) {
            $sql .= "$key = '$dt', ";
        }
        $lenght = strlen($sql);
        $sql = substr($sql, 0, $lenght - 2);
        return $sql;
    }
}
