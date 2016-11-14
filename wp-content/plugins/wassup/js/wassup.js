/**
 * wassup.js - Javascripts for wassup
 *  version 0.1 2015-10-05
 */
//for refresh countdown in wassup-detail and wassup-online
var _countDowncontainer="0";
var _currentSeconds=0;
var tickerID = 0;
function ActivateCountDown(strContainerID, initialValue){_countDowncontainer=document.getElementById(strContainerID);SetCountdownText(initialValue);tickerID=window.setInterval("CountDownTick()",1000);}
function CountDownTick(){if(_currentSeconds >0){SetCountdownText(_currentSeconds-1);}else{clearInterval(tickerID);tickerID=0;}}
function SetCountdownText(seconds){_currentSeconds=seconds;var strText=AddZero(seconds);if(_countDowncontainer){_countDowncontainer.innerHTML=strText;}}
function AddZero(num){return((num >= "0")&&(num < 10))?"0"+num:num+"";}
//common javascripts
function wScrollTop(){document.body.scrollTop=document.documentElement.scrollTop=0;}
