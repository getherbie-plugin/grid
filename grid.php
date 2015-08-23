<?php

use Herbie\DI;
use Herbie\Hook;

class GridPlugin
{

    private static $templates = [];

    public static function install()
    {
        self::$templates = DI::get('Config')->get('plugins.config.grid.templates', []);
        Hook::attach('renderContent', ['GridPlugin', 'renderContent']);
    }

    public static function renderContent($content)
    {
        $replaced = preg_replace_callback(
            '/-{2}\s+grid\s+(.+?)-{2}(.*?)-{2}\s+grid\s+-{2}/msi',
            ['GridPlugin', 'replace'],
            $content
        );
        if (!is_null($replaced)) {
            return $replaced;
        }
        return $content;
    }

    /**
     * @param array $matches
     * @return string
     */
    private static function replace($matches)
    {
        if (empty($matches) || (count($matches) <> 3)) {
            return null;
        }

        $key = trim($matches[1]);
        $content = $matches[2];

        // no template defined
        if (!array_key_exists($key, self::$templates)) {
            // return content as it is
            return $content;
        }
        $template = self::$templates[$key];

        // split cols
        $cols = preg_split('/--\r?\n/', $content);

        $html = '';

        // replace cols
        foreach ($cols as $i => $col) {
            if (isset($template['cols'][$i])) {
                $html .= str_replace(['{col}', '{index}'], [$col, $i+1], $template['cols'][$i]);
            } else {
                $html .= $col;
            }
        }

        // replace row
        if (array_key_exists('row', $template)) {
            return str_replace('{row}', $html, $template['row']);
        }

        // give it back
        return $html;
    }
}

GridPlugin::install();
