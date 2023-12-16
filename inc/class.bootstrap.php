<?php

/**
 * badge
 *
 * @param string $string
 * @param [type] $type
 * @return string
 */
function badge(string $string, string $type = NULL): string
{
    $typeOut = "primary";
    if ($type) {
        $typeOut = $type;
    }
    return "<span class='badge bg-" . $typeOut . "'>" . $string . "</span>";
}

/**
 * btn
 *
 * @param string $string
 * @param $href
 * @param string $type
 * @return string
 */
function btn(string $string, $href, string $type = NULL, $css = NULL, $target = NULL): string
{
    $typeOut = "primary";
    if ($type) {
        $typeOut = $type;
    }

    $hrefOut = "";
    if (is_array($href)) {
        $e = array();
        foreach ($href[1] as $key => $value) {
            $e[] = $key . "=" . $value;
        }
        $e = implode("&", $e);
        $hrefOut = $href[0] . "?e=" . strrev(base64_encode(strrev($e)));
    } else {
        $hrefOut = $href;
    }

    return "<a href='" . $hrefOut . "' class='btn btn-" . $typeOut . " " . $css . "' target='" . $target . "'>" . $string . "</a>";
}

function btnLink($href): string
{
    $hrefOut = "";
    if (is_array($href)) {
        $e = array();
        foreach ($href[1] as $key => $value) {
            $e[] = $key . "=" . $value;
        }
        $e = implode("&", $e);
        $hrefOut = $href[0] . "?e=" . strrev(base64_encode(strrev($e)));
    } else {
        $hrefOut = $href;
    }

    return $hrefOut;
}
