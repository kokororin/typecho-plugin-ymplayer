var typechoYmplayer = {
    idAllCount: [],
    finish: 0,
    count: 0,
    init: function() {
        var that = this;
        var ymplayerAll = document.getElementsByTagName("ymplayer");
        var ymplayerCount = ymplayerAll.length;
        if (ymplayerCount.length == 0) return;

        for (var i = 0; i < ymplayerCount; i++) {
            console.log(i);
            var ymplayer = ymplayerAll[i];
            var field = ymplayer.attributes.field.value;
            var idAll = that.toJSON(field);
            if (typeof idAll['list'] == 'undefined' || typeof idAll['list'] == 'null') {
                that.idAllCount[i] = idAll.length;
                for (var u = 0, v = idAll.length; u < v; u++) {
                    that.request({
                        url: ymplayer_params.url + '?type=song&id=' + idAll[u],
                        success: function(data) {
                            that.createSong(data, ymplayer);
                        },
                        error: function() {
                            that.remove(ymplayer);
                        }

                    });
                }
            } else {
                console.log("233");
                that.request({
                    url: ymplayer_params.url + '?type=playlist&id=' + idAll['list'],
                    success: function(data) {
                        that.idAllCount[i - 1] = data.length;
                        for (var j = 0, playlistCount = data.length; j < playlistCount; j++) {
                            that.createSong(data[j], ymplayer);
                        }
                    },
                    error: function() {
                        that.remove(ymplayer);
                    }
                });
            }

        }
        var timer = setInterval(function() {
            for (var k = 0; k < ymplayerCount; k++) {
                console.log(that.idAllCount[i]);
                if (ymplayer.getElementsByTagName('song').length == that.idAllCount[k]) {
                    ymplayerAll[k].style.display = '';
                } else {
                    return;
                }
            }
            setTimeout(function() {
                Ymplayer.Init();
            }, 2000);
            clearInterval(timer);
        }, 1500);

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
        //console.log(song);
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
    typechoYmplayer.init();
})();
