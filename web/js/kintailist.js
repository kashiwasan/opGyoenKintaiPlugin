$(function(){
  $('#kintai-list').hide();
  $('#kintai-more').hide();
  $('#kintai-loader').show();
  var baseUrl = $('#kintai-list').attr('data-baseurl');
  $.getJSON( baseUrl + 'kintai/ajaxList', { 'day': '0' }, renderJSON);
  $('#kintai-more-button').attr('data-moreday', '3');
  $('#kintai-more-button').click(function(){
    $('#kintai-loader').show();
    $('#kintai-more').hide();
    var moreDay = $(this).attr('data-moreday');
    $.getJSON( baseUrl + 'kintai/ajaxList', { 'day': moreDay,  }, renderJSON);
    moreDay = moreDay + 3;
    $('#kintai-more-button').attr('data-moreday', moreDay);
  });
});
function renderJSON(json) {
  $('#kintai-list-template').tmpl(json.data).appendTo('#kintai-list');
  $('#kintai-loader').hide();
  $('#kintai-more').show();
  $('#kintai-list').show();
  $('#kintai-more').show();
  $("a[rel^='prettyPopinEdit']").prettyPopin({
    width: 500,
    height: false,
    callback: function(){
      $('#kintai-list').empty();
      $('#kintai-list').append("<tr><td width='17%'>Y / m / d</td><td width='25%'>勤怠</td><td width='33%'>作業コメント</td><td width='25%'></td></tr>");
      $('#kintai-list').hide();
      $('#kintai-loader').show();
      var baseUrl = $('#kintai-list').attr('data-baseurl');
      $.getJSON( baseUrl + 'kintai/ajaxList', { 'memberId': kintaiMemberId }, renderJSON);
    }
  }); 
  $("a[rel^='prettyPopinRegist']").prettyPopin({
    width: 720,
    height: 550,
    callback: function(){
      $('#kintai-list').empty();
      $('#kintai-list').append("<tr><td width='17%'>Y / m / d</td><td width='25%'>勤怠</td><td width='33%'>作業コメント</td><td width='25%'></td></tr>");
      $('#kintai-list').hide();
      $('#kintai-loader').show();
      var baseUrl = $('#kintai-list').attr('data-baseurl');
      $.getJSON( baseUrl + 'kintai/ajaxList', { 'memberId': kintaiMemberId }, renderJSON);
    }
  }); 
};

