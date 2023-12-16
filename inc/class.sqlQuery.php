<?php

/**
 * @package sqlQuery
 * @subpackage mysql
 * @author pedro-azeredo <pedro.azeredo93@gmail.com>
 */

class sqlQuery
{
    private $select;
    private $from;
    private $where;
    private $order;
    private $limit;
    private $other;
    private $joins;
    private $groupby;
    private $selectName;

    /**
     * addTable function
     * função responsável por incluir tabelas na query
     *
     * @param string $table
     * @param string $sigla
     * @return void
     */
    public function addTable(string $table, string $sigla = null)
    {
        if (!$sigla) {
            $this->from[] = $table;
        } else {
            $this->from[] = $table . ' AS ' . $sigla;
        }
    }

    /**
     * addcolumn function
     * função responsável por incluir colunas na query
     *
     * @param string $coluna
     * @param string $sigla
     * @return void
     */
    public function addcolumn(string $coluna, string $sigla = null)
    {
        if (!$sigla) {
            $this->select[] = $coluna;
        } else {
            $this->select[] = $coluna . ' AS ' . $sigla;
        }

        $this->selectName[] = $coluna;
    }

    /**
     * addJoin function
     * função responsável por incluir joins na query
     *
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return void
     */
    public function addJoin(string $table, string $condition, string $type = 'INNER')
    {
        $this->joins[] = $type . ' JOIN ' . $table . ' ON ' . $condition;
    }

    /**
     * addWhere function
     * função responsável por incluir novas condições na query
     *
     * @param string $first
     * @param string $condition
     * @param string $second
     * @return void
     */
    public function addWhere(string $first, string $condition = null, string $second = null)
    {
        if (!$second) {
            $this->where[] = $first . ' ' . $condition;
        } else {
            $this->where[] = $first . ' ' . $condition . ' ' . $second;
        }
    }

    /**
     * addOrder function
     * função responsável por criar a ordenação da query
     *
     * @param string $by
     * @param string $order
     * @return void
     */
    public function addOrder(string $by, string $order = null)
    {
        if (!$order) {
            $this->order[] = $by . ' ASC';
        } else {
            $this->order[] = $by . ' ' . $order;
        }
    }

    /**
     * addGroupBy function
     * função responsável por criar agrupamentos da query
     *
     * @param string $by
     * @param string $order
     * @return void
     */
    public function addGroupBy(string $by)
    {
        $this->groupby[] = $by;
    }

    /**
     * setLimit function
     *
     * @param integer $qtd
     * @param integer $start
     * @return void
     */
    public function setLimit(int $qtd, int $start = 0)
    {
        $this->limit = ' LIMIT ' . $start . ', ' . $qtd;
    }

    /**
     * getCount function
     * função responsável por retornar o COUNT da query SEM LIMIT
     *
     *
     */
    public function getCount()
    {
        $out = ' SELECT COUNT(*) as QTD';
        $out .= ' FROM ';
        $out .= implode(', ', $this->from);

        $f_searchTableBuscar = getParam('f_searchTableBuscar');
        $f_searchTablePor = getParam('f_searchTablePor');

        if ($f_searchTableBuscar && $f_searchTablePor) {
            $searchField = $this->selectName[$f_searchTablePor - 1];
            $searchValue = str_replace(['(', ')', '-', '.', '/', ' '], '', $f_searchTableBuscar);
            $this->addWhere('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(' . $searchField . ", '(', ''), ')', ''), '-', ''), '.', ''), ' ', ''), '/', '')", 'like', "'%" . $searchValue . "%'");
        }

        if ($this->where) {
            $out .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if ($this->groupby) {
            $out .= ' GROUP BY ' . implode(' , ', $this->groupby);
        }

        return $out;
    }

    /**
     * getSQL function
     * função para montar e retornar a query
     *
     * @param bool $pre
     * @return string
     */
    public function getSQL(bool $pre = false): string
    {
        $out = ' SELECT ';
        $out .= implode(', ', $this->select);
        $out .= ' FROM ';
        $out .= implode(', ', $this->from);

        if ($this->joins) {
            $out .= ' ' . implode(' ', $this->joins);
        }

        $getE = getParam('e', true);
        $fOrder = $getE['fOrder'];
        $fOrderBy = $getE['fOrderBy'];
        if ($fOrderBy) {
            $this->addOrder($fOrderBy, $fOrder);
        }

        $f_searchTableBuscar = getParam('f_searchTableBuscar');
        $f_searchTablePor = getParam('f_searchTablePor');

        if ($f_searchTableBuscar && $f_searchTablePor) {
            $searchField = $this->selectName[$f_searchTablePor - 1];
            $searchValue = str_replace(['(', ')', '-', '.', '/', ' '], '', $f_searchTableBuscar);
            $this->addWhere('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(' . $searchField . ", '(', ''), ')', ''), '-', ''), '.', ''), ' ', ''), '/', '')", 'like', "'%" . $searchValue . "%'");
        }

        if ($this->where) {
            $out .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if ($this->groupby) {
            $out .= ' GROUP BY ' . implode(' , ', $this->groupby);
        }

        if ($this->order != '' && COUNT($this->order) > 0) {
            $out .= ' ORDER BY ';
            $out .= implode(', ', $this->order);
        }

        if ($this->limit) {
            $out .= $this->limit;
        }

        if ($pre) {
            return '<pre>' . $out . '</pre>';
        }

        return $out;
    }
}
