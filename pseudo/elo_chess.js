function CalculateElo() 

{

var wins = document.score.wins.value * 1;

var draws = document.score.draws.value * 1;

var losses = document.score.losses.value * 1;

var score = wins + draws/2;

var total = wins + draws + losses;

var percentage = (score /  total);

var EloDifference = -400 * Math.log(1 / percentage - 1) / Math.LN10;

var Sign = "";

if (EloDifference > 0) { Sign="+"; }



document.points.points.value = score;

document.points.totalgames.value = total;

document.percent.winning.value = Math.round(percentage*10000)/100;

document.Elo.difference.value = Sign+Math.round(EloDifference);

}



function CalculateEloFromScore() 

{

var score = document.points.points.value;

var total = document.points.totalgames.value;

var percentage = (score /  total);

var EloDifference = -400 * Math.log(1 / percentage - 1) / Math.LN10;

var Sign = "";

if (EloDifference > 0) { Sign="+"; }



document.percent.winning.value = Math.round(percentage*10000)/100;

document.Elo.difference.value = Sign+Math.round(EloDifference);

}



function CalculateEloFromPercent() 

{

var percentage = document.percent.winning.value / 100;

var EloDifference = -400 * Math.log(1 / percentage - 1) / Math.LN10;



var Sign = "";

if (EloDifference > 0) { Sign="+"; }

document.Elo.difference.value = Sign+Math.round(EloDifference);

}



function CalculateRatingChange()

{

var Elo1 = document.rating.elo1.value * 1;

var Elo2 = document.rating.elo2.value * 1;

var K = document.rating.K.value * 1;

var EloDifference = Elo2 - Elo1;

var percentage = 1 / ( 1 + Math.pow( 10, EloDifference / 400 ) );

var win = Math.round( K * ( 1 - percentage ) );

var draw = Math.round( K * ( .5 - percentage ) );

if (win > 0 ) win = "+" + win;

if (draw > 0 ) draw = "+" + draw;

document.ratingchange.win.value = win;

document.ratingchange.draw.value = draw;

document.ratingchange.loss.value = Math.round( K * ( 0 - percentage ) );

document.ratingchange.percent.value =  Math.round( percentage * 100 ) + "%";

}