!(function() {
    typechoYmplayer();
})();

function typechoYmplayer() {
    var ymplayerAll = document.getElementsByTagName("ymplayer");
    if (ymplayerAll.length == 0) return;

    for (var i = 0, l = ymplayerAll.length; i < l; i++) {
        var ymplayer = ymplayerAll[i];
        var field = ymplayer.attributes.field.value;
        var idAll = toJSON(field);
        for (var u = 0, v = idAll.length; u < v; u++) {
            request({
                url: ymplayer_params.url + '?type=song&id=' + idAll[u],
                success: function(data) {
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

                    request({
                        url: ymplayer_params.url + '?type=lyric&id=' + song.attributes.songid.value,
                        success: function(data) {
                            if (data.lyric != 'not found') {
                                song.innerHTML = data.lyric;
                                ymplayer.appendChild(song);
                            }
                            console.log(u);
                        }
                    });

                },
                error: function() {
                    remove(ymplayer);
                }

            });
        }
    }

    for (var i = 0, l = ymplayerAll.length; i < l; i++) {
        ymplayerAll[i].style.display = '';
    }
    YmplayerIniter();

    function remove(element) {
        element.parentNode.removeChild(element);
    }


    function request(o) {
        if (!o.url)
            return;
        var xmlhttp = new XMLHttpRequest() || new ActiveXObject('Microsoft.XMLHTTP');
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && !!o.success) {
                if (xmlhttp.responseText.match("^\{(.+:.+,*){1,}\}$")) {
                    o.success(toJSON(xmlhttp.responseText));
                } else {
                    o.success(xmlhttp.responseText);
                }
            }

            if (xmlhttp.readyState == 4 && xmlhttp.status != 200 && !!o.error)
                o.error();
        };
        xmlhttp.open('GET', o.url, false);
        xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xmlhttp.setRequestHeader('If-Modified-Since', '0');
        xmlhttp.send(null);
    }

    function toJSON(str) {
        return eval("(" + str + ")");
    }
}
