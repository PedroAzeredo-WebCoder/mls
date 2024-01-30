<?php

/**
 * @package table
 * @subpackage class_base
 * @author pedro-azeredo <pedro.azeredo93@gmail.com>
 */

class Table
{
    private $cardHeader;
    private $header = [];
    private $column = [];
    private $searchBy;
    private $count;
    private $tableStatus = [
        ['id' => '1', 'name' => 'Status Ativo'],
        ['id' => '0', 'name' => 'Status Inativo'],
    ];

    /**
     *  addHeader
     * Responsável por incluir novas colunas do tipo header
     *
     * @param string $text
     * @param string $align
     * @param integer $size
     * @param bool $order
     * @return void
     */
    public function addHeader(string $text, string $align = null, string $size = null, bool $ordem = true)
    {
        $getE = getParam('e', true);
        $fOrder = $getE['fOrder'];
        $fOrderBy = $getE['fOrderBy'];
        if (!$fOrder) {
            $fOrder = 'ASC';
        } else {
            if ($fOrderBy == COUNT($this->header) + 1) {
                if ($fOrder == 'ASC') {
                    $fOrder = 'DESC';
                } else {
                    $fOrder = 'ASC';
                }
            } else {
                $fOrder = 'ASC';
            }
        }
        $fOrderBy = COUNT($this->header) + 1;
        $b = 'fOrder=' . $fOrder . '&fOrderBy=' . $fOrderBy;
        $e = strrev(base64_encode(strrev($b)));

        if (!$ordem) {
            $this->header[] = "
                    <th class='" . $align . ' ' . $size . "'>
                        " . $text . '
                    </th>
                ';
        } else {
            $this->searchBy[] = ['id' => $fOrderBy, 'name' => $text];
            $this->header[] = "
                    <th class='" . $align . ' ' . $size . "'>
                        <a href='" . QuemSou() . '?e=' . $e . "'>
                            " . $text . '
                        </a>
                    </th>
                ';
        }
    }

    public function getHeaders()
    {
        return $this->header;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    private $trOffset = 200;
    private $firstRowCreated = false;
    private $rowDelay = 200;

    /**
     * addCol
     * Responsável por incluir novas colunas do tipo body
     *
     * @param string $text
     * @param [type] $align
     * @param [type] $colspam
     * @return void
     */
    public function addCol($text, string $align = null, string $colspan = null)
    {
        if (!$this->firstRowCreated) {
            // Se a primeira linha ainda não foi criada, chame a função para criá-la
            $this->createFirstRow();
        }

        if ($colspan) {
            $_colspan = "colspan='" . $colspan . "'";
        }
        // Cada célula deve ser tratada como um item separado no array de colunas
        $this->column[] = "<td class='" . $align . "' " . $_colspan . '>' . $text . '</td>';
    }
    /**
     * endRow
     * Finaliza uma ROW
     *
     * @return void
     */
    public function endRow()
    {
        // Monta o HTML da linha (tr) de fechamento
        $this->column[] = '</tr>';
        // Define que a próxima linha ainda não foi criada
        $this->firstRowCreated = false;
    }

    /**
     * cardHeader
     * Cria um header na tabela com conteúdo informado
     *
     * @param string $string
     * @return void
     */
    public function cardHeader(string $string)
    {
        $this->cardHeader[] = $string;
    }

    public function setTableStatus($newTableStatus)
    {
        $this->tableStatus = $newTableStatus;
    }

    /**
     * writeHtml
     * Imprimir HTML montado após conclusão das definições do template
     *
     * @return string
     */
    public function writeHtml($form = null)
    {
        $cardHeader = '';
        $optionsBuscarPor = [];

        $tableStatus = $this->tableStatus;


        setcookie('filter_status', getParam('f_searchTableStatus'), time() + (86400 * 5), '/');

        $status = "";
        if (!empty($tableStatus)) {
            $status = "<div class='col-auto mt-1'>" . listField(null, $tableStatus, getParam('f_searchTableStatus'), 'f_searchTableStatus', true) . "</div>";
        }

        $f_searchTableBuscar = getParam('f_searchTableBuscar');
        $f_searchTablePor = getParam('f_searchTablePor');

        $resultCountValue = $this->count ?? 0;
        $resultCountHtml = "<small class='fw-bolder'>Resultados (" . $resultCountValue . ')</small>';

        if ($form == null) {
            $this->cardHeader("
                <form action='#' method='POST' name='searchTable' id='searchTable' class='row align-tems-center'>
                        " . $status . "
                    <div class='col-auto mt-1'>
                        " . textField(null, !empty($f_searchTableBuscar) ? $f_searchTableBuscar : '', 'f_searchTableBuscar') . "
                    </div>
                    <div class='col-auto mt-1'>
                        " . listField(null, $this->searchBy, !empty($f_searchTablePor) ? $f_searchTablePor : '', 'f_searchTablePor') . "
                    </div>
                    <div class='col-auto mt-1'>
                        " . submitBtn('Buscar') . '
                        ' . btn('Limpar', QuemSou(), 'danger') . '
                    </div>
                </form>
            ');
        } else {
            $this->cardHeader($form);
        }

        if (COUNT($this->cardHeader) > 0) {
            $cardHeader = "
                <div class='card-header d-flex flex-row align-items-center justify-content-between'>
                    " . implode('', $this->cardHeader) . "
                </div>
                <div class='ps-2'>
                    " . $resultCountHtml . '
                </div>
            ';
        }

        if (COUNT($this->column) > 0) {

            // Chama a função createFirstRow() caso a primeira linha ainda não tenha sido criada
            if (!$this->firstRowCreated) {
                $this->createFirstRow();
            }

            $trHtml = '';

            // Monta as células (td) com o conteúdo e colspan definidos
            foreach ($this->column as $col) {
                // Se encontrarmos uma tag de fechamento de linha (</tr>), define que a próxima linha ainda não foi criada
                if (strpos($col, '</tr>') !== false) {
                    $this->firstRowCreated = false;
                }
                $trHtml .= $col;
            }

            $outHtml = "
                <div class='row' data-aos='fade-up' data-aos-duration='800'>
                    <div class='col-lg-12 mb-4'>
                        <div class='card pb-2'>
                            " . $cardHeader . "
                            <div class='responsiveTable'>
                                <table class='table align-items-center table-flush table-striped'>
                                    <thead class='thead-light'>
                                        <tr>
                                            " . implode('', $this->header) . "
                                        </tr>
                                    </thead>
                                    <tbody class='table-group-divider'>
                                        {$trHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            ";

            return $outHtml;
        }
    }

    private function createFirstRow()
    {
        $this->column[] = "<tr data-aos='fade-left' data-aos-delay='" . $this->trOffset . "'  data-aos-easing='linear' data-aos-offset='0' data-aos-duration='500'>";
        $this->trOffset += $this->rowDelay;
        $this->firstRowCreated = true;
    }
}
