# Typecho Plugin ymplayer
A typecho plugin for ymplayer https://github.com/kirainmoe/ymplayer


## 介绍
1. 通过简短的代码在文章或页面中插入漂亮的Html5播放器
2. 调用网易云音乐本地API，只输入歌曲id即可
3. 与ymplayer保持同步更新

## 安装方法

* Download ZIP, 解压，将插件目录命名为ymplayer在后台启用即可
* 请确保cache目录可写，插件将缓存的API信息保存到此目录中，禁用插件后将清空缓存
* 如果你的主题**不**带有font-awesome字体，请前往设置页面开启载入font-awesome字体库

## 使用方法

在文章编辑页面中，在要插入播放器的部分点击YM工具栏按钮或输入以下代码：

```
[ymplayer]style=kotori id=123456[/ymplayer]
```

其中：

"style"的值可为honoka,kotori等或空，默认为kotori，具体请参考ymplayer的文档
"id"的值为网易云音乐的歌曲id

## LICENSE

制作过程中参考了[kirainmoe](https://github.com/kirainmoe)和[journey-ad](https://github.com/journey-ad)二位的代码，特此感谢

GPL v2
