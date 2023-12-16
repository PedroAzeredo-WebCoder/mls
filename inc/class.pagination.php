<?php

class Pagination
{
    private $countData;
    private $addParams;

    public function setSQL($sql)
    {
        $this->countData = getDbValue($sql);
    }

    public function startLimit()
    {
        $getE = getParam("e", true);
        $paginaAtual = $getE["fPage"];
        $startLimit = ($paginaAtual - 1) * PAGINATION;
        if ($paginaAtual == 1 || $paginaAtual == "") {
            $startLimit = 0;
        }

        return $startLimit;
    }

    public function addParams($params)
    {
        $this->addParams = "&" . $params;
    }

    public function writeHtml()
    {
        $qtdPaginas = ceil($this->countData / PAGINATION);
        $getE = getParam("e", true);
        $paginaAtual = $getE["fPage"];
        $paginaAnterior = $paginaAtual - 1;

        $startDisabled = "";
        if ($paginaAtual == 1 || !$paginaAtual) {
            $startDisabled = "disabled";
            $paginaAnterior = 1;
            $paginaAtual = 1;
        }

        $paginaPosterior = $paginaAtual + 1;

        $endDisabled = "";
        if ($paginaAtual == $qtdPaginas) {
            $endDisabled = "disabled";
        }

        $paginas = array();

        $paginas[] = "
            <li class='page-item $startDisabled'>
                <a class='page-link' href='" . QuemSou() . "?e=" . strrev(base64_encode(strrev("fPage=" . $paginaAnterior . $this->addParams))) . "'>&laquo;</a>
            </li>
        ";

        for ($x = 1; $x <= $qtdPaginas; $x++) {
            $pageActive = "";

            if ($paginaAtual == $x) {
                $pageActive = "active";
            }

            $startIndex = ($x - 1) * PAGINATION + 1;
            $endIndex = min($x * PAGINATION, $this->countData);

            $paginas[] = "
                <li class='page-item $pageActive'>
                    <a class='page-link' href='" . QuemSou() . "?e=" . strrev(base64_encode(strrev("fPage=" . $x . $this->addParams))) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='$startIndex - $endIndex'>$x</a>
                </li>
            ";
        }

        if ($paginaPosterior == $x) {
            $paginaPosterior = $x;
        }

        $paginas[] = "
            <li class='page-item $endDisabled'>
                <a class='page-link' href='" . QuemSou() . "?e=" . strrev(base64_encode(strrev("fPage=" . $paginaPosterior . $this->addParams))) . "'>&raquo;</a>
            </li>
        ";

        $out = "
            <nav aria-label='Page navigation example'>
                <ul class='pagination pagination-sm justify-content-center'>
                    " . implode("", $paginas) . "
                </ul>
            </nav>
        ";

        if ($this->countData > 0) {
            return $out;
        } else {
            return false;
        }
    }
}
