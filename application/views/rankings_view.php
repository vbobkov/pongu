<div id="pongu_rankings">
	<div class="headers">
		<div class="header">Player</div>
		<div class="header">Rating</div>
		<div class="header">Change</div>
	</div>
	<div class="players">
	</div>
</div>

<script type="text/javascript">
	var ELO_FIDE_DIFF_TABLE = [392,375,358,345,327,316,303,291,279,268,257,246,236,226,216,207,198,189,180,171,163,154,146,138,130,122,114,107,99,92,84,77,69,62,54,47,40,33,26,18,11,4,0];

	function calcRatingChange(r1, r2, score) {
		var k = 40;
		var diff = 0;
		var change = 0;
		var diff = r2 - r1;

		if(Math.abs(diff) > 400) {
			if(r1 > r2) {
				diff = 400;
			}
			else {
				diff = -400;
			}
		}
		change = (score - expected(diff)) * k;
		return change;
	}

	function expected(diff) {
		var exp = 92;
		var i = 0;
		while(i<43 && Math.abs(diff) < ELO_FIDE_DIFF_TABLE[i]) {
			i++;
			exp--;
		}
		if(diff > 0)
			return exp / 100.0;
		else
			return 1 - (exp / 100.0);
	}

	function getRankingChanges(rankings) {
		var r_by_rating = rankings.slice();
		var r_by_realtime_rating = rankings.slice();
		r_by_rating.sort(sortByRating);
		r_by_realtime_rating.sort(sortByRealtimeRating);
	}

	function sortByRating(a, b) {
		return parseInt(a['rating']) - parseInt(b['rating']);
	}

	function sortByRealtimeRating(a, b) {
		return parseInt(a['realtime_rating']) - parseInt(b['realtime_rating']);
	}



	$(document).ready(function() {
		var rankings;
		var PLAYER_RANKING_HTML = '\
			<div class="player">\
				<div class="col info">\
					<input type="hidden" name="player_id">\
					<div class="realname">\
						<span class="fname"></span>\
						<span class="lname"></span>\
					</div>\
					<div class="nickname_container">\
						<div class="nickname_img"></div>\
						<div class="nickname"></div>\
					</div>\
				</div>\
				<div class="col rating"></div>\
				<div class="col change"></div>\
			</div>';

		$.post('/rankings/getRankings', function(response) {
			/*
			rankings = {};
			$.each(JSON.parse(response), function(idx, player) {
				rankings[player['id']] = player;
			});
			*/
			rankings = JSON.parse(response);
			rankings_container = $('#pongu_rankings .players');
			console.log(rankings);

			var player_div;
			$.each(rankings, function(idx, player) {
				player_div = $(PLAYER_RANKING_HTML);
				player_div.find('[name="player_id"]').prop('value', player['id']);
				player_div.find('.nickname').html(player['nickname']);
				if(player['nickname'] != '') {
					player_div.find('.nickname_img').css('background', 'url(/assets/img/player_emblems/[32]' + encodeURIComponent(player['nickname']) + '.png)');
				}
				player_div.find('.fname').html(player['fname']);
				player_div.find('.lname').html(player['lname']);
				player_div.find('.rating').html(player['rating']);
				player_div.find('.realtime_rating').html(player['realtime_rating']);
				rankings_container.append(player_div);
			});
		});
	});
</script>