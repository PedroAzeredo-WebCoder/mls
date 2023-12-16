<?php

    /**
     * @package tabs
     * @subpackage class_base
     * @author pedro-azeredo <pedro.azeredo93@gmail.com>
     */

    class Tabs {

        private $tabs = array();
        private $content = array();

        /**
         *  addTab
         * Responsável por incluir novas abas
         *
         * @param string $text
         * @param string $content
         * @param bool $status
         * @return void
         */
        public function addTab(string $text, string $content, bool $status = false) {
            $getE = getParam("e", true);
            $fTab = $getE["fTab"];
            
            $active = "";
            if($fTab || $status) {
                $active = "active";
            }

            $slug = slug($text);

            $this->tabs[] = "
                <li class='nav-item'>
                    <a class='nav-link $active' id='".$slug."-tab' data-bs-toggle='tab' href='#".$slug."' aria-controls='".$slug."' role='tab' aria-selected='false'>".$text."</a>
                </li>
            ";

            $this->content[] = "
                <div class='tab-pane $active' id='".$slug."' aria-labelledby='".$slug."-tab' role='tabpanel'>
                    ".$content."
                </div>
            ";

        }

        /**
         * writeHtml
         * Imprimir HTML montado após conclusão das definições do template
         *
         * @return string
         */
        public function writeHtml() {
            $outHtml = "
                <div class='row tabs'>
                    <div class='col-lg-12 mb-4'>
                        <ul class='nav nav-tabs' role='tablist'>
                            ".implode("", $this->tabs)."
                        </ul>
                        <div class='tab-content'>
                            ".implode("", $this->content)."
                        </div>
                    </div>
                </div>
            ";
            return $outHtml;
        }
    }
