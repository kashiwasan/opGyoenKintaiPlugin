$(function(){
  $("a[rel^='prettyPopin']").prettyPopin({
    width: 500,
    height: 350,
    callback: function(){
      location.href= "./kintai";
    }
  });
});
