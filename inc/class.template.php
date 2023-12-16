<?php

/**
 * @package template
 * @subpackage class_base
 * @author pedro-azeredo <pedro.azeredo93@gmail.com>
 */

class Template
{

    private $getCss;
    private $getJs;
    private $breadcrumb;
    private $setTittle;
    private $content;
    private $template;

    public function __construct($tittle = NULL)
    {
        $this->setTemplate();
        $this->setTittle = $tittle;
    }

    /**
     * setTemplate
     * Setar template default a ser utilizado na execução da classe
     *
     * @param string $template
     */
    public function setTemplate(string $template = "index")
    {

        if (file_exists(__DIR__ . '/templates/template.' . $template . '.php')) {
            $this->template = __DIR__ . '/templates/template.' . $template . '.php';
            return;
        }
        $this->template = __DIR__ . '/templates/template.index.php';
    }

    private function getTemplate()
    {
        if (file_exists($this->template)) {
            $outString = implode("", file($this->template));
            return $outString;
        }
        $outString = implode("", file($this->template));
        return $outString;
    }

    /**
     * addCss
     * Responsável por registrar CSSs no template
     *
     * @param mixed $cssString
     */
    public function addCss(string $cssString)
    {
        if (file_exists($cssString)) {
            $this->getCss .= "<link href='" . $cssString . "' rel='stylesheet' type='text/css'>";
        } else {
            $this->getCss .= "<s>" . $cssString . "</style>";
        }
    }

    /**
     * addJs
     * Responsável por registrar JSs no template
     *
     * @param mixed $cssString
     */
    public function addJs(string $jsString)
    {
        if (file_exists($jsString)) {
            $this->getJs .= "<link href='" . $jsString . "' rel='stylesheet' type='text/css'>";
        } else {
            $this->getJs .= "<script>" . $jsString . "</script>";
        }
    }

    /**
     * addBreadcrumb
     * Cria código para definir breadcrump (caminho) da tela
     *
     * @param string $local
     * @param string $active
     * @param string $url
     */
    public function addBreadcrumb(string $local, string $url = NULL)
    {
        if ($url != NULL) {
            $content = "<li class='breadcrumb-item'><a href='./" . $url . "'>" . $local . "</a></li>";
        } else {
            $content = "<li class='breadcrumb-item active'>" . $local . "</li>";
        }

        $this->breadcrumb .= $content;
    }

    /**
     * getBreadcrumb
     * Responsável por criar o HTML de breadcrumpb (caminho)
     *
     * @return string
     */
    private function getBreadcrumb(): string
    {

        // incluindo no breadcrumpb automaticamente a página atual
        // $this->addBreadcrumb($this->setTittle);

        $outHtml  = "<ol class='breadcrumb'>";
        $outHtml .= $this->breadcrumb;
        $outHtml .= "</ol>";
        return $outHtml;
    }

    /**
     * addContent
     * Inclusão de conteúdo no template utilizado
     *
     * @param string $content
     * @param string $card
     */
    public function addContent(string $content, string $card = NULL)
    {
        if ($card == true) {
            $this->content .= "
                <div class='card' data-aos='fade-up' data-aos-duration='800'>
                    <div class='card-body'>
                        <div class='card-text'>
                            " . $content . "
                        </div>
                    </div>
                </div>
                ";
        } else {
            $this->content .= $content;
        }
    }

