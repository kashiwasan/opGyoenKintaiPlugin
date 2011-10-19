$(function(){
  $("a[rel^='prettyPopin']").prettyPopin({
    width: 720,
    height: 450,
    callback: function(){
      location.href= "./kintai";
    }
  });
});
