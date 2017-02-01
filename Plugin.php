<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Easy to insert YMPlayer into your article
 *
 * @package ymplayer
 * @author kokororin
 * @version 0.7
 * @link https://kotori.love/
 * @fe kirainmoe
 * @fe-github https://github.com/kirainmoe/ymplayer
 * @fe-homepage https://www.imim.pw/
 * @fe-license GPL v2
 */
class ymplayer_Plugin implements Typecho_Plugin_Interface
{

    const PLUGIN_VERSION = '0.7';
    const PLUGIN_SUPPORT_LINK = 'https://github.com/kokororin/typecho-plugin-ymplayer/blob/master/README.md#安装方法';
    const PLUGIN_SUBMODULE_DOWNLOAD = 'https://raw.githubusercontent.com/kirainmoe/ymplayer/master/dist/assets/ymplayer.js';

    public static function activate()
    {
        if (substr(trim(dirname(__FILE__), '/'), -8) != 'ymplayer') {
            throw new Typecho_Plugin_Exception('插件目录名必须为ymplayer');
        }
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array(__CLASS__, 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array(__CLASS__, 'parse');
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'insertStyle');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'insertScript');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array(__CLASS__, 'button');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array(__CLASS__, 'button');

        return '启用成功，请根据需要设置插件_ (:з」∠) _';
    }

    public static function deactivate()
    {}

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $element = new Typecho_Widget_Helper_Form_Element_Textarea(
            'custom', null, '', '自定义样式', '请直接输入 CSS 代码，不要带&lt;style&gt;标签');
        $form->addInput($element);

        if (!file_exists(dirname(__FILE__) . '/submodules/ymplayer/dist/assets/ymplayer.js')) {
            echo '<h3>检测到 YMPlayer submodule 未被正确安装 _(:з」∠)_。';
            if (is_writeable(dirname(__FILE__))) {
                try {
                    /* Create asset file directory */
                    mkdir(dirname(__FILE__) . '/submodules/ymplayer/dist', 755);
                    mkdir(dirname(__FILE__) . '/submodules/ymplayer/dist/assets', 755);

                    /* Fetch latest source from GitHub */
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, self::PLUGIN_SUBMODULE_DOWNLOAD);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $res = curl_exec($ch);

                    /* Write source file */
                    $src = fopen(dirname(__FILE__) . '/submodules/ymplayer/dist/assets/ymplayer.js', 'w+');
                    fwrite($src, $res);
                    curl_close($ch);

                    echo '插件已尝试自动安装。请刷新本页面，如果这条提示消失，则说明 submodule 安装成功。';
                    echo '否则，请前往 <a href="' . self::PLUGIN_SUPPORT_LINK . '">GitHub</a> 阅读教程手动安装。</h3>';
                } catch (Exception $e) {
                    echo '插件已尝试自动安装，但由于未知原因并没有成功。';
                    echo '请前往 <a href="' . self::PLUGIN_SUPPORT_LINK . '">GitHub</a> 阅读教程手动安装。</h3>';
                }
            } else {
                /* have no writing permission */
                echo '当前目录不支持写入文件，插件无法自动安装子模块。';
                echo '请前往 <a href="' . self::PLUGIN_SUPPORT_LINK . '">GitHub</a> 阅读教程手动安装。</h3>';
            } // is_writeable()
        } // file_exist()
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function insertStyle()
    {
        $custom = Typecho_Widget::widget('Widget_Options')->Plugin('ymplayer')->custom;
        if ($custom != '') {
            echo "<style id=\"ymplayer_custom_style\">\n" . $custom . "\n</style>";
        }
    }

    public static function insertScript()
    {
        echo "\n<script src=\"" . Helper::options()->pluginUrl . "/ymplayer/submodules/ymplayer/dist/assets/ymplayer.js?v=" . self::getPlayerVer() . "\"></script>";
        echo "\n<script src=\"" . Helper::options()->pluginUrl . "/ymplayer/static/plugin.js?v=" . self::PLUGIN_VERSION . "\"></script>";
    }

    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        if ($widget instanceof Widget_Abstract_Contents) {
            $text = preg_replace_callback('/{(YMPlayer)}(.*?){\/YMPlayer}/i', function ($matches) {
                $data = $matches[2];
                $html = '<div id="ymplayer-placeholder" data-opt="' . htmlspecialchars($data) . '"></div>';
                return $html;
            }, $text);
        }
        return $text;
    }

    public static function button()
    {
        ?>
        <div id="ymplayer-dialog-bg" class="wmd-prompt-background" style="display: none;position: absolute; top: 0px; z-index: 1000; opacity: 0.5; height: 1269px; left: 0px; width: 100%;"></div>
        <div id="ymplayer-dialog" style="display: none;margin-top: -200px;" class="wmd-prompt-dialog">
          <div>
            <p><b>render a YMPlayer component</b></p>
            <p>Please follow the instruction on <a href="https://github.com/kirainmoe/ymplayer">https://github.com/kirainmoe/ymplayer</a></p>
            <p>If you want to add more songs, please repeat the above steps.</p>
          </div>
          <form id="ymplayer-dialog-form">
            <input type="text" required data-name="title" placeholder="title" />
            <input type="text" required data-name="artist" placeholder="artist" />
            <input type="text" required data-name="cover" placeholder="cover" />
            <input type="text" required data-name="src" placeholder="src" />
            <input type="text" data-name="lyric" placeholder="lyric" />
            <input type="text" data-name="translation" placeholder="translation" />
            <button data-action="submit" type="button" class="btn btn-s primary">确定</button>
            <button data-action="cancel" type="button" class="btn btn-s">取消</button>
          </form>
        </div>
        <style>#wmd-music-button span{background:url(<?php echo file_get_contents(__DIR__ . '/static/icon.base64'); ?>);font-size:large;text-align:center;color: #999999;font-family:serif;}</style>
        <script type="text/javascript" src="<?php echo Helper::options()->pluginUrl . '/ymplayer/static/admin.js'; ?>"></script>

<?php
}

    private static function getPlayerVer()
    {
        $package = file_get_contents(__DIR__ . '/submodules/ymplayer/package.json');
        $package = json_decode($package);
        return (!empty($package->version) ? $package->version : '4.0');
    }

}
