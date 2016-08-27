# Typecho Plugin YMPlayer
A typecho plugin for YMPlayer https://github.com/kirainmoe/ymplayer

![](screenshot.png)


## 介绍
1. 通过简短的代码在文章或页面中插入漂亮的Html5播放器
2. 与ymplayer保持同步更新
3. 不再提供直接插入网易云外链的方式

## 安装方法
### 常规方法
Download ZIP, 解压，将插件目录命名为ymplayer在后台启用即可
### 使用git安装
```bash
#安装插件
$ cd /path/to/your/typecho/plugin/
$ git clone https://github.com/kokororin/typecho-plugin-ymplayer ymplayer
$ cd ymplayer
$ chown -R www:www * 
#更新插件 
$ cd /path/to/your/typecho/plugin/ymplayer
$ git pull
$ chown -R www:www *
```


## 使用方法

在文章编辑页面中，在要插入播放器的部分点击YM工具栏按钮或输入以下代码：


## LICENSE

制作过程中参考了[kirainmoe](https://github.com/kirainmoe)和[journey-ad](https://github.com/journey-ad)二位的代码，特此感谢

GPL v2
