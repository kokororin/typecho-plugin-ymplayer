var typechoYmplayer = {
    ymplayerCount: 0,
    ymplayerAll: null,
    domInit: function() {
        var that = this;
        that.ymplayerAll = document.getElementsByTagName("ymplayer");
        that.ymplayerCount = that.ymplayerAll.length;
        if (that.ymplayerCount.length == 0) return;

        for (var i = 0; i < that.ymplayerCount; i++) {
            var ymplayer = that.ymplayerAll[i];
            var field = ymplayer.attributes.field.value;
            var idAll = that.toJSON(field);
            if (typeof idAll['list'] == 'undefined' || typeof idAll['list'] == 'null') {
                for (var u = 0, v = idAll.length; u < v; u++) {
                    that.request({
                        url: ymplayer_params.url + '?type=song&id=' + idAll[u],
                        success: function(data) {
                            that.createSong(data, ymplayer);
                            that.playerInit();
                        },
                        error: function() {
                            that.remove(ymplayer);
                        }
                    });

                }
            } else {
                that.request({
                    url: ymplayer_params.url + '?type=playlist&id=' + idAll['list'],
                    success: function(data) {
                        for (var j = 0, playlistCount = data.length; j < playlistCount; j++) {
                            that.createSong(data[j], ymplayer);
                            if (j == (playlistCount - 1)) {
                                that.playerInit();
                            }
                        }
                    },
                    error: function() {
                        that.remove(ymplayer);
                    }
                });
            }
        }



    },
    createSong: function(data, element) {
        var that = this;
        var song = document.createElement('song');
        song.attributes.setNamedItem(document.createAttribute('src'));
        song.attributes.setNamedItem(document.createAttribute('song'));
        song.attributes.setNamedItem(document.createAttribute('artist'));
        song.attributes.setNamedItem(document.createAttribute('cover'));
        song.attributes.setNamedItem(document.createAttribute('songid'));
        song.attributes.src.value = data.src;
        song.attributes.song.value = data.title;
        song.attributes.artist.value = data.artist;
        song.attributes.cover.value = data.cover;
        song.attributes.songid.value = data.song_id;
        that.request({
            url: ymplayer_params.url + '?type=lyric&id=' + data.song_id,
            success: function(data) {
                if (data.lyric != 'not found') {
                    song.innerHTML = data.lyric;
                    element.appendChild(song);
                }
            },
            error: function() {
                song.innerHTML = '';
            }
        });
    },
    playerInit: function() {
        var that = this;
        setTimeout(function() {
            for (var k = 0; k < that.ymplayerCount; k++) {
                that.ymplayerAll[k].style.display = '';
            }
            Ymplayer.Init();
        }, 500);
    },
    remove: function(element) {
        element.parentNode.removeChild(element);
    },
    request: function(o) {
        var that = this;
        if (!o.url)
            return;
        var xmlhttp = new XMLHttpRequest() || new ActiveXObject('Microsoft.XMLHTTP');
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && !!o.success) {
                o.success(that.toJSON(xmlhttp.responseText));
            }

            if (xmlhttp.readyState == 4 && xmlhttp.status != 200 && !!o.error)
                o.error();
        };
        xmlhttp.open('GET', o.url, true);
        xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xmlhttp.setRequestHeader('If-Modified-Since', '0');
        xmlhttp.send(null);
    },
    toJSON: function(str) {
        try {
            return eval("(" + str + ")");
        } catch (e) {
            return str;
        }
    }
};

!(function() {
    typechoYmplayer.domInit();
})();
