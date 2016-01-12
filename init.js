(function() {

    var ymplayer = document.getElementsByTagName("ymplayer")[0];

    request({
        url: ymplayer_params.url + '?type=song&id=' + ymplayer_params.song_id,
        success: function(data) {
            var json = eval("(" + data + ")");
            ymplayer.attributes.src.value = json.src;
            ymplayer.attributes.cover.value = json.cover;
            ymplayer.attributes.song.value = json.title;
            ymplayer.attributes.artist.value = json.artist;
            request({
                url: ymplayer_params.url + '?type=lyric&id=' + ymplayer_params.song_id,
                success: function(data) {
                    var json = eval("(" + data + ")");
                    ymplayer.getElementsByTagName('lrc')[0].innerHTML = json.lyric;
                    YmplayerIniter();
                }
            });
            
        },
        error: function() {
            ymplayer.parentNode.removeChild(ymplayer);
        }

    });


    function request(o) {
        if (!o.url)
            return;
        var xmlhttp = new XMLHttpRequest() || new ActiveXObject('Microsoft.XMLHTTP');
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && !!o.success)
                o.success(xmlhttp.responseText);
            if (xmlhttp.readyState == 4 && xmlhttp.status != 200 && !!o.error)
                o.error();
        };
        xmlhttp.open('GET', o.url, o.async || true);
        xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xmlhttp.setRequestHeader('If-Modified-Since', '0');
        xmlhttp.send(null);
    }
})();
