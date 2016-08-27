if (typeof YMPlayer != 'undefined') {
  (function() {
    var dom = document.getElementById('ymplayer-placeholder');
    if (dom) {
      var opt = dom.getAttribute('data-opt');
      try {
        opt = Base64.decode(opt);
        opt = JSON.parse(opt);
      } catch ( e ) {
        throw new Error('YMPlayer Plugin initialized failed !');
      }
      YMPlayer.render(opt, dom);
    }
  })(YMPlayer);
}