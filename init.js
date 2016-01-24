var typechoYmplayer = {
    idAllCount: [],
    init: function() {
        var that = this;
        var ymplayerAll = document.getElementsByTagName("ymplayer");
        var ymplayerCount = ymplayerAll.length;
        if (ymplayerCount.length == 0) return;

        for (var i = 0; i < ymplayerCount; i++) {
            var ymplayer = ymplayerAll[i];
            var field = ymplayer.attributes.field.value;
            var idAll = that.toJSON(field);
            that.idAllCount[i] = idAll.length;
            if (typeof idAll['list'] == 'undefined' || typeof idAll['list'] == 'null') {
                for (var u = 0, v = idAll.length; u < v; u++) {
                    that.request({
                        url: ymplayer_params.url + '?type=song&id=' + idAll[u],
                        success: function(data) {
                            var song = that.createSong(data);

                            that.request({
                                url: ymplayer_params.url + '?type=lyric&id=' + song.attributes.songid.value,
                                success: function(data) {
                                    if (data.lyric != 'not found') {
                                        song.innerHTML = data.lyric;
                                        ymplayer.appendChild(song);
                                    }
                                }
                            });

                        },
                        error: function() {
                            that.remove(ymplayer);
                        }

                    });
                }
            } else {
                console.log("233");
                request({
                    url: ymplayer_params.url + '?type=playlist&playlist=' + idAll['list'],
                    success: function(data) {
                        ymplayer.innerHTML = data;
                    },
                    error: function() {
                        that.remove(ymplayer);
                    }
                });
            }

        }
        var timer = setInterval(function() {
            for (var i = 0; i < ymplayerCount; i++) {
                if (ymplayer.getElementsByTagName('song').length == that.idAllCount[i]) {
                    ymplayerAll[i].style.display = '';
                } else {
                    return;
                }
            }
            YmplayerIniter();
            clearInterval(timer);
        }, 1000);

    },
    createSong: function(data) {
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
        return song;
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
                if (xmlhttp.responseText.match("^\{(.+:.+,*){1,}\}$")) {
                    o.success(that.toJSON(xmlhttp.responseText));
                } else {
                    o.success(xmlhttp.responseText);
                }
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
        return eval("(" + str + ")");
    }
};

!(function() {
    typechoYmplayer.init();
})();
