$(function(){
  $("a[rel^='prettyPopin']").prettyPopin({
    width: 720,
    height: 500,
    callback: function(){
      location.href= "./kintai";
    }
  });
});
