$(function(){
  $("a[rel^='prettyPopin']").prettyPopin({
    width: 720,
    height: 550,
    callback: function(){
      location.href= "./kintai";
    }
  });
});