    /**
     * writeHtml
     * Imprimir HTML montado após conclusão das definições do template
     *
     * @return void
     */
    public function writeHtml()
    {
        $outHtml = $this->__replace($this->getTemplate(),   "[%description%]",       META["description"]);
        $outHtml = $this->__replace($outHtml,               "[%author%]",            META["author"]);
        $outHtml = $this->__replace($outHtml,               "[%icon%]",              META["icon"]);
        $outHtml = $this->__replace($outHtml,               "[%title%]",             TITTLE);
        $outHtml = $this->__replace($outHtml,               "[%title_page%]",        $this->setTittle);
        $outHtml = $this->__replace($outHtml,               "[%css%]",               $this->getCss);
        $outHtml = $this->__replace($outHtml,               "[%breadcrumb%]",        $this->getBreadcrumb());
        $outHtml = $this->__replace($outHtml,               "[%include_sidebar%]",   $this->getSidebar());
        $outHtml = $this->__replace($outHtml,               "[%include_topbar%]",    $this->getTopbar());
        $outHtml = $this->__replace($outHtml,               "[%include_content%]",   $this->content);
        $outHtml = $this->__replace($outHtml,               "[%js%]",                $this->getJs);
        $outHtml = $this->__replace($outHtml,               "[%sweetalert%]",        getAlert());
        echo $outHtml;
    }

    /**
     * getSidebar
     * Responsável pela montagem do sideBar (menu)
     *
     * @return string
     */
    private function getSidebar(): string
    {

        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USER, DB_PASSWORD) or print($conn->errorInfo());

        $sql = "
        SELECT
            id,
            adm_menu_id,
            icone,
            nome,
            link,
            status,
            (
                SELECT
                    COUNT(*)
                FROM
                    adm_menu AS aa
                WHERE
                    aa.status = 1
                    AND aa.adm_menu_id = adm_menu.id
            ) AS subItens
        FROM
            adm_menu
        WHERE
            status = 1
            AND adm_menu_id IS NULL
            AND (
                (
                    SELECT
                        COUNT(*)
                    FROM
                        adm_menu AS aa
                    WHERE
                        aa.adm_menu_id = adm_menu.id
                        AND status = 1
                        AND id IN (
                            SELECT
                                adm_menu_id
                            FROM
                                cargos_has_permissoes
                            WHERE
                                cad_cargo_id = (
                                    SELECT
                                        id
                                    FROM
                                        cad_cargos
                                    WHERE
                                        id = '" . getUserInfo("cad_cargo_id") . "'
                                )
                        )
                ) > 0
                OR id IN (
                    SELECT
                        adm_menu_id
                    FROM
                        cargos_has_permissoes
                    WHERE
                        cad_cargo_id = (
                            SELECT
                                id
                            FROM
                                cad_cargos
                            WHERE
                                id = '" . getUserInfo("cad_cargo_id") . "'
                        )
                )
            )
        ORDER BY
            nome ASC
        ";

        $menu = array();

        if ($conn->query($sql)) {
            foreach ($conn->query($sql) as $row) {
                $active = '';
                if (QuemSou() == $row['link'] || $row['link'] == str_replace("Cad", "List", QuemSou())) {
                    $active = 'bg-light active';
                }

                if ($row["subItens"] == 0) {
                    $menu[] = "
                            <li class='nav-item " . $active . "'>
                                <a class='d-flex align-items-center' href='" . $row["link"] . "' title='" . $row["nome"] . "'>
                                    <i class='ph ph-" . $row["icone"] . "'></i>
                                    <span class='menu-item text-truncate' data-i18n='" . $row["nome"] . "'>" . $row["nome"] . "</span>
                                </a>
                            </li>
                        ";
                } else {
                    $sqlSubItens = "
                        SELECT
                            id,
                            nome,
                            link
                        FROM
                            adm_menu
                        WHERE
                            status = 1
                            AND adm_menu_id = {$row['id']}
                        ORDER BY 
                            nome ASC
                    ";

                    $menuSubItens = array();
                    if ($conn->query($sqlSubItens)) {
                        foreach ($conn->query($sqlSubItens) as $rowSubItens) {
                            $menuSubItens[] = "
                                <li>
                                    <a class='d-flex align-items-center' href='" . $rowSubItens["link"] . "' title='" . $rowSubItens["nome"] . "'>
                                        <i class='ph ph-circle'></i>
                                        <span class='menu-item text-truncate' data-i18n='" . $rowSubItens["nome"] . "'>" . $rowSubItens["nome"] . "</span>
                                    </a>
                                </li>";
                        }
                    }

                    $menu[] = "
                            <li class='nav-item'>
                                <a class='d-flex align-items-center' href='#' title='" . $row["nome"] . "'>
                                    <i data-feather='" . $row["icone"] . "'></i>
                                    <span class='menu-title text-truncate' data-i18n='" . $row["nome"] . "'>" . $row["nome"] . "</span>
                                </a>
                                    
                                <ul class='menu-content'>
                                    " . implode('', $menuSubItens) . "
                                </ul>
                            </li>
                        ";
                }
            }
        }

