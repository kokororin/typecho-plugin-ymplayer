<?php
if (!defined('__TYPECHO_ROOT_DIR__'))
{
    exit;
}

/**
 * ymplayer with local netease api
 *
 * @package ymplayer
 * @author kokororin
 * @version 0.0
 * @link https://kotori.love/
 * @fe kirainmoe
 * @fe-github https://github.com/kirainmoe/ymplayer
 * @fe-homepage https://www.imim.pw/
 * @fe-license GPL v2
 */
class ymplayer_Plugin implements Typecho_Plugin_Interface
{
    protected static $flag = false;
    protected static $song_id;

    public static function activate()
    {
        if (substr(trim(dirname(__FILE__), '/'), -8) != 'ymplayer')
        {
            throw new Typecho_Plugin_Exception('插件目录名必须为ymplayer');
        }
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array(__CLASS__, 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array(__CLASS__, 'parse');
        Typecho_Plugin::factory('Widget_Archive')->header              = array(__CLASS__, 'insertStyle');
        Typecho_Plugin::factory('Widget_Archive')->footer              = array(__CLASS__, 'insertScript');
        Helper::addRoute('ymplayer_ajax', '/ymplayer.json', 'ymplayer_Action', 'ajax');
    }

    public static function deactivate()
    {
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $font_awesome = new Typecho_Widget_Helper_Form_Element_Radio(
            'font_awesome', array(
                'no'  => '不引入',
                'yes' => '引入'), 'no', '是否引入font-awesome', '如果你使用的主题已经引入，请选否。');
        $form->addInput($font_awesome);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function insertStyle()
    {
        if (self::$flag)
        {
            $font_awesome_option = Typecho_Widget::widget('Widget_Options')->Plugin('ymplayer')->font_awesome;
            if ($font_awesome_option == 'no')
            {
                echo "<link href=\"" . Helper::options()->pluginUrl . "/ymplayer/dist/font-awesome.css\" rel=\"stylesheet\">\n";
            }
            echo "<link href=\"" . Helper::options()->pluginUrl . "/ymplayer/dist/ymplayer.css\" rel=\"stylesheet\">\n";
        }
    }

    public static function insertScript()
    {
        if (self::$flag)
        {
            echo "\n<script type=\"text/javascript\">
var ymplayer_params = " . json_encode(array(
                'url'     => Helper::options()->index . '/ymplayer.json',
                'song_id' => self::$song_id,
            )) . ";
</script>";
            echo "\n<script src=\"" . Helper::options()->pluginUrl . "/ymplayer/dist/ymplayer.min.js\"></script>";
            echo "\n<script src=\"" . Helper::options()->pluginUrl . "/ymplayer/init.js\"></script>";
        }
    }

    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;
        if ($widget instanceof Widget_Abstract_Contents)
        {
            if (isset($widget->fields->ymplayer_songid))
            {
                self::$song_id = $widget->fields->ymplayer_songid;
                self::$flag    = true;
                $text .= '<ymplayer class="kotori" src="{src}" name="with_lyric" loop="no" cover="{cover}" song="{song}" artist="{artist}">';
                $text .= '<lrc>{lrc}</lrc>';
                $text .= '</ymplayer>';
            }
        }
        return $text;
    }

}
