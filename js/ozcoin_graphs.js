// Run the script on DOM ready:
$(function(){
	$('#blocks_over_week').visualize({type: 'area', width: '318px'});
	$('#user_hashrate_lasthour').visualize({type: 'area', width: '889px', height: '250px'});
	$('#user_hashrate_last24').visualize({type: 'area', width: '889px', height: '250px'});
	$('#user_hashrate_lastmonth').visualize({type: 'area', width: '889px', height: '250px'});
});