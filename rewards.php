<?php

include "curl.php";

$addr 		= $_GET['address'];
$url 		= "http://95.216.150.177:3001/insight-api-komodo/addr/" .$addr ."/utxo?noCache=1";
$timeout 	= 10;
$uxtos		= json_decode(UrlGetContentsCurl($url, $timeout, true));


$rewards = 0;
$balance = 0;

foreach ($uxtos as $uxto) {

$amount = $uxto->amount;
$height = $uxto->height;
$trx 	= $uxto->txid;


$balance = $balance + $amount;

if ($amount >=10){
$url = "http://95.216.150.177:3001/insight-api-komodo/tx/" .$trx;

$tx = json_decode(UrlGetContentsCurl($url, $timeout, true));
$tiptime = $tx->time;
$locktime = $tx->locktime;


$minutes = ((time()+60 - $tiptime)/60);

if ($amount >10.01 && $locktime >0){
	if ($minutes > 365*24*60 && $height <1000000){
 		$minutes = 365*24*60;
	}

	if ($minutes > 31*24*60 && $height >=1000000){
	 	$minutes = 31*24*60;
	}

	$minutes -=59;
	if ($minutes >0){
		$reward = ($amount / 10512000) * $minutes;

		$rewards = $rewards + $reward;
	}
}

}
}
$response['address'] = $addr;
$response['balance'] = $balance;
$response['rewards'] = round($rewards,8);
$response['totalbalance'] = round($balance+ $rewards,8);

echo json_encode($response);

/*
-- Calculation by James
uint64_t komodo_interest(uint64_t nValue,uint32_t ntiptime,uint32_t tiptime)
{
int32_t minutes; uint64_t interest = 0;
if ( (minutes= (tiptime - ntiptime) / 60) >= 60 )
{
if ( minutes > 365 * 24 * 60 )
minutes = 365 * 24 * 60;
minutes -= 59;
interest = ((nValue / 10512000) * minutes);
}
return(interest);
}

You would want to call it from an if statement like:

if (  ntiptime >= tiptime_THRESHOLD && tiptime != 0 && ntiptime < tiptime && nValue >= 10*COIN )
    interestsum += komodo_interestnew(nValue,ntiptime,tiptime);

*/
?>