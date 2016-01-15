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
 * @version 0.2
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
        Typecho_Plugin::factory('admin/write-post.php')->bottom        = array(__CLASS__, 'button');
        Typecho_Plugin::factory('admin/write-page.php')->bottom        = array(__CLASS__, 'button');
        Helper::addRoute('ymplayer_ajax', '/ymplayer.json', 'ymplayer_Action', 'ajax');
        return '启用成功，请根据需要设置插件_ (:з」∠) _';
    }

    public static function deactivate()
    {
        Helper::removeRoute('ymplayer_ajax');
        $files = glob(dirname(__FILE__) . '/cache/*');
        foreach ($files as $file)
        {
            if (is_file($file))
            {
                unlink($file);
            }
        }
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $element = new Typecho_Widget_Helper_Form_Element_Radio(
            'font_awesome', array(
                'no'  => '不引入',
                'yes' => '引入'), 'no', '是否引入font-awesome', '如果你使用的主题已经引入，请选否。');
        $form->addInput($element);
        $element = new Typecho_Widget_Helper_Form_Element_Radio(
            'force', array(
                'no'  => '不启用',
                'yes' => '启用'), 'no', '是否开启强制兼容模式', '如果样式有冲突，请启用');
        $form->addInput($element);
        $element = new Typecho_Widget_Helper_Form_Element_Textarea(
            'custom', null, '', '自定义样式', '请直接输入css代码，请不要带&lt;style&gt;标签');
        $form->addInput($element);
        $element = new Typecho_Widget_Helper_Form_Element_Submit();
        $element->value('检查更新');
        $element->setAttribute('style', '');
        $element->input->setAttribute('type', 'button');
        $element->input->setAttribute('style', 'bottom:37px;');
        $element->input->setAttribute('class', 'btn btn-s btn-operate ymplayer-update');
        $element->input->setAttribute('onclick', 'ymplayer_check();');
        $form->addItem($element);
        $script = "<script type=\"text/javascript\">
var ymplayer_check = function() {
    var btn = jQuery('.ymplayer-update');
    jQuery.ajax({
        url: '" . Helper::options()->index . "/ymplayer.json?type=checkUpdate',
        type: 'get',
        beforeSend: function() {
            btn.html('请求中哦=A=');
            btn.attr('disabled', true).fadeTo('slow', 0.5);
        },
        success: function(data) {
            if (data.status == true) {
                btn.html(data.text);
                btn.removeAttr('disabled').fadeTo('', 1);
                btn.attr('onclick', 'ymplayer_download();');
            } else {
                btn.html(data.text);
            }
        },
        error: function() {
            btn.html('网络错误，请重试=A=');
        }
    });
};
var ymplayer_download = function() {
    btn = jQuery('.ymplayer-update');
    jQuery.ajax({
        url: '" . Helper::options()->index . "/ymplayer.json?type=downloadUpdate',
        type: 'get',
        beforeSend: function() {
            btn.html('请求中哦=A=');
            btn.attr('disabled', true).fadeTo('slow', 0.5);
        },
        success: function() {
            if (data == 'success') {
                btn.html('更新成功');
            } else {
                btn.html('更新失败，请重试');
            }
        },
        error: function() {
            btn.html('网络错误，请重试');
        }

    });

};
</script>";
        echo $script;
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function insertStyle()
    {
        if (self::$flag)
        {
            $font_awesome = Typecho_Widget::widget('Widget_Options')->Plugin('ymplayer')->font_awesome;
            $force        = Typecho_Widget::widget('Widget_Options')->Plugin('ymplayer')->force;
            $custom       = Typecho_Widget::widget('Widget_Options')->Plugin('ymplayer')->custom;
            if ($font_awesome == 'yes')
            {
                echo "<link href=\"" . Helper::options()->pluginUrl . "/ymplayer/dist/font-awesome.css\" rel=\"stylesheet\">\n";
            }
            if ($force == 'yes')
            {
                echo "<link href=\"" . Helper::options()->pluginUrl . "/ymplayer/force.css\" rel=\"stylesheet\">\n";
            }
            if ($custom != '')
            {
                echo "<style id=\"ymplayer_custom_style\">\n" . $custom . "\n</style>";
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
            $text = preg_replace_callback('/\[(ymplayer)](.*?)\[\/\\1]/si', function ($matches)
            {
                self::$flag = true;
                $all        = $matches[2];
                $all        = preg_replace('/^\s*$/', ' ', $all);
                $attrs      = explode(' ', $all);
                $data       = array();
                foreach ($attrs as $attr)
                {
                    $pair                  = explode('=', $attr);
                    @$data[trim($pair[0])] = trim($pair[1]);
                }
                if (!isset($data['style']))
                {
                    $data['style'] = '';
                }
                self::$song_id = $data['id'];
                $html          = '<ymplayer class="' . $data['style'] . '" src="" name="' . $data['id'] . '" loop="no" cover="" song="" artist="">';
                $html .= '</ymplayer>';
                return $html;
            }, $text);

        }
        return $text;
    }

    public static function button()
    {
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                if($('#wmd-button-row').length){
                    $('#wmd-button-row').append('<li class="wmd-button" id="wmd-music-button" title="音乐 - Alt+M"><span style="background: none;font-size: large;text-align: center;color: #999999;font-family: serif;">YM</span></li>');
                    $('#wmd-music-button').click(function(){
                        var rs = "[ymplayer]style=kotori id=123456[/ymplayer]";
                        grin(rs);
                    });
                }

                function grin(tag) {
                    var myField;
                    if (document.getElementById('text') && document.getElementById('text').type == 'textarea') {
                        myField = document.getElementById('text');
                    } else {
                        return false;
                    }
                    if (document.selection) {
                        myField.focus();
                        sel = document.selection.createRange();
                        sel.text = tag;
                        myField.focus();
                    }
                    else if (myField.selectionStart || myField.selectionStart == '0') {
                        var startPos = myField.selectionStart;
                        var endPos = myField.selectionEnd;
                        var cursorPos = startPos;
                        myField.value = myField.value.substring(0, startPos)
                        + tag
                        + myField.value.substring(endPos, myField.value.length);
                        cursorPos += tag.length;
                        myField.focus();
                        myField.selectionStart = cursorPos;
                        myField.selectionEnd = cursorPos;
                    } else {
                        myField.value += tag;
                        myField.focus();
                    }
                }

                $('body').on('keydown',function(a){
                    if(a.altKey && a.keyCode == "77"){
                        $('#wmd-music-button').click();
                    }
                });
            });
</script>
<?php
}

}