        $outHtml = "
                <div class='main-menu-content'>
                    <ul class='navigation navigation-main' id='main-menu-navigation' data-menu='menu-navigation'>
                        <li class='nav-item'>
                            <a class='d-flex align-items-center' href='index.php'>
                                <i class='ph ph-house'></i>
                                <span class='menu-item text-truncate' data-i18n='Dashboard'>Dashboard</span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='d-flex align-items-center' href='" . btnLink(["perfil.php", ["cad_usuario_id" => getUserInfo("id")]]) . "'>
                                <i class='ph ph-identification-badge'></i>
                                <span class='menu-item text-truncate' data-i18n='Dashboard'>Meu Perfil</span>
                            </a>
                        </li>
                        " . implode("", $menu) . "
                        <li class='nav-item'>
                            <a class='d-flex align-items-center' href='loginSair.php'>
                                <i class='ph ph-sign-out'></i>
                                <span class='menu-item text-truncate' data-i18n='Sair'>Sair</span>
                            </a>
                        </li>
                    </ul>
                </div>
            ";
        return $outHtml;
    }

    /**
     * gettopbar
     * Responsável por criar o topbar
     *
     * @return void
     */
    private function getTopbar()
    {

        $imagem = '<svg viewBox="0 0 212 212" width="100%"><path fill="#DFE5E7" class="background" d="M106.251,0.5C164.653,0.5,212,47.846,212,106.25S164.653,212,106.25,212C47.846,212,0.5,164.654,0.5,106.25 S47.846,0.5,106.251,0.5z"></path><g><path fill="#FFFFFF" class="primary" d="M173.561,171.615c-0.601-0.915-1.287-1.907-2.065-2.955c-0.777-1.049-1.645-2.155-2.608-3.299 c-0.964-1.144-2.024-2.326-3.184-3.527c-1.741-1.802-3.71-3.646-5.924-5.47c-2.952-2.431-6.339-4.824-10.204-7.026 c-1.877-1.07-3.873-2.092-5.98-3.055c-0.062-0.028-0.118-0.059-0.18-0.087c-9.792-4.44-22.106-7.529-37.416-7.529 s-27.624,3.089-37.416,7.529c-0.338,0.153-0.653,0.318-0.985,0.474c-1.431,0.674-2.806,1.376-4.128,2.101 c-0.716,0.393-1.417,0.792-2.101,1.197c-3.421,2.027-6.475,4.191-9.15,6.395c-2.213,1.823-4.182,3.668-5.924,5.47 c-1.161,1.201-2.22,2.384-3.184,3.527c-0.964,1.144-1.832,2.25-2.609,3.299c-0.778,1.049-1.464,2.04-2.065,2.955 c-0.557,0.848-1.033,1.622-1.447,2.324c-0.033,0.056-0.073,0.119-0.104,0.174c-0.435,0.744-0.79,1.392-1.07,1.926 c-0.559,1.068-0.818,1.678-0.818,1.678v0.398c18.285,17.927,43.322,28.985,70.945,28.985c27.678,0,52.761-11.103,71.055-29.095 v-0.289c0,0-0.619-1.45-1.992-3.778C174.594,173.238,174.117,172.463,173.561,171.615z"></path><path fill="#FFFFFF" class="primary" d="M106.002,125.5c2.645,0,5.212-0.253,7.68-0.737c1.234-0.242,2.443-0.542,3.624-0.896 c1.772-0.532,3.482-1.188,5.12-1.958c2.184-1.027,4.242-2.258,6.15-3.67c2.863-2.119,5.39-4.646,7.509-7.509 c0.706-0.954,1.367-1.945,1.98-2.971c0.919-1.539,1.729-3.155,2.422-4.84c0.462-1.123,0.872-2.277,1.226-3.458 c0.177-0.591,0.341-1.188,0.49-1.792c0.299-1.208,0.542-2.443,0.725-3.701c0.275-1.887,0.417-3.827,0.417-5.811 c0-1.984-0.142-3.925-0.417-5.811c-0.184-1.258-0.426-2.493-0.725-3.701c-0.15-0.604-0.313-1.202-0.49-1.793 c-0.354-1.181-0.764-2.335-1.226-3.458c-0.693-1.685-1.504-3.301-2.422-4.84c-0.613-1.026-1.274-2.017-1.98-2.971 c-2.119-2.863-4.646-5.39-7.509-7.509c-1.909-1.412-3.966-2.643-6.15-3.67c-1.638-0.77-3.348-1.426-5.12-1.958 c-1.181-0.355-2.39-0.655-3.624-0.896c-2.468-0.484-5.035-0.737-7.68-0.737c-21.162,0-37.345,16.183-37.345,37.345 C68.657,109.317,84.84,125.5,106.002,125.5z"></path></g></svg>';
        if (!empty(getUserInfo("imagem"))) {
            $imagem = '<img src="data:image/png;base64,' . getUserInfo("imagem") . '" class="ratio ratio-1x1 rounded-circle avatar">';
        }

        $outHtml = "
            <nav class='header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow' data-aos='fade-down' data-aos-anchor-placement='top-bottom' data-aos-duration='800'>
                <div class='navbar-container d-flex content justify-content-between'>
                    <ul class='nav navbar-nav align-items-center'>
                        <li class='nav-item dropdown dropdown-user'>
                            <a class='nav-link' href='" . btnLink(["perfil.php", ["cad_usuario_id" => getUserInfo("id")]]) . "'>
                                <div class='user-nav d-flex align-items-center gap-1'>
                                " . $imagem . "
                                    <span class='user-name fw-bolder'>" . getDbValue("SELECT nome FROM cad_usuarios WHERE uniqid = '" . getSession("SYSGER") . "'") . "</span>
                                </div>
                            </a>
                            <div class='dropdown-menu dropdown-menu-end' aria-labelledby='dropdown-user'>
                                <!-- <a class='dropdown-item' href='#'><i class='me-50' data-feather='user'></i> Perfil</a> -->
                                <!-- <div class='dropdown-divider'></div> -->
                                <a class='dropdown-item' href='loginSair.php'><i class='me-50 ph ph-sign-out'></i> Sair</a>
                            </div>
                        </li>
                    </ul>
                    <div class='bookmark-wrapper d-flex align-items-center'>
                        <ul class='nav navbar-nav d-xl-none'>
                            <li class='nav-item'><a class='nav-link menu-toggle' href='#'><i class='ficon' data-feather='menu'></i></a></li>
                        </ul>
                        <ul class='nav navbar-nav'>
                            <li class='nav-item'><a class='nav-link' href='loginSair.php'><i class='me-50 ph ph-sign-out'></i></a></li>
                        </ul>
                        <ul class='nav navbar-nav'>
                            <!--<li class='nav-item d-none d-lg-block'><a class='nav-link nav-link-style'><i class='ficon' data-feather='moon'></i></a></li>-->
                        </ul>
                    </div>
                </div>
            </nav>
            ";

        return $outHtml;
    }

    /**
     * __replace
     * Responsável por fazer o str_replace no arquivo template
     *
     * @param [type] $string
     * @param [type] $search
     * @param [type] $replace
     * @return string
     */
    private function __replace($string, $search, $replace): string
    {
        $replaced = "";
        if (!is_array($replace)) {
            $replace = array($replace);
        }
        $replaced = str_replace($search, implode("\r\n", $replace), $string);
        return $replaced;
    }
}
