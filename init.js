(function() {

    var ymplayer = document.getElementsByTagName("ymplayer")[0];
    var id = ymplayer.attributes.name.value;

    request({
        url: ymplayer_params.url + '?type=song&id=' + id,
        success: function(data) {
            ymplayer.attributes.src.value = data.src;
            ymplayer.attributes.cover.value = data.cover;
            ymplayer.attributes.song.value = data.title;
            ymplayer.attributes.artist.value = data.artist;
            request({
                url: ymplayer_params.url + '?type=lyric&id=' + id,
                success: function(data) {
                    if (data.lyric != 'not found') {
                        var lrc = document.createElement('lrc');
                        lrc.innerHTML = data.lyric;
                        ymplayer.appendChild(lrc);
                    }
                    if (typeof YmplayerIniter == 'function')
                        YmplayerIniter();
                    else
                        remove(ymplayer);
                }
            });

        },
        error: function() {
            remove(ymplayer);
        }

    });

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
                    o.success(eval("(" + xmlhttp.responseText + ")"));
                } else {
                    o.success(xmlhttp.responseText);
                }
            }

            if (xmlhttp.readyState == 4 && xmlhttp.status != 200 && !!o.error)
                o.error();
        };
        xmlhttp.open('GET', o.url, o.async || true);
        xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xmlhttp.setRequestHeader('If-Modified-Since', '0');
        xmlhttp.send(null);
    }
})();
