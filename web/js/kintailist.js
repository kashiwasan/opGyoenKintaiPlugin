$(function(){
  $('#kintai-list').hide();
  $('#kintai-loader').show();
  var baseUrl = $('#kintai-list').attr('data-baseurl');
  $.getJSON( baseUrl + '/kintai/ajaxList', { 'memberId': kintaiMemberId }, renderJSON);
});
function renderJSON(json) {
  $('#kintai-list-template').tmpl(json.data).appendTo('#kintai-list');
  $('#kintai-loader').hide();
  $('#kintai-list').show();
  $("a[rel^='prettyPopinEdit']").prettyPopin({
    width: 500,
    height: false,
    callback: function(){
      $('#kintai-list').empty();
      $('#kintai-list').append("<tr><td width='17%'>Y / m / d</td><td width='25%'>勤怠</td><td width='33%'>作業コメント</td><td width='25%'></td></tr>");
      $('#kintai-list').hide();
      $('#kintai-loader').show();
      var baseUrl = $('#kintai-list').attr('data-baseurl');
      $.getJSON( baseUrl + '/kintai/ajaxList', { 'memberId': kintaiMemberId }, renderJSON);
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
      $.getJSON( baseUrl + '/kintai/ajaxList', { 'memberId': kintaiMemberId }, renderJSON);
    }
  }); 
};

